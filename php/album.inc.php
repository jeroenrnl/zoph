<?php

/*
 * A photo album class corresponding to the album table.
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

class album extends zoph_tree_table {

    var $photo_count;
    function album($id = 0) {
        if($id && !is_numeric($id)) { die("album_id must be numeric"); }
        parent::zoph_table("albums", array("album_id"), array("album"));
        $this->set("album_id", $id);
    }
   /**
    * Get the Id
    */

    public function getId() {
        return $this->get("album_id");
    }

    function lookup($user = null) {
        $id = $this->get("album_id");
        if(!is_numeric($id)) { die("album_id must be numeric"); }
        if (!$id) { return; }

        if ($user && !$user->is_admin()) {
            $sql =
                 "select a.* from "  .
                 DB_PREFIX . "albums as a JOIN " .
                 DB_PREFIX . "group_permissions as gp ON " .
                 "gp.album_id = a.album_id JOIN " .
                 DB_PREFIX . "groups_users gu ON " .
                 "gp.group_id = gu.group_id " .
                 "where gp.album_id = '" . escape_string($id) . "'" .
                 " and gu.user_id = '" . 
                 escape_string($user->get("user_id"))."'";
        }
        else {
            $sql =
                "select * from " . DB_PREFIX . "albums " .
                "where album_id = " . escape_string($id);
        }

        return parent::lookup($sql);
    }

    function delete() {
        parent::delete(array("photo_albums", "group_permissions"));
        $users = get_records("user", "user_id", array("lightbox_id" => $this->get("album_id")));
        if ($users) {
          foreach ($users as $user) {
            $user->set_fields(array("lightbox_id" => "null"));
            $user->update();
          }
        }
    }

    function get_name() {
        return $this->get("album");
    }

    function get_indented_name() {
        $indent=str_repeat("&nbsp;&nbsp;&nbsp;", count($this->get_ancestors()));
        return $indent . $this->get_name();
    }

    function get_children($user=null, $order=null) {
        if($order && $order!="name") {
            $order_fields=get_sql_for_order($order);
            $order=" ORDER BY " . $order . ", name ";
        } else if ($order=="name") {
            $order=" ORDER BY name ";
        }

        $id = $this->get("album_id");
        if (!$id) { return; }
        
        if ($user && !$user->is_admin()) {
            $sql =
        "SELECT a.*, album as name " .
            $order_fields . " FROM " .
            DB_PREFIX . "albums as a LEFT JOIN " .
            DB_PREFIX . "photo_albums as pa " .
            "ON a.album_id=pa.album_id LEFT JOIN " .
            DB_PREFIX . "photos as ph " .
            "ON pa.photo_id=ph.photo_id LEFT JOIN " .
            DB_PREFIX . "group_permissions AS gp " .
            "ON a.album_id=gp.album_id JOIN " .
            DB_PREFIX . "groups_users AS gu ON " .
            "gp.group_id = gu.group_id " .
            "WHERE gu.user_id=" . escape_string($user->get("user_id")) .
            " AND parent_album_id=" . escape_string($id) .
            " GROUP BY album_id" .
            escape_string($order);
         } else {
            $sql =
                "SELECT a.*, album as name " .
                $order_fields . " FROM " .
                DB_PREFIX . "albums as a LEFT JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON a.album_id=pa.album_id LEFT JOIN " .
                DB_PREFIX . "photos as ph " .
                "ON pa.photo_id=ph.photo_id " .
                "WHERE parent_album_id=" . escape_string($id) .
                " GROUP BY album_id" .
                escape_string($order);
        }

        $this->children=get_records_from_query("album", $sql);
        return $this->children;
    }    

    function get_photo_count($user = null) {
        if ($this->photo_count) { return $photo_count; }

        $id = $this->get("album_id");

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(*) from " .
                DB_PREFIX . "photo_albums AS pa JOIN " .
                DB_PREFIX . "photos AS p ON " .
                "pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp ON " .
                "gp.album_id = pa.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu ON " .
                "gp.group_id = gu.group_id " .
                "WHERE pa.album_id = " . escape_string($id) .
                " and gu.user_id = '" . escape_string($user->get("user_id")) .
                "' and gp.access_level >= p.level";
        }
        else {
            $sql =
                "select count(*) from " .
                DB_PREFIX . "photo_albums " .
                "where album_id = '" .  escape_string($id) . "'";
        }

        return get_count_from_query($sql);
    }

    function get_total_photo_count($user = null) {
        // Without the lookup, parent_album_id is not available!
        $this->lookup();
        if ($this->get("parent_album_id")) {
            $id_list = $this->get_branch_ids($user);
            $id_constraint = "pa.album_id in ($id_list)";
        }
        else {
            $id_constraint = "";
        }
        if ($user && !$user->is_admin()) {
            $sql =
                "select count(distinct pa.photo_id) from " .
                DB_PREFIX . "photo_albums as pa JOIN " .
                DB_PREFIX . "photos as p ON " .
                "pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp ON " .
                "pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu ON " .
                "gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . escape_string($user->get("user_id")) .
                "' and gp.access_level >= p.level";

            if ($id_constraint) {
                $sql .= " and $id_constraint";
            }
        }
        else {
            $sql =
                "select count(distinct pa.photo_id) from " .
                DB_PREFIX . "photo_albums pa ";

            if ($id_constraint) {
                $sql .= " where $id_constraint";
            }
        }

        return get_count_from_query($sql);
    }

    function get_edit_array($user) {
        if($this->is_root()) {
            $parent=array (
                translate("parent album"),
                translate("Albums"));
        } else {
            $parent=array (
                translate("parent album"),
                create_album_pulldown("parent_album_id",
                $this->get("parent_album_id"), $user));
        }
        return array(
            "album" => 
                array(
                    translate("album name"),  
                    create_text_input("album", $this->get("album"),40,64)),
            "parent_album_id" => $parent,
            "album_description" =>
                array(
                    translate("album description"),
                    create_text_input("album_description",
                        $this->get("album_description"), 40, 128)),
            "pageset" =>
                array(
                    translate("pageset"),
                    create_pulldown("pageset", $this->get("pageset"), get_pageset_select_array())),
            "sortname" =>
                array(
                    translate("sort name"),
                    create_text_input("sortname",
                        $this->get("sortname"))),
            "sortorder" =>
                array(
                    translate("album sort order"),
                    create_photo_field_pulldown("sortorder", $this->get("sortorder")))
        );
    }

    function get_link() {
        if ($this->get("parent_album_id")) {
            $name = $this->get("album");
        }
        else {
            $name = "Albums";
        }

        return "<a href=\"albums.php?parent_album_id=" .
            $this->get("album_id") . "\">$name</a>";
    }

    function xml_rootname() {
        return "albums";
    }

    function xml_nodename() {
        return "album";
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
                $album_where=" WHERE pa.album_id in (" . $this->get_branch_ids($user) .")";
            } else {
                $album_where=" WHERE pa.album_id =" .$this->get("album_id");
            }
            if ($user && !$user->is_admin()) {
                $sql=
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_albums as pa" .
                    " ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "group_permissions as gp ON " .
                    "pa.album_id = gp.album_id JOIN " .
                    DB_PREFIX . "groups_users AS gu ON " .
                    "gp.group_id = gu.group_id " .
                    $album_where .
                    " AND gu.user_id =" . 
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " and pa.photo_id = p.photo_id " .
                    " and gp.access_level >= p.level " .
                    $order;
            } else {
                $sql =
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_albums pa ON" .
                    " pa.photo_id = p.photo_id" .
                    $album_where .
                    " " . $order;
            }
            $coverphoto=array_shift(get_records_from_query("photo", $sql));
        }

        if ($coverphoto) {
            $coverphoto->lookup();
            return $coverphoto->get_image_tag(THUMB_PREFIX);
        } else if (!$children) {
            // No photos found in this album... let's look again, but now 
            // also in sub-albums...
            return $this->get_coverphoto($user, $autothumb, true);
        }
    }
    function is_root() {
        // At this moment the root album is always 1, but this may
        // change in the future, so to be safe we'll make a function for
        // this
        $root_album=get_root_album();
        if($this->get("album_id") == $root_album->get("album_id")) {
            return true;
        } else {
            return false;
        }
    }
   /**
    * Lookup album by name;
    */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $where =
            "lower(album) ='" . escape_string(strtolower($name)) . "'";

        $query = "select album_id from " . DB_PREFIX . "albums where $where";

        return get_records_from_query("album", $query);
    }
        
}

