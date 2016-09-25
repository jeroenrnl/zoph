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

use db\select;
use db\update;
use db\param;
use db\db;
use db\clause;
use db\selectHelper;
use conf\conf;

/**
 * A class corresponding to the places table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class place extends zophTreeTable implements Organizer {

    use showPage;

    /** @param Name of the root node in XML responses */
    const XMLROOT="places";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="place";


    /** @var string The name of the database table */
    protected static $tableName="places";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("place_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("title");
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
        if ($photo->getLocation() == $this) {
            $photo->unsetLocation();
        }
    }

    /**
     * Insert place into database
     */
    public function insert() {
        if ($this->get("timezone_id")) {
            $this->TZidToTimezone();
        }
        unset($this->fields["timezone_id"]);
        parent::insert();
    }

    /**
     * Update existing place with new data
     */
    public function update() {
        if ($this->get("timezone_id")) {
            $this->TZidToTimezone();
        }
        unset($this->fields["timezone_id"]);
        parent::update();
    }

    /**
     * Delete this place from database
     */
    public function delete() {
        $locid=new param(":locid", (int) $this->getId(), PDO::PARAM_INT);
        $locidNull=new param(":locidnull", null, PDO::PARAM_INT);


        $qry=new update(array("p" => "photos"));
        $qry->where(new clause("location_id=:locid"));
        $qry->addSet("location_id", "locidnull");
        $qry->addParam($locid);
        $qry->addParam($locidNull);

        try {
            db::query($qry);
        } catch (PDOException $e) {
            log::msg("Could not remove references", log::FATAL, log::DB);
        }


        $qry=new update(array("ppl" => "people"));
        $qry->where(new clause("home_id=:locid"));
        $qry->addSet("home_id", "locidnull");
        $qry->addParam($locid);
        $qry->addParam($locidNull);

        try {
            db::query($qry);
        } catch (PDOException $e) {
            log::msg("Could not remove references", log::FATAL, log::DB);
        }

        $qry=new update(array("ppl" => "people"));
        $qry->where(new clause("work_id=:locid"));
        $qry->addSet("work_id", "locidnull");
        $qry->addParam($locid);
        $qry->addParam($locidNull);

        try {
            db::query($qry);
        } catch (PDOException $e) {
            log::msg("Could not remove references", log::FATAL, log::DB);
        }

        parent::delete();
    }

    /**
     * Return whether the currently logged on user can see this place
     * @param user Use this user instead of the logged in one
     * @return bool whether or not this place should be visible
     */
    public function isVisible(user $user=null) {
        if (!$user) {
            $user=user::getCurrent();
        }
        $count=$this->getTotalPhotoCount();
        return ($count > 0 || $user->isCreator($this) || $user->isAdmin());
    }


    /**
     * Get children of this place
     * @param string optional order
     * @return array of places.
     */
    public function getChildren($order=null) {
        $qry=new select(array("pl" => "places"));
        $qry->addFields(array("*", "name"=>"title"));
        $qry->join(array("p"  => "photos"), "pl.place_id=p.location_id", "LEFT");

        $where=new clause("parent_place_id=:placeid");
        $qry->addParam(new param(":placeid", (int) $this->getId(), PDO::PARAM_INT));

        $qry->addGroupBy("pl.place_id");

        if ($order=="sortname") {
            # places do not have a sortname
            $order=null;
        }

        $qry=selectHelper::addOrderToQuery($qry, $order);

        if ($order!="name") {
            $qry->addOrder("name");
        }

        if (!user::getCurrent()->canSeeAllPhotos()) {
            $places=static::getAll();
            $placeIds=array();
            foreach ($places as $place) {
                $placeIds[]=$place->getId();
            }

            if (sizeof($placeIds)==0) {
                return array();
            }

            $ids=new param(":placeid", $placeIds, PDO::PARAM_INT);
            $qry->addParam($ids);
            $where->addAnd(clause::InClause("pl.place_id", $ids));
        }
        $qry->where($where);
        $this->children=static::getRecordsFromQuery($qry);
        return $this->children;
    }

    /**
     * Converts timezone id for this place into a named timezone
     */
    private function TZidToTimezone() {
        $tzkey=$this->get("timezone_id");
        if ($tzkey>0) {
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

        $html = $this->getAddress();
        if ($this->get("url")) {
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
        $qry=new select(array("p" => "photos"));
        $qry->addFields(array("photo_id"));
        $where=new clause("location_id=:locid");
        $qry->addParam(new param("locid", (int) $this->getId(), PDO::PARAM_INT));

        $qry = selectHelper::expandQueryForUser($qry);

        $qry->where($where);

        return photo::getRecordsFromQuery($qry);
    }

    /**
     * Get count of photos in this place
     * @return int count
     */
    public function getPhotoCount() {
        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("count" => "COUNT(DISTINCT(p.photo_id))"));
        $where=new clause("location_id=:locid");
        $qry->addParam(new param("locid", (int) $this->getId(), PDO::PARAM_INT));

        $qry = selectHelper::expandQueryForUser($qry);

        $qry->where($where);

        return $qry->getCount();
    }

    /**
     * Get count of photos in this place and it's children
     * @return int count
     */
    public function getTotalPhotoCount() {
        $this->lookup();

        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("count" => "COUNT(DISTINCT(p.photo_id))"));

        $idList=null;
        $this->getBranchIdArray($idList);
        $ids=new param(":locid", $idList, PDO::PARAM_INT);
        $qry->addParam($ids);
        $where=clause::InClause("p.location_id", $ids);

        $qry = selectHelper::expandQueryForUser($qry);

        $qry->where($where);

        return $qry->getCount();
    }

    /**
     * Get coverphoto for this place.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @param bool choose autocover from this place AND children
     * @return photo coverphoto
     */
    public function getAutoCover($autocover=null,$children=false) {
        $coverphoto=$this->getCoverphoto();
        if ($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("photo_id" => "DISTINCT ar.photo_id"));
        $qry->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id");

        if ($children) {
            $ids=new param(":ids",$this->getBranchIdArray(), PDO::PARAM_INT);
            $qry->addParam($ids);
            $where=clause::InClause("p.location_id", $ids);
        } else {
            $where=new clause("p.location_id=:id");
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
            // No photos found in this place... let's look again, but now
            // also in subplaces...
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
        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array(
            "count"     => "COUNT(DISTINCT p.photo_id)",
            "oldest"    => "MIN(DATE_FORMAT(CONCAT_WS(' ',p.date,p.time), GET_FORMAT(DATETIME, 'ISO')))",
            "newest"    => "MAX(DATE_FORMAT(CONCAT_WS(' ',p.date,p.time), GET_FORMAT(DATETIME, 'ISO')))",
            "first"     => "MIN(p.timestamp)",
            "last"      => "MAX(p.timestamp)",
            "lowest"    => "ROUND(MIN(ar.rating),1)",
            "highest"   => "ROUND(MAX(ar.rating),1)",
            "average"   => "ROUND(AVG(ar.rating),2)"));
        $qry->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id");


        $qry->addGroupBy("p.location_id");

        $where=new clause("p.location_id=:locid");
        $qry->addParam(new param(":locid", $this->getId(), PDO::PARAM_INT));

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
        if ($lat && $lon) {
            return static::getPlacesNear((float) $lat, (float) $lon,
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
        if ($lat && $lon) {
            if ($entity=="miles") {
                $distance=(float) $distance * 1.609344;
            }
            $qry=new select(array("pl" => "places"));
            $qry->addFields(array("place_id"));
            $qry->addFunction(array("distance" => "(6371 * acos(" .
                "cos(radians(:lat)) * cos(radians(lat)) * cos(radians(lon) - " .
                "radians(:lon)) + sin(radians(:lat2)) * sin(radians(lat))))"));
            $qry->having(new clause("distance <= :dist"));


            $qry->addParam(new param(":lat", (float) $lat, PDO::PARAM_STR));
            $qry->addParam(new param(":lat2", (float) $lat, PDO::PARAM_STR));
            $qry->addParam(new param(":lon", (float) $lon, PDO::PARAM_STR));
            $qry->addParam(new param(":dist", (float) $distance, PDO::PARAM_STR));

            if ($limit) {
                $qry->addLimit((int) $limit);
            }

            $qry->addOrder("distance");

            return static::getRecordsFromQuery($qry);
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
        if ($autocover instanceof photo) {
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
        if ($count!=$totalcount) {
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
        if ((!$timezone && $lat && $lon)) {
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
        if ($places) {
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
        if (empty($name)) {
            return false;
        }
        $qry=new select(array("pl" => "places"));
        $qry->addFields(array("place_id"));
        $qry->where(new clause("lower(title)=:name"));
        $qry->addParam(new param(":name", strtolower($name), PDO::PARAM_STR));

        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get Top N people
     */
    public static function getTopN() {
        $user=user::getCurrent();
        $qry=new select(array("pl" => "places"));
        $qry->addFields(array("place_id", "title"));
        $qry->addFunction(array("count" => "count(distinct p.photo_id)"));
        $qry->join(array("p" => "photos"), "pl.place_id=p.location_id");
        $qry->addGroupBy("p.location_id");
        $qry->addOrder("count DESC")->addOrder("pl.title");
        $qry->addLimit((int) $user->prefs->get("reports_top_n"));
        $qry = selectHelper::expandQueryForUser($qry);
        return parent::getTopNfromSQL($qry);
    }

    /**
     * Get count of places
     */
    public static function getCount() {
        if (user::getCurrent()->canSeeAllPhotos()) {
            return parent::getCount();
        } else {
            $qry=new select(array("p"=>"photos"));
            $qry->addFunction(array("count" => "COUNT(DISTINCT location_id)"));
            $qry = selectHelper::expandQueryForUser($qry);
            return $qry->getCount();

        }
    }

    /**
     * Get all places
     */
    public static function getAll() {
        $user=user::getCurrent();
        if ($user->canSeeAllPhotos()) {
            return static::getRecords();
        } else {
            $qry=new select(array("pl" => "places"));
            $qry->addFields(array("place_id"));
            $qry->join(array("p" => "photos"), "p.location_id=pl.place_id");
            $qry = selectHelper::expandQueryForUser($qry);

               if ($user->canEditOrganizers()) {
                $subqry=new select(array("pl" => "places"));
                $subqry->addFields(array("place_id"));
                $subqry->where(new clause("pl.createdby=:ownerid"));
                $subqry->addParam(new param(":ownerid", (int) $user->getId(), PDO::PARAM_INT));
                $qry->union($subqry);
            }
            $places=static::getRecordsFromQuery($qry);

            $qry=new select(array("pl" => "places"));

            $ids=static::getAllAncestors($places);
            if (sizeof($ids)==0) {
                return array();
            }
            $ids=new param(":placeid", array_values($ids), PDO::PARAM_INT);
            $qry->addParam($ids);
            $qry->where(clause::InClause("pl.place_id", $ids));

            return static::getRecordsFromQuery($qry);
        }
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
