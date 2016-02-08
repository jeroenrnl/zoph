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

use db\select;
use db\param;
use db\insert;
use db\update;
use db\delete;
use db\db;
use db\clause;
use db\selectHelper;

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
    protected static $tableName="people";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("person_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("first_name");
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

        $result=db::query($qry)->fetch(PDO::FETCH_ASSOC);
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
        $qry->where($where);
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

        $params=array(
            new param(":id", (int) $id, PDO::PARAM_INT)
        );

        $qry=new update(array("people"));
        $qry->addSetFunction("father_id=null");
        $where=new clause("father_id=:id");
        $qry->where($where);
        $qry->addParams($params);
        $qry->execute();

        $qry=new update(array("people"));
        $qry->addSetFunction("mother_id=null");
        $where=new clause("mother_id=:id");
        $qry->where($where);
        $qry->addParams($params);
        $qry->execute();

        $qry=new update(array("people"));
        $qry->addSetFunction("spouse_id=null");
        $where=new clause("spouse_id=:id");
        $qry->where($where);
        $qry->addParams($params);
        $qry->execute();

        $qry=new update(array("photos"));
        $qry->addSetFunction("photographer_id=null");
        $where=new clause("photographer_id=:id");
        $qry->where($where);
        $qry->addParams($params);
        $qry->execute();

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
        return static::getFromId($this->get("father_id"));
    }

    /**
     * Get mother of this person
     * @return person mother
     */
    private function getMother() {
        return static::getFromId($this->get("mother_id"));
    }

    /**
     * Get spouse of this person
     * @return person spouse
     */
    private function getSpouse() {
        return static::getFromId($this->get("spouse_id"));
    }

    /**
     * Get children
     * Since people cannot be nested, always returns null
     */
    public function getChildren() {
        return null;
    }

    /**
     * Get name for this person
     * @return string name
     */
    public function getName() {
        $this->lookup();
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

        return "<a href=\"person.php?person_id=" . $this->getId() . "\">$name</a>";
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
        if ($mother instanceof person) {
            $display[translate("mother")] = $mother->getLink();
        }
        if ($father instanceof person) {
            $display[translate("father")] = $father->getLink();
        }
        if ($spouse instanceof person) {
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
     * @todo This function is almost equal to category::getAutoCover(), should be merged
     */
    public function getAutoCover($autocover=null) {
        $coverphoto=$this->getCoverphoto();
        if ($coverphoto instanceof photo) {
            return $coverphoto;
        }

        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("photo_id" => "DISTINCT ar.photo_id"));
        $qry->join(array("ar" => "view_photo_avg_rating"), "p.photo_id = ar.photo_id")
            ->join(array("pp" => "photo_people"), "p.photo_id = pp.photo_id");

        $where=new clause("pp.person_id=:id");
        $qry->addParam(new param(":id", $this->getId(), PDO::PARAM_INT));

        if (!user::getCurrent()->isAdmin()) {
            list($qry, $where) = selectHelper::expandQueryForUser($qry, $where);
        }

        $qry=selectHelper::getAutoCoverOrder($qry, $autocover);
        $qry->where($where);
        $coverphotos=photo::getRecordsFromQuery($qry);
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
        if (strpos($name, ":")!==false) {
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
     * @todo this function is almost equal to category::getDetails() they should be merged
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

        $qry->addGroupBy("p.photographer_id");

        $where=new clause("p.photographer_id=:photographerid");
        $qry->addParam(new param(":photographerid", $this->getId(), PDO::PARAM_INT));

        if (!user::getCurrent()->isAdmin()) {
            list($qry, $where) = selectHelper::expandQueryForUser($qry, $where);
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
        $details["title"]=translate("Photos taken by this person:", false);
        return parent::getDetailsXML($details);
    }

    /**
     * Lookup person by name;
     * @param string name
     */
    public static function getByName($name) {
        if (empty($name)) {
            return false;
        }
        $qry=new select(array("ppl" => "people"));
        $qry->addFields(array("person_id"));
        $where=new clause("CONCAT_WS(\" \", lower(first_name), lower(last_name))=lower(:name)");
        $qry->addParam(new param(":name", $name, PDO::PARAM_STR));
        $qry->where($where);

        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get Top N people
     */
    public static function getTopN() {
        $user=user::getCurrent();

        $qry=new select(array("ppl" => "people"));
        $qry->addFields(array("person_id", "first_name", "last_name"));
        $qry->addFunction(array("count" => "count(distinct pp.photo_id)"));
        $qry->join(array("pp" => "photo_people"), "ppl.person_id=pp.person_id");
        $qry->addGroupBy("ppl.person_id");
        $qry->addOrder("count DESC")->addOrder("ppl.last_name")->addOrder("ppl.first_name");

        $qry->addLimit((int) $user->prefs->get("reports_top_n"));
        if (!$user->isAdmin()) {
            list($qry, $where) = selectHelper::expandQueryForUser($qry);
            $qry->where($where);
        }
        return parent::getTopNfromSQL($qry);

    }

    /**
     * Get all people
     * @param string part of name to search for
     * @param bool Search for first name
     */
    public static function getAll($search=null, $search_first = false) {
        $where=null;

        $qry=new select(array("ppl" => "people"));
        $qry->addFunction(array("person_id" => "DISTINCT ppl.person_id"));
        if (!is_null($search)) {
            $where=static::getWhereForSearch($search, $search_first);
            $qry->addParam(new param("search", $search, PDO::PARAM_STR));
        }

        $qry->addOrder("ppl.last_name")->addOrder("ppl.called")->addOrder("ppl.first_name");

        if (!user::getCurrent()->isAdmin()) {
            list($qry,$where)=selectHelper::expandQueryForUser($qry, $where);
        }

        if ($where instanceof clause) {
            $qry->where($where);
        }
        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get XML tree of people
     * @param string string to search for
     * @param DOMDocument XML document to add children too
     * @param DOMElement root node
     * @return DOMDocument XML Document
     */
    public static function getXMLdata($search, DOMDocument $xml, DOMElement $rootnode) {
        if ($search=="") {
            $search=null;
        }
        $records=static::getAll($search,true);
        $idname=static::$primaryKeys[0];

        foreach ($records as $record) {
            $record->lookup();
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
        if (isset(static::$sacache)) {
            return static::$sacache;
        }
        $ppl[""] = "";

        $people_array = static::getAll();
        foreach ($people_array as $person) {
            $ppl[$person->getId()] =
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
        if ($user && !$user->isAdmin()) {
            return static::getCount();
        } else {
            $allowed=array();
            $people=static::getAll();
            $photographers=photographer::getAll();
            foreach ($people as $person) {
                $allowed[]=$person->getId();
            }
            foreach ($photographers as $photographer) {
                $allowed[]=$photographer->getId();
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

        $qry=new select(array("ppl" => "people"));
        $qry->addOrder("ppl.last_name")->addOrder("ppl.called")->addOrder("ppl.first_name");


        if (!$user->isAdmin()) {
            $people=(array)static::getAll($search);
            $photographers=(array)photographer::getAll($search);
            foreach ($people as $person) {
                $person->lookup();
                $allowed[]=$person->getId();
            }
            foreach ($photographers as $photographer) {
                $photographer->lookup();
                $allowed[]=$photographer->getId();
            }
            $allowed=array_unique($allowed);
            if (count($allowed)==0) {
                return null;
            }
            $param=new param(":person_ids", $allowed, PDO::PARAM_INT);
            $qry->where(clause::InClause("person_id", $param));
            $qry->addParam($param);
        } else if ($search!==null) {
            $qry->addParam(new param("search", $search, PDO::PARAM_STR));
            $qry->where(static::getWhereForSearch($search));
        }
        return static::getRecordsFromQuery($qry);
    }

    /**
     * Get all people and all photographers for the currently logged on user
     * that are NOT a member of a circle
     */
    public static function getAllNoCircle() {
        $all = static::getAllPeopleAndPhotographers();
        $circles = circle::getRecords();
        $return=array();

        foreach ($all as $person) {
            $return[$person->getId()] = $person;
        }

        foreach ($circles as $circle) {
            $members=$circle->getMembers();
            foreach ($members as $member) {
                if (isset($return[$member->getId()])){
                    unset($return[$member->getId()]);
                }
            }
        }
        return $return;
    }

    /**
     * Get SQL WHERE clause to search for people
     * @param string search string
     * @param bool search for first name
     */
    public static function getWhereForSearch($search, $search_first=false) {
        $where=null;
        if ($search!==null) {
            if ($search==="") {
                $where=new clause("ppl.last_name=''");
                $where->addOr(new clause("ppl.last_name is null"));
            } else {
                $where=new clause("ppl.last_name like lower(concat(:search,'%'))");
                if ($search_first) {
                    $where->addOr(new clause("ppl.first_name like lower(concat(:search, '%'))"));
                }
            }
        }
        return $where;

    }
}

?>
