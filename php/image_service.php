<?php
    session_cache_limiter("public");
    require_once("include.inc.php");

    $photo_id = getvar("photo_id");
    $type = getvar("type");

    $photo = new photo($photo_id);
    $found = $photo->lookup($user);

    if ($found) {

        $annotated = getvar('annotated');
        if (ANNOTATE_PHOTOS && $annotated) {
            $image_path = ANNOTATE_TEMP_DIR . "/" .
                $photo->get_annotated_file_name($user);
        }
        else {
            $name = $photo->get("name");
            $image_path = IMAGE_DIR . $photo->get("path") . "/";
            if ($type) {
                $image_path .= $type . "/" . $type . "_";
                $name = get_converted_image_name($name);
            }
            $image_path .= $name;
        }

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

        $image_type = get_image_type($image_path);
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
      <table class="titlebar">
        <tr>
          <th><h1>
            <?php echo translate("error") ?>
          </h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
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
