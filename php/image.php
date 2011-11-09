<?php
/*
 * This file is part of Zoph.
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
    session_cache_limiter("public");
    require_once("log.inc.php");
    require_once("config.inc.php");
    require_once("variables.inc.php");
    $hash = getvar("hash");

    if (defined("SHARE") && SHARE===1 && !empty($hash) && empty($user)) {
        define("IMAGE_PHP", 1);
        require_once("classes/anonymousUser.inc.php");
        $user = new anonymousUser();
    }
    require_once("include.inc.php");

    $photo_id = getvar("photo_id");
    $type = getvar("type");
    
    if(($type=="import_thumb" || $type=="import_mid") && ($user->is_admin() || $user->get("import"))) {
    
        $md5 = getvar("file");
        $file = file::getFromMD5(IMAGE_DIR . "/" . IMPORT_DIR, $md5);
        
        $photo = new photo();
        $photo->set("name", basename($file));
        $photo->set("path", IMPORT_DIR);
        if($type=="import_thumb") {
            $type="thumb";
        } else if ($type=="import_mid") {
            $type="mid";
        }
        $found=true;
    } else if (defined("SHARE") && SHARE===1 && !empty($hash)) {
        try {
            $photo=photo::getFromHash($hash, "full");
            $photo->lookup();
            $found = true;
        } catch(PhotoNotFoundException $e) {
            try {
                $photo=photo::getFromHash($hash, "mid");
                $photo->lookup();
                $type="mid";
                $found = true;
            } catch(PhotoNotFoundException $e) {
                /** @todo This should be changed into a nicer error display; */
                die($e->getMessage());
            }
        }
    } else if ($type==MID_PREFIX || $type==THUMB_PREFIX || empty($type)) {
        $photo = new photo($photo_id);
        $found = $photo->lookupForUser($user);
    } else {
        die("Illegal type");
    }
    if ($found) {

        $annotated = getvar('annotated');
        if (ANNOTATE_PHOTOS && $annotated) {
            $image_path = ANNOTATE_TEMP_DIR . "/" .
                $photo->get_annotated_file_name($user);
        }
        else {
            $watermark_file="";
            $name = $photo->get("name");
            $image_path = IMAGE_DIR . "/" . $photo->get("path") . "/";
            if (!$user->is_admin()) {
                $permissions = $user->get_permissions_for_photo($photo_id);
                $watermark = $permissions->get("watermark_level");
                $photolevel=$photo->get("level");
                if(WATERMARK && ($photolevel > $watermark)) {
                    $watermark_file = IMAGE_DIR . "/" . WATERMARK;
                    if (!file_exists($watermark_file)) {
                        $watermark_file="";
                    }
                }
            }

            if (WATERMARKING && $watermark_file && !$type) {
                $image_path .= $name;
                $image=imagecreatefromjpeg($image_path);
                watermark_image(&$image, $watermark_file, WM_POSX, WM_POSY, WM_TRANS);
                header("Content-type: image/jpeg");
                imagejpeg($image);
                imagedestroy($image);
                exit;
            } else {
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
                if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
                    $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime) {
                      header("HTTP/1.1 304 Not Modified");
                      exit;
                }
                $image_type = get_image_type($image_path);
                if ($image_type) {
                    header("Content-Length: " . $filesize);
                    header("Content-Disposition: inline; filename=" . $name);
                    header("Last-Modified: " . $gmt_mtime);
                    header("Content-type: " . $image_type);
                    readfile($image_path);
                    exit;
                }
            }
         }
    }
    require_once("header.inc.php");
?>
          <h1>
            <?php echo translate("error") ?>
          </h1>
      <div class="main">
            <?php echo translate("The image you requested could not be displayed.") ?>
</div>
<?php
    require_once("footer.inc.php");
?>