function get_root_album() {
    return new album(1);
}

function get_albums($user = null) {

    if ($user && !$user->is_admin()) {
        $sql =
             "select a.* from " .
             DB_PREFIX . "albums as a JOIN " .
             DB_PREFIX . "group_permissions AS gp " .
             "ON gp.album_id = a.album_id JOIN " .
             DB_PREFIX . "groups_users as gu " .
             "where gu.user_id = '" . escape_string($user->get("user_id")) .
             "order by a.album";
    }
    else {
        $sql = "select * from " . DB_PREFIX . "albums order by album";
    }

    return get_records_from_query("album", $sql);
}

function get_newer_albums($user_id, $date = null) {
    $sql = "select a.* from " .
        DB_PREFIX . "albums as a JOIN " .
        DB_PREFIX . "group_permissions as gp " .
        "ON a.album_id = gp.album_id JOIN " .
        DB_PREFIX . "groups_users as gu " .
        "WHERE gu.user_id = '" . escape_string($user_id) .
        "' AND gp.changedate > '" . escape_string($date) . "' " .
        "ORDER BY a.album_id";

    return get_records_from_query("album", $sql);
}

function get_album_count($user = null) {

    if ($user && !$user->is_admin()) {
        $sql =
            "SELECT COUNT(DISTINCT album_id) FROM " . 
            DB_PREFIX . "group_permissions AS gp JOIN " .
            DB_PREFIX . "groups_users AS gu ON " .
            "gp.group_id = gu.group_id " .
            "where gu.user_id = '" . escape_string($user->get("user_id")) . "'";
    }
    else {
        $sql = "select count(*) from " . DB_PREFIX . "albums";
    }

    return get_count_from_query($sql);
}

