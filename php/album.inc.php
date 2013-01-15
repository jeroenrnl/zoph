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
 * @auther Jeroen Roos
 */

/**
 * Photo album
 *
 * @package Zoph
 * @author Jason Geiger
 * @auther Jeroen Roos
 */
class album extends zophTreeTable implements Organizer {

    /** @var Cache the count of photos */
    private $photoCount;

    /**
     * Create an album object
     * @param int id
     * @return album created object
     */
    function __construct($id = 0) {
        if($id && !is_numeric($id)) { die("album_id must be numeric"); }
        parent::__construct("albums", array("album_id"), array("album"));
        $this->set("album_id", $id);
    }

   /**
    * Get the Id
    */
    public function getId() {
        return (int) $this->get("album_id");
    }

    public function lookup() {
        $user=user::getCurrent();
        $id = $this->get("album_id");
        if(!is_numeric($id)) { die("album_id must be numeric"); }
        if (!$id) { return; }

        if ($user->is_admin()) {
            $sql =
                "select * from " . DB_PREFIX . "albums " .
                "where album_id = " . escape_string($id);
        } else {
            $sql =
                 "select a.* from "  .
                 DB_PREFIX . "albums as a JOIN " .
                 DB_PREFIX . "group_permissions as gp ON " .
                 "gp.album_id = a.album_id JOIN " .
                 DB_PREFIX . "groups_users gu ON " .
                 "gp.group_id = gu.group_id " .
                 "where gp.album_id = '" . escape_string($id) . "'" .
                 " and gu.user_id = '" . 
                 escape_string($user->get("user_id"))."'";
        }
        return $this->lookupFromSQL($sql);
    }
    
    /**
     * Add a photo to this album
     * @param photo Photo to add
     */
    public function addPhoto(photo $photo) {
        $user=user::getCurrent();
        if($user->is_admin() || $user->get_album_permissions($this->get("album_id"))->get("writable")) {
            $sql = "INSERT INTO " . DB_PREFIX . "photo_albums " .
                "(photo_id, album_id) values ('" .
                escape_string($photo->get("photo_id")) . "', '" .
                escape_string($this->get("album_id")) . "')";
            query($sql);
        }
    }

    /**
     * Remove a photo from this album
     * @param photo Photo to remove
     */
    public function removePhoto(photo $photo) {
        $user=user::getCurrent();
        if($user->is_admin() || $user->get_album_permissions($this->get("album_id"))->get("writable")) {
            $sql = "DELETE FROM " . DB_PREFIX . "photo_albums " .
                "WHERE photo_id = '" . escape_string($photo->get("photo_id")) . "'" .
                " AND album_id = '" . escape_string($this->get("album_id")) . "'";
            query($sql);
        }
    }

    /**
     * Delete this album
     */
    public function delete() {
        parent::delete(array("photo_albums", "group_permissions"));
        $users = user::getRecords("user", "user_id", array("lightbox_id" => $this->get("album_id")));
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

    function get_indented_name() {
        $indent=str_repeat("&nbsp;&nbsp;&nbsp;", count($this->get_ancestors()));
        return $indent . $this->getName();
    }

    /**
     * Get the subalbums of this album
     * @param string optional order
     * @return array of albums
     */
    public function getChildren($order=null) {
        $order_fields="";
        if($order && $order!="name") {
            $order_fields=get_sql_for_order($order);
            $sql_order=" ORDER BY " . $order . ", name ";
        } else if ($order=="name") {
            $sql_order=" ORDER BY name ";
        }
        $sql =
            "SELECT a.*, album as name " .
            $order_fields . " FROM " .
            DB_PREFIX . "albums as a LEFT JOIN " .
            DB_PREFIX . "photo_albums as pa " .
            "ON a.album_id=pa.album_id LEFT JOIN " .
            DB_PREFIX . "photos as ph " .
            "ON pa.photo_id=ph.photo_id " .
            "WHERE parent_album_id=" . (int) $this->getId() .
            " GROUP BY album_id " .
            escape_string($sql_order);

        $this->children=album::getRecordsFromQuery("album", $sql);
        return $this->children;
    }

    public function getChildrenForUser($order=null) {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            return $this->getChildren($order);
        }

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

        $this->children=album::getRecordsFromQuery("album", $sql);
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
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photo_albums pa JOIN " .
                DB_PREFIX . "photos ph " .
                "ON ph.photo_id=pa.photo_id LEFT JOIN " .
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
                "'" . translate("In this album:", false) . "' AS title, " .
                "COUNT(ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photo_albums pa JOIN " .
                DB_PREFIX . "photos ph " .
                "ON ph.photo_id=pa.photo_id LEFT JOIN " .
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

        if ($this->photoCount) { return $photoCount; }

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

        return album::getCountFromQuery($sql);
    }

