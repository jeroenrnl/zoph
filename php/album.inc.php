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
        parent::zoph_table("albums", array("album_id"), array("album"));
        $this->set("album_id", $id);
    }

    function lookup($user = null) {
        $id = $this->get("album_id");
        if(!is_numeric($id)) { die("album_id must be numeric"); }
        if (!$id) { return; }

        if ($user && !$user->is_admin()) {
            $sql =
                 "select a.* from "  .
                 DB_PREFIX . "albums as a, " .
                 DB_PREFIX . "album_permissions as ap " .
                 "where ap.album_id = '" . escape_string($id) . "'" .
                 " and ap.user_id = '" . escape_string($user->get("user_id")) .
                 "' and ap.album_id = a.album_id";
        }
        else {
            $sql =
                "select * from " . DB_PREFIX . "albums " .
                "where album_id = " . escape_string($id);
        }

        return parent::lookup($sql);
    }

    function delete() {
        parent::delete(array("photo_albums", "album_permissions"));
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

    function get_children($user = null) {

        $id = $this->get("album_id");
        if (!$id) { return; }

        if ($user && !$user->is_admin()) {
            $sql =
                 "select a.album_id, a.album, a.album_description from " .
                 DB_PREFIX . "albums as a, " .
                 DB_PREFIX . "album_permissions as ap " .
                 "where a.parent_album_id = '" . escape_string($id) . "'" .
                 " and ap.user_id = '" . escape_string($user->get("user_id")) .
                 "' and ap.album_id = a.album_id" .
                 " order by a.album";
        }
        else {
            $sql =
                 "select album_id, album, album_description from " .
                 DB_PREFIX . "albums " .
                 "where parent_album_id = $id order by album";
        }

        $this->children = get_records_from_query("album", $sql);

        return $this->children;

    }

    function get_photo_count($user = null) {
        if ($this->photo_count) { return $photo_count; }

        $id = $this->get("album_id");

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(*) from " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "photos as p, " .
                DB_PREFIX . "album_permissions as ap " .
                "where pa.album_id = $id" .
                " and ap.user_id = '" . escape_string($user->get("user_id")) .
                "' and ap.album_id = pa.album_id" .
                " and pa.photo_id = p.photo_id " .
                " and ap.access_level >= p.level";
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
                "select count(distinct pa.photo_id) from " .
                DB_PREFIX . "photo_albums pa ";

            if ($id_constraint) {
                $sql .= " where $id_constraint";
            }
        }

        return get_count_from_query($sql);
    }

    function get_edit_array() {
        return array(
            "album" => 
                array(
                    translate("album name"),  
                    create_text_input("album", $this->get("album"))),
            "parent_album_id" =>
                array(
                    translate("parent album"),
                    create_pulldown("parent_album_id",
                    $this->get("parent_album_id"), get_albums_select_array())),
            "album_description" =>
                array(
                    translate("album description"),
                    create_text_input("album_description",
                        $this->get("album_description"), 40, 128)),
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

    function get_coverphoto($user,$autothumb=null) {
        if ($this->get("coverphoto")) {
            $coverphoto=new photo($this->get("coverphoto"));
        } else if ($autothumb) {
            $order=get_autothumb_order($autothumb);
            if ($user && !$user->is_admin()) {
                $sql=
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_albums as pa" .
                    " ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "album_permissions as ap " .
                    " ON pa.album_id = ap.album_id" .
                    " WHERE pa.album_id = " . $this->get("album_id") .
                    " AND ap.user_id =" . 
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " and pa.photo_id = p.photo_id " .
                    " and ap.access_level >= p.level " .
                    $order;
            } else {
                $sql =
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_albums pa ON" .
                    " pa.photo_id = p.photo_id" .
                    " WHERE pa.album_id = " . $this->get("album_id") .
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

function get_root_album() {
    return new album(1);
}

function get_albums($user = null) {

    if ($user && !$user->is_admin()) {
        $sql =
             "select a.* from " .
             DB_PREFIX . "albums as a, " .
             DB_PREFIX . "album_permissions as ap " .
             "where ap.user_id = '" . escape_string($user->get("user_id")) .
             "' and ap.album_id = a.album_id " .
             "order by a.album";
    }
    else {
        $sql = "select * from " . DB_PREFIX . "albums order by album";
    }

    return get_records_from_query("album", $sql);
}

function get_newer_albums($user_id, $date = null) {
    $sql = "select a.* from " .
        DB_PREFIX . "albums as a, " .
        DB_PREFIX . "album_permissions as ap " .
        "where ap.user_id = '" . escape_string($user_id) .
        "' and ap.album_id = a.album_id " .
        "and ap.changedate > '" . escape_string($date) . "' " .
        "order by a.album_id";

    return get_records_from_query("album", $sql);
}

function get_album_by_name($album = null) {
    if (!$album) {
        return "";
    }
    $where =
            "lower(album) like '%" . escape_string(strtolower($album))
 . "%'";

    $query = "select album_id from " . DB_PREFIX . "albums where $where";

    return get_records_from_query("album", $query);
}

function get_album_count($user = null) {

    if ($user && !$user->is_admin()) {
        $sql =
            "select count(*) from " . DB_PREFIX . "album_permissions " .
            "where user_id = '" . escape_string($user->get("user_id")) . "'";
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
            "select al.*, count(distinct ph.photo_id) as count from " .
            DB_PREFIX . "albums as al, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
            " and ap.album_id = pa.album_id" .
            " and pa.album_id = al.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level " .
            "group by al.album_id " .
            "order by count desc, al.album " .
            "limit 0, $TOP_N";
    }
    else {
        $sql =
            "select al.*, count(*) as count from " .
            DB_PREFIX . "albums as al, " .
            DB_PREFIX . "photo_albums as pa " .
            "where pa.album_id = al.album_id " .
            "group by al.album_id " .
            "order by count desc, al.album " .
            "limit 0, $TOP_N";
    }

    return get_popular_results("album", $sql);

}

?>