function get_albums_select_array($user = null, $search = 0) {
    return create_tree_select_array("album", $user, null, "", null, $search);
}

function get_albums_search_array($user = null) {
    return get_albums_select_array($user, 1);
}

function get_popular_albums($user) {

    global $TOP_N;

    if ($user && !$user->is_admin()) {
        $sql =
            "SELECT al.*, count(distinct ph.photo_id) AS count FROM " .
            DB_PREFIX . "albums AS al JOIN " .
            DB_PREFIX . "photo_albums AS pa ON " .
            " al.album_id = pa.album_id JOIN " .
            DB_PREFIX . "photos AS ph ON " .
            "pa.photo_id = ph.photo_id JOIN " .
            DB_PREFIX . "group_permissions AS gp ON " .
            "pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users AS gu ON " .
            "gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . escape_string($user->get("user_id")). "'" .
            " AND gp.access_level >= ph.level " .
            "GROUP BY al.album_id " .
            "ORDER BY count desc, al.album " .
            "LIMIT 0, " . escape_string($TOP_N);
    }
    else {
        $sql =
            "select al.*, count(*) as count from " .
            DB_PREFIX . "albums as al, " .
            DB_PREFIX . "photo_albums as pa " .
            "where pa.album_id = al.album_id " .
            "group by al.album_id " .
            "order by count desc, al.album " .
            "limit 0, " . escape_string($TOP_N);
    }

    return get_popular_results("album", $sql);

}

function create_album_pulldown($name, $value=null, $user=null) {
    $text="";

    $id=ereg_replace("^_+", "", $name);
    if($value) {
        $album=new album($value);
        $album->lookup();
        $text=$album->get("album");
    } 
    
    if($user->prefs->get("autocomp_albums") && AUTOCOMPLETE && JAVASCRIPT) {
        $html="<input type=hidden id='" . $id . "' name='" . $name. "'" .
            " value='" . $value . "'>";
        $html.="<input type=text id='_" . $id . "' name='_" . $name. "'" .
            " value='" . $text . "' class='autocomplete'>";
    } else {
        $html=create_pulldown($name, $value, get_albums_search_array($user));
    }
    return $html;
}
?>
