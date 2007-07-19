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
        parent::zoph_table("categories", array("category_id"), array("category"));
        $this->set("category_id", $id);
    }

    function delete() {
        parent::delete(array("photo_categories"));
    }

    function get_name() {
        return $this->get("category");
    }

    function get_children($user=null) {
        if($user) {
            $order = $user->prefs->get("child_sortorder") . ", name";
        } else {
            $order = "name";
        }
        $id = $this->get("category_id");
        if (!$id) { return; }

        $sql =
            "SELECT c.*, category as name, " .
            "min(p.date) as oldest, " .
            "max(p.date) as newest, " .
            "min(p.timestamp) as first, " .
            "max(p.timestamp) as last, " .
            "min(rating) as lowest, " . 
            "max(rating) as highest, " .
            "avg(rating) as average, " .
            "rand() as random from " .
            DB_PREFIX . "categories as c LEFT JOIN " .
            DB_PREFIX . "photo_categories as pc " .
            "ON c.category_id=pc.category_id LEFT JOIN " .
            DB_PREFIX . "photos as p " .
            "ON pc.photo_id=p.photo_id " .
            "WHERE parent_category_id=" . $id .
            " GROUP BY c.category_id " .
            "ORDER BY " . $order;
        $this->children=get_records_from_query("category", $sql);
        return $this->children;
    }    

   // }

    function get_branch_ids($user = null) {
        return parent::get_branch_ids("category",$user);
    }

    function get_photo_count($user) {
        if ($this->photo_count) { return $photo_count; }

        $id = $this->get("category_id");

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(distinct pc.photo_id) from " .
                DB_PREFIX . "photo_categories as pc JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                " ON pc.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "photos as p " .
                " ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "album_permissions as ap " .
                " ON pa.album_id = ap.album_id " .
                "where pc.category_id = '" .  escape_string($id) . "'" .
                " and ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " and ap.access_level >= p.level";
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
                " ON pc.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "photos as p " .
                " ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "album_permissions as ap " .
                " ON pa.album_id = ap.album_id" .
                " where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " and ap.access_level >= p.level";

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

    function get_edit_array() {
        return array(
            "category" =>
                array(
                    translate("category name"),
                    create_text_input("category", $this->get("category"))),
            "parent_category_id" =>
                array(
                    translate("parent category"),
                    create_pulldown("parent_category_id",
                        $this->get("parent_category_id"),
                        get_categories_select_array())),
            "category_description" =>
                array(
                    translate("category description"),
                    create_text_input("category_description",
                        $this->get("category_description"), 40, 128)),
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
        } else if ($autothumb) {
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
                    DB_PREFIX . "photo_albums as pa" .
                    " ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "album_permissions as ap " .
                    " ON pa.album_id = ap.album_id JOIN " .
                    DB_PREFIX . "photo_categories as pc " .
                    " ON pc.photo_id = p.photo_id " .
                    $cat_where .
                    " AND ap.user_id =" .
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " and ap.access_level >= p.level " .
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
            DB_PREFIX . "categories as cat, " .
            DB_PREFIX . "photo_categories as pc, " .
            DB_PREFIX . "photos as ph, " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap " .
            "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = pc.photo_id" .
            " and pc.category_id = cat.category_id" .
            " and pc.photo_id = ph.photo_id" .
            " and ap.access_level >= ph.level " .
            "group by cat.category_id " .
            "order by count desc, cat.category " .
            "limit 0, $TOP_N";
    }
    else {
        $sql =
            "select cat.*, count(*) as count from " .
            DB_PREFIX . "categories as cat, " .
            DB_PREFIX . "photo_categories as pc " .
            "where pc.category_id = cat.category_id " .
            "group by cat.category_id " .
            "order by count desc, cat.category " .
            "limit 0, $TOP_N";
    }

    return get_popular_results("category", $sql);

}

?>
