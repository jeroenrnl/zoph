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

    if ($offset) {
        $ignore = array("_off", "_action");
        $up_qs = update_query_string($request_vars, null, null, $ignore);
        $up_link = "<a href=\"photos.php?$up_qs\">" . translate("Up", 0) . "</a>";
    }

    // jump to edit screen if auto edit pref is set
    // permission to edit checked below
    if ((!$_action || $_action == "search") && $user->prefs->get("auto_edit")) {
        $_action = "edit";
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
        $user->eat_crumb();
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = "zoph.php"; }
        header("Location: " . add_sid($link));
    }
    else if ($_action == "lightbox") {
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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
<?php
    // no photo was found and this isn't a new record
    if ($action != "insert" && !$found) {
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("photo") ?></font></th>
          <td align="right">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <td colspan="2" align="center">
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
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo $title_bar ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
<?php
    $bar = "[";
    if (EMAIL_PHOTOS) {
?>
            <?php echo $bar ?> <a href="mail.php?_action=compose&photo_id=<?php echo $photo->get("photo_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("email") ?></font></a>
<?php
        $bar = "|";
    }

    if ($user->is_admin() || $permissions->get("writable")) {
?>
            <?php echo $bar ?> <a href="photo.php?_action=edit&photo_id=<?php echo $photo->get("photo_id") ?>&_qs=<?php echo $encoded_qs ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("edit") ?></font></a>
<?php
        $bar = "|";

        if ($user->is_admin()) {
?>
            | <a href="photo.php?_action=delete&photo_id=<?php echo $photo->get("photo_id") ?>&_qs=<?php echo $encoded_qs ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a>
<?php
        }
    }

    if ($user->get("lightbox_id")) {
?>
            <?php echo $bar ?> <a href="photo.php?_action=lightbox&<?php echo $qs ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("lightbox", 0) ?></font></a>
<?php
        $bar = "|";
    }
?>
          <?php echo $bar == "|" ? "]" : "" ?></font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<?php
    if (ALLOW_ROTATIONS && ($user->is_admin() || $permissions->get("writable"))) {
?>
        <tr>
          <td colspan="2" align="center">
<form action="<?php echo $PHP_SELF ?>" method="POST">
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">

<select name="_deg">
<option>90</option>
<option>180</option>
<option>270</option>
</select>
<input type="submit" name="_button" value="<?php echo translate("rotate", 0) ?>">
</form>
          </td>
        </tr>
<?php
    }
?>
        <tr>
          <td colspan="2" align="center">
            <table width="100%">
              <tr>
                <td align="left"><?php echo $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></td>
                <td align="center">
<?
    if ($up_link) {
?>
            [ <?php echo $up_link ?> ]<br>
<?php
    }
?>
                  <font size="-1">
                  <?php echo $photo->get_fullsize_link($photo->get("name")) ?> :
                  <?php echo $photo->get("width") ?> x <?php echo $photo->get("height") ?>,
            <?php echo $photo->get("size") ?> <?php echo translate("bytes") ?>
                  </font>
                </td>
                <td align="right"><?php echo $next_link ? "[ $next_link ]" : "&nbsp;" ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <?php echo $photo->get_fullsize_link($photo->get_midsize_img()) ?>
          </td>
        </tr>
<?php
        if ($people_links = get_photo_person_links($photo)) {
?>
        <tr>
          <td colspan="2" align="center">
            <font size="-1">
            <?php echo $people_links ?>
            </font>
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
          <td align="right"><?php echo translate("rating") ?></td>
          <td>
            <table>
              <tr>
                <td>
                  <?php echo $photo->get("rating") != 0 ? $photo->get("rating") . " / 10" : ""; ?>
                </td>
<?php
            if (ALLOW_RATINGS) {
?>
                <td>
<form action="<?php echo $PHP_SELF ?>" method="POST">
<input type="hidden" name="_action" value="rate">
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
<input type="submit" name="_button" value="rate">
<?php echo create_rating_pulldown($photo->get_rating($user->get("user_id"))); ?>
</form>
                </td>
<?php
            }
?>
              </tr>
            </table>
          </td>
        </tr>
<?php
        }
        if ($album_links = create_link_list($photo->lookup_albums($user))) {
?>
        <tr>
          <td align="right"><?php echo translate("albums") ?></td>
          <td><?php echo $album_links ?></td>
        </tr>
<?php
        }

        if ($category_links = create_link_list($photo->lookup_categories())) {
?>
        <tr>
          <td align="right"><?php echo translate("categories") ?></td>
          <td><?php echo $category_links ?></td>
        </tr>
<?php
        }
?>
        <tr>
          <td align="right"><?php echo translate("last modified") ?></td>
          <td><?php echo format_timestamp($photo->get("timestamp")) ?></td>
        </tr>
        <tr bgcolor="<?php echo $TITLE_BG_COLOR?>"> 
          <td colspan="2">
<?php
        if ($photo->get("description")) {
?>
            <table cellspacing="0" cellpadding="4" bgcolor="<?php echo $TABLE_BG_COLOR?>" width="100%">
              <tr>
                <td>
            <?php echo $photo->get("description") ?>
                </td>
              </tr>
            </table>
<?php
        }
?>
          </td>
        </tr>
<?php
        if ($user->prefs->get("camera_info")) {
            echo create_field_html($photo->get_camera_display_array(), 2);
        }
    }
    else if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("photo") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $photo->get("name")) ?>
          </td>
          <td align="right">[
            <a href="photo.php?_action=confirm&photo_id=<?php echo $photo->get("photo_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="photo.php?<?php echo $encoded_qs ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
<?php
    }
    else {
require_once("edit_photo.inc.php");
    }
?>
      </table>
    </td>
  </tr>
</table>
</div>

<?php require_once("footer.inc.php"); ?>
