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

class category extends zophTreeTable {

    var $photoCount;

    function category($id = 0) {
        if($id && !is_numeric($id)) { die("category_id must be numeric"); }
        parent::__construct("categories", array("category_id"), array("category"));
        $this->set("category_id", $id);
    }

    public function getId() {
        return (int) $this->get("category_id");
    }

    function delete() {
        parent::delete(array("photo_categories"));
    }

    function getName() {
        return $this->get("category");
    }

    function getChildren($user=null,$order=null) {
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
        $this->children=category::getRecordsFromQuery("category", $sql);
        if($user && !$user->is_admin()) {
            return(remove_empty($this->children,$user));
        } else {
            return $this->children;
        }
    }    
    
    function get_branch_ids($user = null) {
        return parent::get_branch_ids($user);
    }

    function getPhotoCount($user) {
        if ($this->photoCount) { return $photoCount; }

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

        return category::getCountFromQuery($sql);
    }

    function getTotalPhotoCount($user = null) {
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

        return category::getCountFromQuery($sql);
    }

    function getEditArray($user) {
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

    function getLink() {
        if ($this->get("parent_category_id")) {
            $name = $this->get("category");
        }
        else {
            $name = translate("Categories");
        }
        return "<a href=\"" . $this->getURL() . "\">$name</a>";
    }

    function getURL() {
        return "categories.php?parent_category_id=" . $this->getId();
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
            $coverphoto=array_shift(photo::getRecordsFromQuery("photo", $sql));
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
        $root_cat=category::getRoot();
        if($this->get("category_id") == $root_cat->get("category_id")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get details (statistics) about this category from db
     * @param user Only show albums this user is allowed to see
     * @return array Array with statistics
     */
    public function getDetails(user $user=null) {
        $id = (int) $this->getId();
        if(isset($user)) {
            $user_id = (int) $user->getId();
        } 

        if ($user && !$user->is_admin()) {
            $sql = "SELECT " .
                "COUNT(ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ph.rating),1) AS lowest, " .
                "ROUND(MAX(ph.rating),1) AS highest, " . 
                "ROUND(AVG(ph.rating),2) AS average FROM " . 
                DB_PREFIX . "photo_categories pc JOIN " .
                DB_PREFIX . "photos ph " .
                "ON ph.photo_id=pc.photo_id LEFT JOIN " .
                DB_PREFIX . "photo_albums pa " .
                "ON ph.photo_id=pa.photo_id LEFT JOIN " .
                DB_PREFIX . "group_permissions gp " .
                "ON pa.album_id=gp.album_id LEFT JOIN " . 
                DB_PREFIX . "groups_users gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE ph.level<gp.access_level AND " .
                "gu.user_id=" . escape_string($user_id) . " AND " .
                "pc.category_id=" . escape_string($id) .
                " GROUP BY pc.category_id";
        } else {
            $sql = "SELECT ".
                "COUNT(ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ph.rating),1) AS lowest, " .
                "ROUND(MAX(ph.rating),1) AS highest, " . 
                "ROUND(AVG(ph.rating),2) AS average FROM " . 
                DB_PREFIX . "photo_categories pc JOIN " .
                DB_PREFIX . "photos ph " .
                "ON ph.photo_id=pc.photo_id " .
                "WHERE pc.category_id=" . escape_string($id) .
                " GROUP BY pc.category_id";
        }
        $result=query($sql);
        if($result) {
            return fetch_assoc($result);
        } else {
            return null;
        }
    }

    /**
     * Turn the array from @see getDetails() into XML
     * @param user Show only info about photos this user can see
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(user $user, array $details=null) {
        if(!isset($details)) {
            $details=$this->getDetails($user);
        }
        $details["title"]=translate("In this category:", false);
        return parent::getDetailsXML($user, $details);
    }

   /**
    * Lookup category by name
    */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $where =
            "lower(category)='" . escape_string(strtolower($name)) . "'";

        $query = "select category_id from " . DB_PREFIX . "categories where $where";

        return category::getRecordsFromQuery("category", $query);
    }

    /**
     * Get the root category
     * @todo Once the minimum PHP version is 5.3 this could move to zoph_tree_table
     */
    public static function getRoot() {
        return new category(1);
    }
    
    /**
     * Gets the total count of records in the table
     * @todo Can be removed when minimum PHP version is 5.3 
     */
    public static function getCount($dummy=null) {
        return parent::getCount("category");
    }

    /**
     * Get Top N categories
     */
    public static function getTopN(user $user=null) {

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

        return parent::getTopN("category", $sql);

    }
}

function get_categories_select_array($user = null, $search = 0) {
    return create_tree_select_array("category", $user, null, "", null, $search);
}

function get_categories_search_array($user = null) {
    return get_categories_select_array($user, 1);
}


function create_cat_pulldown($name, $value=null, $user, $sa=null) {
    $text="";
    $id=preg_replace("/^_+/", "", $name);
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
        if(!isset($sa)) {
            $sa=get_categories_select_array($user);
        }
        $html=create_pulldown($name, $value, $sa);
    }
    return $html;
}

function get_category_count($user) {
    if($user && !$user->is_admin()) {
        $sql =
            "SELECT category_id, parent_category_id  FROM " .
            DB_PREFIX . "categories as c";
        $cats=category::getRecordsFromQuery("category", $sql);
        $cat_clean=remove_empty($cats,$user);
        return count($cat_clean);
    } else {
        return category::getCount();
    }
}
 

?>
