<?php
/**
 * A class corresponding to the places table.
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
 * A class corresponding to the places table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class place extends zophTreeTable implements Organizer {
    /** @param Name of the root node in XML responses */
    const XMLROOT="places";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="place";


    /** @var string The name of the database table */
    protected static $table_name="places";
    /** @var array List of primary keys */
    protected static $primary_keys=array("place_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("title");
    /** @var bool keep keys with insert. In most cases the keys are set 
                  by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="place.php?place_id=";

    /**
     * Add a photo to this place
     * @param photo photo to add
     */
    public function addPhoto(photo $photo) {
        $photo->setLocation($this);
    }

    
    /**
     * Remove a photo from this place
     * @param photo photo to remove
     */
    public function removePhoto(photo $photo) {
        if($photo->getLocation() == $this) {
            $photo->unsetLocation();
        }
    }

    /**
     * Insert place into database
     */
    public function insert() {
        if($this->get("timezone_id")) {
            $this->TZidToTimezone();
        }
        unset($this->fields["timezone_id"]);
        parent::insert();
    }
    
    /**
     * Update existing place with new data
     */
    public function update() {
        if($this->get("timezone_id")) {
            $this->TZidToTimezone();
        }
        unset($this->fields["timezone_id"]);
        parent::update();
    }

    /**
     * Delete this place from database
     */
    public function delete() {
        $id=escape_string($this->get("place_id"));
        if(!is_numeric($id)) {die("place_id is not numeric"); }

        $sql="update " . DB_PREFIX . "photos set location_id=null where " .
            "location_id=" . $id;
        query($sql, "Could not remove references:");

        $sql="update " . DB_PREFIX . "people set home_id=null where " .
            "home_id=" .  $id;
        query($sql, "Could not remove references:", $sql);

        $sql="update " . DB_PREFIX . "people set work_id=null where " .
            "work_id=" .  $id;
        query($sql, "Could not remove references:");
        
        parent::delete();
    }

    /**
     * Get children of this place
     * @param string optional order
     * @return array of places.
     */
    public function getChildren($order=null) {
        $order_fields="";

        if($order=="sortname") {
            #places do not have a sortname
            $order="name";
        }
        if($order && $order!="name") {
            $order_fields=get_sql_for_order($order);
            $order=" ORDER BY " . $order . ", name ";
        } else if ($order=="name") {
            $order=" ORDER BY name ";
        }
        
        $sql =
            "SELECT *, title as name " .
            $order_fields . " FROM " .
            DB_PREFIX . "places as pl " .
            "WHERE pl.parent_place_id=" . (int) $this->getId() .
            " GROUP BY pl.place_id " .
            $order; 
        $this->children=self::getRecordsFromQuery($sql);
        return $this->children;
    }    
   
    /**
     * Get this place's children, taking into account permissions for a specific user
     * @param string sort order
     */
    public function getChildrenForUser($order=null) {
        return remove_empty($this->getChildren($order));
    }
    
    /**
     * Converts timezone id for this place into a named timezone
     */
    private function TZidToTimezone() {
        $tzkey=$this->get("timezone_id");
        if($tzkey>0) {
            $tzarray=TimeZone::getSelectArray();
            $tz=$tzarray[$tzkey];
            $this->set("timezone", $tz);
        } else {
            $this->set("timezone", null);
        }
        unset($this->fields["timezone_id"]);
    }

    /**
     * Get the name of this place
     * @return string name of this place
     */
    public function getName() {
        return $this->get("title"); 
    }

    /**
     * Get address as template block
     */
    public function getAddress() {
        $address = array();
        if ($this->get("address"))  {
            $address[]= e($this->get("address"));
        }
        if ($this->get("address2")) {
            $address[]= e($this->get("address2"));
        }

        $city="";
        if ($this->get("city")) { 
            $city=e($this->get("city"));
            if ($this->get("state")) { 
                $city .= ", " . e($this->get("state"));
            }
        } else if ($this->get("state")) { 
            $city .= e($this->get("state")); 
        }
        if ($this->get("zip")) { 
            $city.=" " . e($this->get("zip")); 
        }
        $address[]=$city;

        if ($this->get("country")) {
            $address[]=e($this->get("country"));
        }
        $tpl=new block("multiline", array(
            "class" => "address",
            "lines" => $address
        ));
        return $tpl;
    }

    /**
     * Display this place's data
     * @todo returns HTML
     */
    public function toHTML() {

        $html = "";
        if ($this->get("title"))    {
            $html .= "<h2>" . e($this->get("title")) . "</h2>\n";
        }
        $html .= $this->getAddress();
        if($this->get("url")) {
            $html .= "<br><br>\n";
            $html .= "<a href=\"" . e($this->get("url")) . "\">";
            $html .= e($this->get("urldesc")) . "</a>";
        }

        return $html;
    }

    /**
     * Return an array with this place's data
     */
    public function getDisplayArray() {
        return array(
            translate("address") => $this->get("address"),
            translate("address") . "2" => $this->get("address2"),
            translate("city") => $this->get("city"),
            translate("state") => $this->get("state"),
            translate("zip") => $this->get("zip"),
            translate("country") => $this->get("country"),
            translate("notes") => $this->get("notes"),
            translate("timezone") => $this->get("timezone"));
    }
    
    /**
     * Get photos in this place
     */
    public function getPhotos() {
        $user=user::getCurrent();

        $id = $this->get("place_id");

        if ($user->is_admin()) {
            $sql =
                "select photo_id from " .
                DB_PREFIX . "photos " .
                "where location_id = '" .  escape_string($id) . "'";
        } else {
            $sql =
                "select p.photo_id from " .
                DB_PREFIX . "photos as p JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON p.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE p.location_id = " . escape_string($id) .
                " AND gu.user_id = '" . escape_string($user->get("user_id")) .
                "' AND gp.access_level >= p.level";
        }

        return photo::getRecordsFromQuery($sql);
    }

    /**
     * Get count of photos in this place
     * @return int count
     */
    public function getPhotoCount() {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            $sql =
                "SELECT COUNT(*) FROM " .
                DB_PREFIX . "photos " .
                "WHERE location_id = " . (int) $this->getId();
        } else {
            $sql =
                "SELECT COUNT(DISTINCT p.photo_id) FROM " .
                DB_PREFIX . "photos AS p JOIN " .
                DB_PREFIX . "photo_albums AS pa " .
                "ON p.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE p.location_id = " . (int) $this->getId() .
                " AND gu.user_id = " . (int) $user->getId() .
                " AND gp.access_level >= p.level";
        }
        return photo::getCountFromQuery($sql);
    }

    /**
     * Get count of photos in this place and it's children
     * @return int count
     */
    public function getTotalPhotoCount() {
        $user=user::getCurrent();

        $id_list = $this->getBranchIds();
        $id_constraint = "p.location_id in ($id_list)";

        if ($user->is_admin()) {
            $sql =
                "SELECT COUNT(DISTINCT p.photo_id) FROM " .
                DB_PREFIX . "photos p ";

            if (!empty($id_constraint)) {
                $sql .= " WHERE $id_constraint";
            }
        } else {
            $sql =
                "select count(distinct pa.photo_id) from " .
                DB_PREFIX . "photos as p JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . escape_string($user->get("user_id")) .
                "' AND gp.access_level >= p.level";

            if ($id_constraint) {
                $sql .= " AND $id_constraint";
            }
        }
        return self::getCountFromQuery($sql);
    }

    /**
     * Get coverphoto for this place.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @param bool choose autocover from this place AND children
     * @return photo coverphoto
     */
    public function getAutoCover($autocover=null,$children=false) {
        $user=user::getCurrent();
        $coverphoto=$this->getCoverphoto();
        if($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $order=self::getAutoCoverOrder($autocover);

        if($children) {
            $place_where=" WHERE p.location_id in (" . $this->getBranchIds() .")";
        } else {
            $place_where=" WHERE p.location_id =" .$this->get("place_id");
        }

        if ($user->is_admin()) {
            $sql =
                "SELECT DISTINCT p.photo_id FROM " .
                DB_PREFIX . "photos AS p JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON p.photo_id = ar.photo_id " .
                $place_where . " " . $order;
        } else {
            $sql=
                "SELECT DISTINCT p.photo_id FROM " .
                DB_PREFIX . "photos AS p JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON p.photo_id = ar.photo_id JOIN " .
                DB_PREFIX . "photo_albums AS pa" .
                " ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu " .
                "ON gp.group_id = gu.group_id " .
                $place_where .
                " AND gu.user_id =" .
                " '" . escape_string($user->get("user_id")) . "'" .
                " AND gp.access_level >= p.level " .
                $order;
        }
        $coverphotos=photo::getRecordsFromQuery($sql);
        $coverphoto=array_shift($coverphotos);

        if ($coverphoto instanceof photo) {
            $coverphoto->lookup();
            return $coverphoto;
        } else if (!$children) {
            // No photos found in this place... let's look again, but now 
            // also in sub-places...
            return $this->getAutoCover($autocover, true);
        }
    }

    /**
     * Get Marker to be placed on map
     * @param string icon to be used.
     * @return marker instance of marker class
     */
    public function getMarker($icon="geo-place") {
        return map::getMarkerFromObj($this, $icon);
    }

    /**
     * Get details (statistics) about this place from db
     * @return array Array with statistics
     */
    public function getDetails() {
        $id = (int) $this->getId();
        $user=user::getCurrent();
        $user_id = (int) $user->getId();

        if ($user->is_admin()) {
            $sql = "SELECT ".
                "COUNT(DISTINCT ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photos ph JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON ph.photo_id = ar.photo_id " .
                "WHERE ph.location_id=" . escape_string($id) .
                " GROUP BY ph.location_id";
        } else {
            $sql = "SELECT " .
                "COUNT(DISTINCT ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), " .
                "GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photos ph JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON ph.photo_id = ar.photo_id JOIN " .
                DB_PREFIX . "photo_albums pa " .
                "ON ph.photo_id=pa.photo_id LEFT JOIN " .
                DB_PREFIX . "group_permissions gp " .
                "ON pa.album_id=gp.album_id LEFT JOIN " . 
                DB_PREFIX . "groups_users gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE ph.level<gp.access_level AND " .
                "gu.user_id=" . escape_string($user_id) . " AND " .
                "ph.location_id=" . escape_string($id) .
                " GROUP BY ph.location_id";
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
        $details["title"]=translate("In this place:", false);
        return parent::getDetailsXML($details);
    }

    /**
     * Get places near this place
     * @param int distance in km or miles
     * @param int limit maxiumum number of photos to return
     * @param string entity (km or miles)
     */
    public function getNear($distance, $limit=100, $entity="km") {
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        if($lat && $lon) {
            return self::getPlacesNear((float) $lat, (float) $lon, 
                (float) $distance, (int) $limit, $entity);
        }
    }

    /**
     * Get places near certain lat/lon
     * @param float latitude
     * @param float longitude
     * @param int distance
     * @param int limit number of returned places
     * @param string entity: km|miles
     * @return array places
     */
    public static function getPlacesNear($lat, $lon, $distance, 
            $limit, $entity="km") { 
            
        // If lat and lon are not set, don't bother trying to find
        // near locations
        if($lat && $lon) {
            $lat=(float) $lat;
            $lon=(float) $lon;

            if($entity=="miles") {
                $distance=(float) $distance * 1.609344;
            }
            if($limit) {
                $lim=" limit 0,". (int) $limit;
            }
            $sql="select place_id, (6371 * acos(" .
                "cos(radians(" . $lat . ")) * " .
                "cos(radians(lat) ) * cos(radians(lon) - " .
                "radians(" . $lon . ")) +" . 
                "sin(radians(" . $lat . ")) * " .
                "sin(radians(lat)))) AS distance from " .
                DB_PREFIX . "places " .
                "having distance <= " . $distance . 
                " order by distance" . $lim;

            $near=self::getRecordsFromQuery($sql);
            return $near;
        } else {
            return null;
        }
    }

    /**
     * Get Quick preview as used on the map display
     * @todo Outputs HTML
     */
    public function getQuicklook() {
        $cover="";
        $autocover=$this->getAutoCover(user::getCurrent()->prefs->get("autothumb"));
        if($autocover instanceof photo) {
            $cover=$autocover->getImageTag(THUMB_PREFIX);
        }

        $html="<h2><a href=\"" . $this->getURL() . "\">" . $this->getName() . "</a><\/h2>";
        $html.="<small>" . $this->getAddress() . "<\/small><br>";
        $html.=$cover;
        $count=$this->getPhotoCount();
        $totalcount=$this->getTotalPhotoCount();
        $html.="<br><small>" . 
            e(sprintf(translate("There are %s photos"), $count) .
           " " . translate("in this place")) . "<br>";
        if($count!=$totalcount) {
            $html.=e(sprintf(translate("There are %s photos"),$totalcount) . 
            " " . translate("in this place") . " " . translate("or its children")) . "<br>";
        }
        $html.="<\/small>";

        return str_replace("\n", "", $html);
    }

    /**
     * Guess the timezone based on lat/lon information
     */
    public function guessTZ() {
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        $timezone=$this->get("timezone");
        if((!$timezone && $lat && $lon)) {
            $tz=TimeZone::guess($lat, $lon);
            return $tz;
        }
        return null;
    }

    /**
     * Set the timezone for all places under this place to the same timezone
     */
    public function setTzForChildren() {
        $tz=$this->get("timezone");
        $places=$this->getBranchIdArray($places);
        if($places) {
            foreach ($places as $place_id) {
                $place=new place($place_id);
                $place->set("timezone", $tz);
                $place->update();
            }
        }
    }

    /**
     * Lookup place by name;
     * @param string name
     */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }
        $title=strtolower(escape_string($name));
        $sql="SELECT place_id from " . DB_PREFIX . "places WHERE " .
            " LOWER(title) = \"" . $title . "\";";

        return self::getRecordsFromQuery($sql);
    }

    /**
     * Get Top N people
     */
    public static function getTopN() {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            $sql =
                "select plc.*, count(*) as count from " .
                DB_PREFIX . "places as plc, " .
                DB_PREFIX . "photos as ph " .
                "where plc.place_id = ph.location_id " .
                "group by plc.place_id " .
                "order by count desc, plc.title, plc.city " .
                "limit 0, " . (int) $user->prefs->get("reports_top_n");
        } else {
            $sql =
                "SELECT plc.*, count(distinct ph.photo_id) AS count FROM " .
                DB_PREFIX . "photos as ph JOIN " .
                DB_PREFIX . "places as plc " .
                "ON ph.location_id = plc.place_id JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON pa.photo_id = ph.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . 
                escape_string($user->get("user_id")) . 
                "' AND gp.access_level >= ph.level " .
                "GROUP BY plc.place_id " .
                "ORDER BY count desc, plc.title, plc.city " .
                "LIMIT 0, " . (int) $user->prefs->get("reports_top_n");
        }

        return parent::getTopNfromSQL($sql);

    }
    
    /**
     * Get count of places
     */
    public static function getCount() {
        $user=user::getCurrent();
        if($user->is_admin()) {
            return parent::getCount();
        } else {
            $places=self::getPhotographed($user);
            return count($places);
        }
    }

    /**
     * Get all places
     * @param array constraints, conditions that should be matched
     * @param string conjunctions, and/or
     * @param array ops, operators: =, !=, etc.
     * @param string sort order
     * @return array places
     * @todo it seems this function not used at all
     * @todo should be moved into zophTable
     */
    public static function getAll($constraints = null, $conj = "and", $ops = null,
        $order = "city, title, address") {
        return self::getRecords($order, $constraints, $conj, $ops);
    }

    /**
     * Get places that appear on a photo
     * @param user user
     * @return array places
     */
    private static function getPhotographed($user = null) {
        if ($user && !$user->is_admin()) {
            $sql =
                "SELECT DISTINCT plc.* FROM " .
                DB_PREFIX . "photos AS ph JOIN " .
                DB_PREFIX . "places AS plc " .
                "ON ph.location_id = plc.place_id JOIN " .
                DB_PREFIX . "photo_albums AS pa " .
                "ON pa.photo_id = ph.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu " .
                "ON gp.group_id = gu.group_id " .
                "where gu.user_id = '" . 
                escape_string($user->get("user_id")) .
                "' AND gp.access_level >= ph.level " .
                "ORDER BY plc.city, plc.title";
        } else {
            $sql =
                "select distinct plc.* from " .
                DB_PREFIX . "places as plc, " .
                DB_PREFIX . "photos as ph " .
                "where plc.place_id = ph.location_id " .
                "order by plc.city, plc.title";
        }

        return self::getRecordsFromQuery($sql);
    }

    /**
     * Get autocomplete preferences for people for this user
     */
    public static function getAutocompPref() {
        $user=user::getCurrent();
        return ($user->prefs->get("autocomp_people") && conf::get("interface.autocomplete"));
    }


    /**
     * Create pulldown for zoom
     * @param int current value
     * @param name name for select box
     */
    public static function createZoomPulldown($val = "", $name = "mapzoom") {
        $zoom_array = array(
            "0" => translate("0 - world", 0),
            "1" => translate("1",0),
            "2" => translate("2 - continent",0),
            "3" => translate("3",0),
            "4" => translate("4",0),
            "5" => translate("5",0),
            "6" => translate("6 - country",0),
            "7" => translate("7",0),
            "8" => translate("8",0),
            "9" => translate("9 - city",0),
            "10" => translate("10",0),
            "11" => translate("11",0),
            "12" => translate("12 - neighborhood",0),
            "13" => translate("13",0),
            "14" => translate("14",0),
            "15" => translate("15",0),
            "16" => translate("16 - street",0),
            "17" => translate("17",0),
            "18" => translate("18 - house",0));

        return template::createPulldown($name, $val, $zoom_array);
    }
}

?>
