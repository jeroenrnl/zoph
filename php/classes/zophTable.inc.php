<?php
/**
 * A generic table class.  Is is meant to be subclassed by particular
 * table classes.  A table is represented by a name, an array of
 * primary keys, and an array mapping field names to values.
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
 * @package Zoph
 */

use db\select;
use db\db;
use db\insert;
use db\param;
use db\update;
use db\query;
use db\clause;
use db\delete;

use conf\conf;

use template\block;
use template\template;

/**
 * A generic table class.  Is is meant to be subclassed by particular
 * table classes.  A table is represented by a name, an array of
 * primary keys, and an array mapping field names to values.
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
abstract class zophTable {
    /** @var string The name of the database table */
    protected static $tableName;
    /** @var array List of primary keys */
    protected static $primaryKeys=array();
    /** @var array Fields that are integers */
    protected static $isInteger=array();
    /** @var array Fields that are floats */
    protected static $isFloat=array();
    /** @var array Fields that may not be empty */
    protected static $notNull=array();
    /** @var bool keep keys with insert. In most cases the keys are set
     *   by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url;

    /** @var array Contains the values of attributes that will be stored in the db */
    public $fields=array();

    /** @var array Contains the selectArray cache */
    protected static $sacache;

    /**
     * Create new object
     * @param int object id
     */
    public function __construct($id=0) {
        if ($id && !is_numeric($id)) { die("id for " . get_called_class() . " must be numeric"); }
        $this->set(static::$primaryKeys[0],$id);
    }

    /**
     * Returns the value of a field
     * @param string name of field to get
     * @return string value of the field
     */
    public function get($name) {
        log::msg("<b>GET</b> " . $name, log::DEBUG, log::VARS);
        log::msg("<pre>" . var_export($this->fields, true) . "</pre>", log::MOREDEBUG, log::VARS);
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        } else {
            return "";
        }
    }

    /**
     * Get ID
     * @return int id
     * @throws zophException
     */
    public function getId() {
        if (sizeof(static::$primaryKeys)==1) {
            return (int) $this->get(static::$primaryKeys[0]);
        } else {
            throw new zophException("This class (" . get_class($this) . ") " .
              "requires a specific getId() implementation, please report a bug");
        }
    }

    /**
     * Sets the value of a field.
     * @param string Name of the field to set
     * @param string Value to set it to
     */
    public function set($name, $value) {
        $this->fields[$name] = $value;
    }

    /**
     * Sets fields from the given array.  Can be used to set vars
     * directly from a GET or POST.
     * @param array Variables to be set (like $_GET)
     * @param string Prefix to cut off from beginning of key name
     * @param string Suffic to cut off from end of key name
     * @param bool Whether or not to process empty fields
      */
    public function setFields(array $vars, $prefix = null, $suffix = null, $null=true) {
        foreach ($vars as $key => $val) {
            log::msg("<b>" . $key . "</b> = " . implode(",", (array) $val), log::DEBUG, log::VARS);

            // ignore empty keys or values unless the field must be set.

            if ($null) {
                if ((!in_array($key, static::$notNull)) && (empty($key))) { continue; }
            } else {
                if ((!in_array($key, static::$notNull)) && (empty($key) || $val == "")) {
                    continue;
                }
            }


            if ($prefix) {
                if (strpos($key, $prefix) === 0) {
                    $key = substr($key, strlen($prefix));
                } else {
                    continue;
                }
            } else if ($key[0] == '_') {
                // a leading uderscore signals a non-database field
                continue;
            }

            if ($suffix) {
                $pos = strpos($key, $suffix);
                if (($pos > 0) && (preg_match("/".$suffix."$/", $key))) {
                    $key = substr($key, 0, $pos);
                } else {
                    continue;
                }
            }

            // something in ALL CAPS is probably PHP or HTML related
            if (strtoupper($key) == $key) { continue; }

            $this->fields[$key] = stripslashes($val);

        }
    }

    /**
     * Checks to see if the given field is listed as a primary key.
     * @param string Name of the field
     * @return bool Whether or not field is listed
     */
    public function isKey($name) {
        return in_array($name, static::$primaryKeys);
    }

    /**
     * Looks up a record.
     * @return bool success or fail
     * @todo Should return something more sensible
     */
    public function lookup() {
        $qry=new select(array(static::$tableName));

        list($qry, $where) = $this->addWhereForKeys($qry);

        if (!($where instanceof clause)) {
            log::msg("No constraint found", log::NOTIFY, log::GENERAL);
            return;
        }

        $qry->where($where);

        return $this->lookupFromSQL($qry);
    }

    /**
     * Looks up a record using supplied SQL query
     * @param select SQL query to use
     * @return bool success or fail
     */
    public function lookupFromSQL(select $qry) {
        try {
            $result = db::query($qry);
        } catch (PDOException $e) {
            log::msg("Lookup failed", log::FATAL, log::DB);
        }

        $results=$result->fetchAll(PDO::FETCH_ASSOC);
        $rows=count($results);

        if ($rows == 1) {
            $row=array_pop($results);
            $this->fields = array();
            $this->fields = array_merge($this->fields, $row);

            return true;
        }

        return false;
    }

    /**
     * Inserts a record.
     * The default behavior is to ignore the
     * primary key field(s) with the assumption that these will
     * be generated by the db (auto_increment).  Passing a non null
     * parameter causes these fields to be manually inserted.
     */
    public function insert() {
        $qry=new insert(array(static::$tableName));
        reset($this->fields);

        foreach ($this->fields as $name => $value) {
            if (!static::$keepKeys && $this->isKey($name)) {
                continue;
            }
            if ($value === "now()") {
                /* Lastnotify is normally set to "now()" and should not be escaped */
                $qry->addSet($name, "now()");
            } else {
                $qry=$this->processValues($name, $value, $qry);
            }
        }

        $id=$qry->execute();
        if (count(static::$primaryKeys) == 1 && !static::$keepKeys) {
            $this->fields[static::$primaryKeys[0]] = $id;
        }

        return $id;

    }

    /**
     * Retrieving a the selectarray can take a long time in some cases
     * pages that use it multiple times can cache it, so it only needs
     * to be retrieved once per page request.
     * @param array selectArray;
     */
    public static function setSAcache(array $sa=null) {
        if (!$sa) {
            $sa=static::getSelectArray();
        }
        static::$sacache=$sa;
    }

    /**
     * Deletes a record.  If extra tables are specified, entries from
     * those tables this match the keys are removed as well.
     * @var $extra_tables array Tables to delete referencing objects from
     */
    public function delete() {

        // simulate overloading
        if (func_num_args()>=1) {
            $extra_tables = func_get_arg(0);
        } else {
            $extra_tables = null;
        }

        $qry=new delete(array(static::$tableName));

        list($qry, $where) = $this->addWhereForKeys($qry);

        if (!($where instanceof clause)) {
            log::msg("No constraint found", log::NOTIFY, log::GENERAL);
            return;
        }

        $qry->where($where);

        try {
            $qry->execute();
        } catch (PDOException $e) {
            log::msg("Delete failed", log::FATAL, log::DB);
        }

        if ($extra_tables) {
            foreach ($extra_tables as $table) {
                $qry=new delete(array($table));
                list($qry, $where) = $this->addWhereForKeys($qry);
                $qry->where($where);
                try {
                    $qry->execute();
                } catch (PDOException $e) {
                    log::msg("Delete from " . $table . " failed", log::FATAL, log::DB);
                }
            }
        }
    }

    /**
     * Updates a record.
     */
    public function update() {
        $qry=new update(array(static::$tableName));

        list($qry, $where) = $this->addWhereForKeys($qry);

        reset($this->fields);
        foreach ($this->fields as $name => $value) {
            if ($this->isKey($name)) {
                continue;
            }

            if (substr($name,0,7)=="parent_") {
                $children=array();
                $this->getBranchIdArray($children);
                if (in_array($value, $children)) {
                    die("You cannot set the parent to a child of the current selection!");
                }
            }

            if ($value === "now()") {
                /* Lastnotify is normally set to "now()" and should not be escaped */
                $qry->addSetFunction($name . "=now()");
            } else {
                $qry=$this->processValues($name, $value, $qry);
                $qry->addSet($name, $name);
            }


        }

        if (sizeof($qry->getParams()) === 0 || sizeof($qry->getSet()) === 0) {
            return;
        }

        $qry->where($where);

        try {
            $qry->execute();
        } catch (PDOException $e) {
            log::msg("Update failed: " . $e->getMessage(), log::FATAL, log::DB);
        }
    }

    protected function processValues($name, $value, $qry) {
        if ((is_null($value) || $value==="") && in_array($name, static::$notNull)) {
            throw new notNullValueIsNullDataException(e($name) . "may not be empty");
        } else {
            if (in_array($name, static::$isFloat) && empty($value)) {
                $value = null;
            }
            if (in_array($name, static::$isInteger)) {
                if (is_null($value) || $value==="") {
                    $qry->addParam(new param(":" . $name, null, PDO::PARAM_NULL));
                } else {
                    $qry->addParam(new param(":" . $name, (int) $value, PDO::PARAM_INT));
                }
            } else {
                if (is_null($value)) {
                    $qry->addParam(new param(":" . $name, null, PDO::PARAM_NULL));
                } else {
                    $qry->addParam(new param(":" . $name, $value, PDO::PARAM_STR));
                }
            }
        }
        return $qry;
    }
    /**
     * Creates an alphabetized array of field names and values.
     * @return array Array for displaying object
     */
    public function getDisplayArray() {
        if (!$this->fields) { return; }

        $keys = array_keys($this->fields);
        sort($keys);
        reset($keys);
        $da=array();
        foreach ($keys as $k) {
            if ($this->isKey($k)) { continue; }
            $title = ucfirst(str_replace("_", " ", $k));
            $da[$title] = $this->fields[$k];
        }

        return $da;
    }

    /**
     * Get an URL for the current object
     * @return string URL
     */
    public function getURL() {
        return static::$url . $this->getId();
    }

    /**
     * Turn the array from @see getDetails() into XML
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(array $details=null) {
        if (!isset($details)) {
            $details=$this->getDetails();
        }
        if (isset($details["title"])) {
            $display["title"]=$details["title"];
        }
        if (array_key_exists("count", $details) && $details["count"] > 0) {

            // Remove timezone identifiers from time format
            // Because in the current way Zoph works, they do not make sense
            // It's not completely correct this way, because the data comes
            // from the database where it is not yet timezone-corrected.
            $timezone=array("e", "I", "O", "P", "T", "Z");
            $timeformat=str_replace($timezone, "", conf::get("date.timeformat"));
            $timeformat=trim(preg_replace("/\s\s+/", "", $timeformat));
            $format=conf::get("date.format") . " " . $timeformat;

            $oldest=new Time($details["oldest"]);
            $disp_oldest=$oldest->format($format);

            $newest=new Time($details["newest"]);
            $disp_newest=$newest->format($format);

            $first=new Time($details["first"]);
            $disp_first=$first->format($format);

            $last=new Time($details["last"]);
            $disp_last=$last->format($format);

            $display["count"]=$details["count"] . " " . translate("photos");
            $display["taken"]=sprintf(translate("taken between %s and %s",false),
                $disp_oldest, $disp_newest);
            $display["modified"]=sprintf(translate("last changed from %s to %s",false),
                $disp_first, $disp_last);
            if (isset($details["lowest"]) &&
                isset($details["highest"]) &&
                isset($details["average"])) {
                $display["rated"]=sprintf(
                    translate("rated between %s and %s and an average of %s",false),
                    $details["lowest"], $details["highest"], $details["average"]);
            } else {
                $display["rated"]=translate("no rating", false);
            }
        } else {
            $display["count"]=translate("no photos", false);
        }

        if (isset($details["children"])) {
            $count=$details["children"];
            if ($count==0) {
                $display["children"]="";
                $no="no ";
            } else {
                $display["children"]=$count . " ";
                $no="";
            }

            if ($this instanceof album) {
                $text=translate($no . "sub-albums", false);
            } else if ($this instanceof category) {
                $text=translate($no . "sub-categories", false);
            } else if ($this instanceof place) {
                $text=translate($no . "sub-places", false);
            } else {
                $text=translate($no . "children", false);
            }


            $display["children"].=$text;

        }
        $xml = new DOMDocument('1.0','UTF-8');
        $rootnode=$xml->createElement("details");
        $request=$xml->createElement("request");

        $class=$xml->createElement("class");
        $class->appendChild($xml->createTextNode(get_class($this)));
        $id=$xml->createElement("id");
        $id->appendChild($xml->createTextNode($this->getId()));

        $request->appendChild($class);
        $request->appendChild($id);
        $rootnode->appendChild($request);

        $response=$xml->createElement("response");

        foreach ($display as $subj => $data) {
            $detail=$xml->createElement("detail");
            $subject=$xml->createElement("subject");
            $subject->appendChild($xml->createTextNode($subj));
            $xmldata=$xml->createElement("data");
            $xmldata->appendChild($xml->createTextNode($data));
            $detail->appendChild($subject);
            $detail->appendChild($xmldata);
            $response->appendChild($detail);
        }
        $rootnode->appendChild($response);
        $xml->appendChild($rootnode);
        return $xml->saveXML();
    }

    /**
     * Return object from Id
     * @param int id
     * @return mixed object
     */
    public static function getFromId($id) {
        if (!is_null($id) && $id!=0) {
            $class=get_called_class();
            $obj=new $class($id);
            $obj->lookup();
            return $obj;
        }
    }

    /**
     * Gets the total count of records in the table for the given class.
     * @return int count
     */
    public static function getCount() {
        $qry=new select(array(static::$tableName));
        $qry->addFunction(array("count" => "COUNT(*)"));
        return $qry->getCount();
    }

    /**
     * Generates an array for Top N albums/cat/..
     * Executes a query and returns an array in which each record's
     * link is mapped to its count (dirived by a group by clause).
     * @param string query SQL query to use
     * @return array Table of Top N most popular $class
     */
    protected static function getTopNfromSQL($query) {
        $pop_array=array();
        $records = static::getRecordsFromQuery($query);
        foreach ($records as $rec) {
            $pop_array[] = array(
                "id"    => $rec->getId(),
                "url"   => $rec->getURL(),
                "count" => $rec->get("count"),
                "title" => $rec->getName()
            );
        }
        return $pop_array;
    }

    /**
     * Gets an array of the records for a table by doing a * "select *"
     * and storing the results in classes of the given type.
     * @param string Sort order
     * @param array Constraints, conditions that the records must comply to
     * @param array Conjunctions, and/or
     * @param array Operators =, !=, >, <, >= or <=
     * @return array records
     * @todo This should be an internal (protected) function
     */
    public static function getRecords($order = null, $constraints = null,
            $conj = "AND", $ops = null) {

        $qry = new select(static::$tableName);
        if (is_array($constraints)) {
            $qry->addWhereFromConstraints($constraints, $conj, $ops);
        }
        if ($order) {
            $qry->addOrder($order);
        }

        return static::getRecordsFromQuery($qry);
    }

    /**
     * Return all
     * @return array Array of objects
     */
    public static function getAll() {
        return static::getRecords();
    }

    /**
     * Extract a specific class from vars
     * @param array vars (like $_GET or $_POST)
     * @param string suffix to add to var key (e.g. _id)
     * @return array vars for specific class.
     */
    public static function getFromVars(array $vars, $suffix="") {
        $class=get_called_class();
        $return=array();

        $key="_" . $class . $suffix;

        if (isset($vars[$key])) {
            if (is_array($vars[$key])) {
                foreach ($vars[$key] as $id=>$var) {
                    if (!empty($var)) {
                        $return[$id]=$var;
                    }
                }
            } else {
                $return=(array) $vars[$key];
            }
        }

        return $return;
    }

    /**
     * Stores the results the the given query in an array of objects of
     * this given type.
     * @param select SQL query
     */
    public static function getRecordsFromQuery(select $qry) {
        $class=get_called_class();
        try {
            $result = db::query($qry);
        } catch (PDOException $e) {
            log::msg("Unable to get records: " . $e->getMessage(), log::FATAL, log::DB);
        }
        $objs=array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $obj = new $class;
            $obj->setFields($row);
            $objs[] = $obj;
        }
        return $objs;
    }

    /**
     * Creates a constraint clause based on the given keys
     */
    private function addWhereForKeys(query $query, clause $where = null) {
        foreach (static::$primaryKeys as $key) {
            $value = $this->fields[$key];
            if (!$value) {
                continue;
            }
            $clause = new clause($key . "=:" . $key);
            $query->addParam(new param(":" . $key, $value, PDO::PARAM_INT));

            if ($where instanceof clause) {
                $where->addAnd($clause);
            } else {
                $where = $clause;
            }
        }
        return array($query, $where);
    }

    /**
     * Get coverphoto.
     * @return photo coverphoto
     */
    public function getCoverphoto() {
        if ($this->get("coverphoto")) {
            $coverphoto=new photo($this->get("coverphoto"));
            if ($coverphoto->lookup()) {
                return $coverphoto;
            }
        }
        return false;
    }

    /**
     * Lookup an autocover and create template to display
     * @param how to select the autocover (olders, newest, first, last, random, highest [default])
     * @return block thumb img
     */
    public function displayAutoCover($autocover=null) {
        $cover=$this->getAutoCover($autocover);
        if ($cover instanceof photo) {
            return $cover->getImageTag(THUMB_PREFIX);
        }
    }

    /**
     * Lookup cover and create template to display
     * @return block thumb img
     */
    public function displayCoverPhoto() {
        $cover=$this->getCoverphoto();
        if ($cover instanceof photo) {
            return $cover->getImageTag(THUMB_PREFIX);
        }
    }

    /**
     * Get XML from a database table
     * This is a wrapper around several objects which will call a method from
     * those objects
     * @param string Search string
     */
    public static function getXML($search) {
        $search=strtolower($search);

        $xml = new DOMDocument('1.0','UTF-8');
        $rootnode=$xml->createElement(static::XMLROOT);
        $newchild=$xml->createElement(static::XMLNODE);
        $key=$xml->createElement("key");
        $title=$xml->createElement("title");
        $key->appendChild($xml->createTextNode("null"));
        $title->appendChild($xml->createTextNode("&nbsp;"));
        $newchild->appendChild($key);
        $newchild->appendChild($title);
        $rootnode->appendChild($newchild);

        return static::getXMLdata($search, $xml, $rootnode);
    }

    /**
     * Create a pulldown menu for this object
     * @param string name for this pulldown
     * @param int|string id of value
     */
    public static function createPulldown($name, $value=null) {
        if (static::getAutocompPref()) {
            return static::createAutoCompPulldown($name, $value);
        } else {
            if (isset(static::$sacache)) {
                $sa=static::$sacache;
            } else {
                $sa=static::getSelectArray();
            }
            return template::createPulldown($name, $value, $sa);
        }
    }

    public static function createAutoCompPulldown($name, $value=null) {
        $id=preg_replace("/^_+/", "", $name);
        $text="";
        if ($value) {
            $obj=static::getFromId($value);
            $obj->lookup();
            $text=$obj->getName();
        }

        $tpl=new block("autocomplete", array(
            "id"    => $id,
            "name"  => $name,
            "value" => $value,
            "text"  => $text
        ));
        return $tpl;
    }

    /**
     * Get an array of id => name to build a non-hierarchical array
     * this function does NOT check user permissions
     * @return array
     */
    public static function getSelectArray() {
        $records=static::getRecords();
        $selectArray=array(null => "");
        foreach ($records as $record) {
            $selectArray[(string) $record->getId()] = $record->getName();
        }
        return $selectArray;
    }

}
?>
