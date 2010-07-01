<?php

/*
 * A category class corresponding to the category table.
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

class category extends zoph_tree_table {

    var $photo_count;

    function category($id = 0) {
        if($id && !is_numeric($id)) { die("category_id must be numeric"); }
        parent::zoph_table("categories", array("category_id"), array("category"));
        $this->set("category_id", $id);
    }

    function delete() {
        parent::delete(array("photo_categories"));
    }

    function get_name() {
        return $this->get("category");
    }

    function get_children($user=null,$order=null) {
        if($order && $order!="name") {
            $order_fields=get_sql_for_order($order);
            $order=" ORDER BY " . $order . ", name ";
        } else if ($order=="name") {
            $order=" ORDER BY name ";
        }

        $id = $this->get("category_id");
        if (!$id) { return; }

        $sql =
            "SELECT c.*, category as name " .
            $order_fields . " FROM " .
            DB_PREFIX . "categories as c LEFT JOIN " .
            DB_PREFIX . "photo_categories as pc " .
            "ON c.category_id=pc.category_id LEFT JOIN " .
            DB_PREFIX . "photos as ph " .
            "ON pc.photo_id=ph.photo_id " .
            "WHERE parent_category_id=" . $id .
            " GROUP BY c.category_id " .
            $order;
        $this->children=get_records_from_query("category", $sql);
        if($user && !$user->is_admin()) {
            return(remove_empty($this->children,$user));
        } else {
            return $this->children;
        }
    }    
    
    function get_branch_ids($user = null) {
        return parent::get_branch_ids($user);
    }

    function get_photo_count($user) {
        if ($this->photo_count) { return $photo_count; }

        $id = $this->get("category_id");

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(distinct pc.photo_id) from " .
                DB_PREFIX . "photo_categories as pc JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON pc.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "photos as p " .
                "ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE pc.category_id = '" .  escape_string($id) . "' " .
                "AND gu.user_id = '" . escape_string($user->get("user_id")) . 
                "' AND gp.access_level >= p.level";
        }
        else {
            $sql =
                "select count(photo_id) from " .
                DB_PREFIX . "photo_categories " .
                "where category_id = '" .  escape_string($id) . "'";
        }

        return get_count_from_query($sql);
    }

    function get_total_photo_count($user = null) {
        if ($this->get("parent_category_id")) {
            $id_list = $this->get_branch_ids($user);
            $id_constraint = "pc.category_id in ($id_list)";
        }
        else {
            $id_constraint = "";
        }


        if ($user && !$user->is_admin()) {
            $sql =
                "select count(distinct pc.photo_id) from " .
                DB_PREFIX . "photo_categories as pc JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON pc.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "photos as p " .
                "ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . escape_string($user->get("user_id")) .
                "' AND gp.access_level >= p.level";

            if ($id_constraint) {
                $sql .= " and $id_constraint";
            }
        }
        else {
            $sql =
                "select count(distinct pc.photo_id) from " .
                DB_PREFIX . "photo_categories as pc";

            if ($id_constraint) {
                $sql .= " where $id_constraint";
            }
        }

        return get_count_from_query($sql);
    }

    function get_edit_array($user) {
        if($this->is_root()) {
            $parent=array(
                translate("parent category"),
                translate("Categories"));
        } else {
            $parent=array(
                translate("parent category"),
                create_cat_pulldown("parent_category_id",
                    $this->get("parent_category_id"),
                    $user));
        }
        return array(
            "category" =>
                array(
                    translate("category name"),
                    create_text_input("category", $this->get("category"),40,64)),
            "parent_category_id" => $parent,
            "category_description" =>
                array(
                    translate("category description"),
                    create_text_input("category_description",
                        $this->get("category_description"), 40, 128)),
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
                    translate("category sort order"),
                    create_photo_field_pulldown("sortorder", $this->get("sortorder")))
        );
    }

    function get_link() {
        if ($this->get("parent_category_id")) {
            $name = $this->get("category");
        }
        else {
            $name = translate("Categories");
        }
        return "<a href=\"categories.php?parent_category_id=" . $this->get("category_id") . "\">$name</a>";
    }
    function xml_rootname() {
        return "categories";
    }

    function xml_nodename() {
        return "category";
    }

    function get_coverphoto($user,$autothumb=null,$children=null) {
        if ($this->get("coverphoto")) {
            $coverphoto=new photo($this->get("coverphoto"));
            if ($coverphoto->lookup($user)) {
                $cover=TRUE;
            }
        }
        if ($autothumb && !$cover) {
            $order=get_autothumb_order($autothumb);
            if($children) {
                $cat_where=" WHERE pc.category_id in (" . $this->get_branch_ids($user) .")";
            } else {
                $cat_where=" WHERE pc.category_id =" .$this->get("category_id");
            }

            if ($user && !$user->is_admin()) {
                $sql=
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_albums as pa " .
                    "ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "group_permissions as gp " .
                    "ON pa.album_id = gp.album_id JOIN " .
                    DB_PREFIX . "groups_users as gu " .
                    "ON gp.group_id = gu.group_id JOIN " .
                    DB_PREFIX . "photo_categories as pc " .
                    "ON pc.photo_id = p.photo_id " .
                    $cat_where .
                    " AND gu.user_id =" .
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " AND gp.access_level >= p.level " .
                    $order;
            } else {
                $sql =
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p JOIN " .
                    DB_PREFIX . "photo_categories as pc ON" .
                    " pc.photo_id = p.photo_id" .
                    $cat_where . " " . $order;
            }
            $coverphoto=array_shift(get_records_from_query("photo", $sql));
        }

        if ($coverphoto) {
            $coverphoto->lookup();
            return $coverphoto->get_image_tag(THUMB_PREFIX);
        } else if (!$children) {
            // No photos found in this cat... let's look again, but now 
            // also in subcat...
            return $this->get_coverphoto($user, $autothumb, true);

        }
    }
    function is_root() {
        // At this moment the root cat is always 1, but this may
        // change in the future, so to be safe we'll make a function for
        // this
        $root_cat=get_root_category();
        if($this->get("category_id") == $root_cat->get("category_id")) {
            return true;
        } else {
            return false;
        }
    }
}

function get_root_category() {
    return new category(1);
}

function get_category_by_name($category = null) {
    if (!$category) {
        return "";
    }
    $where =
            "lower(category) like '%" . escape_string(strtolower($category))
 . "%'";

    $query = "select category_id from " . DB_PREFIX . "categories where $where";

    return get_records_from_query("category", $query);
}

function get_categories_select_array($user = null, $search = 0) {
    return create_tree_select_array("category", $user, null, "", null, $search);
}

function get_categories_search_array($user = null) {
    return get_categories_select_array($user, 1);
}

function get_popular_categories($user) {

    global $TOP_N;

    if ($user && !$user->is_admin()) {
        $sql =
            "select cat.*, count(distinct ph.photo_id) as count from " .
            DB_PREFIX . "categories as cat JOIN " .
            DB_PREFIX . "photo_categories as pc ON " .
            "pc.category_id = cat.category_id JOIN " .
            DB_PREFIX . "photos as ph ON " .
            " pc.photo_id = ph.photo_id JOIN " .
            DB_PREFIX . "photo_albums as pa ON " .
            " pa.photo_id = pc.photo_id JOIN " .
            DB_PREFIX . "group_permissions as gp ON " .
            "pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu ON " .
            "gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) . "'" .
            "AND gp.access_level >= ph.level " .
            "GROUP BY cat.category_id " .
            "ORDER BY count desc, cat.category " .
            "LIMIT 0, " . escape_string($TOP_N);
    }
    else {
        $sql =
            "select cat.*, count(*) as count from " .
            DB_PREFIX . "categories as cat, " .
            DB_PREFIX . "photo_categories as pc " .
            "where pc.category_id = cat.category_id " .
            "group by cat.category_id " .
            "order by count desc, cat.category " .
            "limit 0, " . escape_string($TOP_N);
    }

    return get_popular_results("category", $sql);

}

function create_cat_pulldown($name, $value=null, $user) {
    $id=ereg_replace("^_+", "", $name);
    if($value) {
        $cat=new category($value);
        $cat->lookup();
        $text=$cat->get("category");
    }
    if($user->prefs->get("autocomp_categories") && AUTOCOMPLETE && JAVASCRIPT) {
        $html="<input type=hidden id='" . e($id) . "' name='" . e($name) . "'" .
            " value='" . e($value) . "'>";
        $html.="<input type=text id='_" . e($id) . "' name='_" . e($name) . 
            "'" .  " value='" . e($text) . "' class='autocomplete'>";
    } else {
        $html=create_pulldown($name, $value, get_categories_search_array($user));
    }
    return $html;
}

function get_category_count($user) {
    if($user && !$user->is_admin()) {
        $sql =
            "SELECT category_id, parent_category_id  FROM " .
            DB_PREFIX . "categories as c";
        $cats=get_records_from_query("category", $sql);
        $cat_clean=remove_empty($cats,$user);
        return count($cat_clean);
    } else {
        return get_count("category");
    }
}
 

?>
