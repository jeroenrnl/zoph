<?php

/*
 * A class corresponding to the people table.
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

class person extends zoph_table {

    var $home;
    var $work;

    function person($id = 0) {
        if($id && !is_numeric($id)) { die("person_id must be numeric"); }
        parent::zoph_table("people", array("person_id"), array("first_name"));
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
        $id=escape_string($this->get("person_id"));
        if (!is_numeric($id)) { die("person_id is not numeric"); }
        $sql="update " . DB_PREFIX . "people set father_id=null " .
            "where father_id=" .  $id;
        query($sql, "Could not remove references:");

        $sql="update " . DB_PREFIX . "people set mother_id=null " . 
            "where mother_id=" .  $id;
        query($sql, "Could not remove references:");
        
        $sql="update " . DB_PREFIX . "people set spouse_id=null " .
            "where spouse_id=" .  $id;
        query($sql, "Could not remove references:");
        
        $sql="update " . DB_PREFIX . "photos set photographer_id=null where " .
            "photographer_id=" .  $id;
        query($sql, "Could not remove references:");
        
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
        $constraints["father_id"] = $this->get("person_id");
        $constraints["mother_id"] = $this->get("person_id");
        return get_people($constraints, "or");
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

    function get_email() {
       $email = $this->get("email");
       return $email;
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
    function xml_rootname() {
        return "people";
    }

    function xml_nodename() {
        return "person";
    }

    function get_coverphoto($user,$autothumb=null) {
        if ($this->get("coverphoto")) {
            $coverphoto=new photo($this->get("coverphoto"));
            if(!$coverphoto->lookup($user)) {
                unset($coverphoto);
            }
        } 
        if ($autothumb && !$coverphoto) {
            $order=get_autothumb_order($autothumb);
            if ($user && !$user->is_admin()) {
                $sql=
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos AS p JOIN " .
                    DB_PREFIX . "photo_albums AS pa " .
                    "ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "group_permissions AS gp " .
                    "ON pa.album_id = gp.album_id JOIN " .
                    DB_PREFIX . "groups_users AS gu " .
                    "ON gp.group_id = gu.group_id JOIN " .
                    DB_PREFIX . "photo_people AS pp " .
                    "ON pp.photo_id = p.photo_id " .
                    "WHERE pp.person_id = " . 
                    escape_string($this->get("person_id")) .
                    " AND gu.user_id =" .
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " AND gp.access_level >= p.level " .
                    $order;
            } else {
                $sql =
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_people as pp" .
                    " ON pp.photo_id = p.photo_id " .
                    " WHERE pp.person_id = " . 
                    escape_string($this->get("person_id")) .
                    " " . $order;
            }
            $coverphoto=array_shift(get_records_from_query("photo", $sql));
        }

        if ($coverphoto) {
            $coverphoto->lookup();
            return $coverphoto->get_image_tag(THUMB_PREFIX);
        }
    }
 
}

function get_people($constraints = null, $conj = "and", $ops = null,
    $order = "last_name, first_name", $user=null) {
    return get_records("person", $order, $constraints, $conj, $ops);
}

function get_people_count($user = null, $search = null) {
    if($user && !$user->is_admin()) {
        $allowed=array();
        $people=get_photographed_people($user, $search);
        $photographers=get_photographers($user, $search);
        foreach($people as $person) {
            $allowed[]=$person->get("person_id");
        }
        foreach($photographers as $photographer) {
            $allowed[]=$photographer->get("person_id");
        }

        $allowed=array_unique($allowed);

        return count($allowed);
    } else {
        return get_count("person");
    }
}

function get_all_people($user = null, $search = null, $search_first = false) {
    $allowed=array();

    if($user && !$user->is_admin()) {
        $people=get_photographed_people($user, $search, $search_first);
        $photographers=get_photographers($user, $search, $search_first);
        foreach($people as $person) {
            $allowed[]=$person->get("person_id");
        }
        foreach($photographers as $photographer) {
            $allowed[]=$photographer->get("person_id");
        }

        $allowed=array_unique($allowed);
        if(count($allowed)==0) {
            return null;
        }
        $keys=implode(",", $allowed);
        $where=" WHERE person_id IN (" .$keys . ")";
    } else if ($search!==null) {
        $where=get_where_for_search(" WHERE ", $search, $search_first);
    }

    $sql="SELECT * FROM " . DB_PREFIX . "people AS ppl " . $where .
        " ORDER BY last_name, called, first_name";

    return get_records_from_query("person", $sql);
}

function get_photographed_people($user = null, $search=null, $search_first = false) {
    $where=get_where_for_search(" and ", $search, $search_first);
    if ($user && !$user->is_admin()) {
        $sql =
            "select distinct ppl.* from " .
            DB_PREFIX . "people AS ppl JOIN " .
            DB_PREFIX . "photo_people AS pp " .
            "ON ppl.person_id = pp.person_id JOIN " . 
            DB_PREFIX . "photos AS ph " .
            "ON ph.photo_id = pp.photo_id JOIN " .
            DB_PREFIX . "photo_albums AS pa " .
            "ON pa.photo_id = ph.photo_id JOIN " .
            DB_PREFIX . "group_permissions as gp " .
            "ON pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu " .
            "ON gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) . "' " .
            " AND gp.access_level >= ph.level" . $where .
            " ORDER BY ppl.last_name, ppl.called, ppl.first_name";
    }
    else {
        $sql =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photo_people as pp " .
            "where ppl.person_id = pp.person_id " . $where .
            " order by ppl.last_name, ppl.called, ppl.first_name";
    }

    return get_records_from_query("person", $sql);
}

function get_photographers($user = null, $search = null, $search_first = null) {
    $where=get_where_for_search(" and ", $search, $search_first);
    if ($user && !$user->is_admin()) {
        $sql =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl " .
            "WHERE person_id in " .
            "(SELECT photographer_id FROM " .
            DB_PREFIX . "photos as ph JOIN " .
            DB_PREFIX . "photo_albums as pa " .
            "ON pa.photo_id = ph.photo_id JOIN " .
            DB_PREFIX . "group_permissions AS gp " .
            "ON pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu " .
            "ON gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) . "' " .
            $where .
            " AND gp.access_level >= ph.level)" .
            " ORDER BY ppl.last_name, ppl.called, ppl.first_name";
    }
    else {
        $sql =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photos as ph " .
            "where ppl.person_id = ph.photographer_id " . $where . 
            "order by ppl.last_name, ppl.called, ppl.first_name";
    }

    return get_records_from_query("person", $sql);
}

function get_where_for_search($conj, $search, $search_first) {
    if($search!==null) {
        if($search==="") {
            $where=$conj . " (ppl.last_name='' or ppl.last_name is null)";
        } else {
            $search=escape_string($search);
            $where=$conj . " (ppl.last_name like lower('" . $search . "%')";
            if ($search_first) {
                $where.="or ppl.first_name like lower('" . $search . "%'))";
            } else {
                $where.=")";
            }
        }
    }
    return $where;
}

function get_people_select_array($people_array = null, $user = null) {

    $ppl[""] = "";

    if (!$people_array) {
        $people_array = get_people(null,null,null,"last_name, first_name",$user);
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

    $sql = "select person_id from " . DB_PREFIX . "people where $where";

    return get_records_from_query("person", $sql);
}

function get_popular_people($user) {

    global $TOP_N;

    if ($user && !$user->is_admin()) {
        $sql =
            "SELECT ppl.*, COUNT(DISTINCT ph.photo_id) AS count FROM " .
            DB_PREFIX . "people as ppl JOIN " .
            DB_PREFIX . "photo_people as pp " .
            "ON pp.person_id = ppl.person_id JOIN " .
            DB_PREFIX . "photos as ph " .
            "ON pp.photo_id = ph.photo_id JOIN " .
            DB_PREFIX . "photo_albums as pa " .
            "ON pa.photo_id = pp.photo_id JOIN " .
            DB_PREFIX . "group_permissions as gp " .
            "ON pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu " .
            "ON gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) . "' " .
            " AND gp.access_level >= ph.level " .
            "GROUP BY ppl.person_id " .
            "ORDER BY count DESC, ppl.last_name, ppl.first_name " .
            "LIMIT 0, " . escape_string($TOP_N);
    }
    else {
        $sql =
            "select ppl.*, count(*) as count from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photo_people as pp " .
            "where ppl.person_id = pp.person_id " .
            "group by ppl.person_id " .
            "order by count desc, ppl.last_name, ppl.first_name " .
            "limit 0, " . escape_string($TOP_N);
    }

    return get_popular_results("person", $sql);

}

function create_person_pulldown($name, $value=null, $user) {
    $id=ereg_replace("^_+", "", $name);
    if($value) {
        $person=new person($value);
        $person->lookup();
        $text=$person->get_name();
    }
    if($user->prefs->get("autocomp_people") && AUTOCOMPLETE && JAVASCRIPT) {
        $html="<input type=hidden id='" . $id . "' name='" . $name. "'" .
            " value='" . $value . "'>";
        $html.="<input type=text id='_" . $id . "' name='_" . $name. "'" .
            " value='" . $text . "' class='autocomplete'>";
    } else {
        $html=create_pulldown($name, $value, get_people_select_array(null,$user));
    }
    return $html;
}

function create_photographer_pulldown($name, $value=null, $user) {
    $text="";

    $id=ereg_replace("^_+", "", $name);
    if($value) {
        $person=new person($value);
        $person->lookup();
        $text=$person->get_name();
    }
    if($user->prefs->get("autocomp_photographer") && AUTOCOMPLETE && JAVASCRIPT) {
        $html="<input type=hidden id='" . $id . "' name='" . $name. "'" .
            " value='" . $value . "'>";
        $html.="<input type=text id='_" . $id . "' name='_" . $name. "'" .
            " value='" . $text . "' class='autocomplete'>";
    } else {
        $html=create_pulldown($name, $value, get_people_select_array(null, $user));
    }
    return $html;
}

?>
