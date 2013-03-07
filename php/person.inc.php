<?php

/**
 * A class corresponding to the people table.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 *
 * @package Zoph
 */

/**
 * Person class
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class person extends zophTable implements Organizer {
    /** @var string The name of the database table */
    protected static $table_name="people";
    /** @var array List of primary keys */
    protected static $primary_keys=array("person_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("first_name");
    /** @var bool keep keys with insert. In most cases the keys are set by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="person.php?person_id=";

    /** @var location Home address of this person */
    public $home;
    /** @var location Work address of this person */
    public $work;

    /**
     * Add this person to a photo.
     * This records in the database that this person appears on the photo
     * @param photo Photo to add the person to
     */
    public function addPhoto(photo $photo) {
        $pos = $photo->getLastPersonPos();
        $pos++;
        $sql = "INSERT INTO " . DB_PREFIX . "photo_people " .
            "(photo_id, person_id, position) " .
            "values (" . (int) $photo->getId() . ", " .
            (int) $this->getId() . ", " . (int) $pos . ")";
        query($sql, "Failed to add person");
    }

    /**
     * Remove person from a photo
     * @param photo photo to remove the person from
     */
    public function removePhoto(photo $photo) {
       // First, get the position for the person who is about to be removed
        $sql = "SELECT position FROM " . DB_PREFIX . "photo_people " .
            "WHERE photo_id = '" . (int) $photo->getId() . "' " .
            "AND person_id = '" . (int) $this->getId() . "'";
        $result=fetch_array(query($sql));
        $pos=$result["position"];

        // Remove the victim
        $sql = "DELETE FROM " . DB_PREFIX . "photo_people " .
            "WHERE photo_id = '" . (int) $photo->getId() . "'" .
            " AND person_id = '" . (int) $this->getId() . "'";
        query($sql);

        // Finally, lower the position for everyone with a higher position by one
        $sql=
            "UPDATE " . DB_PREFIX . "photo_people " .
            "SET position=position-1 " .
            "WHERE photo_id = '" . (int) $photo->getId() . "' " .
            "AND position > " . (int) $pos;
        query($sql);
    }

    function lookup() {
        parent::lookup();
        $this->lookup_places();
    }

    function lookup_places() {
        if ($this->get("home_id") > 0) {
            $this->home = new place($this->get("home_id"));
            $this->home->lookup();
        }
        if ($this->get("work_id") > 0) {
            $this->work = new place($this->get("work_id"));
            $this->work->lookup();
        }
    }

    public function getPhotographer() {
        $photographer=new photographer($this->getId());
        $photographer->lookup();
        return $photographer;
    }

    function delete() {
        $id=escape_string($this->get("person_id"));
        if (!is_numeric($id)) { die("person_id is not numeric"); }
        $sql="update " . DB_PREFIX . "people set father_id=null " .
            "where father_id=" .  $id;
        query($sql, "Could not remove references:");

        $sql="update " . DB_PREFIX . "people set mother_id=null " . 
            "where mother_id=" .  $id;
        query($sql, "Could not remove references:");
        
        $sql="update " . DB_PREFIX . "people set spouse_id=null " .
            "where spouse_id=" .  $id;
        query($sql, "Could not remove references:");
        
        $sql="update " . DB_PREFIX . "photos set photographer_id=null where " .
            "photographer_id=" .  $id;
        query($sql, "Could not remove references:");
        
        parent::delete(array("photo_people"));
    }

    function get_gender() {
        if ($this->get("gender") == 1) { return translate("male"); }
        if ($this->get("gender") == 2) { return translate("female"); }
        return;
    }

    public static function getFromId($person_id) {
        $person=null;
        if(!is_null($person_id) && $person_id!=0) {
            $person=new person($person_id);
            $person->lookup();
        }
        return $person;
    }


    function getFather() {
        return person::getFromId($this->get("father_id"));
    }

    function getMother() {
        return person::getFromId($this->get("mother_id"));
    }

    function getSpouse() {
        return person::getFromId($this->get("spouse_id"));
    }

    /** @todo I don't think this function is ever called */
    function getChildren() {
        $constraints["father_id"] = $this->get("person_id");
        $constraints["mother_id"] = $this->get("person_id");
        return get_people($constraints, "or");
    }

    /** @todo I don't think this function is ever called */
    function getChildrenForUser() {
        return $this->getChildren();
    }


    function getName() {
        if ($this->get("called")) {
            $name = $this->get("called");
        }
        else {
            $name = $this->get("first_name");
        }

        if ($this->get("last_name")) {
            $name .= " " . $this->get("last_name");
        }

        return $name;
    }

    function get_email() {
       $email = $this->get("email");
       return $email;
    }

    function toHTML() {
        return getName();
    }

    /**
     * Get a link to this person
     * @todo Not proper OO, parent function does not have parameter
     */
    function getLink($show_last_name = 1) {
        if ($show_last_name) {
            $name = $this->getName();
        }
        else {
            $name = $this->get("called") ? $this->get("called") :
                $this->get("first_name");
        }

        return "<a href=\"person.php?person_id=" . $this->get("person_id") . "\">$name</a>";
    }

    /**
     * Get URL to this person
     */

    function getURL() {
        return "person.php?person_id=" . $this->getId();
    }

    function getDisplayArray() {
        $mother=$this->getMother();
        $father=$this->getFather();
        $spouse=$this->getSpouse();

        $display=array(
            translate("called") => e($this->get("called")),
            translate("date of birth") => create_date_link(e($this->get("dob"))),
            translate("date of death") => create_date_link(e($this->get("dod"))),
            translate("gender") => e($this->get_gender()));
        if($mother instanceof person) {
            $display[translate("mother")] = $mother->getLink();
        }
        if($father instanceof person) {
            $display[translate("father")] = $father->getLink();
        }
        if($spouse instanceof person) {
            $display[translate("spouse")] = $spouse->getLink();
        }
        return $display;
    }
    function xml_rootname() {
        return "people";
    }

    function xml_nodename() {
        return "person";
    }

    /**
     * Return the number of photos this person appears on
     * @return int count
     */
    public function getPhotoCount() {
        $user=user::getCurrent();
        
        $ignore=null;
        $vars=array(
            "person_id" => $this->getId()
        );
        return get_photos($vars, 0, 1, $ignore, $user);
    }

    /**
     * Return the number of photos this person appears on.
     * Wrapper around getPhotoCount() because there is no
     * concept of sub-persons.
     * @return int count
     */
    public function getTotalPhotoCount() {
        return $this->getPhotoCount();
    }
    
    /**
     * Get coverphoto for this person.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @return photo coverphoto
     */
    public function getAutoCover($autocover=null) {
        $user=user::getCurrent();

        $coverphoto=$this->getCoverphoto();
        if($coverphoto instanceof photo) {
            return $coverphoto;
        }
        
        $order=self::getAutoCoverOrder($autocover);
        if ($user->is_admin()) {
            $sql =
                "SELECT DISTINCT p.photo_id FROM " .
                DB_PREFIX . "photos AS p LEFT JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON p.photo_id = ar.photo_id JOIN " .
                DB_PREFIX . "photo_people AS pp" .
                " ON pp.photo_id = p.photo_id " .
                " WHERE pp.person_id = " . 
                escape_string($this->get("person_id")) .
                " " . $order;
        } else {
            $sql=
                "SELECT DISTINCT p.photo_id FROM " .
                DB_PREFIX . "photos AS p LEFT JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON p.photo_id = ar.photo_id JOIN " .
                DB_PREFIX . "photo_albums AS pa " .
                "ON pa.photo_id = p.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu " .
                "ON gp.group_id = gu.group_id JOIN " .
                DB_PREFIX . "photo_people AS pp " .
                "ON pp.photo_id = p.photo_id " .
                "WHERE pp.person_id = " . 
                escape_string($this->get("person_id")) .
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
        }
    }

    /**
     * Set first, middle, last and called name from single string
     * "first", "first last", "first middle last last last"
     * or "first:middle:last:called"
     */

    public function setName($name) {
        if(strpos($name, ":")!==false) {
            $name_array=array_pad(explode(":", $name),4,null);
            $this->set("first_name", $name_array[0]);
            $this->set("middle_name", $name_array[1]);
            $this->set("last_name", $name_array[2]);
            $this->set("called", $name_array[3]);
        } else {
            $name_array=explode(" ", $name);
            switch (sizeof($name_array)) {
            case 0:
                // shouldn't happen..
                die("something went wrong, report a bug");
                break;
            case 1:
                // Only one word, assume this is a first name
                $this->set("first_name", $name_array[0]);
                break;
            case 2:
                // Two words, asume this is first & last
                $this->set("first_name", $name_array[0]);
                $this->set("last_name", $name_array[1]);
                break;
            default:
                // 3 or more, assume first two are first, middle, rest is last
                $this->set("first_name", array_shift($name_array));
                $this->set("middle_name", array_shift($name_array));
                $this->set("last_name", implode($name_array, " "));
                break;
            }
        }
    }
    
    /**
     * Get details (statistics) about this person from db
     * @return array Array with statistics
     * @todo For now, this only tells about the photos this person
     *       has taken. Details about the photos this person appears
     *       should be added some time.
     */
    public function getDetails() {
        $user=user::getCurrent();
        $user_id = (int) $user->getId();
        $id = (int) $this->getId();

        if ($user->is_admin()) {
            $sql = "SELECT ".
                "COUNT(ph.photo_id) AS count, " .
                "MIN(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS oldest, " .
                "MAX(DATE_FORMAT(CONCAT_WS(' ',ph.date,ph.time), GET_FORMAT(DATETIME, 'ISO'))) AS newest, " .
                "MIN(ph.timestamp) AS first, " .
                "MAX(ph.timestamp) AS last, " .
                "ROUND(MIN(ar.rating),1) AS lowest, " .
                "ROUND(MAX(ar.rating),1) AS highest, " . 
                "ROUND(AVG(ar.rating),2) AS average FROM " . 
                DB_PREFIX . "photos ph LEFT JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar" .
                " ON ph.photo_id = ar.photo_id " .
                "WHERE ph.photographer_id=" . escape_string($id) .
                " GROUP BY ph.photographer_id";
        } else {
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
                "ph.photographer_id=" . escape_string($id) .
                " GROUP BY ph.photographer_id";
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
    public function getDetailsXML(array $details=null) {
        if(!isset($details)) {
            $details=$this->getDetails();
        }
        $details["title"]=translate("Photos taken by this person:", false);
        return parent::getDetailsXML($details);
    }


   /**
    * Lookup person by name;
    */
    public static function getByName($name) {
       if(empty($name)) {
           return false;
       }

        $sql = "SELECT person_id FROM " . DB_PREFIX . "people WHERE " .
            "CONCAT_WS(\" \", lower(first_name), lower(last_name))=" .
            "lower(\"" . escape_string($name) . "\")";
        return person::getRecordsFromQuery($sql);
    }

    /**
     * Get Top N people
     */
    public static function getTopN() {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            $sql =
                "select ppl.*, count(*) as count from " .
                DB_PREFIX . "people as ppl, " .
                DB_PREFIX . "photo_people as pp " .
                "where ppl.person_id = pp.person_id " .
                "group by ppl.person_id " .
                "order by count desc, ppl.last_name, ppl.first_name " .
                "limit 0, " . escape_string($user->prefs->get("reports_top_n"));
        } else {
            $sql =
                "SELECT ppl.*, COUNT(DISTINCT ph.photo_id) AS count FROM " .
                DB_PREFIX . "people as ppl JOIN " .
                DB_PREFIX . "photo_people as pp " .
                "ON pp.person_id = ppl.person_id JOIN " .
                DB_PREFIX . "photos as ph " .
                "ON pp.photo_id = ph.photo_id JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON pa.photo_id = pp.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . 
                escape_string($user->get("user_id")) . "' " .
                " AND gp.access_level >= ph.level " .
                "GROUP BY ppl.person_id " .
                "ORDER BY count DESC, ppl.last_name, ppl.first_name " .
                "LIMIT 0, " . escape_string($user->prefs->get("reports_top_n"));
        }

        return static::getTopNfromSQL($sql);

    }
}

function get_people($constraints = null, $conj = "and", $ops = null,
    $order = "last_name, first_name", $user=null) {
    return person::getRecords($order, $constraints, $conj, $ops);
}

function get_people_count($user = null, $search = null) {
    if($user && !$user->is_admin()) {
        $allowed=array();
        $people=get_photographed_people($user, $search);
        $photographers=photographer::getAll($search);
        foreach($people as $person) {
            $allowed[]=$person->get("person_id");
        }
        foreach($photographers as $photographer) {
            $allowed[]=$photographer->get("person_id");
        }

        $allowed=array_unique($allowed);

        return count($allowed);
    } else {
        return person::getCount();
    }
}

function get_all_people($user = null, $search = null, $search_first = false) {
    $allowed=array();

    if($user && !$user->is_admin()) {
        $people=get_photographed_people($user, $search, $search_first);
        $photographers=photographer::getAll($search, $search_first);
        foreach($people as $person) {
            $allowed[]=$person->get("person_id");
        }
        foreach($photographers as $photographer) {
            $allowed[]=$photographer->get("person_id");
        }

        $allowed=array_unique($allowed);
        if(count($allowed)==0) {
            return null;
        }
        $keys=implode(",", $allowed);
        $where=" WHERE person_id IN (" .$keys . ")";
    } else if ($search!==null) {
        $where=get_where_for_search(" WHERE ", $search, $search_first);
    } else {
        $where="";
    }

    $sql="SELECT * FROM " . DB_PREFIX . "people AS ppl " . $where .
        " ORDER BY last_name, called, first_name";

    return person::getRecordsFromQuery($sql);
}

function get_photographed_people($user = null, $search=null, $search_first = false) {
    $where=get_where_for_search(" and ", $search, $search_first);
    if ($user && !$user->is_admin()) {
        $sql =
            "select distinct ppl.* from " .
            DB_PREFIX . "people AS ppl JOIN " .
            DB_PREFIX . "photo_people AS pp " .
            "ON ppl.person_id = pp.person_id JOIN " . 
            DB_PREFIX . "photos AS ph " .
            "ON ph.photo_id = pp.photo_id JOIN " .
            DB_PREFIX . "photo_albums AS pa " .
            "ON pa.photo_id = ph.photo_id JOIN " .
            DB_PREFIX . "group_permissions as gp " .
            "ON pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu " .
            "ON gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) . "' " .
            " AND gp.access_level >= ph.level" . $where .
            " ORDER BY ppl.last_name, ppl.called, ppl.first_name";
    }
    else {
        $sql =
            "select distinct ppl.* from " .
            DB_PREFIX . "people as ppl, " .
            DB_PREFIX . "photo_people as pp " .
            "where ppl.person_id = pp.person_id " . $where .
            " order by ppl.last_name, ppl.called, ppl.first_name";
    }

    return person::getRecordsFromQuery($sql);
}

function get_where_for_search($conj, $search, $search_first) {
    $where="";
    if($search!==null) {
        if($search==="") {
            $where=$conj . " (ppl.last_name='' or ppl.last_name is null)";
        } else {
            $search=escape_string($search);
            $where=$conj . " (ppl.last_name like lower('" . $search . "%')";
            if ($search_first) {
                $where.="or ppl.first_name like lower('" . $search . "%'))";
            } else {
                $where.=")";
            }
        }
    }
    return $where;
}

function get_people_select_array(user $user = null, array $people_array = null) {
    $ppl[""] = "";

    if (!$people_array) {
        $people_array = get_people(null,null,null,"last_name, first_name",$user);
    }
    if ($people_array) {
        foreach ($people_array as $person) {
            $ppl[$person->get("person_id")] =
                 ($person->get("last_name") ? $person->get("last_name") .  ", " : "") .
                 ($person->get("called") ? $person->get("called") : $person->get("first_name"));
        }
    }

    return $ppl;
}


function get_photo_person_links($photo) {

    $links = "";
    if (!$photo) { return $links; }
    $people = $photo->getPeople();
    if ($people) {
        foreach ($people as $person) {
            if ($links) { $links .= ", "; }
            $links .= $person->getLink(0);
        }
    }

    return $links;
}


function create_person_pulldown($name, $value=null, user $user, $sa=null) {
    $id=preg_replace("/^_+/", "", $name);
    if($value) {
        $person=new person($value);
        $person->lookup();
        $text=$person->getName();
    } else {
        $text = "";
    }
    if($user->prefs->get("autocomp_people") && conf::get("interface.autocomplete")) {
        $html="<input type=hidden id='" . e($id) . "' name='" . e($name) . "'" .
            " value='" . e($value) . "'>";
        $html.="<input type=text id='_" . e($id) . "' name='_" . e($name) . "'" .
            " value='" . e($text) . "' class='autocomplete'>";
    } else {
        if(!isset($sa)) {
            $sa=get_people_select_array($user);
        }
        $html=create_pulldown($name, $value, $sa);
    }
    return $html;
}

?>
