<?php
/**
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
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */

/**
 * A category class corresponding to the category table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class category extends zophTreeTable implements Organizer {

    /** @param Name of the root node in XML responses */
    const XMLROOT="categories";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="category";



    /** @var string The name of the database table */
    protected static $table_name="categories";
    /** @var array List of primary keys */
    protected static $primary_keys=array("category_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("category");
    /** @var bool keep keys with insert. In most cases the keys are set 
                  by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="categories.php?parent_category_id=";
    /** @var int cached photocount */
    protected $photoCount;
    /** @var int cached photoTotalCount */
    protected $photoTotalCount;

    /**
     * Add a photo to this album
     * @param photo Photo to add
     * @todo Permissions are currently not checked, this should be done before calling this function
     */
    public function addPhoto(photo $photo) {
        $sql = "INSERT INTO " . DB_PREFIX . "photo_categories " .
            "(photo_id, category_id) VALUES ('" .
            escape_string($photo->getId()) . "', '" .
            escape_string($this->getId()) . "')";
        query($sql);
    }

    /**
     * Remove a photo from this album
     * @param photo Photo to remove
     * @todo Permissions are currently not checked, this should be done before calling this function
     */
    public function removePhoto(photo $photo) {
        $sql = "DELETE FROM " . DB_PREFIX . "photo_categories " .
            "WHERE photo_id = '" . escape_string($photo->getId()) . "'" .
            " AND category_id = '" . escape_string($this->getId()) . "'";
        query($sql);
    }
    
    /**
     * Delete category
     */
    public function delete() {
        parent::delete(array("photo_categories"));
    }

    /**
     * Get the name of this category
     * @todo can be moved into zophTable?
     */
    public function getName() {
        return $this->get("category");
    }

    /**
     * Get sub-categories
     * @param string order
     */
    public function getChildren($order=null) {
        $order_fields="";
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
            DB_PREFIX . "categories as c " .
            "WHERE parent_category_id=" . $id .
            " GROUP BY c.category_id " .
            $order;

        $this->children=self::getRecordsFromQuery($sql);
        return $this->children;
    }
    
    /**
     * Get children of this category, with categories this user cannot see, filtered out.
     * @param string sort order.
     * @return array category tree
     */
    public function getChildrenForUser($order=null) {
        return remove_empty($this->getChildren($order));
    }
    
    /**
     * Get count of photos in this album
     */
    public function getPhotoCount() {
        $db=db::getHandle();

        if ($this->photoCount) { 
            return $this->photoCount; 
        }

        $id = $this->getId();
        $qry=new query(array("pc" => "photo_categories"));
        $qry->join(array(), array("p" => "photos"), "pc.photo_id = p.photo_id");
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        $where=new clause("category_id = :cat_id", array(new param(":cat_id", $id, PDO::PARAM_INT)));
        
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = $this->expandQueryForUser($qry, $where);
        }

        $qry->where($where);
        $count=self::getCountFromQuery($qry);
        $this->photoCount=$count;
        return $count;
    }

    /**
     * Get count of photos for this category and all subcategories
     */
    public function getTotalPhotoCount() {
        $where=null;
        $db=db::getHandle();

        if ($this->photoTotalCount) { 
            return $this->photoTotalCount; 
        }

        $qry=new query(array("pc" => "photo_categories")); 
        $qry->join(array(), array("p" => "photos"), "pc.photo_id = p.photo_id");
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = $this->expandQueryForUser($qry, $where);
        }

        if ($this->get("parent_category_id")) {
            $id_list=null;
            $this->getBranchIdArray($id_list);
            $ids=new param(":cat_id", $id_list, PDO::PARAM_INT);

            $catids=new clause("category_id IN (" . implode(", ", $ids->getName()) .")", array($ids));
            if($where instanceof clause) {
                $where->addAnd($catids);
            } else { 
                $where=$catids;
            }
        }

        if($where instanceof clause) {
            $qry->where($where);
        }

        $count=self::getCountFromQuery($qry);
        $this->photoTotalCount=$count;
        return $count;
    }

    /**
     * Get array that can be used to create an edit form
     * @todo Returns HTML, move into template
     */
    public function getEditArray() {
        if($this->isRoot()) {
            $parent=array(
                translate("parent category"),
                translate("Categories"));
        } else {
            $parent=array(
                translate("parent category"),
                self::createPulldown("parent_category_id", $this->get("parent_category_id"))
            );
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
                    template::createPulldown("pageset", $this->get("pageset"), 
                        get_pageset_select_array())),
            "sortname" =>
                array(
                    translate("sort name"),
                    create_text_input("sortname",
                        $this->get("sortname"))),
            "sortorder" =>
                array(
                    translate("category sort order"),
                    template::createPhotoFieldPulldown("sortorder", $this->get("sortorder")))
        );
    }

    /**
     * Create a link to this category
     * @todo returns HTML, needs to be replaced by getURL()
     */
    public function getLink() {
        if ($this->get("parent_category_id")) {
            $name = $this->get("category");
        }
        else {
            $name = translate("Categories");
        }
        return "<a href=\"" . $this->getURL() . "\">$name</a>";
    }

    /**
     * Return an URL for this category
     * @todo Can be moved into zophTable
     */
    public function getURL() {
        return "categories.php?parent_category_id=" . $this->getId();
    }

    /**
     * Get coverphoto for this category.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @param bool choose autocover from this album AND children
     * @return photo coverphoto
     */
    public function getAutoCover($autocover=null,$children=false) {
        $coverphoto=$this->getCoverphoto();
        if($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $qry=new query(array("p" => "photos"));
        $qry->addFunction(array("photo_id" => "DISTINCT ar.photo_id")); 
        $qry->join(array(), array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id")      
            ->join(array(), array("pc" => "photo_categories"), "pc.photo_id = ar.photo_id");

        if($children) {
            $ids=new param(":ids",$this->getBranchIdArray(), PDO::PARAM_INT);
            $where=new clause("pc.category_id IN (" . implode(", ", $ids->getName()) . ")", array($ids));
        } else {
            $where=new clause("pc.category_id=:id", array(new param(":id", $this->getId(), PDO::PARAM_INT)));
        }
       
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = $this->expandQueryForUser($qry, $where);
        }

        $qry=self::getAutoCoverOrderNew($qry, $autocover);
        $qry->where($where);
        $coverphotos=photo::getRecordsFromQuery($qry);
        $coverphoto=array_shift($coverphotos);

        if ($coverphoto instanceof photo) {
            $coverphoto->lookup();
            return $coverphoto;
        } else if (!$children) {
            // No photos found in this cat... let's look again, but now 
            // also in subcat...
            return $this->getAutoCover($autocover, true);

        }
    }

    /**
     * Get autocomplete preference for categories, for the current user
     */
    public static function getAutocompPref() {
        $user=user::getCurrent();
        return ($user->prefs->get("autocomp_categories") && conf::get("interface.autocomplete"));
    }

    /**
     * Get details (statistics) about this category from db
     * @return array Array with statistics
     */
    public function getDetails() {
        $user=user::getCurrent();
        $user_id = (int) $user->getId();
        $id = (int) $this->getId();

        $qry=new query(array("pc" => "photo_categories"));
        $qry->addFunction(array(
            "count"     => "COUNT(DISTINCT p.photo_id)",
            "oldest"    => "MIN(DATE_FORMAT(CONCAT_WS(' ',p.date,p.time), GET_FORMAT(DATETIME, 'ISO')))",
            "newest"    => "MAX(DATE_FORMAT(CONCAT_WS(' ',p.date,p.time), GET_FORMAT(DATETIME, 'ISO')))",
            "first"     => "MIN(p.timestamp)",
            "last"      => "MAX(p.timestamp)",
            "lowest"    => "ROUND(MIN(ar.rating),1)",
            "highest"   => "ROUND(MAX(ar.rating),1)",
            "average"   => "ROUND(AVG(ar.rating),2)"));
        $qry->join(array(), array("p" => "photos"), "pc.photo_id = p.photo_id")      
            ->join(array(), array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id");

        $qry->addGroupBy("pc.category_id");

        $where=new clause("pc.category_id=:catid", array(new param(":catid", $this->getId(), PDO::PARAM_INT)));
        
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = $this->expandQueryForUser($qry, $where);
        }

        $qry->where($where);


        $result=query($qry);
        if($result) {
            return fetch_assoc($result);
        } else {
            return null;
        }
    }

    /**
     * Turn the array from @see getDetails() into XML
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(array $details=null) {
        if(!isset($details)) {
            $details=$this->getDetails();
        }
        $details["title"]=translate("In this category:", false);
        return parent::getDetailsXML($details);
    }

    /**
     * Lookup category by name
     * @param string name
     */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $where =
            "lower(category)='" . escape_string(strtolower($name)) . "'";

        $query = "select category_id from " . DB_PREFIX . "categories where $where";

        return self::getRecordsFromQuery($query);
    }

    /**
     * Get Top N categories
     */
    public static function getTopN() {
        $user=user::getCurrent();
        if ($user->is_admin()) {
            $sql =
                "select cat.*, count(*) as count from " .
                DB_PREFIX . "categories as cat, " .
                DB_PREFIX . "photo_categories as pc " .
                "where pc.category_id = cat.category_id " .
                "group by cat.category_id " .
                "order by count desc, cat.category " .
                "limit 0, " . escape_string($user->prefs->get("reports_top_n"));
        } else {
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
                "LIMIT 0, " . escape_string($user->prefs->get("reports_top_n"));
        }

        return parent::getTopNfromSQL($sql);

    }

    /**
     * Get number of categories for the currently logged on user
     */
    public static function getCountForUser() {
        $user=user::getCurrent();

        if($user && $user->is_admin()) {
            return self::getCount();
        } else {
            $sql =
                "SELECT category_id, parent_category_id  FROM " .
                DB_PREFIX . "categories as c";
            $cats=self::getRecordsFromQuery($sql);
            $cat_clean=remove_empty($cats);
            return count($cat_clean);
        }
    }
}    
?>
