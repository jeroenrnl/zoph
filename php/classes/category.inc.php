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

use db\select;
use db\param;
use db\insert;
use db\delete;
use db\db;
use db\clause;
use db\selectHelper;

/**
 * A category class corresponding to the category table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class category extends zophTreeTable implements Organizer {

    use showPage;

    /** @param Name of the root node in XML responses */
    const XMLROOT="categories";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="category";

    /** @var string The name of the database table */
    protected static $tableName="categories";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("category_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("category");
    /** @var bool keep keys with insert. In most cases the keys are set
                  by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="categories.php?parent_category_id=";
    /** @var int cached photocount */
    protected $photoCount;
    /** @var int cached photoTotalCount */
    protected $photoTotalCount;

    public static $categoryCache=null;

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

    public static function getAll() {
        if (static::$categoryCache) {
            return static::$categoryCache;
        }
        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("*", "name" => "category"));

        $user=user::getCurrent();
        if (!$user->canSeeAllPhotos()) {
            $userQry=new select(array("c" => "categories"));
            $userQry->addFields(array("category_id", "parent_category_id"));

            $userQry = selectHelper::expandQueryForUser($userQry);

            $categories=static::getRecordsFromQuery($userQry);

            $ids=static::getAllAncestors($categories);
            if (sizeof($ids)==0) {
                return array();
            }
            $ids=new param(":catid", array_values($ids), PDO::PARAM_INT);
            $qry->addParam($ids);
            $qry->where(clause::InClause("c.category_id", $ids));

        }
        static::$categoryCache=static::getRecordsFromQuery($qry);
        return static::$categoryCache;
    }


    /**
     * Get sub-categories
     * @param string order
     */
    public function getChildren($order="name") {
        if (!in_array($order,
            array("name", "sortname", "oldest", "newest", "first", "last", "lowest", "highest", "average", "random"))) {
            $order="name";
        }

        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("*", "name" => "category"));

        $categories=static::getAll();
        $catIds=array();
        foreach ($categories as $category) {
            $catIds[]=$category->getId();
        }

        if (sizeof($catIds)==0) {
            return array();
        }

        $ids=new param(":catid", $catIds, PDO::PARAM_INT);
        $qry->addParam($ids);
        $where=clause::InClause("c.category_id", $ids);

        $parent=new clause("parent_category_id=:parentid");

        $qry->addParam(new param(":parentid", (int) $this->getId(), PDO::PARAM_INT));
        $qry->addGroupBy("c.category_id");

        $qry=selectHelper::addOrderToQuery($qry, $order);

        if ($order!="name") {
            $qry->addOrder("name");
        }

        if ($where instanceof clause) {
            $where->addAnd($parent);
        } else {
            $where=$parent;
        }

        if ($where instanceof clause) {
            $qry->where($where);
        }
        $this->children=static::getRecordsFromQuery($qry);
        return $this->children;
    }

    /**
     * Get count of photos in this category
     * @todo This function is very similar to album::getPhotoCount, should be merged
     */
    public function getPhotoCount() {
        if ($this->photoCount) {
            return $this->photoCount;
        }

        $qry=new select(array("pc" => "photo_categories"));

        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        $where=new clause("category_id = :cat_id");
        $qry->addParam(new param(":cat_id", $this->getId(), PDO::PARAM_INT));

        $qry = selectHelper::expandQueryForUser($qry);

        $qry->where($where);
        $count=$qry->getCount();
        $this->photoCount=$count;
        return $count;
    }

    /**
     * Get count of photos for this category and all subcategories
     */
    public function getTotalPhotoCount() {
        $where=null;

        if ($this->photoTotalCount) {
            return $this->photoTotalCount;
        }

        $qry=new select(array("pc" => "photo_categories"));
        $qry->join(array("p" => "photos"), "pc.photo_id = p.photo_id");
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));

        $qry = selectHelper::expandQueryForUser($qry);

        if ($this->get("parent_category_id")) {
            $id_list=null;
            $this->getBranchIdArray($id_list);
            $ids=new param(":cat_id", $id_list, PDO::PARAM_INT);
            $qry->addParam($ids);
            $catids=clause::InClause("category_id", $ids);
            if ($where instanceof clause) {
                $where->addAnd($catids);
            } else {
                $where=$catids;
            }
        }

        if ($where instanceof clause) {
            $qry->where($where);
        }

        $count=$qry->getCount();
        $this->photoTotalCount=$count;
        return $count;
    }

    /**
     * Get array that can be used to create an edit form
     * @todo Returns HTML, move into template
     */
    public function getEditArray() {
        if ($this->isRoot()) {
            $parent=array(
                translate("parent category"),
                translate("Categories"));
        } else {
            $parent=array(
                translate("parent category"),
                static::createPulldown("parent_category_id", $this->get("parent_category_id"))
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
                         template::createSelectArray(pageset::getRecords("title"), array("title"), true))),
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
     * Get coverphoto for this category.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @param bool choose autocover from this category AND children
     * @return photo coverphoto
     * @todo This function is almost equal to album::getAutoCover(), should be merged
     */
    public function getAutoCover($autocover=null,$children=false) {
        $coverphoto=$this->getCoverphoto();
        if ($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("photo_id" => "DISTINCT ar.photo_id"));
        $qry->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id")
            ->join(array("pc" => "photo_categories"), "pc.photo_id = ar.photo_id");

        if ($children) {
            $ids=new param(":ids",$this->getBranchIdArray(), PDO::PARAM_INT);
            $qry->addParam($ids);
            $where=clause::InClause("pc.category_id", $ids);
        } else {
            $where=new clause("pc.category_id=:id");
            $qry->addParam(new param(":id", $this->getId(), PDO::PARAM_INT));
        }

        $qry = selectHelper::expandQueryForUser($qry);

        $qry=selectHelper::getAutoCoverOrder($qry, $autocover);
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
        $qry->join(array("p" => "photos"), "pc.photo_id = p.photo_id")
            ->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id");

        $qry->addGroupBy("pc.category_id");

        $where=new clause("pc.category_id=:catid");
        $qry->addParam(new param(":catid", $this->getId(), PDO::PARAM_INT));

        $qry = selectHelper::expandQueryForUser($qry);

        $qry->where($where);


        $result=db::query($qry);
        if ($result) {
            return $result->fetch(PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    /**
     * Turn the array from @see getDetails() into XML
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(array $details=null) {
        if (!isset($details)) {
            $details=$this->getDetails();
        }
        $details["title"]=translate("In this category:", false);
        return parent::getDetailsXML($details);
    }

    /**
     * Lookup category by name
     * @param string name
     * @todo This function is almost equal to album::getByName() these should be merged
     */
    public static function getByName($name, $like=false) {
        if (empty($name)) {
            return false;
        }
        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("category_id"));
        if ($like) {
            $qry->where(new clause("lower(category) LIKE :name"));
            $qry->addParam(new param(":name", "%" . strtolower($name) . "%", PDO::PARAM_STR));
        } else {
            $qry->where(new clause("lower(category)=:name"));
            $qry->addParam(new param(":name", strtolower($name), PDO::PARAM_STR));
        }
        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get Top N categories
     */
    public static function getTopN() {
        $user=user::getCurrent();

        $qry=new select(array("c" => "categories"));
        $qry->addFields(array("category_id", "category"));
        $qry->addFunction(array("count" => "count(distinct pc.photo_id)"));
        $qry->join(array("pc" => "photo_categories"), "pc.category_id=c.category_id");
        $qry->addGroupBy("c.category_id");
        $qry->addOrder("count DESC")->addOrder("c.category");
        $qry->addLimit((int) $user->prefs->get("reports_top_n"));
        if (!$user->canSeeAllPhotos()) {
            $qry->join(array("p" => "photos"), "pc.photo_id=p.photo_id");
            $qry = selectHelper::expandQueryForUser($qry);
        }
        return parent::getTopNfromSQL($qry);

    }

    /**
     * Get number of categories for the currently logged on user
     */
    public static function getCountForUser() {
        $user=user::getCurrent();

        if ($user->canSeeAllPhotos()) {
            return static::getCount();
        } else {
            $qry=new select(array("pc" => "photo_categories"));
            $qry->addFunction(array("category_id" => "distinct pc.category_id"));

            $qry = selectHelper::expandQueryForUser($qry);

            $categories=static::getRecordsFromQuery($qry);
            $ids=static::getAllAncestors($categories);
            return count($ids);
        }
    }
}
?>
