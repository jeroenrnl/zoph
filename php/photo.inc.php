<?php

/*
 * A class corresponding to the photos table.
 */
class photo extends zoph_table {

    var $photographer;
    var $location;

    function photo($id = 0) {
        parent::zoph_table("photos", array("photo_id"));
        $this->set("photo_id",$id);
    }

    function lookup($user = null) {

        if (!$this->get("photo_id")) { return; }

        if ($user && !$user->is_admin()) {
            $sql =
                "select p.* from " .
                DB_PREFIX . "photos as p, " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "album_permissions as ap " .
                "where p.photo_id = '" . escape_string($this->get("photo_id")) . "'" .
                " and p.photo_id = pa.photo_id" .
                " and pa.album_id = ap.album_id" .
                " and ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " and ap.access_level >= p.level " .
                "limit 0, 1";
        }
        else {
            $sql =
                "select * from " . DB_PREFIX . "photos " .
                "where photo_id = '" . escape_string($this->get("photo_id")) . "'";
        }

        $success = parent::lookup($sql);

        if ($success) {
            $this->lookup_photographer();
            $this->lookup_location();
        }

        return $success;
    }

    function lookup_photographer() {
        if ($this->get("photographer_id") > 0) {
            $this->photographer = new person($this->get("photographer_id"));
            $this->photographer->lookup();
        }
    }

    function lookup_location() {
        if ($this->get("location_id") > 0) {
            $this->location = new place($this->get("location_id"));
            $this->location->lookup();
        }
    }

    function delete() {
        parent::delete(null, array("photo_people", "photo_categories",
            "photo_albums"));
    }

    function update($vars = null) {
        parent::update();

        if (!$vars) { return; }

        if ($vars["_album"]) {
            if ($vars["_remove"]) {
                $this->remove_from_album($vars["_album"]);
            }
            else {
                $this->add_to_album($vars["_album"]);
            }
        }

        if ($vars["_category"]) {
            if ($vars["_remove"]) {
                $this->remove_from_category($vars["_category"]);
            }
            else {
                $this->add_to_category($vars["_category"]);
            }
        }

        if ($vars["_person"]) {
            if ($vars["_remove"]) {
                $this->remove_from_person($vars["_person"]);
            }
            else {
                $this->add_to_person($vars["_person"], $vars["_position"]);
            }
        }

    }

    function add_to_album($album_id) {
        $sql =
            "insert into " . DB_PREFIX . "photo_albums " .
            "(photo_id, album_id) values ('" .
            escape_string($this->get("photo_id")) . "', '" .
            escape_string($album_id) . "')";
        execute_query($sql, 1);
    }

    function remove_from_album($album_id) {
        $sql =
            "delete from " . DB_PREFIX . "photo_albums " .
            "where photo_id = '" . escape_string($this->get("photo_id")) . "'" .
            " and album_id = '" . escape_string($album_id) . "'";
        execute_query($sql, 1);
    }

    function add_to_category($category_id) {
        $sql =
            "insert into " . DB_PREFIX . "photo_categories " .
            "(photo_id, category_id) values ('" .
            escape_string($this->get("photo_id")) . "', '" .
            escape_string($category_id) . "')";
        execute_query($sql, 1);
    }

    function remove_from_category($category_id) {
        $sql =
            "delete from " . DB_PREFIX . "photo_categories " .
            "where photo_id = '" . escape_string($this->get("photo_id")) . "'" .
            " and category_id = '" . escape_string($category_id) . "'";
        execute_query($sql, 1);
    }

    function add_to_person($person_id, $position = "null") {
        if ($position && $position != "null") {
            $position = "'" . escape_string($position) . "'";
        }

        $sql =
            "insert into " . DB_PREFIX . "photo_people " .
            "(photo_id, person_id, position) " .
            "values ('" . escape_string($this->get("photo_id")) . "', '" .
            escape_string($person_id) . "', $position)";
        execute_query($sql);
    }

