<?php

/*
 * A class corresponding to the places table.
 */
class place extends zoph_table {

    function place($id = 0) {
        parent::zoph_table("places", array("place_id"));
        $this->set("place_id", $id);
    }

    function get_name() {
        if ($this->get("title")) { return $this->get("title"); }

        if ($this->get("address")) { $name = $this->get("address"); }
        if ($this->get("city")) { $name .= ", " . $this->get("city"); }
        return $name;
    }

    function get_address() {
        $html = "";
        if ($this->get("address"))  {
            $html .= $this->get("address") . "<br>\n";
        }
        if ($this->get("address2")) {
            $html .= $this->get("address2") . "<br>\n";
        }
        if ($this->get("city")) { $html .= $this->get("city"); }
        if ($this->get("city") && $this->get("state")) { $html .= ", "; }
        if ($this->get("state")) { $html .= $this->get("state"); }
        if ($this->get("zip")) { $html .= " " . $this->get("zip"); }
        if ($this->get("country")) {
            $html .= "<br>\n" . $this->get("country") . "\n";
        }

        return $html;
    }

    function to_html() {

        $html = "";
        if ($this->get("title"))    {
            $html .= "<p><strong>" . $this->get("title") . "</strong></p>\n";
        }
        $html .= $this->get_address();

        return $html;
    }

    function get_link() {
        $link = "<a href=\"place.php?place_id=" . $this->get("place_id") . "\">" . $this->get_name() . "</a>";

        // add city link if title exists (and so was used by get_name())
        if ($this->get("title") && $this->get("city")) {
            $link .= ", <a href=\"places.php?_l=" . rawurlencode(strtolower($this->get("city"))) . "\">" . $this->get("city") . "</a>";
        }

        return $link;
    }


    function get_display_array() {
        return array(
            translate("address") => $this->get("address"),
            translate("address") . "2" => $this->get("address2"),
            translate("city") => $this->get("city"),
            translate("state") => $this->get("state"),
            translate("zip") => $this->get("zip"),
            translate("country") => $this->get("country"),
            translate("notes") => $this->get("notes"));
    }
}

function get_places($constraints = null, $conj = "and", $ops = null,
    $order = "city, title, address") {

    return get_records("place", $order, $constraints, $conj, $ops);
}

function get_photographed_places($user = null) {

    if ($user && !$user->is_admin()) {
        $query =
            "select distinct plc.* " .
            "from places as plc, photos as ph, " .
            "photo_albums as pa, album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "' " .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level" .
            " and ph.location_id = plc.place_id " .
            "order by plc.city, plc.title";
    }
    else {
        $query =
            "select distinct plc.* " .
            "from places as plc, photos as ph " .
            "where plc.place_id = ph.location_id " .
            "order by plc.city, plc.title";
    }

    return get_records_from_query("place", $query);
}

function get_places_select_array($user = null) {

    $plc[""] = "";
    if ($user) { // also used on search page
        $places_array = get_photographed_places($user);
    }
    else {
        $places_array = get_places();
    }

    if ($places_array) {
        foreach ($places_array as $place) {
            $c = $place->get("city");
            if (!$c) { $c = "[No City]"; }
            $t = $place->get("title");
            $plc[$place->get("place_id")] = $t ? "$c, $t" : "$c";
        }
    }

    return $plc;
}

function get_popular_places($user) {

    global $TOP_N;

    if ($user && !$user->is_admin()) {
        $sql =
            "select plc.*, count(*) as count " .
            "from places as plc, photos as ph, " .
            "photo_albums as pa, album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "' " .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level" .
            " and ph.location_id = plc.place_id " .
            "group by plc.place_id " .
            "order by count desc, plc.title, plc.city " .
            "limit 0, $TOP_N";
    }
    else {
        $sql =
            "select plc.*, count(*) as count " .
            "from places as plc, photos as ph " .
            "where plc.place_id = ph.location_id " .
            "group by plc.place_id " .
            "order by count desc, plc.title, plc.city " .
            "limit 0, $TOP_N";
    }

    return get_popular_results("place", $sql);

}

?>
