<?php
    require_once("include.inc.php");

    $_cols = getvar("_cols");
    $_rows = getvar("_rows");
    $_off = getvar("_off");
    $_order = getvar("_order");
    $_dir = getvar("_dir");

    if (!$_cols) { $_cols = $DEFAULT_COLS; }
    if (!$_rows) { $_rows = $DEFAULT_ROWS; }
    if (!$_off)  { $_off = 0; }

    if (!$_order) { $_order = $DEFAULT_ORDER; }
    if (!$_dir)   { $_dir = $DEFAULT_DIRECTION; }

    $cells = $_cols * $_rows;
    $offset = $_off;

    $thumbnails;
    $num_photos =
        get_photos($request_vars, $offset, $cells, $thumbnails, $user);

    $num_thumbnails = sizeof($thumbnails);

    if  ($num_thumbnails) {
        $num_pages = ceil($num_photos / $cells);
        $page_num = floor($offset / $cells) + 1;

        $num = min($cells, $num_thumbnails);

        $title = sprintf(translate("Edit Photos (Page %s/%s)", 0), $page_num, $num_pages);
        $title_bar = sprintf(translate("edit photos %s to %s of %s"), ($offset + 1), ($offset + $num), $num_photos);
    }
    else {
        $title = translate("No Photos Found");
        $title_bar = translate("edit photos");
    }

    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar"
        <tr>
          <th><h1><?php echo $title_bar ?></h1></th>
          <td class="actionlink">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <form action="edit_photos.php" method="POST">
      <table class="main">
