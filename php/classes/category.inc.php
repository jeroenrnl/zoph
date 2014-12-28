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
     * Add a photo to this category
     * @param photo Photo to add
     * @todo Permissions are currently not checked, this should be done before calling this function
     */
    public function addPhoto(photo $photo) {
        $qry=new insert(array("photo_categories"));
        $qry->addParam(new param(":photo_id", $photo->getId(), PDO::PARAM_INT));
        $qry->addParam(new param(":category_id", $this->getId(), PDO::PARAM_INT));
        $qry->execute();
    }

    /**
     * Remove a photo from this category
     * @param photo Photo to remove
     * @todo Permissions are currently not checked, this should be done before calling this function
     */
    public function removePhoto(photo $photo) {
        $qry=new delete(array("photo_categories"));
        $where=new clause("photo_id=:photoid");
        $where->addAnd(new clause("category_id=:catid"));
        $qry->where($where);

        $qry->addParams(array(
            new param(":photoid", $photo->getId(), PDO::PARAM_INT),
            new param(":catid", $this->getId(), PDO::PARAM_INT)
        ));

        $qry->execute();
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
    public function getChildren($order="name") {
        if(!in_array($order, 
            array("name", "sortname", "oldest", "newest", "first", "last", "lowest", "highest", "average", "random"))) {
            $order="name";
        }
        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("*", "name" => "category"));
        $qry->where(new clause("parent_category_id=:catid"));
        
        $qry->addParam(new param(":catid", (int) $this->getId(), PDO::PARAM_INT));
        $qry->addGroupBy("c.category_id");
        
        $qry=self::addOrderToQuery($qry, $order);
        
        if($order!="name") {
            $qry->addOrder("name");
        }
        $this->children=self::getRecordsFromQuery($qry);
        return $this->children;
    }

    /**
     * Get children of this category, with categories this user cannot see, filtered out.
     * @param string sort order.
     * @return array category tree
     */
    public function getChildrenForUser($order="name") {
        return remove_empty($this->getChildren($order));
    }
    
    /**
     * Get count of photos in this category 
     * @todo This function is very similar to album::getPhotoCount, should be merged
     */
    public function getPhotoCount() {
        $db=db::getHandle();

        if ($this->photoCount) { 
            return $this->photoCount; 
        }

        $qry=new select(array("pc" => "photo_categories"));
        $qry->join(array(), array("p" => "photos"), "pc.photo_id = p.photo_id");
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        $where=new clause("category_id = :cat_id");
        $qry->addParam(new param(":cat_id", $this->getId(), PDO::PARAM_INT));
        
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = self::expandQueryForUser($qry, $where);
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

        $qry=new select(array("pc" => "photo_categories")); 
        $qry->join(array(), array("p" => "photos"), "pc.photo_id = p.photo_id");
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = self::expandQueryForUser($qry, $where);
        }

        if ($this->get("parent_category_id")) {
            $id_list=null;
            $this->getBranchIdArray($id_list);
            $ids=new param(":cat_id", $id_list, PDO::PARAM_INT);
            $qry->addParam($ids);
            $catids=clause::InClause("category_id", $ids);
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
     * @param bool choose autocover from this category AND children
     * @return photo coverphoto
     * @todo This function is almost equal to album::getAutoCover(), should be merged
     */
    public function getAutoCover($autocover=null,$children=false) {
        $coverphoto=$this->getCoverphoto();
        if($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("photo_id" => "DISTINCT ar.photo_id")); 
        $qry->join(array(), array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id")      
            ->join(array(), array("pc" => "photo_categories"), "pc.photo_id = ar.photo_id");

        if($children) {
            $ids=new param(":ids",$this->getBranchIdArray(), PDO::PARAM_INT);
            $qry->addParam($ids);
            $where=clause::InClause("pc.category_id", $ids);
        } else {
            $where=new clause("pc.category_id=:id");
            $qry->addParam(new param(":id", $this->getId(), PDO::PARAM_INT));
        }
       
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = self::expandQueryForUser($qry, $where);
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
     * @todo This function is almost equal to album::getDetails() these should be merged
     */
    public function getDetails() {
        $qry=new select(array("pc" => "photo_categories"));
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

        $where=new clause("pc.category_id=:catid");
        $qry->addParam(new param(":catid", $this->getId(), PDO::PARAM_INT));
        
        if (!user::getCurrent()->is_admin()) {
            list($qry, $where) = self::expandQueryForUser($qry, $where);
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
     * @todo This function is almost equal to category::getByName() these should be merged
     */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("category_id"));
        $qry->where(new clause("lower(category)=:name"));
        $qry->addParam(new param(":name", strtolower($name), PDO::PARAM_STR));

        return self::getRecordsFromQuery($qry);
    }

    /**
     * Get Top N categories
     */
    public static function getTopN() {
        $user=user::getCurrent();

        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("category_id", "category"));
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        $qry->join(array(), array("pc" => "photo_categories"), "pc.category_id=c.category_id");
        $qry->addGroupBy("c.category_id");
        $qry->addOrder("count DESC")->addOrder("c.category");
        $qry->addLimit((int) $user->prefs->get("reports_top_n"));
        if (!$user->is_admin()) {
            $qry->join(array(), array("p" => "photos"), "pc.photo_id=p.photo_id");
            list($qry, $where) = self::expandQueryForUser($qry);
            $qry->where($where);
        }
        return parent::getTopNfromSQL($qry);

    }

    /**
     * Get number of categories for the currently logged on user
     */
    public static function getCountForUser() {
        $user=user::getCurrent();

        if($user && $user->is_admin()) {
            return self::getCount();
        } else {
            $qry=new select(array("c" => "categories"));
            $qry->addFields(array("category_id", "parent_category_id"));
            $cats=self::getRecordsFromQuery($qry);
            $cat_clean=remove_empty($cats);
            return count($cat_clean);
        }
    }
}    
?>
