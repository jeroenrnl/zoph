<?php
    require_once("include.inc.php");

    $_cols = getvar("_cols");
    $_rows = getvar("_rows");
    $_off = getvar("_off");
    $_order = getvar("_order");
    $_dir = getvar("_dir");
    $_show = getvar("_show");

    if (!$_cols) { $_cols = $DEFAULT_COLS; }
    if (!$_rows) { $_rows = $DEFAULT_ROWS; }
    if (!$_off)  { $_off = 0; }

    if (!$_order) { $_order = $DEFAULT_ORDER; }
    if (!$_dir)   { $_dir = $DEFAULT_DIRECTION; }

    $cells = $_cols * $_rows;
    $offset = $_off;

    // remove photo from lightbox
    $photo_id = getvar("_photo_id");
    if ($user->get("lightbox_id") && $photo_id) {
        $photo = new photo($photo_id);
        $photo->remove_from_album($user->get("lightbox_id"));
    }

    $album_id = getvar("album_id");
    if ($album_id && $user->get("lightbox_id") &&
        $album_id == $user->get("lightbox_id")) {

        $lightbox = true;
    }

    $thumbnails;
    $num_photos =
        get_photos($request_vars, $offset, $cells, $thumbnails, $user);

    $num_thumbnails = sizeof($thumbnails);

    if  ($num_thumbnails) {
        $num_pages = ceil($num_photos / $cells);
        $page_num = floor($offset / $cells) + 1;

        $num = min($cells, $num_thumbnails);

        $name = $lightbox ? "Lightbox" : "Photos";

        $title = sprintf(translate("$name (Page %s/%s)", 0), $page_num, $num_pages);
        $title_bar = sprintf(translate("photos %s to %s of %s"), ($offset + 1), ($offset + $num), $num_photos);
    }
    else {
        $title = translate("No Photos Found");
        $title_bar = translate("photos");
    }

    if ($num_thumbnails == 0 || $_cols <= 4) {
        $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    }
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo $title_bar ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
            [
            <a href="slideshow.php?<?php echo update_query_string($request_vars, "_off", $offset) ?>"><?php echo translate("Slideshow") ?></a>
            ]
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<?php
    if ($num_thumbnails <= 0) {
?>
        <tr>
          <td align="center">
       <?php echo translate("No photos were found matching your search criteria.") ?>
          </td>
        </tr>
<?php
    }
    else {
?>
        <tr>
          <td>
<form action="photos.php" method="GET">
<?php echo create_form($request_vars) ?>
            <?php echo translate("order by", 0) ?>
 <?php echo create_photo_field_pulldown("_order", $_order) ?>
          </td>
          <td width="20" align="center">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
              <tr>
                <td><a href="photos.php?<?php echo update_query_string($request_vars, "_dir", "asc") ?>"><img src="images/up<?php echo $_dir == "asc" ? 1 : 2 ?>.gif" border="0" width="16" height="8"></a></td>
              </tr>
              <tr>
                <td><a href="photos.php?<?php echo update_query_string($request_vars, "_dir", "desc") ?>"><img src="images/down<?php echo $_dir == "asc" ? 2 : 1 ?>.gif" border="0" width="16" height="8"></a></td>
              </tr>
            </table>
          </td>
          <td align="right">
<?php echo create_integer_pulldown("_rows", $_rows, 1, 10) ?>
            <?php echo translate("rows") ?>

<?php echo create_integer_pulldown("_cols", $_cols, 1, 10) ?>
            <?php echo translate("cols") ?>
            <input type="submit" name="_button" value="<?php echo translate("go", 0) ?>">
</form>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center">
            <table border="0">
              <tr>
<?php
        if (MAX_THUMB_DESC && $user->prefs->get("desc_thumbnails")) {
            $desc_thumbnails = true;
        }

        for ($i = 0; $i < $num; $i++) {

            if ($i > 0 && $i % $_cols == 0) {
                echo "              </tr>\n              <tr>\n";
            }

            $ignore = array("_photo_id");
?>
                <td width="<?php echo THUMB_SIZE ?>" align="center">
                  <?php echo $thumbnails[$i]->get_thumbnail_link("photo.php?" . update_query_string($request_vars, "_off", $offset + $i, $ignore)) . "\n" ?>
<?php
            if ($desc_thumbnails && $thumbnails[$i]->get("description")) {
?>
                <br>
                <font size="-1"><?php echo substr($thumbnails[$i]->get("description"), 0, MAX_THUMB_DESC) ?></font>
<?php
                if (strlen($thumbnails[$i]->get("description")) > MAX_THUMB_DESC) { echo "..."; }
            }

            if ($lightbox) {
                if (!defined($desc_thumbnails)) { echo "<br>\n"; }
?>
                <a href="photos.php?<?php echo update_query_string($request_vars, "_photo_id", $thumbnails[$i]->get("photo_id"), $ignore) ?>"><font size="-1">x</font></a>
<?php
            }

?>
                </td>
<?php
        }

        $diff = $cells - $num_thumbnails;
        if ($diff > 0) {
            for ($i = $diff % $_cols; $i > 0; $i--) {
                echo "                <td>&nbsp;</td>\n";
            }
        }
?>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center">
            <table border="0" width="100%">
              <tr>
<?php
        if ($offset > 0) {
            $new_offset = max(0, $offset - $cells);
?>
          <td width="20%">
            [ <a href="photos.php?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><?php echo translate("Prev") ?></a> ]
          </td>
<?php
        }
        else {
            echo "          <td width=\"20%\">&nbsp;</td>\n";
        }

        if ($num_pages > 1) {
?>
          <td align="center" width="60%">[
<?php
        $mid_page = floor($MAX_PAGER_SIZE / 2);
        $page = $page_num - $mid_page;
        if ($page <= 0) { $page = 1; }

        $last_page = $page + $MAX_PAGER_SIZE - 1;
        if ($last_page > $num_pages) {
            $page = $page - $last_page + $num_pages;
            if ($page <= 0) { $page = 1; }
            $last_page = $num_pages;
        }

        if ($page > 1) {
?>
            <a href="photos.php?<?php echo update_query_string($request_vars, "_off", 0) ?>">1</a> ... 
<?php
        }

        while ($page <= $last_page) {
            $new_offset = ($page - 1) * $cells;
?>
            <a href="photos.php?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><font<?php echo $page == $page_num ? " color=\"red\"" : "" ?>><?php echo $page ?></font></a>
<?php
            $page++;
        }

        if ($page <= $num_pages) {
?>
            ... <a href="photos.php?<?php echo update_query_string($request_vars, "_off", ($num_pages-1) * $cells) ?>"><?php echo $num_pages ?></a>
<?php
        }
?>
          ]</td>
<?php
        }
        else {
            echo "          <td width=\"60%\">&nbsp;</td>\n";
        }

        if ($num_photos > $offset + $num) {
            $new_offset = $offset + $cells;
?>
          <td align="right" width="20%">
            [ <a href="photos.php?<?php echo update_query_string($request_vars, "_off", $new_offset) ?>"><?php echo translate("Next") ?></a> ]
          </td>
<?php
        }
        else {
            echo "          <td width=\"20%\">&nbsp;</td>\n";
        }

    } // if photos
?>
              </tr>
            </table>
          </td>
        </table>
      </tr>
    </td>
  </tr>
</table>
</div>

<?php require_once("footer.inc.php"); ?>
