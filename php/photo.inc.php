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
        if($id && !is_numeric($id)) { die("photo_id must be numeric"); }
        parent::zoph_table("photos", array("photo_id"), array(""));
        $this->set("photo_id",$id);
    }

    function lookup($user = null) {

        if (!$this->get("photo_id")) { return; }

        if ($user && !$user->is_admin()) {
            $sql =
                "select p.* from " .
                DB_PREFIX . "photos as p JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON p.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE p.photo_id = '" . escape_string($this->get("photo_id")) . "'" .
                " AND gu.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " AND gp.access_level >= p.level " .
                "LIMIT 0, 1";
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

    function update($vars = null, $suffix = '', $user=null) {
        parent::update();
        if (!$vars) { return; }
        $this->update_relations($vars, $suffix, $user);
    }

    function update_relations($vars, $suffix = '', $user=null) {
        if ($vars["_album$suffix"]) {
            $this->add_to_album($vars["_album$suffix"], $user);
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
        for ($i = 0; $i < MAX_PEOPLE_SLOTS; $i++) {
           if ($vars["_person_" . $i . $suffix]) {
               $this->add_to_person($vars["_person_" . $i . $suffix], 
                  $vars["_position_" . $i . $suffix]);
           }
        }
	
        if ($vars["_remove_person$suffix"]) {
            $this->remove_from_person($vars["_remove_person$suffix"]);
        }
    }

    function add_to_album($album_id, $user=null) {
        // This is only done when either $user has write permissions to the
        // album, when the user is admin or when $user = null, this is to
        // retain compatibility with calls from import, which checks user
        // permissions before calling this function and bulk edit, which is
        // only accessible for admin users.
       $sql =
           "insert into " . DB_PREFIX . "photo_albums " .
           "(photo_id, album_id) values ('" .
            escape_string($this->get("photo_id")) . "', '" .
            escape_string($album_id) . "')";
        
        if($user) {
            $album_permissions=$user->get_album_permissions($album_id);

            if($user->is_admin() || $album_permissions->get("writable")) {
                query($sql);
            }
        } else {
            query($sql);
        }
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
            query($sql);
        }
    }

    function add_to_category($category_id) {
        $sql =
            "insert into " . DB_PREFIX . "photo_categories " .
            "(photo_id, category_id) values ('" .
            escape_string($this->get("photo_id")) . "', '" .
            escape_string($category_id) . "')";
        query($sql);
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
            query($sql);
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
        query($sql, "Failed to add person");
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
            query($sql);
        }
    }

    function lookup_albums($user = null) {

        if ($user && !$user->is_admin()) {
            $sql =
                "SELECT al.album_id, al.parent_album_id, al.album FROM " .
                DB_PREFIX . "albums AS al JOIN " .
                DB_PREFIX . "photo_albums AS pa " .
                "ON al.album_id = pa.album_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE pa.photo_id = '" .
                escape_string($this->get("photo_id")) . "'" .
                " AND gu.user_id = '" .
                escape_string($user->get("user_id")) . "' " .
                " AND gp.access_level >= " .
                escape_string($this->get("level")) .
                " ORDER BY al.album";
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
        $alt = escape_string($this->get("title"));
return "<img src=\"$image_href\" class=\"" . $type . "\" " . $size_string . " alt=\"$alt\"" . ">";
}

    function get_rating($user) {

        $photo_id = $this->get("photo_id");
        $user_id=$user->get("user_id");

        if ($user->get("allow_multirating")) {
            // This user is allowed to rate the same photoe  multiple 
            // times, however we will allow only one from the same IP
            $where = " and ipaddress = '" . 
                escape_string($_SERVER["REMOTE_ADDR"])."' ";
        }

        $query =
            "select rating from " . DB_PREFIX . "photo_ratings " .
            "where user_id = '" . escape_string($user_id) . "'" .
            " and photo_id = '". escape_string($this->get("photo_id")) . "'" .
            $where;

        $result = query($query, "Rating lookup failed");

        $rating = null;
        if ($row = fetch_array($result)) {
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
    function rate($user, $rating) {

        if (!$user || !$rating) {
            return null;
        }
        $user_id=$user->get("user_id");
        if(!($user->is_admin() || $user->get("allow_rating"))) {
            return;
        }

        $photo_id = $this->get("photo_id");

        if ($user->get("allow_multirating")) {
            // This user is allowed to rate the same photoe  multiple 
            // times, however we will allow only one from the same IP
            $where = " and ipaddress = '" . 
                escape_string($_SERVER["REMOTE_ADDR"])."' ";
        }

        $query =
            "select * from " . DB_PREFIX . "photo_ratings " .
            "where user_id = '" . escape_string($user_id) . "'" .
            " and photo_id = '". escape_string($photo_id) . "'" .
            $where;

        $result = query($query, "Rating lookup failed");

        //if the user has already voted, update the vote, else insert a new one

        if (num_rows($result) > 0) {
            $query =
                "update " . DB_PREFIX . "photo_ratings " .
                "set rating = '" . escape_string($rating) . "', " .
                " ipaddress = '" . escape_string($_SERVER["REMOTE_ADDR"])."' " .
                "where user_id = '" . escape_string($user_id) . "'" .
                " and photo_id = '". escape_string($photo_id) . "'" .
                $where . " LIMIT 1";
                // The limit makes sure only 1 vote is updated, this is 
                // needed if you ever change the allow_multirating to
                // 'no' and there already have been multiple votes
                // by this user. It will, however, simply update the first
                // vote it encounters...
        }
        else {
            $query =
                "insert into " . DB_PREFIX . "photo_ratings " .
                "(photo_id, user_id, ipaddress, rating) values " .
                " ('" . escape_string($photo_id) . "', '" .
                escape_string($user_id) . "', '" .
                escape_string($_SERVER["REMOTE_ADDR"])."', '" .
                escape_string($rating) . "')";
        }

        $result = query($query, "Rating input failed");

        //now recalculate the average, and input it in the photo table
        $this->recalculate_rating();
    }

    function recalculate_rating() {
        $photo_id = $this->get("photo_id");
        $query = "select avg(rating) from " . DB_PREFIX . "photo_ratings ".
            " where photo_id = '" . escape_string($photo_id) . "'";


        $result = query($query, "Rating recalculation failed");

        $row = fetch_array($result);

        $avg = (round(100 * $row[0])) / 100.0;
        
        if($avg == 0) {
            $avg = "null";
        }
      
        $query = "update " . DB_PREFIX . "photos set rating = $avg" .
            " where photo_id = '" . escape_string($photo_id) . "'";

        $result = query($query, "Inserting average rating failed");

        return $avg;
    }

    function delete_rating($rating_id) {
        if(!is_numeric($rating_id)) { 
            die("<b>rating_id</b> must be numeric!"); 
        }
        $sql = "DELETE FROM " . DB_PREFIX . "photo_ratings WHERE " .
            "rating_id = " . escape_string($rating_id);
        query($sql);
        $this->recalculate_rating();
        return;
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
        $converted_name = get_converted_image_name($name);

        $images[$dir . THUMB_PREFIX . '/' . THUMB_PREFIX . '_' . 
            $converted_name] = 
            $dir . THUMB_PREFIX . '/rot_' . THUMB_PREFIX . '_' . 
            $converted_name;

        $images[$dir . MID_PREFIX . '/' . MID_PREFIX . '_' . $converted_name] =
            $dir . MID_PREFIX . '/rot_' . MID_PREFIX . '_' . $converted_name;

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
        $datetime=$this->get_time();
        return array(
            translate("title") => $this->get("title"),
            translate("location") => $this->location
                ? $this->location->get_link() : "",
            translate("view") => $this->get("view"),
            translate("date") => create_date_link($datetime[0]),
            translate("time") => $this->get_time_details($datetime[1]),
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

    function get_time($timezone=null) {
        if(minimum_version("5.1.0")) {
            if(valid_tz($timezone)) {
                $place_tz=new TimeZone($timezone);
            } else { 
                $this->lookup_location();
                $loc=$this->location;
                if($loc && valid_tz($loc->get("timezone"))) {
                    $place_tz=new TimeZone($loc->get("timezone"));
                } 
            }
            if(valid_tz(CAMERA_TZ)) {
                $camera_tz=new TimeZone(CAMERA_TZ);
            }    
                
            if(!$place_tz && $camera_tz) {
                // Camera timezone is known, place timezone is not.
                $place_tz=$camera_tz;
            } else if ($place_tz && !$camera_tz) {
                // Place timezone is known, camera timezone is not.
                $camera_tz=$place_tz;
            } else if (!$place_tz && !$camera_tz) {
                // Neither are set
                $camera_tz=new TimeZone(date_default_timezone_get());
                $place_tz=$camera_tz;
            }
            
            $camera_time=new Time(
                $this->get("date") . " " .
                $this->get("time"),
                $camera_tz);
            $place_time=$camera_time;
            $place_time->setTimezone($place_tz);
            $corr=$this->get("time_corr");
            if($corr) {
                $place_time->modify($corr . " minutes");
            }
            
            $date=$place_time->format(DATE_FORMAT);
            $time=$place_time->format(TIME_FORMAT);
        } else {
            // Timezone support was introduced in PHP 5.1
            // so we'll just return date and time as they are
            // stored in db.
            $date=$this->get("date");
            $time=$this->get("time");
        }
        return array($date,$time);
    }

    function get_time_details($content, $open=false) {
        $html="&nbsp;";
        if ($open) {
            $html.="<span onclick=\"unbranch(this)\">-&nbsp;</span>";
        } else {
            $html.="<span onclick=\"branch(this)\">+&nbsp;</span>";
        }
        $html.="<span class='showhide'>" . $content . "</span>";
        $html.="<div class='timedetail'>\n<dl>\n";
        $html.="<h3>" . translate("database") . "</h3>\n";
        $html.="<dt>" . translate("date") . "</dt>\n";
        $html.="<dd>" . $this->get("date") . "</dd>\n";
        $html.="<dt>" . translate("time") . "</dt>\n";
        $html.="<dd>" . $this->get("time") . "</dd>\n";
        $html.="<dt>" . translate("timezone") . "</dt>\n";
        if(valid_tz(CAMERA_TZ)) {
            $html.="<dd>" . CAMERA_TZ . "</dd>\n<br>\n";
        } else {
            $html.="<dd><i>" . translate ("not set") . "</i></dd><br>\n";
        }
        $corr=$this->get("time_corr");
        if($corr) {
            $html.="<dt>" . translate("correction") . "</dt>\n";
            $html.="<dd>" . $corr . " " . translate("minutes") . "</dd>\n";
        }
        $html.="<br>";
        $this->lookup_location();
        $place=$this->location;
        if($place) {
            $html.="<h3>" . translate("location") . "</h3>\n";
            $html.="<dt>" . translate("location") . "</dt>\n";
            $html.="<dd>" . $place->get("title") . "</dd>\n";
            $html.="<dt>" . translate("timezone") . "</dt>\n";
            $tz=$place->get("timezone");
            $datetime=$this->get_time();
            if($tz) {
                $html.="<dd>" . $tz . "</dd>\n<br>\n";
            } else {
                $html.="<dd><i>" . translate ("not set") . "</i></dd><br>\n";
            }
        }
        $html.="<h3>" .translate("calculated time") . "</h3>\n";
        $html.="<dt>" . translate("date") . "</dt>\n";
        $html.="<dd>" . $datetime[0] . "</dd>\n";
        $html.="<dt>" . translate("time") . "</dt>\n";
        $html.="<dd>" . $datetime[1] . "</dd>\n";
        $html.="</dl>\n<br>\n</div>";
        return $html;
    }

    function get_rating_details($content, $open=false) {
        $sql="SELECT rating_id, user_id, rating, ipaddress, timestamp FROM " .
            DB_PREFIX . "photo_ratings WHERE photo_id=" .
            escape_string($this->get("photo_id"));

        $result=query($sql);
        
        $html="&nbsp;";
        if ($open) {
            $html.="<span onclick=\"unbranch(this)\">-&nbsp;</span>";
        } else {
            $html.="<span onclick=\"branch(this)\">+&nbsp;</span>";
        }
        $html.="<span class='showhide'>" . $content . "</span>";
        $html.="<div class='ratingdetail'>\n";
        $html.="<table class='ratingdetail'>\n<tr>\n";
        $html.="<th>" . translate("user") . "</th>";
        $html.="<th>" . translate("rating") . "</th>";
        $html.="<th>" . translate("IP address") . "</th>";
        $html.="<th>" . translate("date") . "</th></tr>";

        while($row=fetch_row($result)) {
            $html.="<tr>\n";
            $this_user=new user($row[1]);
            $this_user->lookup();
            $html.="<td>" . $this_user->get_link() . "</td>\n" .
                "<td>" . $row[2] . "</td>\n" .
                "<td>" . $row[3] . "</td>\n" .
                "<td>" . $row[4] . "</td>\n" .
                "<td><span class='actionlink'>" .
                "<a href='photo.php?_action=delrate" .
                "&photo_id=" . $this->get("photo_id") .
                "&_rating_id=" .  $row[0] . "'>" . 
                translate("delete") . "</a></span></td>" .
                "</tr>\n";
        }
        $html.="</table>\n</div>";
        return $html;
    }
    function get_comments() {
        $sql = "select comment_id from " . DB_PREFIX . "photo_comments where" .
            " photo_id = " .  $this->get("photo_id");
        $comments=get_records_from_query("comment", $sql);
        return $comments;
    }

    function get_related() {
        $sql = "select photo_id_1 as photo_id from " . 
            DB_PREFIX . "photo_relations where" .
            " photo_id_2 = " .  $this->get("photo_id") .
            " union select photo_id_2 as photo_id from " . 
            DB_PREFIX . "photo_relations where" .
            " photo_id_1 = " .  $this->get("photo_id");
        $related=get_records_from_query("photo", $sql);
        return $related;
    }

    function check_related($photo_id) {
        $related=$this->get_related();
        foreach($related as $rel_photo) {
            if ($rel_photo->get("photo_id") == $photo_id) {
                return true;
            }
        }
        return false;
    }
    function get_relation_desc($photo_id_2) {
        $sql = "select desc_1 from " . DB_PREFIX . "photo_relations where" .
            " photo_id_2 = " . escape_string($this->get("photo_id")) . " and " .
            " photo_id_1 = " . escape_string($photo_id_2) . 
            " union select desc_2 from " . DB_PREFIX . "photo_relations where" .
            " photo_id_1 = " . escape_string($this->get("photo_id")) . " and " .
            " photo_id_2 = " . escape_string($photo_id_2) . " limit 1";
        $result=query($sql, "Could not get description for related photo:");
        $result=fetch_row($result);
        return $result[0];
    }
    
    function create_relation($photo_id_2, $desc_1 = null, $desc_2 = null) {
        $sql = "insert into " . DB_PREFIX . "photo_relations values (" .
            escape_string($this->get("photo_id")) . "," .
            escape_string($photo_id_2) . "," .
            "\"" . escape_string($desc_1) . "\"," .
            "\"" . escape_string($desc_2) . "\")";
        $result=query($sql, "Could not create relation");
        }
        
    function update_relation($photo_id_2, $desc_1 = null, $desc_2 = null) {
        $photo_id_1=escape_string($this->get("photo_id"));
        $photo_id_2=escape_string($photo_id_2);
        $sql = "update " . DB_PREFIX . "photo_relations set" .
            " desc_1=\"" . escape_string($desc_1) . "\"," .
            " desc_2=\"" . escape_string($desc_2) . "\"" .
            " where photo_id_1=" . $photo_id_1 .
            " and photo_id_2=" . $photo_id_2;
        query($sql, "Could not update relation:");
        // A relation may be the other way around...
        $sql = "update " . DB_PREFIX . "photo_relations set" .
            " desc_2=\"" . escape_string($desc_1) . "\"," .
            " desc_1=\"" . escape_string($desc_2) . "\"" .
            " where photo_id_2=" . $photo_id_1 .
            " and photo_id_1=" . $photo_id_2;
        query($sql, "Could not update relation:");
    }
    
    function delete_relation($photo_id_2) {
        $ids="(" . escape_string($this->get("photo_id")) . "," .
            escape_string($photo_id_2) . ")"; 
        $sql = "delete from " . DB_PREFIX . "photo_relations" .
            " where photo_id_1 in " . $ids .
            " and photo_id_2 in " . $ids;
        $result=query($sql, "Could not delete relation:");
    }    
    
    function exif_to_html() {
        if (exif_imagetype($this->get_file_path())==IMAGETYPE_JPEG) {
            $exif=read_exif_data($this->get_file_path());
            if ($exif) {
                $return="<dl id=\"allexif\">\n";

                foreach($exif as $key => $value) {
                    if(!is_array($value)) {
                        $return .="    <dt>$key</dt>\n" .
                                  "    <dd>" . preg_replace("/[^[:print:]]/", "", $value) . "</dd>\n";
                    } else {
                        $return .="    <dt>$key</dt>\n" .
                                  "    <dd>\n" .
                                  "        <dl>\n";
                        foreach ($value as $subkey => $subval) {
                            $return .= "            <dt>$subkey</dt>\n" .
                                       "            <dd>" . preg_replace("/[^[:print:]]/", "", $subval) . "</dd>\n";
                        }
                        $return .= "         </dl>\n" .
                                   "    </dd>\n";
                    }
                }
                $return .= "</dl><br>";
            } else {
                $return=false;
            }
        } else {
            $return=false;
        }
        return $return;
    }

    function get_quicklook($user) {
        $title=escape_string($this->get("title"));
        $file=$this->get("name");

        if($title) {
            $html="<h2>" . $title . "</h2><p>" . $file . "</p>";
        } else {
            $html="<h2>" . $file . "</h2>";
        }    
        $html.=$this->get_thumbnail_link() .
          "<p><small>" . 
          $this->get("date") . " " . $this->get("time") . "<br>" .
          translate("by",0) . " " . $this->photographer->get_link(1) . "<br>" .
          "</small></p>";
        return $html;
    }

    function get_marker($user, $check_loc=true) {
        $icon=ICONSET . "/geo-photo.png";
        $js=parent::get_marker($user, $icon); 
        if(!$js && $check_loc) {
            $loc=$this->location;
            if($loc) {
                $js=$loc->get_marker($user); 
            }
        }
        if($js) {
            return($js);
        } else {
            return null;
        }
    }

    function get_mapping_js($user,$edit=false) {
         return parent::get_mapping_js($user, ICONSET . "/geo-photo.png", $edit);
    }

    function get_near($distance, $limit=100, $entity="km") { 
        $lat=$this->get("lat");
        $lon=$this->get("lon");

        if($lat && $lon) {
            // If lat and lon are not set, don't bother trying to find
            // near photos
            if($entity=="miles") {
                $distance=$distance * 1.609344;
            }
            if($limit) {
                $lim=" limit 0,". $limit;
            }
            $sql="select photo_id, (6371 * acos(" .
                "cos(radians(" . $lat . ")) * " .
                "cos(radians(lat) ) * cos(radians(lon) - " .
                "radians(" . $lon . ")) +" . 
                "sin(radians(" . $lat . ")) * " .
                "sin(radians(lat)))) AS distance from " .
                DB_PREFIX . "photos " .
                "having distance <= " . $distance . 
                " order by distance" . $lim;

            $near=get_records_from_query("photo", $sql);
            return $near;
        } else {
            return null;
        }
    }


}

function get_photo_sizes_sum() {
    $sql = "select sum(size) from " . DB_PREFIX . "photos";
    return get_count_from_query($sql);
}

function get_filesize($photos, $human=false) {
    $bytes=0;
    foreach($photos as $photo) {
//    var_dump($photo);   #->get("size");
        $photo->lookup();
        $bytes+=$photo->get("size");
    }

    if($human) {
        return get_human($bytes);
    } else {
        return $bytes;
    }
}
function create_rating_graph($user) {

    if ($user && !$user->is_admin()) {
        $query =
            "select floor(ph.rating+0.5), " . 
            "count(distinct ph.photo_id) as count from " .
            DB_PREFIX . "photos as ph JOIN " .
            DB_PREFIX . "photo_albums as pa " .
            "ON ph.photo_id = pa.photo_id JOIN " .
            DB_PREFIX . "group_permissions as gp " .
            "ON pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu " .
            "ON gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) .
            "' AND gp.access_level >= ph.level " .
            "GROUP BY floor(rating+0.5) ORDER BY floor(rating+0.5)";
    } else {
        $query =
            "select floor(rating+0.5), count(*) from " . DB_PREFIX . "photos " .
            "group by floor(rating+0.5) order by floor(rating+0.5)";
    }

    $result = query($query, "Rating grouping failed");

    while ($row = fetch_array($result)) {
    	$ratings[($row[0] ? $row[0] : translate("Not rated"))]=$row[1];
	}
    $html="<h3>" . translate("photo ratings") . "</h3>";
    $legend=array(translate("rating"),translate("count"));
    while (list($range, $count) = each($ratings)) {
        if($range>0) {
            $min_rating=$range-0.5;
	        $max_rating=$range+0.5;
            $link =
              "search.php?rating%5B0%5D=" . $min_rating . 
              "&amp;_rating_op%5B0%5D=%3E%3D" .
              "&amp;rating%5B1%5D=" . $max_rating . 
              "&amp;_rating_op%5B1%5D=%3C&amp;_action=" . translate("search");
        } else {
            $link = "photos.php?rating=null";
        }  
        $row=array($range, $link, $count);
        $value_array[]=$row;
    }

    if($value_array) {
        $html.=create_bar_graph($legend, $value_array, 150);
    } else {
        $html.=translate("No photo was found.") . "\n";
    }

    return $html;
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
