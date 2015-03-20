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

    /** @param Name of the root node in XML responses */
    const XMLROOT="people";
    /** @param Name of the leaf nodes in XML responses */
    const XMLNODE="person";


    /** @var string The name of the database table */
    protected static $table_name="people";
    /** @var array List of primary keys */
    protected static $primary_keys=array("person_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("first_name");
    /** @var bool keep keys with insert. In most cases the keys are set 
                  by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="person.php?person_id=";

    /** @var array Cached Search Array */
    protected static $sacache;

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

        $qry=new insert(array("photo_people"));
        $qry->addParams(array(
            new param(":photo_id", (int) $photo->getId(), PDO::PARAM_INT),
            new param(":person_id", (int) $this->getId() , PDO::PARAM_INT),
            new param(":position", (int) $pos, PDO::PARAM_INT)
        ));
        $qry->execute();
    }

    /**
     * Remove person from a photo
     * @param photo photo to remove the person from
     */
    public function removePhoto(photo $photo) {
        // First, get the position for the person who is about to be removed
        $qry=new select(array("photo_people"));
        $where=new clause("photo_id=:photo_id");
        $where->addAnd(new clause("person_id=:person_id"));

        $params=array(
            new param(":photo_id", (int) $photo->getId(), PDO::PARAM_INT),
            new param(":person_id", (int) $this->getId(), PDO::PARAM_INT)
        );

        $qry->where($where);
        $qry->addParams($params);

        $result=fetch_array(query($qry));
        $pos=$result["position"];

        $qry=new delete("photo_people");
        $qry->where($where);
        $qry->addParams($params);
        $qry->execute();

        $qry=new update(array("photo_people"));

        $where=new clause("photo_id=:photo_id");
        $where->addAnd(new clause("position>:pos"));

        $qry->addSetFunction("position=position-1");
        
        $params=array(
            new param(":photo_id", (int) $photo->getId(), PDO::PARAM_INT),
            new param(":pos", (int) $pos, PDO::PARAM_INT)
        );
        
        $qry->addParams($params);
        $qry->execute();

    }

    /**
     * Lookup from database
     */
    public function lookup() {
        parent::lookup();
        $this->lookupPlaces();
    }

    /**
     * Lookup home and work for this person
     */
    private function lookupPlaces() {
        if ($this->get("home_id") > 0) {
            $this->home = new place($this->get("home_id"));
            $this->home->lookup();
        }
        if ($this->get("work_id") > 0) {
            $this->work = new place($this->get("work_id"));
            $this->work->lookup();
        }
    }

    /**
     * Returns a photographer object for this person
     * @return photographer
     */
    public function getPhotographer() {
        $photographer=new photographer($this->getId());
        $photographer->lookup();
        return $photographer;
    }

    /**
     * Delete this person
     * @todo calls 'die'
     */
    public function delete() {
        $id=(int) $this->getId();
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

    /**
     * Get gender
     * @return string "male|female"
     */
    private function getGender() {
        if ($this->get("gender") == 1) { return translate("male"); }
        if ($this->get("gender") == 2) { return translate("female"); }
    }

    /**
     * Get father of this person
     * @return person father
     */
    private function getFather() {
        return self::getFromId($this->get("father_id"));
    }

    /**
     * Get mother of this person
     * @return person mother
     */
    private function getMother() {
        return self::getFromId($this->get("mother_id"));
    }

    /**
     * Get spouse of this person
     * @return person spouse
     */
    private function getSpouse() {
        return self::getFromId($this->get("spouse_id"));
    }

    /**
     * Get children for this person
     * @todo This function is currently not used  
     */
    public function getChildren() {
        $constraints["father_id"] = $this->get("person_id");
        $constraints["mother_id"] = $this->get("person_id");
        return self::getAll($constraints, "or");
    }

    /**
     * Get only children this user can see
     * @todo This function is currently not used  
     * @todo This function currently does not filter out persons 
     *       this user cannot see
     */
    public function getChildrenForUser() {
        return $this->getChildren();
    }

    /**
     * Get name for this person
     * @return string name
     */
    public function getName() {
        if ($this->get("called")) {
            $name = $this->get("called");
        } else {
            $name = $this->get("first_name");
        }

        if ($this->get("last_name")) {
            $name .= " " . $this->get("last_name");
        }

        return $name;
    }

    /**
     * Get mail address for this person
     * @return string mailaddress
     */
    public function getEmail() {
        return $this->get("email");
    }

    /**
     * HTML display of this person
     * Returns only name for this person
     * @return string name
     */
    public function toHTML() {
        return getName();
    }

    /**
     * Get a link to this person
     * @todo Not proper OO, parent function does not have parameter
     * @todo returns HTML
     * @param int|bool show last name in link
     */
    public function getLink($show_last_name = 1) {
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

    public function getURL() {
        return "person.php?person_id=" . $this->getId();
    }

    /**
     * Get an array of the properties of this person object, for display
     * @return array
     */
    public function getDisplayArray() {
        $mother=$this->getMother();
        $father=$this->getFather();
        $spouse=$this->getSpouse();

        $display=array(
            translate("called") => e($this->get("called")),
            translate("date of birth") => create_date_link(e($this->get("dob"))),
            translate("date of death") => create_date_link(e($this->get("dod"))),
            translate("gender") => e($this->getGender()));
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
                DB_PREFIX . "photos AS p JOIN " .
                DB_PREFIX . "view_photo_avg_rating ar " .
                " ON p.photo_id = ar.photo_id JOIN " .
                DB_PREFIX . "photo_people AS pp" .
                " ON pp.photo_id = ar.photo_id " .
                " WHERE pp.person_id = " . 
                escape_string($this->get("person_id")) .
                " " . $order;
        } else {
            $sql=
                "SELECT DISTINCT p.photo_id FROM " .
                DB_PREFIX . "photos AS p JOIN " .
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
     * @param string name
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
                "WHERE ph.photographer_id=" . escape_string($id) .
                " GROUP BY ph.photographer_id";
        } else {
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
     * @param string name
     */
    public static function getByName($name) {
        if(empty($name)) {
            return false;
        }

        $sql = "SELECT person_id FROM " . DB_PREFIX . "people WHERE " .
            "CONCAT_WS(\" \", lower(first_name), lower(last_name))=" .
            "lower(\"" . escape_string($name) . "\")";
        return self::getRecordsFromQuery($sql);
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
    
    /**
     * Get all people
     * @param string part of name to search for
     * @param bool Search for first name
     */
    public static function getAll($search=null, $search_first = false) {
        $user=user::getCurrent();
        $where=self::getWhereForSearch("", $search, $search_first);
        if ($user->is_admin()) {
            if($where!="") {
                $where="WHERE " . $where;
            }
            $sql =
                "SELECT * FROM " .
                DB_PREFIX . "people " .
                $where .
                " ORDER BY last_name, called, first_name";
        } else {
            if($where!="") {
                $where="AND " . $where;
            }
            $sql =
                "SELECT DISTINCT ppl.* FROM " .
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
                "WHERE gu.user_id = " . (int) $user->getId() .
                " AND gp.access_level >= ph.level " . $where .
                " ORDER BY ppl.last_name, ppl.called, ppl.first_name";
        }

        return self::getRecordsFromQuery($sql);
    }

    /**
     * Get XML tree of people
     * @param string string to search for
     * @param DOMDocument XML document to add children too
     * @param DOMElement root node
     * @return DOMDocument XML Document
     */
    public static function getXMLdata($search, DOMDocument $xml, DOMElement $rootnode) {
        if($search=="") {
            $search=null;
        }
        $records=static::getAll($search,true);
        $idname=static::$primary_keys[0];

        foreach($records as $record) {
            $newchild=$xml->createElement(static::XMLNODE);
            $key=$xml->createElement("key");
            $title=$xml->createElement("title");
            $key->appendChild($xml->createTextNode($record->get($idname)));
            $title->appendChild($xml->createTextNode($record->getName()));
            $newchild->appendChild($key);
            $newchild->appendChild($title);
            $rootnode->appendChild($newchild);
        }
        $xml->appendChild($rootnode);
        return $xml;
    }

    /**
     * Get autocomplete preference for people for the current user
     * @return bool whether or not to autocomplete
     */
    public static function getAutocompPref() {
        $user=user::getCurrent();
        return ($user->prefs->get("autocomp_people") && conf::get("interface.autocomplete"));
    }

    /**
     * Get array to build select box
     * @return array
     */
    public static function getSelectArray() {
        if(isset(static::$sacache)) {
            return static::$sacache;
        }
        $ppl[""] = "";

        $people_array = self::getAll();
        foreach ($people_array as $person) {
            $ppl[$person->get("person_id")] =
                 ($person->get("last_name") ? $person->get("last_name") .  ", " : "") .
                 ($person->get("called") ? $person->get("called") : $person->get("first_name"));
        }

        return $ppl;
    }   

    /**
     * Get number of people for a specific user
     * @return int count
     */
    public static function getCountForUser() {
        $user=user::getCurrent();
        if($user && !$user->is_admin()) {
            return self::getCount();
        } else {
            $allowed=array();
            $people=self::getAll();
            $photographers=photographer::getAll();
            foreach($people as $person) {
                $allowed[]=$person->get("person_id");
            }
            foreach($photographers as $photographer) {
                $allowed[]=$photographer->get("person_id");
            }

            $allowed=array_unique($allowed);

            return count($allowed);
        }
    }

    /**
     * Get all people and all photographers for the current logged on user
     * @param string only return people whose name starts with this string
     * @return int count
     */
    public static function getAllPeopleAndPhotographers($search = null) {
        $user=user::getCurrent();
        $allowed=array();

        if($user && !$user->is_admin()) {
            $people=self::getAll($search);
            $photographers=photographer::getAll($search);
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
            $where=self::getWhereForSearch(" WHERE ", $search);
        } else {
            $where="";
        }

        $sql="SELECT * FROM " . DB_PREFIX . "people AS ppl " . $where .
            " ORDER BY last_name, called, first_name";

        return self::getRecordsFromQuery($sql);
    }

    /**
     * Get SQL WHERE statement to search for people
     * @param string [and|or]
     * @param string search string
     * @param bool search for first name
     */
    public static function getWhereForSearch($conj, $search, $search_first=false) {
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
}

?>
