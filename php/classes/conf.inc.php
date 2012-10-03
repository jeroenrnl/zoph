<?php
/**
 * Via this class Zoph can read configurations from the database
 * the configurations themselves are stored in confItem objects
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
 * @author Jeroen Roos
 */

require_once("database.inc.php");

/**
 * conf is the main object for access to Zoph's configuration
 * in the database
 */
class conf {

    /**
     * @var array Groups are one or more configuration objects that
     *            belong together;
     */
    private static $groups=array();
    
    /**
     * @var bool whether or not the configuration has been loaded from the db
     */
    private static $loaded=false;

    /**
     * Initialize object
     */
    public static function init() {
        self::getDefault();
        self::loadFromDB();
    }

    /**
     * Read configuration from database
     */
    public static function loadFromDB() {
        $sql="SELECT conf_id, value FROM " . DB_PREFIX . "conf";

        $result=query($sql, "Cannot load configuration from database");

        while($row= fetch_row($result)) {
            $key=$row[0];
            $value=$row[1];
            $item=conf::getItemByName($key);
            $item->setValue($value);
        }
        self::$loaded=true;
        
    }

    /**
     * Read configuration from submitted form
     * @param array $_GET or $_POST variables
     * @todo: bug - when submit contains both GET and POST, only GET is loaded in $vars
     *        POST is needed, so in case there are GET vars, this will not work!
     */
    public static function loadFromRequestVars(array $vars) {
        foreach($vars as $key=>$value) {
            if(substr($key,0,1) == "_") { continue; }
            $key=str_replace("_", ".", $key);
            try {
                $item=conf::getItemByName($key);
                $item->setValue($value);
                $item->update();
            } catch(ConfigurationException $e) { 
                log::msg("Configuration cannot be updated: " . $e->getMessage(), log::ERROR, log::CONFIG);
            }
        }
    }


    /**
     * Get a configuration item by name
     * @param string Name of item to return
     * @return confItem Configuration item
     * @throws ConfigurationException
     */
    public static function getItemByName($name) {
        $name_arr=explode(".", $name);
        $group=array_shift($name_arr);
        if(isset(self::$groups[$group]) && isset(self::$groups[$group][$name])) {
            return self::$groups[$group][$name];
        } else {
            throw new ConfigurationException("Unknown configuration item " . $id);
        }
    }

    /**
     * Get the value of a configuration item
     * @param string Name of item to return
     * @return string Value of parameter
     */
    public static function get($key) {
        $item=conf::getItemByName($key);
        return $item->getValue();
            
    }

    /**
     * Get all configuration items (in groups)
     * @return array Array of group objects
     */
    public static function getAll() {
        return self::$groups;
    }

    /**
     * Create a new confGroup and add it to the list
     * @param string name
     * @param string description
     */
    public static function addGroup($name, $desc = "") {
        $group = new confGroup();

        $group->setName($name);
        $group->setDesc($desc);


        self::$groups[$name]=$group;
        return $group;
    }

    /**
     * Returns the default configuration
     * This is used to define all configurable items in Zoph
     */
    private static function getDefault() {
        $interface = self::addGroup("interface", "Zoph interface settings");

        $int_title = new confItemString();
        $int_title->setName("interface.title");
        $int_title->setLabel("title");
        $int_title->setDesc("The title for the application. This is what appears on the home page and in the browser's title bar.");
        $int_title->setDefault("Zoph");
        $int_title->setRegex("^[\x20-\x7E]+$");
        $int_title->setRegex("^.*$");
        $interface[]=$int_title;

        $int_css = new confItemString(); 
        $int_css->setName("interface.css");
        $int_css->setLabel("style sheet");
        $int_css->setDesc("The CSS file Zoph uses");
        $int_css->setDefault("css.php");
        $int_css->setRegex("^[A-Za-z0-9_\.]+$");
        $interface[]=$int_css;


        $path = self::addGroup("path", "File and directory locations");
        

        $path_images = new confItemString();
        $path_images->setName("path.images");
        $path_images->setLabel("Images directory");
        $path_images->setDesc("Location of the images on the filesystem");
        $path_images->setDefault("/data/images");
        $path_images->setRegex("^\/[A-Za-z0-9_\.\/]+$");
        $path_images->setTitle("Alphanumeric characters (A-Z, a-z and 0-9), forward slash (/), dot (.), and underscore (_).");
        $path[]=$path_images;


        $maps = self::addGroup("maps", "Mapping support");

        $maps_provider = new confItemSelect();
        $maps_provider->setName("maps.provider");
        $maps_provider->setDesc("Enable or disable mapping support and choose the mapping provider");
        $maps_provider->setLabel("Mapping provider");
        $maps_provider->addOption("", "Disabled");
        $maps_provider->addOption("google", "Google Maps");
        $maps_provider->addOption("googlev3", "Google Maps v3");
        $maps_provider->addOption("yahoo", "Yahoo maps");
        $maps_provider->addOption("cloudmade", "Cloudmade (OpenStreetMap)");
        $maps_provider->setDefault("");

        $maps[]=$maps_provider;

        $date = self::addGroup("date", "Date and time");

        $date_tz = new confItemSelect();
        $date_tz->setName("date.tz");
        $date_tz->setLabel("Timezone");
        $date_tz->setDesc("This setting determines the timezone to which your camera is set. Leave empty if you do not want to use this feature and always set your camera to the local timezone");

        $date_tz->addOptions(TimeZone::getTzArray());
        $date_tz->setDefault("");

        $date[]=$date_tz;
    }
}