    function remove_from_person($person_id) {
        $sql =
            "delete from " . DB_PREFIX . "photo_people " .
            "where photo_id = '" . escape_string($this->get("photo_id")) . "'" .
            " and person_id = '" . escape_string($person_id) . "'";
        execute_query($sql);
    }

    function lookup_albums($user = null) {

        if ($user && !$user->is_admin()) {
            $sql =
                "select al.album_id, al.parent_album_id, al.album from " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "albums as al, " .
                DB_PREFIX . "album_permissions as ap " .
                "where pa.photo_id = '" .
                escape_string($this->get("photo_id")) . "'" .
                " and pa.album_id = al.album_id" .
                " and al.album_id = ap.album_id" .
                " and ap.user_id = '" .
                escape_string($user->get("user_id")) . "' " .
                " and ap.access_level >= " .
                escape_string($this->get("level")) . " " .
                "order by al.album";
        }
        else {
            $sql =
                "select al.album_id, al.parent_album_id, al.album from " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "albums as al " .
                "where pa.photo_id = '" .
                escape_string($this->get("photo_id")) . "'" .
                " and pa.album_id = al.album_id order by al.album";
        }

        return get_records_from_query("album", $sql);
    }

    function lookup_categories($user = null) {
        $sql =
            "select cat.category_id, cat.parent_category_id, cat.category from " .
            DB_PREFIX . "photo_categories as pc, " .
            DB_PREFIX . "categories as cat " .
            "where pc.photo_id = '" . escape_string($this->get("photo_id")) . "'" .
            " and pc.category_id = cat.category_id order by cat.category";

        return get_records_from_query("category", $sql);
    }

    function lookup_people() {
        $sql =
            "select psn.person_id, psn.last_name, " .
            "psn.first_name, psn.called from " .
            DB_PREFIX . "photo_people as pp, " .
            DB_PREFIX . "people as psn " .
            "where pp.photo_id = '" .
            escape_string($this->get("photo_id")) . "'" .
            " and pp.person_id = psn.person_id order by pp.position";

        return get_records_from_query("person", $sql);
    }

    function get_midsize_img() {
        return $this->get_image_tag(MID_PREFIX);
    }

    function get_thumbnail_link($link = null) {
        if (!$link) {
            $link = "photo.php?photo_id=" . $this->get("photo_id");
        }
        return "<a href=\"$link\">" . $this->get_image_tag(THUMB_PREFIX) . "</a>";
    }

    function get_fullsize_link($title) {
        $image = $this->get_image_href();
        return "<a href=\"$image\">$title</a>";
    }

    function get_image_href($type = null, $use_file = 0) {

        if (USE_IMAGE_SERVICE && !$use_file) {
            $image_href = "image_service.php?photo_id=" . $this->get("photo_id");
            if ($type) {
                $image_href .= "&type=" . $type;
            }

            if (SID) {
                $image_href .= "&" . SID;
            }
        }
        else {
            if ($use_file) {
                $dir = IMAGE_DIR;
            }
            else {
                $dir = WEB_IMAGE_DIR;
            }

            $image_href = $dir . $this->get("path") . "/";

            if ($type) {
                $image_href .= $type . "/" . $type . "_" .
                    get_converted_image_name($this->get("name"));
            }
            else {
                $image_href .= $this->get("name");
            }

            $image_href = encode_href($image_href);
        }

        return $image_href;
    }

    function get_image_tag($type = null) {

        $image_href = $this->get_image_href($type);

        if (!$image_href) {
            return "";
        }

        $size_string = "";

        $width = $this->get("width");
        $height = $this->get("height");

        if ($type) {
            if ($type == THUMB_PREFIX) {
                $max_side = THUMB_SIZE;
            }
            else if ($type == MID_PREFIX) {
                $max_side = MID_SIZE;
            }

            if ($max_side) {
                if (!$width || !$height) {
                    // pick some reasonable values
                    $width = $max_side;
                    $height = (int)round(0.75 * $width);
                }
                else if ($width >= $height) {
                    $height = (int)round(($max_side/$width) * $height);
                    $width = $max_side;
                }
                else {
                    $width = (int)round(($max_side/$height) * $width);
                    $height = $max_side;
                }
            }
        }

        $size_string = " width=\"$width\" height=\"$height\"";

        return "<img border=\"0\" src=\"$image_href\"" . $size_string . ">";
    }

