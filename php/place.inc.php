<?php

/*
 * A class corresponding to the places table.
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

class place extends zoph_tree_table {

    function place($id = 0) {
        parent::zoph_table("places", array("place_id"), array("title"));
        $this->set("place_id", $id);
    }

    function insert() {
        $this->tzid_to_timezone();
        parent::insert();
    }

    function update() {
        $this->tzid_to_timezone();
        parent::update();
    }

    function delete() {
        $id=escape_string($this->get("place_id"));
        if(!is_numeric($id)) {die("place_id is not numeric"); }

        $sql="update " . DB_PREFIX . "photos set location_id=null where " .
            "location_id=" . $id;
        if (DEBUG) { echo "$sql<br>\n"; }
        mysql_query($sql) or die_with_mysql_error("Could not remove references:", $sql);

        $sql="update " . DB_PREFIX . "people set home_id=null where " .
            "home_id=" .  $id;
        if (DEBUG) { echo "$sql<br>\n"; }
        mysql_query($sql) or die_with_mysql_error("Could not remove references:", $sql);

        $sql="update " . DB_PREFIX . "people set work_id=null where " .
            "work_id=" .  $id;
        if (DEBUG) { echo "$sql<br>\n"; }
        mysql_query($sql) or die_with_mysql_error("Could not remove references:", $sql);
        
        parent::delete();
    }


    function tzid_to_timezone() {
        $tzkey=$this->get("timezone_id");
        if($tzkey>0) {
            $tzarray=get_tz_select_array();
            $tz=$tzarray[$tzkey];
            $this->set("timezone", $tz);
        }
        unset($this->fields["timezone_id"]);
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
            $html .= $this->get("address") . "<br>";
        }
        if ($this->get("address2")) {
            $html .= $this->get("address2") . "<br>";
        }
        if ($this->get("city")) { $html .= $this->get("city"); }
        if ($this->get("city") && $this->get("state")) { $html .= ", "; }
        if ($this->get("state")) { $html .= $this->get("state"); }
        if ($this->get("zip")) { $html .= " " . $this->get("zip"); }
        if ($this->get("country")) {
            $html .= "<br>" . $this->get("country");
        }

        return $html;
    }

    function to_html() {

        $html = "";
        if ($this->get("title"))    {
            $html .= "<h2>" . $this->get("title") . "</h2>\n";
        }
        $html .= $this->get_address();
        if($this->get("url")) {
            $html .= "<br><br>\n";
            $html .= "<a href=\"" . $this->get("url") . "\">";
            $html .= $this->get("urldesc") . "</a>";
        }

        return $html;
    }

    function get_link() {
        $link = "<a href=\"places.php?parent_place_id=" . $this->get("place_id") . "\">" . $this->get_name() . "</a>";

        // add city link if title exists (and so was used by get_name())
  //      if ($this->get("title") && $this->get("city")) {
    //        $link .= ", <a href=\"places.php?_l=" . rawurlencode(strtolower($this->get("city"))) . "\">" . $this->get("city") . "</a>";
     //   }
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
            translate("notes") => $this->get("notes"),
            translate("timezone") => $this->get("timezone"));
    }
    
    function get_photo_count($user = null) {
        $id = $this->get("place_id");

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(*) from " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "photos as p, " .
                DB_PREFIX . "album_permissions as ap " .
                "where p.location_id = $id" .
                " and ap.user_id = '" . escape_string($user->get("user_id")) .
                "' and ap.album_id = pa.album_id" .
                " and pa.photo_id = p.photo_id " .
                " and ap.access_level >= p.level";
}
        else {
            $sql =
                "select count(*) from " .
                DB_PREFIX . "photos " .
                "where location_id = '" .  escape_string($id) . "'";
        }

        return get_count_from_query($sql);
    }

    function get_total_photo_count($user = null) {
        if ($this->get("parent_place_id")) {
            $id_list = $this->get_branch_ids($user);
            $id_constraint = "p.location_id in ($id_list)";
        }
        else {
            $id_constraint = "";
        }

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(distinct pa.photo_id) from " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "photos as p, " .
                DB_PREFIX . "album_permissions as ap " .
                "where ap.user_id = '" . escape_string($user->get("user_id")) .
                "' and ap.album_id = pa.album_id " .
                " and pa.photo_id = p.photo_id " .
                " and ap.access_level >= p.level";

            if ($id_constraint) {
                $sql .= " and $id_constraint";
            }
        }
        else {
            $sql =
                "select count(distinct p.photo_id) from " .
                DB_PREFIX . "photos p ";

            if ($id_constraint) {
                $sql .= " where $id_constraint";
            }
        }

        return get_count_from_query($sql);
    }

    function xml_rootname() {
        return "places";
    }

    function xml_nodename() {
        return "place";
    }

    function get_coverphoto($user,$autothumb=null,$children=null) {
        if ($this->get("coverphoto")) {
            $coverphoto=new photo($this->get("coverphoto"));
            if($coverphoto->lookup($user)) {
                $cover=TRUE;
            }
        } 
        if ($autothumb && !$cover) {
            $order=get_autothumb_order($autothumb);
            if($children) {
                $place_where=" WHERE p.location_id in (" . $this->get_branch_ids($user) .")";
            } else {
                $place_where=" WHERE p.location_id =" .$this->get("place_id");
            }

            if ($user && !$user->is_admin()) {
                $sql=
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_albums as pa" .
                    " ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "album_permissions as ap " .
                    " ON pa.album_id = ap.album_id " .
                    $place_where .
                    " AND ap.user_id =" .
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " and ap.access_level >= p.level " .
                    $order;
            } else {
                $sql =
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p" .
                    $place_where . " " . $order;
            }
            $coverphoto=array_shift(get_records_from_query("photo", $sql));
        }

        if ($coverphoto) {
            $coverphoto->lookup();
            return $coverphoto->get_image_tag(THUMB_PREFIX);
        } else if (!$children) {
            // No photos found in this place... let's look again, but now 
            // also in sub-places...
            return $this->get_coverphoto($user, $autothumb, true);
        }

    }
    function get_mapping_js($user,$edit=false) {
         $js=parent::get_mapping_js($user, ICONSET . "/geo-place.png", $edit);
         if (!$edit) {
            $js.=get_markers($this->get_children(), $user);
        }
        return $js;
    }
    
    function get_quicklook($user) {
        $html="<h2>" . $this->get_link() . "</h2>";
        $html.="<small>" . $this->get_address() . "</small><br>";
        $html.=$this->get_coverphoto($user, $user->prefs->get("autothumb"));
        $count=$this->get_photo_count($user);
        $totalcount=$this->get_total_photo_count($user);
       
        $html.="<br><small>" . 
            escape_string(sprintf(translate("There are %s photos"), $count)) .
            " " . translate("in this place") . "<br>";
        if($count!=$totalcount) {
            $html.=escape_string(sprintf(translate("There are %s photos"),$totalcount) . 
            " " . translate("in this place") . " " . translate("or its children")) . "<br>";
        }
        $html.="</small>";
        return $html;
    }

    function get_marker($user) {
        $icon=ICONSET . "/geo-place.png";
        return parent::get_marker($user, $icon);
    }

    function guess_tz() {
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        $timezone=$this->get("timezone");
        if(!$timezone && $lat && $lon) {
            $tz=guess_tz($lat, $lon);
            $html="<span class='actionlink'>" .
                "<a href=place.php?_action=update&place_id=" .
                $this->get("place_id") . "&timezone=" . $tz .
                ">" . $tz . "</a></span>";
            return $html;
        }
        return null;
    }

    function set_tz_children($tz) {
        $places=$this->get_children();
        if($places) {
            foreach ($places as $place) {
                $place->set("timezone", $tz);
                $place->update();
            }
        }
    }
}

function get_places($constraints = null, $conj = "and", $ops = null,
    $order = "city, title, address") {
    return get_records("place", $order, $constraints, $conj, $ops);
}

/* function get_children() {
// This function looks so broken, I can only assume it was never used.
    $id = $this->get("album_id");
    if (!$id) { return; }

   $sql =
         "select place_id, title, address, address2, city, ".
         "state, zip, country, notes from " .
         DB_PREFIX . "places" .
         "where p.parent_place_id = '" . escape_string($id) . "'" .
         " order by p.title";

    $this->children = get_records_from_query("album", $sql);

    return $this->children;

} */

