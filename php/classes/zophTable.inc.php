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
    protected static $table_name;
    /** @var array List of primary keys */
    protected static $primary_keys=array();
    /** @var array Fields that may not be empty */
    protected static $not_null=array();
    /** @var bool keep keys with insert. In most cases the keys are set by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url;

    /** @var array Contains the values of attributes that will be stored in the db */
    public $fields=array();

    /**
     * Create new object
     * @param int object id
     */
    public function __construct($id=0) {
        if($id && !is_numeric($id)) { die("id for " . get_called_class() . " must be numeric"); }
        $this->set(static::$primary_keys[0],$id);
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
     * @throws ZophException
     */
    public function getId() {
        if(sizeof(static::$primary_keys)==1) {
            return (int) $this->get(static::$primary_keys[0]);
        } else {
            var_dump($this);
            throw new ZophException("This class (" . get_class($this) . ") requires a specific getId() implementation, please report a bug");
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

        reset($vars);
        while (list($key, $val) = each($vars)) {
            log::msg("<b>" . $key . "</b> = " . implode(",", (array) $val), log::DEBUG, log::VARS);

            // ignore empty keys or values unless the field must be set.

            if ($null) {
                if ((!in_array($key, static::$not_null)) && (empty($key) )) { continue; }
            } else {
                if ((!in_array($key, static::$not_null)) && (empty($key) || $val == "")) { continue; }
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
                }
                else {
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
        return in_array($name, static::$primary_keys);
    }

    /**
     * Looks up a record.
     * @return mixed 1 or 0
     * @todo Should return something more sensible
     */
    public function lookup() {
        $constraint = $this->createConstraints();

        if (!$constraint) {
            log::msg("No constraint found", log::NOTIFY, log::GENERAL);
            return;
        }

        $sql = "SELECT * FROM " . DB_PREFIX . static::$table_name . " WHERE $constraint";

        return $this->lookupFromSQL($sql);
    }

    /**
     * Looks up a record using supplied SQL query
     * @param string SQL query to use 
     */
    public function lookupFromSQL($sql) {
        $result = query($sql, "Lookup failed:");
        if (num_rows($result) == 1) {
            $row = fetch_assoc($result);

            $this->fields = array();

            $this->fields = array_merge($this->fields, $row);

            return 1;
        }

        return 0;
    }

    /**
     * Inserts a record.  The default behavior is to ignore the
     * primary key field(s) with the assumption that these will
     * be generated by the db (auto_increment).  Passing a non null
     * parameter causes these fields to be manually inserted.
     * @param bool Whether or not a key should be overwritten
     * @todo the $keep_key makes this function incompatible with 
     *       descendants of this class. It is used by the
     *       @see group_permissions class.
     */
    public function insert() {
        $names=null;
        $values=null;
        while (list($name, $value) = each($this->fields)) {
            if (!static::$keepKeys && $this->isKey($name)) {
                continue;
            }

            if (!empty($names)) {
                $names .= ", ";
                $values .= ", ";
            } 

            $names .= $name;

            if ($name == "password") {
                $values .= "password('" . escape_string($value) . "')";
            }
            else if ($value == "now()") {
                /* Lastnotify is normaly set to "now()" and should not be escaped */
                $values .=  $value ;
            } else if ($value =="" && in_array($name, static::$not_null)) {
	    	    die("<p class='error'><b>$name</b> may not be empty</p>");
	        } else if ($value !== "") {
                $values .= "'" . escape_string($value) . "'";
            } else {
                $values .= "null";
            }

        }

        $sql = "INSERT INTO " . DB_PREFIX . static::$table_name . "(" . $names . ") VALUES (" . $values . ")";

        query($sql, "Insert failed:");

        $id = insert_id();

        if (count(static::$primary_keys) == 1) {
            $this->fields[static::$primary_keys[0]] = $id;
        }

        return $id;

    }

    /**
     * Deletes a record.  If extra tables are specified, entries from
     * those tables this match the keys are removed as well.
     * @param array Tables to delete referencing objects from
     */
    public function delete() {
        
        // simulate overloading
        if(func_num_args()>=1) {
            $extra_tables = func_get_arg(0);
        } else {
            $extra_tables = null;
        }
        $keys = static::$primary_keys;

        $constraints = $this->createConstraints();

        if (!$constraints) {
            log::msg("No constraint found", log::NOTIFY, log::GENERAL);
            return;
        }

        $sql = "DELETE FROM " . DB_PREFIX . static::$table_name . " WHERE " . $constraints;

        query($sql, "Delete failed:");

        if ($extra_tables) {
            foreach ($extra_tables as $table) {
                $sql = "DELETE FROM " . DB_PREFIX . $table . " WHERE " . $constraints;
                query($sql, "Delete from " . DB_PREFIX . " $table failed:");
            }
        }
    }

    /**
     * Updates a record.
     */
    public function update() {
        $keys = static::$primary_keys;

        $constraints = $this->createConstraints();

        reset($this->fields);
        $values=null;
        $names=null;
        while (list($name, $value) = each($this->fields)) {
            if ($this->isKey($name)) { continue; }

            if (!empty($values)) { $values .= ", "; }
            
            if (substr($name,0,7)=="parent_") {
                $children=array();
                $this->getBranchIdArray($children);
                if(in_array($value, $children)) {
                    die("You cannot set the parent to a child of the current selection!");
                } 
            }

            if ($name == "password") {
                $values .= "$name = password('" . escape_string($value) . "')";
            } else if ($value == "now()" ) {
                $values .= "$name = " . $value . "";
            } else if ($value == "" && in_array($name, static::$not_null)) {
	    	    die("<p class='error'><b>$name</b> may not be empty</p>");
	        } else if ($value !== "" && !is_null($value)) {
                $values .= "$name = '" . escape_string($value) . "'";
            } else {
                $values .= "$name = null";
            }
        }

        if (!$values) { return; }

        $sql = "UPDATE " . DB_PREFIX . static::$table_name ." SET " . $values . " WHERE " . $constraints;

        query($sql, "Update failed:");

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
        foreach ($keys as $k) {
            if ($this->isKey($k)) { continue; }
            $title = make_title($k);
            $da[$title] = $this->fields[$k];
        }

        return $da;
    }

    /**
     * Creates an alphabetized array of field names and text input blocks.
     * @todo Returns HTML, should be moved to template
     * @param user Unused, but some of the decendant classes do.
     * @return array of field names and HTML text input fields
     */
    public function getEditArray() {
        if (!$this->fields) { return; }

        $field_lengths = get_field_lengths(static::$table_name);

        $keys = array_keys($field_lengths);
        sort($keys);
        reset($keys);
        foreach ($keys as $k) {
            if ($this->isKey($k)) { continue; }
            $title = make_title($k);

            $len = $field_lengths[$k];
            $size = min($len, 20);

            $ea[$title] = create_text_input($k, $this->fields[$k], $size, $len);
        }

        return $ea;
    }

    /**
     * Turn the array from @see getDetails() into XML
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(array $details=null) {
        if(!isset($details)) {
            $details=$this->getDetails();
        }
        if(isset($details["title"])) {    
            $display["title"]=$details["title"];
        }
        if(array_key_exists("count", $details) && $details["count"] > 0) {

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
            $disp_last=$newest->format($format);
            
            $display["count"]=$details["count"] . " " . translate("photos");
            $display["taken"]=sprintf(translate("taken between %s and %s",false), $disp_oldest, $disp_newest);
            $display["modified"]=sprintf(translate("last changed from %s to %s",false), $disp_first, $disp_last);
            if(isset($details["lowest"]) && isset($details["highest"]) && isset($details["average"])) {
                $display["rated"]=sprintf(translate("rated between %s and %s and an average of %s",false), $details["lowest"], $details["highest"], $details["average"]);
            } else {
                $display["rated"]=translate("no rating", false);
            }
        } else {
            $display["count"]=translate("no photos", false);
        }

        if(isset($details["children"])) {
            $count=$details["children"];
            if($count==0) {
                $display["children"]="";
                $no="no ";
            } else {
                $display["children"]=$count . " ";
                $no="";
            }

            if($this instanceof album) {
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

        foreach($display as $subj => $data) {
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
     * Gets the total count of records in the table for the given class.
     * @return int count
     */
    public static function getCount() {
        $sql = "SELECT COUNT(*) FROM " . DB_PREFIX . static::$table_name;

        return static::getCountFromQuery($sql);
    }

    /**
     * Generates an array for Top N albums/cat/.. 
     * Executes a query and returns an array in which each record's
     * link is mapped to its count (dirived by a group by clause).
     * @param string classname
     * @param string query SQL query to use
     * @return array Table of Top N most popular $class
     */
    protected static function getTopNfromSQL($query) {
        $pop_array=array();
        $records = static::getRecordsFromQuery($query);
        foreach ($records as $rec) {
            $pop_array[$rec->getLink()] = $rec->get("count");
        }
        return $pop_array;
    }
    
    /**
     * Executes a "SELECT COUNT(*) FROM ..." query and returns the counter
     */
    public static function getCountFromQuery($sql) {
        $result = query($sql, "Unable to get count");
        return result($result, 0, 0);
    }


    /**
     * Gets an array of the records for a table by doing a * "select *"
     * and storing the results in classes of the given type.
     * @todo the $class can be removed when PHP5.3 is min version
     */
    public static function getRecords($order = null, $constraints = null, $conj = "and", $ops = null) {
        $sql = "SELECT * FROM " . DB_PREFIX . static::$table_name;
        if ($constraints) {
            while (list($name, $value) = each($constraints)) {
                if (!empty($constraint_string)) {
                    $constraint_string .= " $conj ";
                } else {
                    $constraint_string =  " WHERE ";
                }

                $op = "=";
                if ($ops && !empty($ops["$name"])) {
                    $op = $ops["$name"];
                }

                $n = strpos($name, "#");
                if ($n > 1) {
                    $name = substr($name, 0, $n);
                }

                if ($value == "null" || $value == "''") {
                    // ok
                } else {
                    $value = "'" . escape_string($value) . "'";
                }

                $constraint_string .= "$name $op $value";
            }
            $sql .= $constraint_string;
        }

        if ($order) {
            $sql .= " ORDER BY $order";
        }
        return static::getRecordsFromQuery($sql);
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
        if(isset($vars[$key])) {
            $return=(array) $vars[$key];
        }

        return $return;
    }

    /**
     * Stores the results the the given query in an array of objects of
     * this given type.
     */
    public static function getRecordsFromQuery($sql, $min = 0, $num = 0) {
        $class=get_called_class();
        $result = query($sql, "Unable to get records");

        if ($min) {
            data_seek($result, $min);
        }

        if ($num) {
            $limit = true;
        } else {
            $limit = false;
        }

        $objs = array();
        while ((!$limit || $num-- > 0) && $row = fetch_assoc($result)) {
            $obj = new $class;
            $obj->setFields($row);
            $objs[] = $obj;
        }

        free_result($result);
        return $objs;
    }


    /**
     * Creates a constraint clause based on the given keys
     * @param array Fields to use for contstraints
     */
    private function createConstraints() {
        $constraints=null;
        foreach (static::$primary_keys as $key) {
            $value = $this->fields[$key];
            if (!$value) { continue; }
            if (!empty($constraints)) { $constraints .= " and "; }
            $constraints .= "$key = '" . escape_string($value) . "'";
        }
        return $constraints;
    }

    /**
     * Get XML from a database table
     * This is a wrapper around several objects which will call a method from 
     * those objects
     * @param string Name of the class to be used
     * @param string Search string
     * @param user Only return records that can be seen by this user
     * @todo This should be replaced by a proper OO construction
     */
    public static function getXML($class, $search,$user=null) {
        $seach=strtolower($search);
        if($class=="location" || $class=="home" || $class=="work") {
            $class="place";
        } else if ($class=="photographer") {
            $class="person";
            $subclass="photographer";
        } else if ($class=="father" || $class=="mother" || $class=="spouse") {
            $class="person";
        }


        $search=strtolower($search);
        if($class=="person") {
            $tree=false;
        } else {
            $tree=true;
        }

        if($class=="timezone") {
            $tz=new TimeZone("UTC");
            return $tz->get_xml($search);
        } else if($class=="import_progress") {
            $import=new WebImport($search);
            return $import->get_xml();
        } else if($class=="import_thumbs") {
            return WebImport::getThumbsXML();
        } else if (class_exists($class)) {
            $obj=new $class;
            $rootname=$obj->xml_rootname();
            $nodename=$obj->xml_nodename();
            $idname=$obj::$primary_keys[0];

            $xml = new DOMDocument('1.0','UTF-8');
            $rootnode=$xml->createElement($obj->xml_rootname());
            $newchild=$xml->createElement($obj->xml_nodename());
            $key=$xml->createElement("key");
            $title=$xml->createElement("title");
            $key->appendChild($xml->createTextNode("null"));
            $title->appendChild($xml->createTextNode("&nbsp;"));
            $newchild->appendChild($key);
            $newchild->appendChild($title);
            $rootnode->appendChild($newchild);

            if ($tree) {
                $obj = $class::getRoot();
                $obj->lookup();
                $tree=$obj->get_xml_tree($xml, $search, $user);
                $rootnode->appendChild($tree);
            } else {
                if($class=="person") {
                    if($search=="") {
                        $search=null;
                    }
                    if($user->is_admin()) {
                       $records=get_all_people($user,$search, true);
                    } else {
                        if($subclass=="photographer") {
                            $records=get_photographers($user,$search,true);
                        } else {
                            $records=get_photographed_people($user,$search,true);
                        }
                    }
                } else {
                    $records=get_records($class, $order, $constraints, $conj, $ops);
                } 
               
                foreach($records as $record) {
                    $newchild=$xml->createElement($nodename);
                    $key=$xml->createElement("key");
                    $title=$xml->createElement("title");
                    $key->appendChild($xml->createTextNode($record->get($idname)));
                    $title->appendChild($xml->createTextNode($record->getName()));
                    $newchild->appendChild($key);
                    $newchild->appendChild($title);
                    $rootnode->appendChild($newchild);
                 }
            }
        } else {
            die("illegal class $class");
        }
        $xml->appendChild($rootnode);
        return $xml->saveXML();
}
}
?>
