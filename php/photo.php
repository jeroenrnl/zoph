<?php
    require_once("include.inc.php");

    $photo_id = getvar("photo_id");
    $_off = getvar("_off");

    /*
    Before deciding to include the Prev and Next links, it was as
    simple as this.  But now we go through get_photos().

    $photo = new photo($photo_id);
    */

    $qs = preg_replace('/_crumb=\d+&?/', '', $QUERY_STRING);
    $qs = preg_replace('/_action=\w+&?/', '', $qs);

    $encoded_qs = getvar("_qs");
    if (empty($encoded_qs)) {
        $encoded_qs = rawurlencode($qs);
    }

    if ($photo_id) { // would be passed for edit or delete
        $photo = new photo($photo_id);
    }
    else { // for display
        if (!$_off)  { $_off = 0; }
        $offset = $_off;

        $thumbnails;
        $num_photos = get_photos($request_vars, $offset, 1, $thumbnails, $user);

        $num_thumbnails = sizeof($thumbnails);

        if  ($num_thumbnails) {
            $photo = $thumbnails[0];
            $photo_id = $photo->get("photo_id");

            if ($offset > 0) {
                $newoffset = $offset - 1;
                $prev_link = "<a href=\"$PHP_SELF?" . str_replace("_off=$offset", "_off=$newoffset", $qs) . "\">" . translate("Prev") . "</a>";
            }

            if ($offset + 1 < $num_photos) {
                $newoffset = $offset + 1;
                $next_link = "<a href=\"$PHP_SELF?" . str_replace("_off=$offset", "_off=$newoffset", $qs) . "\">" . translate("Next") . "</a>";
            }
        }
        else {
            $photo = new photo();
        }
    }

    if (isset($offset)) {
        $ignore = array("_off", "_action");
        $up_qs = update_query_string($request_vars, null, null, $ignore);
        $up_link = "<a href=\"photos.php?$up_qs\">" . translate("Up", 0) . "</a>";
    }

    // jump to edit screen if auto edit pref is set
    // permission to edit checked below
    if ((!$_action || $_action == "search") && $user->prefs->get("auto_edit")) {
        $_action = "edit";
    }

    // 2005-04-10 --JCT
    //
    // moved from below so they are allowed
    // prior to $user->is_admin() check
    //
    if ($_action == "lightbox") {
        $photo->add_to_album($user->get("lightbox_id"));
        $action = "display";
    }
    else if ($_action == "rate") {
        if (ALLOW_RATINGS) {
            $rating = getvar("rating");
            $photo->rate($user->get("user_id"), $rating);
        }
        $action = "display";
    }


    if (!$user->is_admin()) {
        if ($_action == "new" || $_action == "insert" ||
            $_action == "delete" || $_action == "confirm") {
            // only an admin can do these
            $_action = "display"; // in case redirect fails
            header("Location: " . add_sid("zoph.php"));
        }

        $permissions = $user->get_permissions_for_photo($photo_id);
        if (!$permissions) {
            $photo = new photo(-1); // in case redirect fails
            header("Location: " . add_sid("zoph.php"));
        }
        else if ($permissions->get("writable") == 0) {
            $_action = "display";
        }
    }

    if ($_action == "edit") {
        $action = "update";
    }
    else if ($_action == "update") {
        $photo->set_fields($request_vars);
        $photo->update($request_vars); // pass again for add people, cats, etc
        $action = "update";
    }
    else if ($_action == "new") {
        $action = "insert";
    }
    else if ($_action == "insert") {
        $photo->set_fields($request_vars);
        $photo->insert();
        $action = "update";
    }
    else if ($_action == "delete") {
        $action = "confirm";
    }
    else if ($_action == "confirm") {
        $photo->delete();
        if (!$user->prefs->get("auto_edit")) {
            $user->eat_crumb();
        }
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = "zoph.php"; }
        header("Location: " . add_sid($link));
    }
    // 2005-04-10 --JCT
    //
    // lightbox and rate actions moved
    // to prior to $user->is_admin() check
    //
    else {
        $action = "display";
    }

    if ($action != "insert") {
        $found = $photo->lookup($user);
        $title = $photo->get("name");

        $_deg = getvar("_deg");
        $_thumbnail = getvar("_thumbnail");
        if ($_deg) {
            if (ALLOW_ROTATIONS) {
                $photo->rotate($_deg);
            }
        } // thumbnails already recreated for rotations
        else if ($_thumbnail) {
            $photo->thumbnail();
        }
    }
    else {
        $title = translate("New Photo");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
<?php
    // no photo was found and this isn't a new record
    if ($action != "insert" && !$found) {
?>
        <tr>
          <th><H1><?php echo translate("photo") ?></H1></th>
          <td class="actionlink">nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class=titlebar>
        <tr>
          <td colspan="2">
           <?php echo translate("No photo was found.") ?>
          </td>
        </tr>
<?php
    }
    else if ($action == "display") {
        $title_bar = translate("photo");
        if ($num_photos) {
            $title_bar .= " " . ($offset + 1) . " of $num_photos";
        }
?>
        <tr class="titlebar">
          <th><H1><?php echo $title_bar ?></H1></th>
          <td class="actionlink">
<?php
        $bar = "[";
        if (EMAIL_PHOTOS) {
?>
            <?php echo $bar ?> <a href="mail.php?_action=compose&amp;photo_id=<?php echo $photo->get("photo_id") ?>"><?php echo translate("email") ?></a>
<?php
            $bar = "|";
        }

        if ($user->is_admin() || $permissions->get("writable")) {
?>
            <?php echo $bar ?> <a href="photo.php?_action=edit&amp;photo_id=<?php echo $photo->get("photo_id") ?>&_qs=<?php echo $encoded_qs ?>"><?php echo translate("edit") ?></a>
<?php
            $bar = "|";

            if ($user->is_admin()) {
?>
            | <a href="photo.php?_action=delete&amp;photo_id=<?php echo $photo->get("photo_id") ?>&_qs=<?php echo $encoded_qs ?>"><?php echo translate("delete") ?></a>
<?php
            }
        }
        if ($user->get("lightbox_id")) {
?>
            <?php echo $bar ?> <a href="photo.php?_action=lightbox&amp;<?php echo $qs ?>"><?php echo translate("lightbox", 0) ?></a>
<?php
            $bar = "|";
        }
?>
          <?php echo $bar == "|" ? "]" : "" ?></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="<?php echo $PHP_SELF ?>" method="POST">
      <table class="main">
<?php
        if (ALLOW_ROTATIONS && ($user->is_admin() || $permissions->get("writable"))) {
?>
        <tr>
          <td colspan="2" class="rotate">
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">

<select name="_deg">
<option>90</option>
<option>180</option>
<option>270</option>
</select>
<input type="submit" name="_button" value="<?php echo translate("rotate", 0) ?>">
          </td>
        </tr>
      </table>
</form>
<?php
        }
?>
        <table class="main">
        <tr>
          <td colspan="2">
            <table width="100%">
              <tr>
                <td class="prev"><?php echo $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></td>
                <td class="photohdr">
<?php
        if ($up_link) {
?>
            [ <?php echo $up_link ?> ]<br>
<?php
        }
?>
                  <?php echo $photo->get_fullsize_link($photo->get("name")) ?> :
                  <?php echo $photo->get("width") ?> x <?php echo $photo->get("height") ?>,
            <?php echo $photo->get("size") ?> <?php echo translate("bytes") ?>
                </td>
                <td class="next"><?php echo $next_link ? "[ $next_link ]" : "&nbsp;" ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="photo">
            <?php echo $photo->get_fullsize_link($photo->get_midsize_img()) ?>
          </td>
        </tr>
<?php
        if ($people_links = get_photo_person_links($photo)) {
?>
        <tr>
          <td colspan="2" class="personlink">
            <?php echo $people_links ?>
          </td>
        </tr>
<?php
        }
?>
<?php echo create_field_html($photo->get_display_array(), 2) ?>
<?php
        if (ALLOW_RATINGS || $photo->get("rating")) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("rating") ?></td>
          <td>
<form action="<?php echo $PHP_SELF ?>" method="POST">
            <table>
              <tr>
                <td class="field">
                  <?php echo $photo->get("rating") != 0 ? $photo->get("rating") . " / 10" : ""; ?>
                </td>
<?php
            if (ALLOW_RATINGS) {
?>
                <td class="field">
<input type="hidden" name="_action" value="rate">
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
<input type="submit" name="_button" value="<?php echo translate("rate", 0) ?>">
<?php echo create_rating_pulldown($photo->get_rating($user->get("user_id"))); ?>
                </td>
<?php
            }
?>
              </tr>
            </table>
</form>
          </td>
        </tr>
<?php
        }
        if ($album_links = create_link_list($photo->lookup_albums($user))) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("albums") ?></td>
          <td class="field"><?php echo $album_links ?></td>
        </tr>
<?php
        }

        if ($category_links = create_link_list($photo->lookup_categories())) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("categories") ?></td>
          <td class="field"><?php echo $category_links ?></td>
        </tr>
<?php
        }
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("last modified") ?></td>
          <td class="field"><?php echo format_timestamp($photo->get("timestamp")) ?></td>
        </tr>
        <tr>
          <td colspan="2" class="photodesc">
<?php
        if ($photo->get("description")) {
            echo $photo->get("description");
        }
?>
          </td>
        </tr>
<?php
        if ($user->prefs->get("camera_info")) {
            echo create_field_html($photo->get_camera_display_array(), 2);
            echo "</table>";
        }
    }
    else if ($action == "confirm") {
?>
        <tr>
          <th><h1><?php echo translate("photo") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $photo->get("name")) ?>
          </td>
          <td class="actionlink">[
            <a href="photo.php?_action=confirm&amp;photo_id=<?php echo $photo->get("photo_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="photo.php?<?php echo $encoded_qs ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
      </table>

<?php
    }
    else {
require_once("edit_photo.inc.php");
    }
?>
    </td>
  </tr>
</table>

<?php require_once("footer.inc.php"); ?>
