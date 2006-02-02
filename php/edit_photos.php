<?php
/* This file is part of Zoph.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
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
    $clean_vars = clean_request_vars($request_vars);
    $num_photos =
        get_photos($clean_vars, $offset, $cells, $thumbnails, $user);

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
          <h1><?php echo $title_bar ?></h1>
      <div class="main">
      <form action="edit_photos.php" method="POST">
<?php
    if ($num_thumbnails <= 0) {
?>
          <div class="error">
       <?php echo translate("No photos were found matching your search criteria.") ?>
          </div>
<?php
    }
    else {
        // create once
        $category_pulldown = create_pulldown("_category", "", get_categories_select_array($user));
        $album_pulldown = create_pulldown("_album", "", get_albums_select_array($user));
        $place_pulldown = get_places_select_array();
	
        // used to create hidden fields for recreating the results query
        $queryIgnoreArray[] = '_action';
        $queryIgnoreArray[] = '_overwrite';
        $queryIgnoreArray[] = '__location_id__all';
        $queryIgnoreArray[] = '__photographer_id__all';
        $queryIgnoreArray[] = '_rating__all';
        $queryIgnoreArray[] = '_album__all';
        $queryIgnoreArray[] = '_category__all';
?>
            <input type="submit" value="<? echo translate("update", 0) ?>">
            <fieldset class="editphotos">
                <legend><?php echo translate("All photos")?></legend>
                  <input type="hidden" name="_action" value="update">
                  <label for="overwrite"><?php echo translate("overwrite values below", 0) ?></label>
                  <?php echo create_pulldown("_overwrite", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?><br>
                  <label for="location_id__all"><?php echo translate("location") ?></label>
                  <?php echo create_smart_pulldown("__location_id__all", null, $place_pulldown) ?><br>
                  <label for="photographer_id__all"><?php echo translate("photographer") ?></label>
            <?php echo create_smart_pulldown("__photographer_id__all", null, get_people_select_array()) ?><br>
                  <label for="rating__all"><?php echo translate("rating") ?></label>
                  <?php echo create_rating_pulldown(null, "_rating__all") ?><br>
                  <label for="album"><?php echo translate("albums") ?></label>
                  <?php echo str_replace("_album", "_album__all", $album_pulldown) ?><br>
                  <label for="category"><?php echo translate("categories") ?></label>
                  <?php echo str_replace("_category", "_category__all", $category_pulldown) ?><br>
                </fieldset>
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
                if ($deg && $deg != 0) {
                    $photo->lookup($user);
                    $photo->rotate($deg);
                }
            }
            else if ($can_edit && $action == 'delete') {
                $photo->delete();
                continue;
            }

            if ($action == "update") {
                $request_vars["_action"]="display";
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
            $queryIgnoreArray[] = "_remove_person__$photo_id";
            for ($p = 0; $p < $user->prefs->get("people_slots"); $p++ ) {
                $queryIgnoreArray[] = "_position_" . $p . "__" . $photo_id;
                $queryIgnoreArray[] = "_person_" . $p . "__" . $photo_id;
            }
            $queryIgnoreArray[] = "_deg__$photo_id";
            $queryIgnoreArray[] = "_action__$photo_id";
?>
            <fieldset class="editphotos">
                <legend><?php echo $photo->get('name')?></legend>
                <div class="editchoice">
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
                </div>
                <div class="thumbnail">
                  <?php echo $photo->get_thumbnail_link("photo.php?photo_id=$photo_id") . "\n" ?><br>
<?php
                if ($can_edit && ALLOW_ROTATIONS &&
                    ($user->is_admin() || $permissions->get("writable"))) {
?>
                  <?php echo translate("rotate", 0) ?>
                  <select name="_deg__<?php echo $photo_id ?>">
                    <option>&nbsp;</option>
                    <option>90</option>
                    <option>180</option>
                    <option>270</option>
                  </select>
<?php
                }
?>
                </div>
<?php
            if (!$can_edit) {
?>
                <?php echo $photo->get('name') ?>:<br>
                <?php echo translate("Insufficient permissions to edit photo", 0)?>.
<?php
            }
            else {
?>
                    <fieldset class="editphotos-fields">
                      <label for="location_id__<?php echo $photo_id ?>"><?php echo translate("location") ?></label>
            <?php echo create_smart_pulldown("__location_id__$photo_id", $photo->get("location_id"), $place_pulldown) ?><br>
                      <label for="photographer_id__<?php echo $photo_id?>"><?php echo translate("photographer") ?></label>
            <?php echo create_smart_pulldown("__photographer_id__$photo_id", $photo->get("photographer_id"), get_people_select_array()) ?><br>
                      <label for="rating__<?php echo $photo_id?>"><?php echo translate("rating") ?></label>
<?php
    $rating = $photo->get('rating');
    if (ALLOW_RATINGS) {
        $rating = $photo->get_rating($user->get('user_id'));
    }
?>
                        <?php echo create_rating_pulldown($rating, "_rating__$photo_id") ?>
                    <br>
                      <label for="album__<?php echo $photo_id?>"><?php echo translate("albums") ?></label>
                      <fieldset class="checkboxlist">
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
                        <?php echo str_replace("album", "album__$photo_id", $album_pulldown) ?>
                      </fieldset>
                      <label for="category__<?php echo $photo_id?>"><?php echo translate("categories") ?></label>
                      <fieldset class="checkboxlist">
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
                        <?php echo str_replace("category", "category__$photo_id", $category_pulldown) ?>
                      </fieldset>
                      <label for="person_0__<?php echo $photo_id ?>"><?php echo translate("people") ?></label>
                      <fieldset class="checkboxlist">
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
                for ($p = 0; $p < $user->prefs->get("people_slots"); $p++ ) {
?>
                       <?php echo create_smart_pulldown("_person_" . $p . "__" . $photo_id, "", get_people_select_array()) ?>
                       <?php echo translate("position") ?>:
                       <?php echo create_text_input("_position_" . $p ."__" . $photo_id, $next_pos + $p, 2, 2) ?><br>
<?php
                }
?>
                      </fieldset>
                      <label for="title__<?php echo $photo_id?>"><?php echo translate("title") ?></label>
                      <?php echo create_text_input("__title__$photo_id", $photo->get("title"), 40, 64) ?>
                    <br>
                      <label for="description__<?php echo $photo_id?>"><?php echo translate("description") ?></label>
                        <textarea name="__description__<?php echo $photo_id ?>" id="description__<?php echo $photo_id?>" class="desc" cols="50" rows="3"><?php echo $photo->get("description") ?></textarea>
                     <br>
                  </fieldset>
                  </fieldset>
<?php
            }
        }
?>
<?php echo create_form($clean_vars, $queryIgnoreArray) ?>
                  <input type="submit" value="<? echo translate("update", 0) ?>">
                  </form>
<?php include "pager.inc.php" ?>
<?php
    } // if photos
?>
<br>
</div>

<?php require_once("footer.inc.php"); ?>
