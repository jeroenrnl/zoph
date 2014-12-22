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

/**
 * Photo album
 *
 * @package Zoph
 * @author Jason Geiger
 * @auther Jeroen Roos
 */
class album extends zophTreeTable implements Organizer {

    /** @param Name of the root node in XML responses */
    const XMLROOT="albums";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="album";

    /** @var string The name of the database table */
    protected static $table_name="albums";
    /** @var array List of primary keys */
    protected static $primary_keys=array("album_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("album");
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
        if(!is_numeric($id)) { 
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

        if (!$user->is_admin()) {
            $qry->join(array(), array("gp" => "group_permissions"), "a.album_id=gp.album_id")
                ->join(array(), array("gu" => "groups_users"), "gp.group_id=gu.group_id");
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
        if($user->is_admin() || $user->get_album_permissions($this->getId())->get("writable")) {
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
        if($user->is_admin() || $user->get_album_permissions($this->getId())->get("writable")) {
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
                $user->setFields(array("lightbox_id" => "null"));
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
        $qry=new select(array("a" => "albums"));
        $qry->addFields(array("*", "name"=>"album"));
        $qry->join(array(), array("pa" => "photo_albums"), "a.album_id=pa.album_id", "LEFT")
            ->join(array(), array("p"  => "photos"      ), "pa.photo_id=p.photo_id", "LEFT");

        $qry->where(new clause("parent_album_id=:album_id"));
        $qry->addGroupBy("a.album_id");

        $qry->addParam(new param(":album_id", (int) $this->getId(), PDO::PARAM_INT));

        $qry=self::addOrderToQuery($qry, $order);
        
        if($order!="name") {
            $qry->addOrder("name");
        }

        $this->children=self::getRecordsFromQuery($qry);
        return $this->children;
    }

    /**
     * Get the subalbums of this album, for the current user
     * @param string optional order
     * @return array of albums
     */
    public function getChildrenForUser($order=null) {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            return $this->getChildren($order);
        }

        $sql_order="";

        $order_fields="";
        if($order && $order!="name") {
            $order_fields=get_sql_for_order($order);
            $sql_order=" ORDER BY " . $order . ", name ";
        } else if ($order=="name") {
            $sql_order=" ORDER BY name ";
        }

        $sql = "SELECT a.*, album as name " .
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
            "WHERE gu.user_id=" . (int) $user->getId() .
            " AND parent_album_id=" . (int) $this->getId() .
            " GROUP BY album_id" .
            escape_string($sql_order);
        $this->children=self::getRecordsFromQuery($sql);
        return $this->children;
    }

    /**
     * Get details (statistics) about this album from db
     * @return array Array with statistics
     */
    public function getDetails() {
        $id = (int) $this->getId();
        $user=user::getCurrent();
        $user_id = (int) $user->getId();

        if (!$user->is_admin()) {
            $sql = "SELECT " .
                "COUNT(ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photo_albums pa JOIN " .
                DB_PREFIX . "photos ph " .
                "ON ph.photo_id=pa.photo_id JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON ph.photo_id = ar.photo_id LEFT JOIN " .
                DB_PREFIX . "group_permissions gp " .
                "ON pa.album_id=gp.album_id LEFT JOIN " . 
                DB_PREFIX . "groups_users gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE ph.level<gp.access_level AND " .
                "gu.user_id=" . escape_string($user_id) . " AND " .
                "pa.album_id=" . escape_string($id) .
                " GROUP BY pa.album_id";
        } else {
            $sql = "SELECT ".
                "COUNT(ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photo_albums pa JOIN " .
                DB_PREFIX . "photos ph " .
                "ON ph.photo_id=pa.photo_id JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON ph.photo_id = ar.photo_id " .
                "WHERE pa.album_id=" . escape_string($id) .
                " GROUP BY pa.album_id";
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
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(array $details=null) {
        if(!isset($details)) {
            $details=$this->getDetails();
        }
        $details["title"]=translate("In this album:", false);
        return parent::getDetailsXML($details);
    }

    /**
     * Return the amount of photos in this album
     */
    public function getPhotoCount() {
        $user=user::getCurrent();

        if ($this->photoCount) { return $this->photoCount; }

        $id = $this->get("album_id");

        if ($user->is_admin()) {
            $sql =
                "SELECT COUNT(*) FROM " .
                DB_PREFIX . "photo_albums " .
                "WHERE album_id = '" .  escape_string($id) . "'";
        } else {
            $sql =
                "SELECT COUNT(*) FROM " .
                DB_PREFIX . "photo_albums AS pa JOIN " .
                DB_PREFIX . "photos AS p ON " .
                "pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp ON " .
                "gp.album_id = pa.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu ON " .
                "gp.group_id = gu.group_id " .
                "WHERE pa.album_id = " . escape_string($id) .
                " AND gu.user_id = '" . escape_string($user->get("user_id")) .
                "' AND gp.access_level >= p.level";
        }

        return self::getCountFromQuery($sql);
    }

    /**
     * Return the amount of photos in this album and it's children
     */
    public function getTotalPhotoCount() {
        $user=user::getCurrent();
        // Without the lookup, parent_album_id is not available!
        $this->lookup();
        if ($this->get("parent_album_id")>0) {
            $id_list = $this->getBranchIds();
            $id_constraint = "pa.album_id in ($id_list)";
        } else if ($this->get("parent_album_id")==="0") {
            $id_constraint="";
        } else {
            return 0;
        }
        if ($user->is_admin()) {
            $sql = "SELECT COUNT(distinct pa.photo_id) FROM " .
                DB_PREFIX . "photo_albums pa ";

            if ($id_constraint) {
                $sql .= " WHERE $id_constraint";
            }
        } else {
            $sql =
                "SELECT COUNT(distinct pa.photo_id) FROM " .
                DB_PREFIX . "photo_albums as pa JOIN " .
                DB_PREFIX . "photos as p ON " .
                "pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp ON " .
                "pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu ON " .
                "gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . escape_string($user->get("user_id")) .
                "' AND gp.access_level >= p.level";

            if ($id_constraint) {
                $sql .= " and $id_constraint";
            }
        }

        return self::getCountFromQuery($sql);
    }

    /**
     * Get array of fields/values to create an edit form
     * @return array fields/values
     */
    public function getEditArray() {
        if($this->isRoot()) {
            $parent=array (
                translate("parent album"),
                translate("Albums"));
        } else {
            $parent=array (
                translate("parent album"),
                self::createPulldown("parent_album_id", $this->get("parent_album_id")));
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
                        get_pageset_select_array())),
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
     * Return the URL to the current album
     * This should eventually replace getLink, since that contains
     * HTML.
     * @todo PHP 5.3 -> move into zophTable
     * @return string URL
     */
    public function getURL() {
        return "albums.php?parent_album_id=" . $this->getId();
    }

    /**
     * Get coverphoto for this album.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @param bool choose autocover from this album AND children
     * @return photo coverphoto
     */
    public function getAutoCover($autocover=null, $children=false) {
        $user=user::getCurrent();

        $coverphoto=$this->getCoverphoto();
        if($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $order=self::getAutoCoverOrder($autocover);
        if($children) {
            $album_where=" WHERE pa.album_id in (" . $this->getBranchIds() .")";
        } else {
            $album_where=" WHERE pa.album_id =" .$this->get("album_id");
        }
        if ($user->is_admin()) {
            $sql =
                "select distinct ar.photo_id from " .
                DB_PREFIX . "photos AS p JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar " .
                "ON ar.photo_id=p.photo_id JOIN " .
                DB_PREFIX . "photo_albums pa ON" .
                " pa.photo_id = ar.photo_id" .
                $album_where .
                " " . $order;
        } else {
            $sql=
                "select distinct p.photo_id from " .
                DB_PREFIX . "photos AS p JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON p.photo_id = ar.photo_id JOIN " .
                DB_PREFIX . "photo_albums AS pa" .
                " ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp ON " .
                "pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu ON " .
                "gp.group_id = gu.group_id " .
                $album_where .
                " AND gu.user_id =" . 
                " '" . escape_string($user->get("user_id")) . "'" .
                " AND pa.photo_id = p.photo_id " .
                " AND gp.access_level >= p.level " .
                $order;
        }
        $coverphotos=photo::getRecordsFromQuery($sql);
        $coverphoto=array_shift($coverphotos);

        if ($coverphoto instanceof photo) {
            $coverphoto->lookup();
            return $coverphoto;
        } else if (!$children) {
            // No photos found in this album... let's look again, but now 
            // also in sub-albums...
            return $this->getAutoCover($autocover, true);
        }
    }

    /**
     * Lookup album by name;
     * @param string name
     */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $where =
            "lower(album) ='" . escape_string(strtolower($name)) . "'";

        $query = "select album_id from " . DB_PREFIX . "albums where $where";

        return self::getRecordsFromQuery($query);
    }

    /**
     * Get Top N albums
     */
    public static function getTopN() {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            $sql =
                "select al.*, count(*) as count from " .
                DB_PREFIX . "albums as al, " .
                DB_PREFIX . "photo_albums as pa " .
                "where pa.album_id = al.album_id " .
                "group by al.album_id " .
                "order by count desc, al.album " .
                "limit 0, " . escape_string($user->prefs->get("reports_top_n"));
        } else {
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
                "LIMIT 0, " . escape_string($user->prefs->get("reports_top_n"));
        }

        return parent::getTopNfromSQL($sql);

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

        if ($user && $user->is_admin()) {
            $sql = "select * from " . DB_PREFIX . "albums order by album";
        } else {
            $sql =
                 "select distinct(a.album_id) from " .
                 DB_PREFIX . "albums as a JOIN " .
                 DB_PREFIX . "group_permissions AS gp " .
                 "ON gp.album_id = a.album_id JOIN " .
                 DB_PREFIX . "groups_users as gu " .
                 "where gu.user_id = '" . escape_string($user->get("user_id")) . "' " .
                 "order by a.album";
        }

        return self::getRecordsFromQuery($sql);
    }
    /**
     * Get albums newer than a certain date
     * @param user get albums for this user
     * @param string date
     */
    public static function getNewer(user $user, $date) {
        $sql = "SELECT distinct(a.album_id) FROM " .
            DB_PREFIX . "albums AS a JOIN " .
            DB_PREFIX . "group_permissions AS gp " .
            "ON a.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users AS gu " .
            "WHERE gu.user_id = '" . escape_string($user->getId()) .
            "' AND gp.changedate > '" . escape_string($date) . "' " .
            "ORDER BY a.album_id";

        return self::getRecordsFromQuery($sql);
    }

    /**
     * Get number of albums for the currently logged on user
     */
    public static function getCount() {
        $user=user::getCurrent();
        if ($user && $user->is_admin()) {
            $sql = "SELECT COUNT(*) FROM " . DB_PREFIX . "albums";
        } else {
            $sql =
                "SELECT COUNT(DISTINCT album_id) FROM " . 
                DB_PREFIX . "group_permissions AS gp JOIN " .
                DB_PREFIX . "groups_users AS gu ON " .
                "gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . escape_string($user->get("user_id")) . "'";
        }

        return self::getCountFromQuery($sql);
    }
}

?>
