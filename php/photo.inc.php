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
            "select ph.rating, count(*) as count from " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id " .
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

?>