function get_root_place() {
    return new place(1);
}

function get_photographed_places($user = null) {

    if ($user && !$user->is_admin()) {
        $query =
            "select distinct plc.* from " .
            DB_PREFIX . "places as plc, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "' " .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level" .
            " and ph.location_id = plc.place_id " .
            "order by plc.city, plc.title";
    }
    else {
        $query =
            "select distinct plc.* from " .
            DB_PREFIX . "places as plc, " .
            DB_PREFIX . "photos as ph " .
            "where plc.place_id = ph.location_id " .
            "order by plc.city, plc.title";
    }

    return get_records_from_query("place", $query);
}

function get_places_select_array($user = null, $search = 0) {
    return create_tree_select_array("place", $user, null, "", null, $search);
}

function get_places_search_array($user = null) {
    return get_places_select_array($user, 1);
}

function get_popular_places($user) {

    global $TOP_N;

    if ($user && !$user->is_admin()) {
        $sql =
            "select plc.*, count(distinct ph.photo_id) as count from " .
            DB_PREFIX . "places as plc, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ph.location_id = plc.place_id" .
            " and ap.access_level >= ph.level " .
            "group by plc.place_id " .
            "order by count desc, plc.title, plc.city " .
            "limit 0, $TOP_N";
    }
    else {
        $sql =
            "select plc.*, count(*) as count from " .
            DB_PREFIX . "places as plc, " .
            DB_PREFIX . "photos as ph " .
            "where plc.place_id = ph.location_id " .
            "group by plc.place_id " .
            "order by count desc, plc.title, plc.city " .
            "limit 0, $TOP_N";
    }

    return get_popular_results("place", $sql);

}

function create_zoom_pulldown($val = "", $name = "mapzoom") {
    $zoom_array = array(
        "0" => translate("0 - world", 0),
        "1" => translate("1",0),
        "2" => translate("2 - continent",0),
        "3" => translate("3",0),
        "4" => translate("4",0),
        "5" => translate("5",0),
        "6" => translate("6 - country",0),
        "7" => translate("7",0),
        "8" => translate("8",0),
        "9" => translate("9 - city",0),
        "10" => translate("10",0),
        "11" => translate("11",0),
        "12" => translate("12 - neighborhood",0),
        "13" => translate("13",0),
        "14" => translate("14",0),
        "15" => translate("15",0),
        "16" => translate("16 - street",0),
        "17" => translate("17",0),
        "18" => translate("18 - house",0));

    return create_pulldown($name, $val, $zoom_array);
}

?>
