<?php
    session_cache_limiter("public");
    require_once("include.inc.php");

    $photo_id = getvar("photo_id");
    $type = getvar("type");

    $photo = new photo($photo_id);
    $found = $photo->lookup($user);

    if ($found) {
        $name = $photo->get("name");
        $image_path = IMAGE_DIR . $photo->get("path") . "/";
        if ($type) {
            $image_path .= $type . "/" . $type . "_";
            $name = get_converted_image_name($name);
        }
        $image_path .= $name;

        // the following thanks to Alan Shutko
        $mtime = filemtime($image_path);
        $filesize = filesize($image_path);
        $gmt_mtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
  
        // we assume that the client generates proper RFC 822/1123 dates
        //   (should work for all modern browsers and proxy caches)
        if ($HTTP_IF_MODIFIED_SINCE == $gmt_mtime) {
              header("HTTP/1.1 304 Not Modified");
              exit;
        }

        $image_type = get_image_type($name);
        if ($image_type) {
            header("Content-Length: " . $filesize);
            header("Content-Disposition: inline; filename=" . $name);
            header("Last-Modified: " . $gmt_mtime);

            header("Content-type: $image_type");
            readfile($image_path);
            exit;
        }
    }

    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TITLE_BG_COLOR?>">
        <tr>
          <th align="left">
            <?php echo translate("error") ?>
          </th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <td align="center">
            <?php echo translate("The image you requested could not be displayed.") ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php
    require_once("footer.inc.php");
?>
