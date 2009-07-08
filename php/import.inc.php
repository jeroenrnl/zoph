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

require_once("exif.inc.php");

function get_files($dir_name) {
    $files = array();

    if ($dir = @opendir($dir_name)) {
        while(($file = readdir($dir)) !== false) {
            if (valid_image($file)) {
                $files[] = $dir_name . '/' . $file;
            }
        }
        closedir($dir);
    }
    else {
        echo translate("Could not open directory") . ": $dir_name<br>\n";
    }

    return $files;
}

function process_images($images, $path, $fields) {
    $date = "";
    $absolute_path = "/" . cleanup_path(IMAGE_DIR . $path);

    echo "<p>" . sprintf(translate("Processing %s image(s)."), count($images)) . "</p>\n";
    $loaded = 0;
    foreach ($images as $image) {

        if (file_exists($image) == false) {
            echo sprintf(translate("Skipping %s: File does not exist."), $image) . "<br>\n";
            continue;
        }
        create_dir("$absolute_path");
        if (minimum_version('4.2.0')) {
            $exif_data = process_exif($image);
            if($fields["date"]) {
                $date_array=explode("-", $fields["date"]);
                if(preg_match("/^[0-9]{4}\-[01][0-9]\-[0-3][0-9]$/", $fields["date"]) && checkdate(intval($date_array[2]), intval($date_array[1]), intval($date_array[0]))) {
                    $exif_data["date"]=$fields["date"];
                } else {
                    printf("Date %s is invalid, ignoring" . "<br>", htmlentities($fields["date"]));
                }
            }
            if ((USE_DATED_DIRS) && !(HIER_DATED_DIRS)) {
                $date = cleanup_path(str_replace("-", ".", $exif_data["date"]));
                create_dir($absolute_path . "/" . $date);
            } elseif ((USE_DATED_DIRS) && (HIER_DATED_DIRS)) {
                $date = cleanup_path(str_replace("-", "/", $exif_data["date"]));
                create_dir_recursive($absolute_path . "/" .  $date . "/");
            }
        } else {
            echo "PHP version too old (<4.2.0), not trying to get EXIF info";
        }
        create_dir("$absolute_path" . "/" . "$date" . "/" . THUMB_PREFIX);
        create_dir("$absolute_path" . "/" . "$date" . "/" . MID_PREFIX);
        $image_dir = dirname($image);
        $image_name = basename($image);

        if (cleanup_path($image_dir) != cleanup_path($absolute_path . "/" . $date)) {
            $new_image = $absolute_path . "/" . $date . "/" . $image_name;

            if (!copy($image, $new_image)) {
                echo sprintf(translate("Could not copy %s to %s."), $image, $new_image) . "<br>\n";
                continue;
            } 

            echo "$image -> $new_image<br>\n";
            if (IMPORT_MOVE) {
                echo sprintf(translate("Deleting %s"), $image) . "<br>\n";
                unlink($image);
            }

            $image = $new_image;
        }
        else {
            echo "$image<br>\n";
        }

        flush();

        $photo = new photo();
        $photo->set("name", $image_name);
        $photo->set("path", cleanup_path($path . "/" . $date));

        //$width = imagesx($img_src);
        //$height = imagesy($img_src);
        $image_info = getimagesize($image);
        $width = $image_info[0];
        $height = $image_info[1];

        if (!$photo->thumbnail()) {
            echo translate("Could not create thumbnail") . ": $image_name<br>\n";
        }

        // first try the insert
        if ($photo->insert()) {

            // then do everything else as an update
            // (which is smart enough to handle the
            // album/category/people tables)

            $photo->set("size", filesize($image));
            $photo->set("width", $width);
            $photo->set("height", $height);

            if ($fields) {
                $photo->set_fields($fields);
            }

            // exif functions introduced in PHP 4.2.0
            if (minimum_version('4.2.0')) {
                $photo->set_fields($exif_data);
            }

            $photo->update($fields);

            $loaded++;
        }
        else {
            echo translate("Insert failed.") . "<br>\n";
        }

    }

    return $loaded;
}

function create_dir($directory) {
    if (file_exists($directory) == false) {
        if (mkdir($directory, DIR_MODE)) {
            echo translate("Created directory") . ": $directory<br>\n";
            return true;
        }
        else {
            echo translate("Could not create directory") . ": $directory<br>\n";
            return false;
        }
    }
    return 0;
}

function create_dir_recursive($directory){
  foreach(split('/',$directory) as $subdir) {
    $result=create_dir($nextdir="$nextdir$subdir/");
  }
  return $result;
}
?>
