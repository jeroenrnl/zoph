<?php
/**
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
 * Photo album
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class album extends zophTreeTable implements Organizer {

    use showPage;

    /** @param Name of the root node in XML responses */
    const XMLROOT="albums";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="album";

    /** @var string The name of the database table */
    protected static $tableName="albums";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("album_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("album");
    /** @var bool keep keys with insert. In most cases the keys
                  are set by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="albums.php?parent_album_id=";

    /** @var Cache the count of photos */
    private $photoCount;

    /**
     * lookup this album in the db
     */
    public function lookup() {
        $user=user::getCurrent();
        $id = $this->getId();
        if (!is_numeric($id)) {
            die("album_id must be numeric");
        }
        if (!$id) {
            return;
        }

        $qry=new select(array("a" => "albums"));
        $distinct=true;
        $qry->addFields(array("*"), $distinct);
        $where=new clause("a.album_id=:albumid");
        $qry->addParam(new param(":albumid", (int) $this->getId(), PDO::PARAM_INT));

        if (!$user->isAdmin()) {
            $qry->join(array("gp" => "group_permissions"), "a.album_id=gp.album_id")
                ->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
            $where->addAnd(new clause("gu.user_id=:userid"));
            $qry->addParam(new param(":userid", (int) $user->getId(), PDO::PARAM_INT));
        }
        $qry->where($where);
        return $this->lookupFromSQL($qry);
    }

    /**
     * Add a photo to this album
     * @param photo Photo to add
     */
    public function addPhoto(photo $photo) {
        $user=user::getCurrent();
        if ($user->isAdmin() || $user->getAlbumPermissions($this)->get("writable")) {
            $qry=new insert(array("photo_albums"));
            $qry->addParam(new param(":photo_id", (int) $photo->getId(), PDO::PARAM_INT));
            $qry->addParam(new param(":album_id", (int) $this->getId(), PDO::PARAM_INT));
            $qry->execute();
        }
    }

    /**
     * Remove a photo from this album
     * @param photo Photo to remove
     */
    public function removePhoto(photo $photo) {
        $user=user::getCurrent();
        if ($user->isAdmin() || $user->getAlbumPermissions($this)->get("writable")) {
            $qry=new delete("photo_albums");
            $where=new clause("photo_id=:photo_id");
            $where->addAnd(new clause("album_id=:album_id"));
            $qry->where($where);
            $qry->addParams(array(
                new param(":photo_id", (int) $photo->getId(), PDO::PARAM_INT),
                new param(":album_id", (int) $this->getId(), PDO::PARAM_INT)
            ));
            $qry->execute();
        }
    }

    /**
     * Delete this album
     */
    public function delete() {
        parent::delete(array("photo_albums", "group_permissions"));
        $users = user::getRecords("user_id", array("lightbox_id" => $this->get("album_id")));
        if ($users) {
            foreach ($users as $user) {
                $user->setFields(array("lightbox_id" => null));
                $user->update();
            }
        }
    }

    /**
     * Get the name of this album
     */
    public function getName() {
        return $this->get("album");
    }

    /**
     * Get the subalbums of this album
     * @param string optional order
     * @return array of albums
     */
    public function getChildren($order=null) {
        $user=user::getCurrent();

        $qry=new select(array("a" => "albums"));
        $qry->addFields(array("*", "name"=>"album"));

        $where=new clause("parent_album_id=:album_id");

        $qry->addGroupBy("a.album_id");

        $qry->addParam(new param(":album_id", (int) $this->getId(), PDO::PARAM_INT));

        $qry=selectHelper::addOrderToQuery($qry, $order);

        if ($order!="name") {
            $qry->addOrder("name");
        }

        if (!$user->isAdmin()) {
            $qry->join(array("gp" => "group_permissions"), "a.album_id=gp.album_id")
                ->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
            $where->addAnd(new clause("gu.user_id=:userid"));
            $qry->addParam(new param(":userid", (int) $user->getId(), PDO::PARAM_INT));
        }

        $qry->where($where);
        $this->children=static::getRecordsFromQuery($qry);
        return $this->children;
    }

    /**
     * Get details (statistics) about this album from db
     * @return array Array with statistics
     * @todo this function is almost equal to category::getDetails() they should be merged
     */
    public function getDetails() {
        $user=user::getCurrent();

        $qry=new select(array("pa" => "photo_albums"));
        $qry->addFunction(array(
            "count"     => "COUNT(DISTINCT p.photo_id)",
            "oldest"    => "MIN(DATE_FORMAT(CONCAT_WS(' ',p.date,p.time), GET_FORMAT(DATETIME, 'ISO')))",
            "newest"    => "MAX(DATE_FORMAT(CONCAT_WS(' ',p.date,p.time), GET_FORMAT(DATETIME, 'ISO')))",
            "first"     => "MIN(p.timestamp)",
            "last"      => "MAX(p.timestamp)",
            "lowest"    => "ROUND(MIN(ar.rating),1)",
            "highest"   => "ROUND(MAX(ar.rating),1)",
            "average"   => "ROUND(AVG(ar.rating),2)"));
        $qry->join(array("p" => "photos"), "pa.photo_id = p.photo_id")
            ->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id");

        $qry->addGroupBy("pa.album_id");

        $where=new clause("pa.album_id=:albid");
        $qry->addParam(new param(":albid", $this->getId(), PDO::PARAM_INT));

        if (!$user->isAdmin()) {
            $qry->join(array("gp" => "group_permissions"), "pa.album_id=gp.album_id")
                ->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
            $where->addAnd(new clause("gu.user_id=:userid"));
            $qry->addParam(new param(":userid", (int) $user->getId(), PDO::PARAM_INT));
        }

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
        $details["title"]=translate("In this album:", false);
        return parent::getDetailsXML($details);
    }

    /**
     * Get count of photos in this album
     * @todo This function is very similar to album::getPhotoCount, should be merged
     */
    public function getPhotoCount() {
        if ($this->photoCount) {
            return $this->photoCount;
        }

        $qry=new select(array("pa" => "photo_albums"));
        $qry->join(array("p" => "photos"), "pa.photo_id = p.photo_id");
        $qry->addFunction(array("count" => "count(distinct pa.photo_id)"));
        $where=new clause("pa.album_id = :alb_id");
        $qry->addParam(new param(":alb_id", $this->getId(), PDO::PARAM_INT));

        if (!user::getCurrent()->isAdmin()) {
            $qry = selectHelper::expandQueryForUser($qry);
        }

        $qry->where($where);
        $count=$qry->getCount();
        $this->photoCount=$count;
        return $count;
    }

    /**
     * Return the amount of photos in this album and it's children
     */
    public function getTotalPhotoCount() {
        // Without the lookup, parent_album_id is not available!
        $this->lookup();

        $qry=new select(array("pa" => "photo_albums"));
        $qry->addFunction(array("count" => "COUNT(DISTINCT pa.photo_id)"));

        $id_list=null;
        $this->getBranchIdArray($id_list);
        $ids=new param(":alb_id", $id_list, PDO::PARAM_INT);
        $qry->addParam($ids);
        $where=clause::InClause("pa.album_id", $ids);

        if (!user::getCurrent()->isAdmin()) {
            $qry=selectHelper::expandQueryForUser($qry);
        }
        $qry->where($where);

        return $qry->getCount();
    }

    /**
     * Get the photos in this album
     * Does NOT check user permissions!
     */
    public function getPhotos() {
        $qry=new select(array("pa" => "photo_albums"));
        $qry->addFields(array("photo_id" => "pa.photo_id"));
        $qry->where(new clause("pa.album_id = :alb_id"));
        $qry->addParam(new param(":alb_id", $this->getId(), PDO::PARAM_INT));

        return photo::getRecordsFromQuery($qry);
    }

    /**
     * Get array of fields/values to create an edit form
     * @return array fields/values
     */
    public function getEditArray() {
        if ($this->isRoot()) {
            $parent=array (
                translate("parent album"),
                translate("Albums"));
        } else {
            $parent=array (
                translate("parent album"),
                static::createPulldown("parent_album_id", $this->get("parent_album_id")));
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
                    template::createPulldown("pageset", $this->get("pageset"),
                        template::createSelectArray(pageset::getRecords("title"), array("title"), true))),
            "sortname" =>
                array(
                    translate("sort name"),
                    create_text_input("sortname",
                        $this->get("sortname"))),
            "sortorder" =>
                array(
                    translate("album sort order"),
                    template::createPhotoFieldPulldown("sortorder", $this->get("sortorder")))
        );
    }

    /**
     * Get a link to this album
     * @return link to this album
     * @todo returns HTML, should be phased out in favour of getURL()
     */
    public function getLink() {
        if ($this->get("parent_album_id")) {
            $name = $this->get("album");
        }
        else {
            $name = "Albums";
        }

        return "<a href=\"" .  $this->getURL() . "\">$name</a>";
    }

    /**
     * Get coverphoto for this album.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @param bool choose autocover from this album AND children
     * @return photo coverphoto
     * @todo This function is almost equal to category::getAutoCover(), should be merged
     */
    public function getAutoCover($autocover=null, $children=false) {
        $coverphoto=$this->getCoverphoto();
        if ($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("photo_id" => "DISTINCT ar.photo_id"));
        $qry->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id")
            ->join(array("pa" => "photo_albums"), "pa.photo_id = ar.photo_id");

        if ($children) {
            $ids=new param(":ids",$this->getBranchIdArray(), PDO::PARAM_INT);
            $qry->addParam($ids);
            $where=clause::InClause("pa.album_id", $ids);
        } else {
            $where=new clause("pa.album_id=:id");
            $qry->addParam(new param(":id", $this->getId(), PDO::PARAM_INT));
        }

        if (!user::getCurrent()->isAdmin()) {
            $qry = selectHelper::expandQueryForUser($qry);
        }

        $qry=selectHelper::getAutoCoverOrder($qry, $autocover);
        $qry->where($where);
        $coverphotos=photo::getRecordsFromQuery($qry);
        $coverphoto=array_shift($coverphotos);

        if ($coverphoto instanceof photo) {
            $coverphoto->lookup();
            return $coverphoto;
        } else if (!$children) {
            // No photos found in this album... let's look again, but now
            // also in subalbum...
            return $this->getAutoCover($autocover, true);
        }
    }

    /**
     * Lookup album by name
     * @param string name
     * @todo This function is almost equal to category::getByName(), should be merged
     */
    public static function getByName($name, $like=false) {
        if (empty($name)) {
            return false;
        }
        $qry=new select(array("a" => "albums"));
        $qry->addFields(array("album_id"));
        if ($like) {
            $qry->where(new clause("lower(album) LIKE :name"));
            $qry->addParam(new param(":name", "%" . strtolower($name) . "%", PDO::PARAM_STR));
        } else {
            $qry->where(new clause("lower(album)=:name"));
            $qry->addParam(new param(":name", strtolower($name), PDO::PARAM_STR));
        }
        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get Top N albums
     */
    public static function getTopN() {
        $user=user::getCurrent();

        $qry=new select(array("a" => "albums"));
        $qry->addFields(array("album_id", "album"));
        $qry->addFunction(array("count" => "count(distinct pa.photo_id)"));
        $qry->join(array("pa" => "photo_albums"), "pa.album_id=a.album_id");
        $qry->addGroupBy("a.album_id");
        $qry->addOrder("count DESC")->addOrder("a.album");
        $qry->addLimit((int) $user->prefs->get("reports_top_n"));
        if (!$user->isAdmin()) {
            $qry->join(array("gp" => "group_permissions"), "a.album_id=gp.album_id")
                ->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
            $qry->where(new clause("gu.user_id=:userid"));
            $qry->addParam(new param(":userid", (int) $user->getId(), PDO::PARAM_INT));
        }
        return parent::getTopNfromSQL($qry);

    }

    /**
     * Get autocomplete preference for albums for the current user
     */
    public static function getAutocompPref() {
        $user=user::getCurrent();
        return ($user->prefs->get("autocomp_albums") && conf::get("interface.autocomplete"));
    }
    /**
     * Return all albums
     */
    public static function getAll() {
        $user=user::getCurrent();

        $qry=new select(array("a" => "albums"));
        $qry->addFields(array("album_id"), true);
        $qry->addOrder("album");

        if (!$user->isAdmin()) {
            $qry->join(array("gp" => "group_permissions"), "gp.album_id = a.album_id");
            $qry->join(array("gu" => "groups_users"), "gp.group_id = gu.group_id");
            $qry->where(new clause("gu.user_id=:userid"));
            $qry->addParam(new param(":userid", $user->getId(), PDO::PARAM_INT));
        }


        return static::getRecordsFromQuery($qry);
    }
    /**
     * Get albums newer than a certain date
     * @param user get albums for this user
     * @param string date
     */
    public static function getNewer(user $user, $date) {
        $qry=new select(array("a" => "albums"));
        $qry->addFields(array("album_id"), true);
        $qry->join(array("gp" => "group_permissions"), "a.album_id=gp.album_id")
            ->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
        $where=new clause("user_id=:userid");
        $where->addAnd(new clause("gp.changedate>:changedate"));

        $qry->addParams(array(
                new param(":userid", $user->getId(), PDO::PARAM_INT),
                new param(":changedate", $date, PDO::PARAM_STR)));
        $qry->where($where)
            ->addOrder("a.album_id");
        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get number of albums for the currently logged on user
     */
    public static function getCount() {
        $user=user::getCurrent();
        $qry=new select(array("a" => "albums"));
        $qry->addFunction(array("count" => "COUNT(DISTINCT a.album_id)"));

        if (!$user->isAdmin()) {
            $qry->join(array("gp" => "group_permissions"), "a.album_id=gp.album_id")
                ->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
            $where=new clause("user_id=:userid");
            $qry->addParam(new param(":userid", $user->getId(), PDO::PARAM_INT));
            $qry->where($where);
        }
        return $qry->getCount();
    }

    /**
     * Get an array of id => name to build a non-hierarchical array
     * this function always returns ALL albums and does NOT check user permissions
     * @retrun array albums
     */
    public static function getSelectArray() {
        $albums=static::getRecords();
        $selectArray=array(null => "");
        foreach ($albums as $album) {
            $selectArray[(string) $album->getId()] = $album->getName();
        }
        return $selectArray;
    }
}

?>
