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
      <table class="titlebar">
        <tr>
          <th><h1><?php echo $title_bar ?></h1></th>
          <td class="actionlink">
            [
<?php
    $qs = preg_replace('/_crumb=\d+&?/', '', $QUERY_STRING);
    if ($user->is_admin()) {
?>
            <a href="edit_photos.php?<?php echo $qs ?>"><?php echo translate("edit") ?></a> |
<?php
    }
?>
            <a href="slideshow.php?<?php echo $qs ?>"><?php echo translate("Slideshow") ?></a>
            ]
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="photos.php" method="GET">
      <table class="main">
<?php
    if ($num_thumbnails <= 0) {
?>
        <tr>
          <td class="center">
       <?php echo translate("No photos were found matching your search criteria.") ?>
          </td>
        </tr>
<?php
    }
    else {
?>
        <tr>
          <td>
<?php echo create_form($request_vars) ?>
            <?php echo translate("order by", 0) ?>
 <?php echo create_photo_field_pulldown("_order", $_order) ?>
          </td>
          <td>
                <a href="photos.php?<?php echo update_query_string($request_vars, "_dir", "asc") ?>"><img class="up" alt="sort ascending" src="images/up<?php echo $_dir == "asc" ? 1 : 2 ?>.gif"></a>
                <a href="photos.php?<?php echo update_query_string($request_vars, "_dir", "desc") ?>"><img class="down" alt="sort descending" src="images/down<?php echo $_dir == "asc" ? 2 : 1 ?>.gif"></a>
          </td>
          <td class="actionlink">
<?php echo create_integer_pulldown("_rows", $_rows, 1, 10) ?>
            <?php echo translate("rows") ?>

<?php echo create_integer_pulldown("_cols", $_cols, 1, 10) ?>
            <?php echo translate("cols") ?>
            <input type="submit" name="_button" value="<?php echo translate("go", 0) ?>">
          </td>
        </tr>
      </table>
</form>
      <table class="main">
        <tr>
          <td colspan="3" class="center">
            <table class="content">
              <tr>
<?php
        if (MAX_THUMB_DESC && $user->prefs->get("desc_thumbnails")) {
            $desc_thumbnails = true;
        }

        for ($i = 0; $i < $num; $i++) {

            if ($i > 0 && $i % $_cols == 0) {
                echo "              </tr>\n              <tr>\n";
            }

            $ignore = array("_action", "_photo_id");
?>
                <td class="thumbnail">
                  <?php echo $thumbnails[$i]->get_thumbnail_link("photo.php?" . update_query_string($request_vars, "_off", $offset + $i, $ignore)) . "\n" ?>
<?php
            if ($desc_thumbnails && $thumbnails[$i]->get("description")) {
?>
                <br>
                <div class="thumbdesc"><?php echo substr($thumbnails[$i]->get("description"), 0, MAX_THUMB_DESC) ?></div>
<?php
                if (strlen($thumbnails[$i]->get("description")) > MAX_THUMB_DESC) { echo "..."; }
            }

            if ($lightbox) {
                if (!defined($desc_thumbnails)) { echo "<br>\n"; }
?>
                <div class="actionlink"><a href="photos.php?<?php echo update_query_string($request_vars, "_photo_id", $thumbnails[$i]->get("photo_id"), $ignore) ?>">x</a></div>
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
        </table>
<?php include "pager.inc.php" ?>
<?php
    } // if photos
?>
      </tr>
      </table>
    </td>
  </tr>
</table>

<?php require_once("footer.inc.php"); ?>