    function rotate($deg) {

        if (!ALLOW_ROTATIONS || !$this->get('name') || !$this->get('path')) {
            return;
        }

        $dir = IMAGE_DIR . $this->get("path") . "/";
        $name = $this->get('name');

        $images[$dir . THUMB_PREFIX . '/' . THUMB_PREFIX . '_' . $name] =
            $dir . THUMB_PREFIX . '/rot_' . THUMB_PREFIX . '_' . $name;

        $images[$dir . MID_PREFIX . '/' . MID_PREFIX . '_' . $name] =
            $dir . MID_PREFIX . '/rot_' . MID_PREFIX . '_' . $name;

        $images[$dir . $name] = $dir . 'rot_' . $name;

        if (BACKUP_ORIGINAL) {
            $backup_name = BACKUP_PREFIX . $name;

            // file_exists() check from From Michael Hanke:
            // Once a rotation had occurred, the backup file won't be
            // overwritten by future rotations and the original file
            // is always preserved.
            if (!file_exists($dir . $backup_name)) {
                if (!copy($dir . $name, $dir . $backup_name)) {
                    echo sprintf(translate("Could not copy %s to %s."), $name, $backup_name) . "<br>\n";
                    return;
                }
            }
        }

        // make a system call to convert() to do the rotation
        // in the future, use PHP's imagerotate() function,
        // but it only appears >= 4.3.0 (and is buggy at the moment)
        while (list($file, $tmp_file) = each($images)) {

            /*
              From Michael Hanke:
              This is buggy, because non-quadratic images are truncated 
              The function goodrotate checks if images are nonquadratic

              This is not being used because, as Michael says,

              "I haven't found a reasonable way to preserve the exif-data
               stored in the original jpeg file. imagejpeg() (the gd
               function) doesn't write it into the exported image file.
               ... I propose to stick to 'convert' which keeps the exif
               metadata as it is."

              $imrot = @imagecreatefromjpeg($file);
              $new_image = $this->goodrotate($imrot, $deg);
              imagejpeg($new_image, $tmp_file, 95);
            */

            $cmd =
                'convert -rotate ' . escapeshellarg($deg) . ' ' .
                escapeshellarg($file) . ' ' . escapeshellarg($tmp_file) .
                ' 2>&1';
;
            //echo "$cmd<br>\n";
            $output = system($cmd);

            if ($output) { // error
                echo translate("An error occurred.") . " $output<br>\n";
                continue; // or return;
            }

            if (!rename($tmp_file, $file)) {
                echo sprintf(translate("Could not rename %s to %s."), $tmp_file, $file) . "<br>\n";
                continue; // or return;
            }

        }

        // update the size and dimensions
        // (only if original was rotated)
        $file = $dir . $name;
        $size = filesize($file);
        $dimensions = getimagesize($file);
        $this->set('size', $size);
        $this->set('width', $dimensions[0]);
        $this->set('height', $dimensions[1]);
        $this->update();

        return 1;
    }

