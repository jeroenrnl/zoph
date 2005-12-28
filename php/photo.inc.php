<?php

/*
 * A class corresponding to the photos table.
 *
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

class photo extends zoph_table {

    var $photographer;
    var $location;

    function photo($id = 0) {
        parent::zoph_table("photos", array("photo_id"), array(""));
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

    function update($vars = null, $suffix = '') {
        parent::update();

        if (!$vars) { return; }

        $this->update_relations($vars, $suffix);
    }

    function update_relations($vars, $suffix = '') {
        if ($vars["_album$suffix"]) {
            $this->add_to_album($vars["_album$suffix"]);
        }

        if ($vars["_remove_album$suffix"]) {
            $this->remove_from_album($vars["_remove_album$suffix"]);
        }

        if ($vars["_category$suffix"]) {
            $this->add_to_category($vars["_category$suffix"]);
        }

        if ($vars["_remove_category$suffix"]) {
            $this->remove_from_category($vars["_remove_category$suffix"]);
        }

        if ($vars["_person$suffix"]) {
            $this->add_to_person($vars["_person$suffix"], $vars["_position$suffix"]);
        }

        if ($vars["_remove_person$suffix"]) {
            $this->remove_from_person($vars["_remove_person$suffix"]);
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

    function remove_from_album($album_ids) {
        if (!is_array($album_ids)) {
            $album_ids = array($album_ids);
        }

        foreach ($album_ids as $album_id) {
            $sql =
                "delete from " . DB_PREFIX . "photo_albums " .
                "where photo_id = '" . escape_string($this->get("photo_id")) . "'" .
                " and album_id = '" . escape_string($album_id) . "'";
            execute_query($sql, 1);
        }
    }

    function add_to_category($category_id) {
        $sql =
            "insert into " . DB_PREFIX . "photo_categories " .
            "(photo_id, category_id) values ('" .
            escape_string($this->get("photo_id")) . "', '" .
            escape_string($category_id) . "')";
        execute_query($sql, 1);
    }

    function remove_from_category($category_ids) {
        if (!is_array($category_ids)) {
            $category_ids = array($category_ids);
        }

        foreach ($category_ids as $category_id) {
            $sql =
                "delete from " . DB_PREFIX . "photo_categories " .
                "where photo_id = '" . escape_string($this->get("photo_id")) . "'" .
                " and category_id = '" . escape_string($category_id) . "'";
            execute_query($sql, 1);
        }
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

    function remove_from_person($person_ids) {
        if (!is_array($person_ids)) {
            $person_ids = array($person_ids);
        }

        foreach ($person_ids as $person_id) {
            $sql =
                "delete from " . DB_PREFIX . "photo_people " .
                "where photo_id = '" . escape_string($this->get("photo_id")) . "'" .
                " and person_id = '" . escape_string($person_id) . "'";
            execute_query($sql);
        }
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

    function get_file_path() {
        return IMAGE_DIR . $this->get("path") . "/" . $this->get("name");
    }

    function get_midsize_img() {
        return $this->get_image_tag(MID_PREFIX);
    }

    function get_thumbnail_link($link = null) {
        if (!$link) {
            $link = "photo.php?photo_id=" . $this->get("photo_id");
        }
        return "            <a href=\"$link\">" . $this->get_image_tag(THUMB_PREFIX) . "</a>";
    }

    function get_fullsize_link($title, $FULLSIZE_NEW_WIN) {
        $image = $this->get_image_href();
        $newwin = ($FULLSIZE_NEW_WIN ? "target=\"_blank\"" : "");
        return "<a href=\"$image\" $newwin>$title</a>";
    }

    function get_image_href($type = null, $use_file = 0) {

        if (USE_IMAGE_SERVICE && !$use_file) {
            $image_href = "image_service.php?photo_id=" . $this->get("photo_id");
            if ($type) {
                $image_href .= "&amp;type=" . $type;
            }

            if (SID) {
                $image_href .= "&amp;" . SID;
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
        $alt = $this->get("title");
return "<img src=\"$image_href\" class=\"" . $type . "\" " . $size_string . "alt=\"$alt\"" . ">";
}

    function get_rating($user_id) {

        $photo_id = $this->get("photo_id");

        $query =
            "select rating from " . DB_PREFIX . "photo_ratings " .
            "where user_id = '" . escape_string($user_id) . "'" .
            " and photo_id = '". escape_string($this->get("photo_id")) . "'";

        if (DEBUG > 1) { echo "$query<br>\n"; }

        $result = mysql_query($query)
            or die_with_mysql_error("Rating lookup failed");

        $rating = null;
        if ($row = mysql_fetch_array($result)) {
            $rating = $row[0];
        }

        return $rating;
    }

    /*
     * Stores the rating of a photo for a user and updates the
     * average rating.
     *
     * This function from Jan Miczaika
     */
    function rate($user_id, $rating) {

        if (!$user_id || !$rating) {
            return null;
        }

        $photo_id = $this->get("photo_id");

        $query =
            "select * from " . DB_PREFIX . "photo_ratings " .
            "where user_id = '" . escape_string($user_id) . "'" .
            " and photo_id = '". escape_string($photo_id) . "'";

        if (DEBUG > 1) { echo "$query<br>\n"; }

        $result = mysql_query($query)
            or die_with_mysql_error("Rating lookup failed");

        //if the user has already voted, update the vote, else insert a new one

        if (mysql_num_rows($result) > 0) {
            $query =
                "update " . DB_PREFIX . "photo_ratings " .
                "set rating = '" . escape_string($rating) . "' " .
                "where user_id = '" . escape_string($user_id) . "'" .
                " and photo_id = '". escape_string($photo_id) . "'";
        }
        else {
            $query =
                "insert into " . DB_PREFIX . "photo_ratings " .
                "(photo_id, user_id, rating) values " .
                " ('" . escape_string($photo_id) . "', '" .
                escape_string($user_id) . "', '" .
                escape_string($rating) . "')";
        }

        if (DEBUG > 1) { echo "$query<br>\n"; }

        $result = mysql_query($query)
            or die_with_mysql_error("Rating input failed");

        //now recalculate the average, and input it in the photo table

        $query = "select avg(rating) from " . DB_PREFIX . "photo_ratings ".
            " where photo_id = '" . escape_string($photo_id) . "'";

        if (DEBUG > 1) { echo "$query<br>\n"; }

        $result = mysql_query($query)
            or die_with_mysql_error("Rating recalculation failed");

        $row = mysql_fetch_array($result);

        $avg = (round(100 * $row[0])) / 100.0;

        $query = "update " . DB_PREFIX . "photos set rating = $avg" .
            " where photo_id = '" . escape_string($photo_id) . "'";

        if (DEBUG > 1) { echo "$query<br>\n"; }

        $result = mysql_query($query)
            or die_with_mysql_error("Inserting new rating failed");

        return $avg;
    }

    function get_image_resource() {
        $file = $this->get_file_path();
        $img_src = null;
        $image_info = getimagesize($file);
        switch ($image_info[2]) {
            case 1:
                $img_src = imagecreatefromgif($file);
                break;
            case 2:
                $img_src = imagecreatefromjpeg($file);
                break;
            case 3:
                $img_src = imagecreatefrompng($file);
                break;
            default:
                break;
        }

        return $img_src;
    }

    function thumbnail($img_src = null) {
        return
            $this->create_thumbnail(THUMB_PREFIX, THUMB_SIZE, $img_src) &&
            $this->create_thumbnail(MID_PREFIX, MID_SIZE, $img_src);
    }

    function create_thumbnail($prefix, $size, $img_src) {
        $destroy = false;
        if ($img_src == null) {
            $img_src = $this->get_image_resource();
            $destroy = true;
        }

        $image_info = getimagesize($this->get_file_path());
        $width = $image_info[0];
        $height = $image_info[1];

        if ($width >= $height) {
            $new_width = $size;
            $new_height = round(($new_width / $width) * $height);
        }
        else {
            $new_height = $size;
            $new_width = round(($new_height / $height) * $width);
        }

        $img_dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0,
            $new_width, $new_height, $width, $height);

        $new_image = IMAGE_DIR . $this->get("path") . '/' . $prefix . '/' .
            $prefix . '_' .  get_converted_image_name($this->get("name"));

        $image_type = get_image_type($new_image);

        // a little fast a loose but usually ok
        $func = "image" . substr($image_type, strpos($image_type, '/') + 1);

        $return = 1;
        if (!$func($img_dst, $new_image)) {
            $return = 0;
        }

        imagedestroy($img_dst);

        if ($destroy) {
            imagedestroy($img_src);
        }

        return $return;
    }

    function rotate($deg) {
/*
        This line breaks things if dated-dirs are not used: in that case the path field is empty...
        if (!ALLOW_ROTATIONS || !$this->get('name') || !$this->get('path')) {
            return;
        }
*/
        if (!ALLOW_ROTATIONS || !$this->get('name')) {
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

        // make a system call to convert or jpegtran to do the rotation.
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

            $cmd = ROTATE_CMD;
            if (strpos(" $cmd", 'jpegtran')) {
                $cmd .= ' -copy all -rotate ' .  escapeshellarg($deg) .
                    ' -outfile ' .  escapeshellarg($tmp_file) . ' ' .
                    escapeshellarg($file);
            }
            else if (strpos(" $cmd", 'convert')) {
                $cmd .= ' -rotate ' . escapeshellarg($deg) . ' ' .
                    escapeshellarg($file) . ' ' . escapeshellarg($tmp_file);
            }

            $cmd .= ' 2>&1';

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
         *  the width of the text lines.
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

                $out_array[$real_key] = translate($real_key, 0) . ": " .
                    $real_val;
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
        ImageFill($noted_image, 0, ImageSY($orig_image) +1, $offwhite);
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
            translate("photographer") => $this->photographer
                ? $this->photographer->get_link() : ""
        );
    }

    function get_email_array() {
        return array(
            translate("title") => $this->get("title"),
            translate("location") => $this->location
                ? $this->location->get("title") : "",
            translate("view") => $this->get("view"),
            translate("date") => $this->get("date"),
            translate("time") => $this->get("time"),
            translate("photographer") => $this->photographer
                ? $this->photographer->get_name() : "",
            translate("description") => $this->get("description")
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
            "select round(ph.rating), count(distinct ph.photo_id) as count from " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level " .
            "group by round(rating) order by round(rating)";
    }
    else {
        $query =
            "select round(rating), count(*) from " . DB_PREFIX . "photos " .
            "group by round(rating) order by round(rating)";
    }

    if (DEBUG) { echo "$query<br>\n"; }

    $result = mysql_query($query)
        or die_with_mysql_error("Rating grouping failed");

    $max_count = 0;
    while ($row = mysql_fetch_array($result)) {
        $max_count = max($max_count, $row[1]);	
    	$ratings[($row[0] ? $row[0] : translate("Not rated"))]=$row[1];
	}

    if ($max_count) { 
    $table =
        "<table class=\"ratings\">\n  <tr>\n    <th colspan=\"3\"><h3>" .
        translate("photo ratings") . "</h3></th>\n  </tr>\n  <tr>\n    <th>" .
        translate("rating") . "</th>\n    <th>&nbsp;</th>\n    " .
        "<th>" . translate("count") . "</th>\n  </tr>\n";

    $scale = 150.0 / $max_count;
	
    while (list($range, $count) = each($ratings)) {
        if($range>0) {
	   $min_rating=$range-0.5;
	   $max_rating=$range+0.5;
           $qs =
              "photos.php?rating=" . $min_rating . "&_rating-op=%3E%3D" .
              "&rating%232=" . $max_rating . "&_rating-op%232=%3C";
        } else {
           $qs = "photos.php?rating=null";
        }  
        $table .=
            "  <tr>\n    <td>\n" .
            "      <a href=\"$qs\">$range</a></td>\n" .
            "    <td>&nbsp;</td>\n    <td>\n";

	$table .= "<div class=\"ratings\" style=\"width: " . ceil($scale * $count) . "px;\">&nbsp;</div>";
        $table .= "[$count]\n    </td>\n  </tr>\n";
    }

    $table .="</table>\n";

    }
    else {
        $table .=
            "  <tr>\n    <td colspan=\"2\" class=\"center\">\n" .
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