    /**
     * Return the amount of photos in this album and it's children
     */
    function getTotalPhotoCount() {
        $user=user::getCurrent();
        // Without the lookup, parent_album_id is not available!
        $this->lookup();
        if ($this->get("parent_album_id")) {
            $id_list = $this->getBranchIds();
            $id_constraint = "pa.album_id in ($id_list)";
        }
        else {
            $id_constraint = "";
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

        return album::getCountFromQuery($sql);
    }

    /**
     * Get array of fields/values to create an edit form
     * @return array fields/values
     */
    public function getEditArray() {
        $user=user::getCurrent();
        if($this->is_root()) {
            $parent=array (
                translate("parent album"),
                translate("Albums"));
        } else {
            $parent=array (
                translate("parent album"),
                create_album_pulldown("parent_album_id",
                $this->get("parent_album_id"), $user));
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
                    create_pulldown("pageset", $this->get("pageset"), get_pageset_select_array())),
            "sortname" =>
                array(
                    translate("sort name"),
                    create_text_input("sortname",
                        $this->get("sortname"))),
            "sortorder" =>
                array(
                    translate("album sort order"),
                    create_photo_field_pulldown("sortorder", $this->get("sortorder")))
        );
    }

    function getLink() {
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
    function getURL() {
        return "albums.php?parent_album_id=" . $this->getId();
    }

    function xml_rootname() {
        return "albums";
    }

    function xml_nodename() {
        return "album";
    }

    function getCoverphoto($autothumb=null,$children=null) {
        $user=user::getCurrent();
        $coverphoto=null;
        $cover=false;
        if ($this->get("coverphoto")) {
            $coverphoto=new photo($this->get("coverphoto"));
            if($coverphoto->lookup()) {
                $cover=TRUE;
            }
        }
        if ($autothumb && !$cover) {
            $order=get_autothumb_order($autothumb);
            if($children) {
                $album_where=" WHERE pa.album_id in (" . $this->getBranchIds() .")";
            } else {
                $album_where=" WHERE pa.album_id =" .$this->get("album_id");
            }
            if ($user->is_admin()) {
                $sql =
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p LEFT JOIN " .
                    DB_PREFIX . "view_photo_avg_rating ar" .
                    " ON p.photo_id = ar.photo_id JOIN " .
                    DB_PREFIX . "photo_albums pa ON" .
                    " pa.photo_id = p.photo_id" .
                    $album_where .
                    " " . $order;
            } else {
                $sql=
                    "select distinct p.photo_id from " .
                    DB_PREFIX . "photos as p LEFT JOIN " .
                    DB_PREFIX . "view_photo_avg_rating ar" .
                    " ON p.photo_id = ar.photo_id JOIN " .
                    DB_PREFIX . "photo_albums as pa" .
                    " ON pa.photo_id = p.photo_id JOIN " .
                    DB_PREFIX . "group_permissions as gp ON " .
                    "pa.album_id = gp.album_id JOIN " .
                    DB_PREFIX . "groups_users AS gu ON " .
                    "gp.group_id = gu.group_id " .
                    $album_where .
                    " AND gu.user_id =" . 
                    " '" . escape_string($user->get("user_id")) . "'" .
                    " and pa.photo_id = p.photo_id " .
                    " and gp.access_level >= p.level " .
                    $order;
            }
            $coverphotos=photo::getRecordsFromQuery("photo", $sql);
            $coverphoto=array_shift($coverphotos);
        }

        if ($coverphoto instanceof photo) {
            $coverphoto->lookup();
            return $coverphoto->getImageTag(THUMB_PREFIX);
        } else if (!$children) {
            // No photos found in this album... let's look again, but now 
            // also in sub-albums...
            return $this->getCoverphoto($autothumb, true);
        }
    }
    function is_root() {
        // At this moment the root album is always 1, but this may
        // change in the future, so to be safe we'll make a function for
        // this
        $root_album=album::getRoot();
        if($this->get("album_id") == $root_album->get("album_id")) {
            return true;
        } else {
            return false;
        }
    }
   /**
    * Lookup album by name;
    */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $where =
            "lower(album) ='" . escape_string(strtolower($name)) . "'";

        $query = "select album_id from " . DB_PREFIX . "albums where $where";

        return album::getRecordsFromQuery("album", $query);
    }