    /*
     * Creates a jpeg photo with
     * text annotation at the bottom.
     *
     * Copyright 2003, Nixon P. Childs
     * License: The same as the rest of Zoph.
     */
    function annotate($vars, $user, $size = 'mid') {
        if ($vars['_size']) {
            $size = $vars['_size'];
        }

        if ($size == 'mid') {
            $font = 4;
            $padding = 2;
            $indent = 8;
        }
        else if ($size == 'full') {
            $font = 5;
            $padding = 2;
            $indent = 8;
        }
        else {
            return '';
        }

        /* ********************************
         *  Read in original image.
         *  Need to do now so we know
         *  the widhth of the text lines.
         * ********************************/

        $image_path = IMAGE_DIR . $this->get("path");
        if ($size == 'full') {
            $image_path .= "/" . $this->get("name");
        }
        else {
            $image_path .= "/" . $size . "/" . $size . "_" . $this->get("name");
        }

        $image_info = getimagesize($image_path);
        switch ($image_info[2]) {
            case 1:
                $orig_image = imagecreatefromgif($image_path);
                break;
            case 2:
                $orig_image = imagecreatefromjpeg($image_path);
                break;
            case 3:
                $orig_image = imagecreatefrompng($image_path);
                break;
            default:
                if (DEBUG) { echo "Unsupported image type."; }
                return '';
        }

        $row = ImageSY($orig_image) + ($padding/2);
        $maxWidthPixels = ImageSX($orig_image) - (2 * $indent);
        $maxWidthChars = floor($maxWidthPixels / ImageFontWidth($font)) - 1;

        /*
         * Sets fields from the given array.  Can be used to set vars
         * directly from a GET or POST.
         */
        reset($vars);
        $lines = 0;
        while (list($key, $val) = each($vars)) {

            // ignore empty keys or values
            if (empty($key) || $val == "") { continue; }

            if (strcmp(Substr($key, strlen($key) - 3), "_cb") == 0) {

                /* *****************************************
                 *  Everthing else uses the checkbox name
                 *  as the "get" key.
                 * *****************************************/

                $real_key = Substr($key, 0, strlen($key) - 3);
                $real_val = $vars[$real_key];
                remove_magic_quotes($real_val);

                /* *****************************************
                 *  Have to handle title separately because
                 *  breadcrumbs.inc.php assumes title is
                 *  the page title.
                 * *****************************************/

                if ($real_key == "photo_title") {
                   $real_key = "title";
                }
                else if ($real_key == "extra") {
                   $real_key = $vars["extra_name"];
                   remove_magic_quotes($real_key);
                }

                $out_array[$real_key] = $real_key . ": " . $real_val;
                $lines += ceil(strlen($out_array[$real_key]) / $maxWidthChars);
            }
        }

        /* **********************************************
         *  Create Image
         *  In order to create the text area, we must
         *  first create the text and determine how much
         *  space it requires.
         *
         *  I tried implode;wordwrap;explode, but
         *  wordwrap doesn't respect \n's in the text.
         *  To complicate things, ImageString just
         *  renders \n as an upside-down Y.
         *
         *  So the current solution is a little awkward,
         *  but it works.  The only (known) problem is
         *  that wrapped lines don't have the same
         *  right margin as non-wrapped lines.  This is
         *  because wordwrap doesn't take into account
         *  the line separation string.
         * **********************************************/


        /*
        $tmpString = implode("\n", $out_array);
echo ("tmpString:<br>\n" . $tmpString);
        $out_string = wordwrap($tmpString, floor($maxWidthPixels / ImageFontWidth($font)) - 1, "\n     ");
echo ("<br>\noutString:<br>\n" . $out_string);
        $formatted_array = explode("\n", $out_string);
        $lines = sizeof($formatted_array);
        */

        $count = 0;
        array($final_array);
        if ($out_array) {
            while (list($key, $val) = each($out_array)) {
                $tmp_array = explode("\n", wordwrap($val, $maxWidthChars, "\n   "));
                while (list($key1, $val1) = each($tmp_array)) {
                    $final_array[$count++] = $val1;
                }
            }
        }

        $noted_image = ImageCreateTrueColor (ImageSX($orig_image), ImageSY($orig_image) + ((ImageFontHeight($font) + $padding) * $count));
        $white = ImageColorAllocate($noted_image, 255,255, 255);

        /* ********************************
         *  Use a light grey background to
         *  hide the jpeg artifacts caused
         *  by the sharp edges in text.
         * ******************************/

        $offwhite = ImageColorAllocate($noted_image, 240,240, 240);
        ImageFill($noted_image, 0, ImageSX($orig_image) +1, $offwhite);
        $black = ImageColorAllocate($noted_image, 0, 0, 0);
        ImageColorTransparent($noted_image, $black);

        ImageCopy($noted_image, $orig_image, 0, 0, 0, 0, ImageSX($orig_image), ImageSY($orig_image));

        if ($final_array) {
            while (list($key, $val) = each($final_array)) {
                ImageString ($noted_image, $font, $indent, $row, $val, $black);
                $row += ImageFontHeight($font) + $padding;
            }
        }

        /*
        while (list($key, $val) = each($out_array)) {
            ImageStringWrap ($noted_image, $font, $padding, $row, $val, $black, $maxWidthPixels);
            $row += ImageFontHeight($font) + $padding;
            //echo ($val . "<br>");
        }
        */

        //$rnd_name = rand(1, 10000);
        //$temp_name = "zoph" . $user->get("user_id") . "_" . $rnd_name . $photo->get("name");

        $temp_name = $this->get_annotated_file_name($user);
        ImageJPEG($noted_image, ANNOTATE_TEMP_DIR . "/" . $temp_name);
        ImageDestroy($orig_image);
        ImageDestroy($noted_image);

        return $temp_name;
    }

