<?php

/*
 * A category class corresponding to the category table.
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

    function get_children() {
        return parent::get_children(null, "category");
    }

    function get_branch_ids($user = null) {
        return parent::get_branch_ids("category", $user);
    }

    function get_photo_count($user) {

        if ($this->photo_count) { return $photo_count; }

        $id = $this->get("category_id");

        if ($user && !$user->is_admin()) {
            $sql =
                "select count(distinct pc.photo_id) from " .
                DB_PREFIX . "photo_categories as pc, " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "photos as p, " .
                DB_PREFIX . "album_permissions as ap " .
                "where pc.category_id = '" .  escape_string($id) . "'" .
                " and ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " and pc.photo_id = pa.photo_id" .
                " and pa.album_id = ap.album_id" .
                " and pa.photo_id = p.photo_id" .
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
                DB_PREFIX . "photo_categories as pc, " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "photos as p, " .
                DB_PREFIX . "album_permissions as ap " .
                "where ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " and pc.photo_id = pa.photo_id" .
                " and pa.album_id = ap.album_id" .
                " and pa.photo_id = p.photo_id" .
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
            translate("category name") =>
                create_text_input("category", $this->get("category")),
            translate("parent category") =>
                create_pulldown("parent_category_id",
                    $this->get("parent_category_id"),
                    get_categories_select_array()),
            translate("category description") =>
                create_text_input("category_description",
                    $this->get("category_description"), 40, 128));
    }

    function get_link() {
        if ($this->get("parent_category_id")) {
            $name = $this->get("category");
        }
        else {
            $name = "Categories";
        }
        return "<a href=\"categories.php?parent_category_id=" . $this->get("category_id") . "\">$name</a>";
    }

}

function get_root_category() {
    return new category(1);
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