    /**
     * Get the root album
     * @todo Once the minimum PHP version is 5.3 this could move to zoph_tree_table
     */
    public static function getRoot() {
        return new album(1);
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

        return parent::getTopNfromSQL("album", $sql);

    }

}


function get_albums($user = null) {

    if ($user && !$user->is_admin()) {
        $sql =
             "select a.* from " .
             DB_PREFIX . "albums as a JOIN " .
             DB_PREFIX . "group_permissions AS gp " .
             "ON gp.album_id = a.album_id JOIN " .
             DB_PREFIX . "groups_users as gu " .
             "where gu.user_id = '" . escape_string($user->get("user_id")) .
             "order by a.album";
    }
    else {
        $sql = "select * from " . DB_PREFIX . "albums order by album";
    }

    return album::getRecordsFromQuery("album", $sql);
}

function get_newer_albums($user_id, $date = null) {
    $sql = "select a.* from " .
        DB_PREFIX . "albums as a JOIN " .
        DB_PREFIX . "group_permissions as gp " .
        "ON a.album_id = gp.album_id JOIN " .
        DB_PREFIX . "groups_users as gu " .
        "WHERE gu.user_id = '" . escape_string($user_id) .
        "' AND gp.changedate > '" . escape_string($date) . "' " .
        "ORDER BY a.album_id";

    return album::getRecordsFromQuery("album", $sql);
}

function get_album_count($user = null) {

    if ($user && !$user->is_admin()) {
        $sql =
            "SELECT COUNT(DISTINCT album_id) FROM " . 
            DB_PREFIX . "group_permissions AS gp JOIN " .
            DB_PREFIX . "groups_users AS gu ON " .
            "gp.group_id = gu.group_id " .
            "where gu.user_id = '" . escape_string($user->get("user_id")) . "'";
    }
    else {
        $sql = "select count(*) from " . DB_PREFIX . "albums";
    }

    return album::getCountFromQuery($sql);
}

function get_albums_select_array($user = null, $search = 0) {
    return create_tree_select_array("album", $user, null, "", null, $search);
}

function get_albums_search_array($user = null) {
    return get_albums_select_array($user, 1);
}


function create_album_pulldown($name, $value=null, user $user=null, $sa=null) {
    $text="";

    $id=preg_replace("/^_+/", "", $name);
    if($value) {
        $album=new album($value);
        $album->lookup();
        $text=$album->get("album");
    } 
    
    if($user->prefs->get("autocomp_albums") && conf::get("interface.autocomplete")) {
        $html="<input type=hidden id='" . e($id) . "' name='" . e($name) . "'" .
            " value='" . e($value) . "'>";
        $html.="<input type=text id='_" . e($id) . "' name='_" . e($name) . 
            "'" .  " value='" . e($text) . "' class='autocomplete'>";
    } else {
        if(!isset($sa)) {
            $sa=get_albums_select_array($user);
        }
        $html=create_pulldown($name, $value, $sa);
    }
    return $html;
}
?>
