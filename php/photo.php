<?php
    require_once("include.inc.php");

    $photo_id = getvar("photo_id");
    $_off = getvar("_off");
    $_lightbox = getvar("_lightbox");

    /*
    Before deciding to include the Prev and Next links, it was as
    simple as this.  But now we go through get_photos().

    $photo = new photo($photo_id);
    */

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

            $qs = preg_replace('/_crumb=\d+&?/', '', $QUERY_STRING);

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

    if ($_lightbox) {
        $photo->add_to_album($user->get("lightbox_id"));
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
    else {
        $action = "display";
    }

    if ($action != "insert") {
        $found = $photo->lookup($user);
        $title = $photo->get("name");
    }
    else {
        $title = translate("New Photo");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
<?php
    // no photo was found and this isn't a new record
    if ($action != "insert" && !$found) {
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("photo") ?></font></th>
          <td align="right">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
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
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?= $title_bar ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">
<?php
    $bar = "[";
    if (EMAIL_PHOTOS) {
?>
            <?= $bar ?> <a href="mail.php?_action=compose&photo_id=<?= $photo->get("photo_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("email") ?></font></a>
<?php
        $bar = "|";
    }

    if ($user->is_admin() || $permissions->get("writable")) {
?>
            <?= $bar ?> <a href="photo.php?_action=edit&photo_id=<?= $photo->get("photo_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("edit") ?></font></a>
<?php
        $bar = "|";

        if ($user->is_admin()) {
?>
            | <a href="photo.php?_action=delete&photo_id=<?= $photo->get("photo_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a>
<?php
        }
    }

    if ($user->get("lightbox_id")) {
?>
            <?= $bar ?> <a href="photo.php?photo_id=<?= $photo->get("photo_id") ?>&_lightbox=1"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("lightbox", 0) ?></font></a>
<?php
        $bar = "|";
    }
?>
          <?= $bar == "|" ? "]" : "" ?></font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td colspan="2" align="center">
            <table width="100%">
              <tr>
                <td align="left"><?= $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></td>
                <td align="center">
                  <font size="-1">
                  <?= $photo->get_fullsize_link($photo->get("name")) ?> :
                  <?= $photo->get("width") ?> x <?= $photo->get("height") ?>,
            <?= $photo->get("size") ?> <?php echo translate("bytes") ?>
                  </font>
                </td>
                <td align="right"><?= $next_link ? "[ $next_link ]" : "&nbsp;" ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <?= $photo->get_fullsize_link($photo->get_midsize_img()) ?>
          </td>
        </tr>
<?php
        if ($people_links = get_photo_person_links($photo)) {
?>
        <tr>
          <td colspan="2" align="center">
            <font size="-1">
            <?= $people_links ?>
            </font>
          </td>
        </tr>
<?php
        }
?>
<?= create_field_html($photo->get_display_array(), 2) ?>
<?php
        if ($album_links = create_link_list($photo->lookup_albums($user))) {
?>
        <tr>
          <td align="right"><?php echo translate("albums") ?></td>
          <td><?= $album_links ?></td>
        </tr>
<?php
        }

        if ($category_links = create_link_list($photo->lookup_categories())) {
?>
        <tr>
          <td align="right"><?php echo translate("categories") ?></td>
          <td><?= $category_links ?></td>
        </tr>
<?php
        }
?>
        <tr>
          <td align="right"><?php echo translate("last modified") ?></td>
          <td><?= format_timestamp($photo->get("timestamp")) ?></td>
        </tr>
        <tr bgcolor="<?=$TITLE_BG_COLOR?>"> 
          <td colspan="2">
<?php
        if ($photo->get("description")) {
?>
            <table cellspacing="0" cellpadding="4" bgcolor="<?=$TABLE_BG_COLOR?>" width="100%">
              <tr>
                <td>
            <?= $photo->get("description") ?>
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
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("photo") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $photo->get("name")) ?>
          </td>
          <td align="right">[
            <a href="photo.php?_action=confirm&photo_id=<?= $photo->get("photo_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="photo.php?_action=display&photo_id=<?= $photo->get("photo_id") ?>"><?php echo translate("cancel") ?></a>
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
