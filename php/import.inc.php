<?php

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

        // patch from Patrick Lam
        $image_info = getimagesize($image);
        switch ($image_info[2]) {
            case 1:
                $img_src = imagecreatefromgif($image);
                break;
            case 2:
                $img_src = imagecreatefromjpeg($image);
                break;
            case 3:
                $img_src = imagecreatefrompng($image);
                break;
            default:
                break;
        }

        $width = imagesx($img_src);
        $height = imagesy($img_src);

        if (!create_thumbnail($img_src, $image_name, $absolute_path, $width,
            $height, THUMB_PREFIX, THUMB_SIZE)) {

            echo translate("Could not create thumbnail") . ": " . THUMB_PREFIX . "_$image_name<br>\n";
            imagedestroy($img_src);
            continue;
        }

        if (!create_thumbnail($img_src, $image_name, $absolute_path, $width,
            $height, MID_PREFIX, MID_SIZE)) {

            echo translate("Could not create thumbnail") . ": " . MID_PREFIX . "$_image_name<br>\n";
            imagedestroy($img_src);
            continue;
        }

        imagedestroy($img_src);

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

function create_thumbnail($img_src, $image_name, $absolute_path,
    $width, $height, $prefix, $size) {

    if ($width >= $height) {
        $new_width = $size;
        $new_height = round(($new_width / $width) * $height);
    }
    else {
        $new_height = $size;
        $new_width = round(($new_height / $height) * $width);
    }

    $img_dst = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $new_width, $new_height,
        $width, $height);

    $new_image = $absolute_path . '/' . $prefix . '/' .
        $prefix . '_' .  get_converted_image_name($image_name);

    $image_type = get_image_type($new_image);

    // a little fast a loose but usually ok
    $func = "image" . substr($image_type, strpos($image_type, '/') + 1);

    $return = 1;
    if (!$func($img_dst, $new_image)) {
        $return = 0;
    }

    imagedestroy($img_dst);

    return $return;
}

?>
