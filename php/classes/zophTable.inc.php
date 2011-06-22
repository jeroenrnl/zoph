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
 * @author Jason Geiger, Jeroen Roos
 * @package Zoph
 */

/**
 * A generic table class.  Is is meant to be subclassed by particular
 * table classes.  A table is represented by a name, an array of
 * primary keys, and an array mapping field names to values.
 */
abstract class zophTable {
    /** @var string The name of the database table */
    public $table_name;
    /** @var array Lisy of primary keys */
    public $primary_keys;
    /** @var array Contains the values of attributes that will be stored in the db */
    public $fields;
    /** @var array Fields that may not be empty */
    public $not_null; 

    /**
     * This construnctor should be called from the constructor
     * of a subclass.
     * @param string Name of the db table
     * @param array List of primary keys
     * @param array List of fields that may not be NULL
     */
    public function __construct($table_name, array $primary_keys, array $not_null) {
        $this->table_name = DB_PREFIX . $table_name;
        $this->primary_keys = $primary_keys;
        $this->not_null = $not_null;
        $this->fields = array();
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

            log::msg("<b>" . $key . "</b> = " . $val, log::DEBUG, log::VARS);

            // ignore empty keys or values unless the field must be set.

            if ($null) {
                if ((!in_array($key, $this->not_null)) && (empty($key) )) { continue; }
            } else {
                if ((!in_array($key, $this->not_null)) && (empty($key) || $val == "")) { continue; }
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
     * @param array List of primary keys to be used instead class-defined list
     * @return bool Whether or not field is listed
     */
    public function isKey($name, $keys = null) {
        if (!$keys) { $keys = $this->primary_keys; }
        return in_array($name, $keys);
    }

    /**
     * Looks up a record.
     * @param string SQL query to use instead of the generated query
     * @return mixed 1 or 0
     * @todo Should return something more sensible
     * @todo Check if the 'supply your own SQL' is used anywhere
     */
    public function lookup($sql = null) {

        if (!$this->table_name || !$this->primary_keys || !$this->fields) {
            log::msg("Missing data", log::ERROR, log::GENERAL);
            return;
        }

        if (!$sql) {
            $constraint = $this->createConstraints($this->primary_keys);

            if (!$constraint) {
                log::msg("No constraint found", log::NOTIFY, log::GENERAL);
                return;
            }

            $sql = "select * from $this->table_name where $constraint";
        }

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
     * @return int Key ID
     * @todo Check if the keep_key function is ever used
     */
    function insert($keep_key = null) {

        if (!$this->table_name || !$this->fields) {
            log::msg("Missing data", log::ERROR, log::GENERAL);
            return;
        }
        $names=null;
        $values=null;
        while (list($name, $value) = each($this->fields)) {
            if ($this->primary_keys && !$keep_key && $this->isKey($name)) {
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
            } else if ($value =="" && in_array($name, $this->not_null)) {
	    	    die("<p class='error'><b>$name</b> may not be empty</p>");
	        } else if ($value !== "") {
                $values .= "'" . escape_string($value) . "'";
            } else {
                $values .= "null";
            }

        }

        $sql = "insert into $this->table_name ($names) values ($values)";

        query($sql, "Insert failed:");

        $id = insert_id();

        if ($this->primary_keys && count($this->primary_keys) == 1) {
            $this->fields[$this->primary_keys[0]] = $id;
        }

        return $id;

    }

    /**
     * Deletes a record.  If extra tables are specified, entries from
     * those tables this match the keys are removed as well.
     * @param array Fields to use as primary keys
     * @param array Tables to delete referencing objects from
     */
    public function delete(array $keys = null, array $extra_tables = null) {
        if (!$keys) { $keys = $this->primary_keys; }

        if (!$this->table_name || !$keys || !$this->fields) {
            log::msg("Missing data", log::ERROR, log::GENERAL);
            return;
        }

        $constraints = $this->createConstraints($keys);

        if (!$constraints) {
            log::msg("No constraint found", log::NOTIFY, log::GENERAL);
            return;
        }

        $sql = "delete from $this->table_name where $constraints";

        query($sql, "Delete failed:");

        if ($extra_tables) {
            foreach ($extra_tables as $table) {
                $table = DB_PREFIX . $table;
                $sql = "delete from $table where $constraints";
                query($sql, "Delete from $table failed:");
            }
        }
    }

    /**
     * Updates a record.
     * @param array Fields to use as primary keys
     */
    public function update(array $keys = null) {
        if (!$keys) { $keys = $this->primary_keys; }

        if (!$this->table_name || !$keys || !$this->fields) {
            log::msg("Missing data", log::ERROR, log::GENERAL);
            return;
        }

        $constraints = $this->createConstraints($keys);

        if (!$constraints) {
            log::msg("No constraint found", log::NOTIFY, log::GENERAL);
            return;
        }
        reset($this->fields);
        $values=null;
        $names=null;
        while (list($name, $value) = each($this->fields)) {
            if ($this->isKey($name, $keys)) { continue; }

            if (!empty($values)) { $values .= ", "; }
            
            if (substr($name,0,7)=="parent_") {
                $children=array();
                $this->get_branch_id_array($children);
                if(in_array($value, $children)) {
                    die("You cannot set the parent to a child of the current selection!");
                } 
            }

            if ($name == "password") {
                $values .= "$name = password('" . escape_string($value) . "')";
            } else if ($value == "now()" ) {
                $values .= "$name = " . $value . "";
            } else if ($value == "" && in_array($name, $this->not_null)) {
	    	    die("<p class='error'><b>$name</b> may not be empty</p>");
	        } else if ($value !== "" && !is_null($value)) {
                $values .= "$name = '" . escape_string($value) . "'";
            } else {
                $values .= "$name = null";
            }
        }

        if (!$values) { return; }

        $sql = "update $this->table_name set $values where $constraints";

        query($sql, "Update failed:");

    }

    /**
     * Creates a constraint clause based on the given keys
     * @param array Fields to use for contstraints
     */
    private function createConstraints(array $keys) {
        $constraints=null;
        foreach ($keys as $key) {
            $value = $this->fields[$key];
            if (!$value) { continue; }
            if (!empty($constraints)) { $constraints .= " and "; }
            $constraints .= "$key = '" . escape_string($value) . "'";
        }
        return $constraints;
    }

    /**
     * Creates an alphabetized array of field names and values.
     * @return array Array for displaying object
     */
    function getDisplayArray() {
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
     * @return array of field names and HTML text input fields
     */
    public function getEditArray() {
        if (!$this->fields) { return; }

        $field_lengths = get_field_lengths($this->table_name);

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
     * Get Javascript for map
     * This is here because it is used by both location and photo
     * but this is really not a good place, since other objects do not use it
     * @todo Move this to another object.
     * @todo Remove user object
     * @todo Contains javascript
     * @param user User object, seems to be unused
     * @param string Icon to be used
     * @param bool true when JS is used for editable map
     */
    protected function getMappingJs(user $user, $icon,$edit=false) {
        $marker=true;
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        $zoom=$this->get("mapzoom");
        if(!$lat && !$lon) { 
            $marker=false;

            if($this instanceof photo) {
                $lat=$this->location->get("lat");
                $lon=$this->location->get("lon");
                $zoom=$this->location->get("mapzoom");
            } else if ($this instanceof place) {
                foreach($this->get_ancestors() as $parent) {
                    $lat=$parent->get("lat");
                    $lon=$parent->get("lon");
                    $zoom=$parent->get("mapzoom");
                    if($lat && $lon) {
                        break;
                    }
                }
            }
        }
        if(!$lat) { $lat=0; }
        if(!$lon) { $lon=0; }
        if(!$zoom) { $zoom=2; }
        $js="  var center=new mxn.LatLonPoint(" .
                $lat . "," .
                $lon . ");\n" .
            "  var zoomlevel=" . $zoom . ";\n" .
            "  mapstraction.setCenterAndZoom(center,zoomlevel);\n";
         if ($marker ) {
            $js.="  zMaps.createMarker(" . $lat . "," . $lon . ",'" . $icon . "',null, null);\n";
         }
         if ($edit) {
            $js.="  zMaps.setUpdateHandlers();\n";
         }
        return $js;
    }

    /**
     * Get Javascript for marker
     * This is here because it is used by both location and photo
     * but this is really not a good place, since other objects do not use it
     * @todo Move this to another object
     * @param user logged in user
     * @param string icon to use
     * @return string Javascript to display a marker on the map
     */
    protected function getMarker(user $user, $icon) {
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        $title=$this->get("title");
        if($lat && $lon) {
            $quicklook=$this->get_quicklook($user);
            return "  zMaps.createMarker(" . $lat . "," . $lon . ", '" . $icon .
                    "','" .  e($title) . "','" . 
                    $quicklook . "');\n";
        } else {
            return null;
        }
    }

    /**
     * Gets the total count of records in the table for the given class.
     * @param string Classname
     * @return int count
     * @todo Once the mimimum PHP version is 5.3, the $class param should
     *       be removed and replaced by get_called_class()
     */
    public static function getCount($class) {
        if (class_exists($class)) {
            $obj = new $class;
            $table = $obj->table_name;
        } else {
            $table = DB_PREFIX . $class;
        }

        $sql = "select count(*) from $table";

        return $obj::getCountFromQuery($sql);
    }

    /**
     * Generates an array for Top N albums/cat/.. 
     * Executes a query and returns an array in which each record's
     * link is mapped to its count (dirived by a group by clause).
     * @param string classname
     * @param string query SQL query to use
     * @return array Table of Top N most popular $class
     * @todo Once minimum PHP version is 5.3, the $class can be replaced by
     *       get_called_class()
     */
    public static function getTopN($class, $query) {
        $records = $class::getRecordsFromQuery($class, $query);
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
    public static function getRecords($class, $order = null, $constraints = null,
        $conj = "and", $ops = null) {
        

        $obj = new $class;
        $sql = "select * from $obj->table_name";
        if ($constraints) {
            while (list($name, $value) = each($constraints)) {
                if (!empty($constraint_string)) {
                    $constraint_string .= " $conj ";
                } else {
                    $constraint_string =  " where ";
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
                }
                else {
                    $value = "'" . escape_string($value) . "'";
                }

                $constraint_string .= "$name $op $value";
            }
            $sql .= $constraint_string;
        }

        if ($order) {
            $sql .= " order by $order";
        }
        return self::getRecordsFromQuery($class, $sql);
    }

    /*
     * Stores the results the the given query in an array of objects of
     * this given type.
     * @todo the $class can be removed when PHP5.3 is min version
     */
    public static function getRecordsFromQuery($class, $sql, $min = 0, $num = 0) {

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
        if ($class != null) {
            while ((!$limit || $num-- > 0) && $row = fetch_assoc($result)) {
                $obj = new $class;
                $obj->setFields($row);
                $objs[] = $obj;
            }
        } else {
            // use to grab ids, for example
            while ((!$limit || $num-- > 0) && $row = fetch_row($result)) {
                $objs[] = $row[0];
            }
        }

        free_result($result);
        return $objs;
    }
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
function get_xml($class, $search,$user=null) {
    $search=strtolower($search);
    if($class=="location" || $class=="home" || $class=="work") {
        $class="place";
    } else if ($class=="photographer") {
        $class="person";
        $subclass="photographer";
    } else if ($class=="father" || $class=="mother" || $class=="spouse") {
        $class="person";
    }


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
        $idname=$obj->primary_keys[0];

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

?>
