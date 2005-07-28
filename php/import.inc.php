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

    $absolute_path = IMAGE_DIR . $path;

    $thumb_path = $absolute_path . '/' . THUMB_PREFIX;
    if (file_exists($thumb_path) == false) {
        if (mkdir($thumb_path, DIR_MODE)) {
            echo translate("Created directory") . ": $thumb_path<br>\n";
        }
        else {
            echo translate("Could not create directory") . ": $thumb_path<br>\n";
            return -1;
        }
    }

    $mid_path = $absolute_path . '/' . MID_PREFIX;
    if (file_exists($mid_path) == false) {
        if (mkdir($mid_path, DIR_MODE)) {
            echo translate("Created directory") . ": $mid_path<br>\n";
        }
        else {
            echo translate("Could not create directory") . ": $mid_path<br>\n";
            return -1;
        }
    }

    echo "<p>" . sprintf(translate("Processing %s image(s)."), count($images)) . "</p>\n";
    $loaded = 0;
    foreach ($images as $image) {

        if (file_exists($image) == false) {
            echo sprintf(translate("Skipping %s: File does not exist."), $image) . "<br>\n";
            continue;
        }

        $image_dir = dirname($image);
        $image_name = basename($image);

        if ($image_dir != $absolute_path) {
            $new_image = $absolute_path . '/' . $image_name;
            if (!copy($image, $new_image)) {
                echo sprintf(translate("Could not copy %s to %s."), $image, $new_image) . "<br>\n";
                continue;
            }

            echo "$image -> $new_image<br>\n";

            $image = $new_image;
        }
        else {
            echo "$image<br>\n";
        }

        flush();

        $photo = new photo();
        $photo->set("name", $image_name);
        $photo->set("path", $path);

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
                $exif_data = process_exif($image);
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

?>