<?php
    if ($num_thumbnails <= 0) {
?>
        <tr>
          <td class="error">
       <?php echo translate("No photos were found matching your search criteria.") ?>
          </td>
        </tr>
<?php
    }
    else {
        // create once
        $category_pulldown = create_pulldown("_category", "", get_categories_select_array($user));
        $album_pulldown = create_pulldown("_album", "", get_albums_select_array($user));

        // used to create hidden fields for recreating the results query
        $queryIgnoreArray[] = '_action';
        $queryIgnoreArray[] = '_overwrite';
        $queryIgnoreArray[] = '__location_id__all';
        $queryIgnoreArray[] = '__photographer_id__all';
        $queryIgnoreArray[] = '_rating__all';
        $queryIgnoreArray[] = '_album__all';
        $queryIgnoreArray[] = '_category__all';
?>
        <tr>
          <td colspan="3">
            <table class="content">
              <tr>
                <td colspan="3">
                  <input type="hidden" name="_action" value="update">
                  <table class="content">
                    <tr>
                      <td class="fieldtitle"><?php echo translate("overwrite values below", 0) ?></td>
                      <td class="field">
                        <?php echo create_pulldown("_overwrite", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
                      </td>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("location") ?></td>
                      <td class="field">
            <?php echo create_smart_pulldown("__location_id__all", null, get_places_select_array()) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("photographer") ?></td>
                      <td class="field">
            <?php echo create_smart_pulldown("__photographer_id__all", null, get_people_select_array()) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("rating") ?></td>
                      <td class="field">
                        <?php echo create_rating_pulldown(null, "_rating__all") ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("albums") ?></td>
                      <td class="field">
                        <?php echo str_replace("_album", "_album__all", $album_pulldown) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("categories") ?></td>
                      <td class="field">
                        <?php echo str_replace("_category", "_category__all", $category_pulldown) ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td colspan="3">
                  <hr>
                </td>
              </tr>
<?php
        for ($i = 0; $i < $num_thumbnails; $i++) {
            $photo_id = $thumbnails[$i]->get('photo_id');

            $permissions = $user->get_permissions_for_photo($photo_id);
            if (!$user->is_admin() && !$permissions) {
                continue;
            }

            $can_edit = false;
            $can_edit = $user->is_admin() || $permissions->get("writable");

            $photo = new photo($photo_id);

            $action = $request_vars["_action__$photo_id"];
            if ($can_edit && $action == 'update') {

                $rating = null;
                if ($request_vars['_overwrite']) {
                    // set any specific fields
                    $photo->set_fields($request_vars, '__', "__$photo_id");
                    // set "apply to all" fields
                    $photo->set_fields($request_vars, '__', '__all');

                    $rating = $request_vars["_rating__$photo_id"];
                    if ($request_vars["_rating__all"]) {
                        $rating = $request_vars["_rating__all"];
                    }
                }
                else { // reverse order
                    $photo->set_fields($request_vars, '__', '__all');
                    $photo->set_fields($request_vars, '__', "__$photo_id");

                    $rating = $request_vars["_rating__all"];
                    if ($request_vars["_rating__$photo_id"]) {
                        $rating = $request_vars["_rating__$photo_id"];
                    }
                }

                if ($rating != null) {
                    if (ALLOW_RATINGS) { // multiple ratings
                        $photo->rate($user->get('user_id'), $rating);
                    }
                    else { // single rating
                        $photo->set('rating', $rating);
                    }
                }

                // this will update any specific albums, cats & people
                $photo->update($request_vars, "__$photo_id");

                // update "apply to all" albums, cats & people
                $photo->update_relations($request_vars, '__all');

                $deg = $request_vars["_deg__$photo_id"];
                if ($deg) {
                    $photo->lookup($user);
                    $photo->rotate($deg);
                }
            }
            else if ($can_edit && $action == 'delete') {
                $photo->delete();
                continue;
            }

            $photo->lookup($user);

            $queryIgnoreArray[] = "__photo_id__$photo_id";
            $queryIgnoreArray[] = "__location_id__$photo_id";
            $queryIgnoreArray[] = "__photographer_id__$photo_id";
            $queryIgnoreArray[] = "__title__$photo_id";
            $queryIgnoreArray[] = "__description__$photo_id";
            $queryIgnoreArray[] = "_rating__$photo_id";
            $queryIgnoreArray[] = "_album__$photo_id";
            $queryIgnoreArray[] = "_remove_album__$photo_id";
            $queryIgnoreArray[] = "_category__$photo_id";
            $queryIgnoreArray[] = "_remove_category__$photo_id";
            $queryIgnoreArray[] = "_person__$photo_id";
            $queryIgnoreArray[] = "_remove_person__$photo_id";
            $queryIgnoreArray[] = "_position__$photo_id";
            $queryIgnoreArray[] = "_deg__$photo_id";
            $queryIgnoreArray[] = "_action__$photo_id";
?>
              <tr>
                <td class="editchoice">
<?php
            if ($can_edit) {
?>
                  <input type="hidden" name="__photo_id__<?php echo $photo_id ?>" value="<?php echo $photo_id ?>">
                  <input type="radio" name="_action__<?php echo $photo_id ?>" value="update" checked><?php echo translate("edit", 0) ?><br>
                  <input type="radio" name="_action__<?php echo $photo_id ?>" value=""><?php echo translate("skip", 0) ?><br>
                  <input type="radio" name="_action__<?php echo $photo_id ?>" value="delete"><?php echo translate("delete", 0) ?>
<?php
            }
            else {
                echo "&nbsp;";
            }
?>
                </td>
                <td class="thumbnail">
                  <?php echo $photo->get_thumbnail_link("photo.php?photo_id=$photo_id") . "\n" ?><br>
<?php
                if ($can_edit && ALLOW_ROTATIONS &&
                    ($user->is_admin() || $permissions->get("writable"))) {
?>
                  <?php echo translate("rotate", 0) ?>
                  <select name="_deg__<?php echo $photo_id ?>">
                    <option></option>
                    <option>90</option>
                    <option>180</option>
                    <option>270</option>
                  </select>
<?php
                }
?>
                </td>
                <td>
<?php
            if (!$can_edit) {
?>
                <?php echo $photo->get('name') ?>:<br>
                <?php echo translate("Insufficient permissions to edit photo", 0)?>.
<?php
            }
            else {
?>
                  <table class="content">
                    <tr>
                      <td class="fieldtitle"><?php echo translate("location") ?></td>
                      <td class="field">
            <?php echo create_smart_pulldown("__location_id__$photo_id", $photo->get("location_id"), get_places_select_array()) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("photographer") ?></td>
                      <td class="field">
            <?php echo create_smart_pulldown("__photographer_id__$photo_id", $photo->get("photographer_id"), get_people_select_array()) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("rating") ?></td>
                      <td class="field">
<?php
    $rating = $photo->get('rating');
    if (ALLOW_RATINGS) {
        $rating = $photo->get_rating($user->get('user_id'));
    }
?>
                        <?php echo create_rating_pulldown($rating, "_rating__$photo_id") ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("albums") ?></td>
                      <td class="field">
<?php
                $albums = $photo->lookup_albums($user);
                if ($albums) {
                    $append = "";
                    foreach ($albums as $album) {
?>
                       <?php echo $append ?>
                       <input type="checkbox" name="_remove_album__<?php echo $photo_id ?>[]" value="<?php echo $album->get("album_id") ?>">
                       <?php echo $album->get_link() ?>
<?php
                        $append = "<br>";
                    }
                    echo "<br>\n";
                }
?>
                        <?php echo str_replace("_album", "_album__$photo_id", $album_pulldown) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("categories") ?></td>
                      <td class="field">
<?php
                $categories = $photo->lookup_categories($user);
                if ($categories) {
                    $append = "";
                    foreach ($categories as $category) {
?>
                       <?php echo $append ?>
                       <input type="checkbox" name="_remove_category__<?php echo $photo_id ?>[]" value="<?php echo $category->get("category_id") ?>">
                       <?php echo $category->get_link() ?>
<?php
                        $append = "<br>\n";
                    }
                    echo "<br>\n";
                }
?>
                        <?php echo str_replace("_category", "_category__$photo_id", $category_pulldown) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("people") ?></td>
                      <td class="field">
<?php
                $people = $photo->lookup_people();
                $next_pos = 1;
                if ($people) {
                    $append = "";
                    foreach ($people as $person) {
                        $next_pos++;
?>
                       <?php echo $append ?>
                       <input type="checkbox" name="_remove_person__<?php echo $photo_id ?>[]" value="<?php echo $person->get("person_id") ?>">
                       <?php echo $person->get_link() ?>
<?php
                        $append = "<br>\n";
                    }
                    echo "<br>\n";
                }
?>
                       <?php echo create_smart_pulldown("_person__$photo_id", "", get_people_select_array()) ?>
                       <?php echo translate("position") ?>:
                       <?php echo create_text_input("_position__$photo_id", $next_pos, 2, 2) ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("title") ?></td>
                      <td class="field"><?php echo create_text_input("__title__$photo_id", $photo->get("title"), 40, 64) ?></td>
                    </tr>
                    <tr>
                      <td class="fieldtitle"><?php echo translate("description") ?></td>
                      <td class="field">
                        <textarea name="__description__<?php echo $photo_id ?>" cols="50" rows="3"><?php echo $photo->get("description") ?></textarea>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td colspan="3">
                  <hr class="wide">
                </td>
              </tr>
<?php
            }
        }
?>
              <tr>
                <td colspan="3" class="center">
<?php echo create_form($request_vars, $queryIgnoreArray) ?>
                  <input type="submit" value="submit">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        </table>
                  </form>
<?php include "pager.inc.php" ?>
<?php
    } // if photos
?>
      </tr>
</table>

<?php require_once("footer.inc.php"); ?>
