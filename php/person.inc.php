<?php

/*
 * A class corresponding to the people table.
 */
class person extends zoph_table {

    var $home;
    var $work;

    function person($id = 0) {
        parent::zoph_table("people", array("person_id"));
        $this->set("person_id", $id);
    }

    function lookup() {
        parent::lookup();
        $this->lookup_places();
    }

    function lookup_places() {
        if ($this->get("home_id") > 0) {
            $this->home = new place($this->get("home_id"));
            $this->home->lookup();
        }
        if ($this->get("work_id") > 0) {
            $this->work = new place($this->get("work_id"));
            $this->work->lookup();
        }
    }

    function delete() {
        parent::delete(null, array("photo_people"));
    }

    function get_gender() {
        if ($this->get("gender") == 1) { return translate("male"); }
        if ($this->get("gender") == 2) { return translate("female"); }
        return;
    }

    function get_father() {
        return get_person($this->get("father_id"));
    }

    function get_mother() {
        return new person($this->get("mother_id"));
    }

    function get_spouse() {
        return new person($this->get("spouse_id"));
    }

    function get_children() {
        $constraints["falther_id"] = $this->get("father_id");
        $constraints["mother_id"] = $this->get("mother_id");
        return get_people($constraints, "or", "dob");
    }

    function get_name() {
        if ($this->get("called")) {
            $name = $this->get("called");
        }
        else {
            $name = $this->get("first_name");
        }

        if ($this->get("last_name")) {
            $name .= " " . $this->get("last_name");
        }

        return $name;
    }

    function to_html() {
        return get_name();
    }

    function get_link($show_last_name = 1) {
        if ($show_last_name) {
            $name = $this->get_name();
        }
        else {
            $name = $this->get("called") ? $this->get("called") :
                $this->get("first_name");
        }

        return "<a href=\"person.php?person_id=" . $this->get("person_id") . "\">$name</a>";
    }

    function get_display_array() {
        return array(
            translate("called") => $this->get("called"),
            translate("date of birth") => create_date_link($this->get("dob")),
            translate("date of death") => create_date_link($this->get("dod")),
            translate("gender") => $this->get_gender(),
            translate("mother") => get_link("person", $this->get("mother_id")),
            translate("father") => get_link("person", $this->get("father_id")),
            translate("spouse") => get_link("person", $this->get("spouse_id")));
    }

}

function get_people($constraints = null, $conj = "and", $ops = null,
    $order = "last_name, first_name") {

    return get_records("person", $order, $constraints, $conj, $ops);
}

function get_photographed_people($user = null) {

    if ($user && !$user->is_admin()) {
        $query =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_people as pp, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "' " .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level" .
            " and ph.photo_id = pp.photo_id " .
            " and pp.person_id = ppl.person_id " .
            "order by ppl.last_name, ppl.called, ppl.first_name";
    }
    else {
        $query =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photo_people as pp " .
            "where ppl.person_id = pp.person_id " .
            "order by ppl.last_name, ppl.called, ppl.first_name";
    }

    return get_records_from_query("person", $query);
}

function get_photographers($user = null) {

    if ($user && !$user->is_admin()) {
        $query =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "' " .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level" .
            " and ph.photographer_id = ppl.person_id " .
            "order by ppl.last_name, ppl.called, ppl.first_name";
    }
    else {
        $query =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photos as ph " .
            "where ppl.person_id = ph.photographer_id " .
            "order by ppl.last_name, ppl.called, ppl.first_name";
    }

    return get_records_from_query("person", $query);
}

function get_people_select_array($people_array = null) {

    $ppl[""] = "";

    if (!$people_array) {
        $people_array = get_people();
    }

    if ($people_array) {
        foreach ($people_array as $person) {
            $ppl[$person->get("person_id")] =
                 ($person->get("last_name") ? $person->get("last_name") .  ", " : "") .
                 ($person->get("called") ? $person->get("called") : $person->get("first_name"));
        }
    }

    return $ppl;
}


function get_photo_person_links($photo) {

    $links = "";
    if (!$photo) { return $links; }
    $people = $photo->lookup_people();
    if ($people) {
        foreach ($people as $person) {
            if ($links) { $links .= ", "; }
            $links .= $person->get_link(0);
        }
    }

    return $links;
}

function get_person_by_name($first_name = null, $last_name = null) {
    if (!$first_name && !$last_name) {
        return "";
    }

    if ($first_name) {
        $first_name =
            "lower(first_name) like '%" . escape_string(strtolower($first_name)) . "%'";
    }

    if ($last_name) {
        $last_name =
            "lower(last_name) like '%" . escape_string(strtolower($last_name)) . "%'";
    }

    $where = $first_name;
    if ($first_name && $last_name) {
        $where .= " and ";
    }
    $where .= $last_name;

    $query = "select person_id from " . DB_PREFIX . "people where $where";

    return get_records_from_query("person", $query);
}

function get_popular_people($user) {

    global $TOP_N;

    if ($user && !$user->is_admin()) {
        $sql =
            "select ppl.*, count(*) as count from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photo_people as pp, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "' " .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = pp.photo_id" .
            " and pp.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level" .
            " and pp.person_id = ppl.person_id " .
            "group by ppl.person_id " .
            "order by count desc, ppl.last_name, ppl.first_name " .
            "limit 0, $TOP_N";
    }
    else {
        $sql =
            "select ppl.*, count(*) as count from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photo_people as pp " .
            "where ppl.person_id = pp.person_id " .
            "group by ppl.person_id " .
            "order by count desc, ppl.last_name, ppl.first_name " .
            "limit 0, $TOP_N";
    }

    return get_popular_results("person", $sql);

}

?>