    function get_annotated_file_name($user) {
        return ANNOTATE_TEMP_PREFIX . $user->get("user_id") . "_" . $this->get("name");
    }

    function get_display_array() {
        return array(
            translate("title") => $this->get("title"),
            translate("location") => $this->location
                ? $this->location->get_link() : "",
            translate("view") => $this->get("view"),
            translate("date") => create_date_link($this->get("date")),
            translate("time") => $this->get("time"),
            translate("rating") => $this->get("rating")
                ? $this->get("rating") . " / 10" : "",
            translate("photographer") => $this->photographer
                ? $this->photographer->get_link() : ""
        );
    }

    function get_email_array() {
        return array(
            "Title" => $this->get("title"),
            "Location" => $this->location
                ? $this->location->get("title") : "",
            "View" => $this->get("view"),
            "Date" => $this->get("date"),
            "Time" => $this->get("time"),
            "Photographer" => $this->photographer
                ? $this->photographer->get_name() : ""
        );
    }

    function get_camera_display_array() {
        return array(
            translate("camera make") => $this->get("camera_make"),
            translate("camera model") => $this->get("camera_model"),
            translate("flash used") => $this->get("flash_used"),
            translate("focal length") => $this->get("focal_length"),
            translate("exposure") => $this->get("exposure"),
            translate("aperture") => $this->get("aperture"),
            translate("compression") => $this->get("compression"),
            translate("iso equiv") => $this->get("iso_equiv"),
            translate("metering mode") => $this->get("metering_mode"),
            translate("focus distance") => $this->get("focus_dist"),
            translate("ccd width") => $this->get("ccd_width"),
            translate("comment") => $this->get("comment"));
    }

    function get_edit_array() {
        return array(
            "Title" => create_text_input("title", $this->title),
            "Date" => create_text_input("date", $this->date_taken),
            "Photographer" => create_text_input("photographer",
                $this->photographer ? $this->photographer->get_name() : ""),
            "Location" => create_text_input("location",
                $this->location ? $this->location->get_name() : ""),
            "View" => create_text_input("view", $this->view),
            "Level" => create_text_input("level", $this->level, 4, 2));
    }

}

function get_photo_sizes_sum() {
    $sql = "select sum(size) from " . DB_PREFIX . "photos";
    return get_count_from_query($sql);
}

function create_rating_graph($user) {

    if ($user && !$user->is_admin()) {
        $query =
            "select ph.rating, count(distinct ph.photo_id) as count from " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level " .
            "group by rating order by rating";
    }
    else {
        $query =
            "select rating, count(*) from " . DB_PREFIX . "photos " .
            "group by rating order by rating";
    }

    if (DEBUG) { echo "$query<br>\n"; }

    $result = mysql_query($query)
        or die_with_mysql_error("Rating grouping failed");

    $max_count = 0;
    while ($row = mysql_fetch_array($result)) {
        if ($row[0]) { $rating = $row[0]; }
        else { $rating = "null"; }

        $ratings[] = $rating;
        $counts[$rating] = $row[1];

        if ($row[1] > $max_count) { $max_count = $row[1]; }
    }

    if ($max_count) {

    $table =
        "<table>\n  <tr>\n    <th colspan=\"3\" align=\"center\">" .
        translate("photo ratings") . "</th>\n  </tr>\n  <tr>\n    <th>" .
        translate("rating") . "</th>\n    <th>&nbsp</th>\n    " .
        "<th>" . translate("count") . "</th>\n  </tr>\n";

    $scale = 20.0 / $max_count;

    foreach ($ratings as $rating) {
        $count = $counts[$rating];

        $table .=
            "  <tr>\n    <td align=\"right\">\n" .
            "      <a href=\"photos.php?rating=$rating\">$rating</a></td>\n" .
            "    <td>&nbsp;</td>\n    <td>\n";

        $ticks = ceil($scale * $count);
        while ($ticks > 0) {
            $table .= "*";
            $ticks--;
        }
        $table .= " [$count]\n    </td>\n  </tr>\n";
    }

    $table .="</table>\n";

    }
    else {
        $table .=
            "  <tr>\n    <td colspan=\"2\" align=\"center\">\n" .
            translate("No photo was found.") . "\n    </td>\n  </tr>\n";
    }

    return $table;
}

/*
 * Rotates (non-quadratic) images correctly using imagerotate().
 * It is currently not being used because it apparently does not
 * preserve exif info.
 *
 * This function provided by Michael Hanke, who found it on php.net.
 *
 *
 * (c) 2002 php at laer dot nu
 * Function to rotate an image
 */
function goodrotate($src_img, $degrees = 90) {
    // angles = 0°
    $degrees %= 360;
    if($degrees == 0) {
        $dst_img = $src_image;
    } Elseif ($degrees == 180) {
        $dst_img = imagerotate($src_img, $degrees, 0);
    } Else {
        $width = imagesx($src_img);
        $height = imagesy($src_img);
        if ($width > $height) {
           $size = $width;
        } Else {
           $size = $height;
        }
        $dst_img = imagecreatetruecolor($size, $size);
        imagecopy($dst_img, $src_img, 0, 0, 0, 0, $width, $height);
        $dst_img = imagerotate($dst_img, $degrees, 0);
        $src_img = $dst_img;
        $dst_img = imagecreatetruecolor($height, $width);
        if ((($degrees == 90) && ($width > $height)) || (($degrees == 270) && ($width < $height))) {
            imagecopy($dst_img, $src_img, 0, 0, 0, 0, $size, $size);
        }
        if ((($degrees == 270) && ($width > $height)) || (($degrees == 90) && ($width < $height))) {
            imagecopy($dst_img, $src_img, 0, 0, $size - $height, $size - $width, $size, $size);
        }
    }
    return $dst_img;
}

/*
 * For Nixon Childs' annotate function.
 *
 * Shamelessly stolen from the php.net comment board.
 */
function ImageStringWrap($image, $font, $x, $y, $text, $color, $maxwidth) {
    $fontwidth = ImageFontWidth($font);
    $fontheight = ImageFontHeight($font);

    if ($maxwidth != NULL) {
        $maxcharsperline = floor($maxwidth / $fontwidth);
        $text = wordwrap($text, $maxcharsperline, "\n", 1);
    }

    $lines = explode("\n", $text);
    while (list($numl, $line) = each($lines)) {
        ImageString($image, $font, $x, $y, $line, $color);
        $y += $fontheight;
    }
}

?>
