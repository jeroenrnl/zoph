<?php
/**
 * Via this class Zoph can read configurations from the database
 * the configurations themselves are stored in conf\item objects
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

namespace conf;

use PDO;

use db\select;
use db\param;
use db\delete;
use db\db;
use db\clause;
use log;

/**
 * conf is the main object for access to Zoph's configuration
 * in the database
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class conf {

    /**
     * @var array Groups are one or more configuration objects that
     *            belong together;
     */
    private static $groups=array();

    /** @var bool whether or not the configuration has been loaded from the db */
    private static $loaded=false;

    /** @var array During loading from database this will be filled with warnings (if any)
                   These can later be displayed through conf::getWarnings() */
    private static $warnings=array();

    /**
     * Get the Id of the conf item
     */
    public function  getId() {
        return $this->get("conf_id");
    }

    /**
     * Read configuration from database
     */
    public static function loadFromDB() {
        confDefault::getConfig();
        $qry=new select(array("co" => "conf"));
        $qry->addFields(array("conf_id", "value"));

        try {
            $result=db::query($qry);
        } catch (\PDOException $e) {
            log::msg("Cannot load configuration from database", log::FATAL, log::CONFIG | log::DB);
        }

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $key=$row[0];
            $value=$row[1];
            try {
                $item=static::getItemByName($key);
                try {
                    $item->setValue($value);
                    if ($item->isDeprecated() && $value != $item->getDefault()) {
                        static::$warnings[]="Deprecated configuration item <b>" . $key . "</b> is used!";
                    }
                } catch (\ConfigurationException $e) {
                    /* An illegal value is automatically set to the default */
                    log::msg($e->getMessage(), log::ERROR, log::CONF);
                }
            } catch (\ConfigurationException $e) {
                /* An unknown item will automatically be deleted from the
                   database, so we can remove items without leaving a mess */
                log::msg($e->getMessage(), log::NOTIFY, log::CONF);
                $qry=new delete(array("co" => "conf"));
                $qry->where(new clause("conf_id=:confid"));
                $qry->addParam(new param(":confid", $key, PDO::PARAM_STR));
                $qry->execute();
            }

        }
        static::$loaded=true;

    }

    /**
     * Read configuration from submitted form
     * @param array of $_GET or $_POST variables
     */
    public static function loadFromRequestVars(array $vars) {
        confDefault::getConfig();
        foreach ($vars as $key=>$value) {
            if (substr($key,0,1) == "_") {
                if (substr($key,0,7) == "_reset_") {
                    $key=substr(str_replace("_", ".", $key),7);
                    $item=static::getItemByName($key);
                    $item->delete();
                }
                continue;
            }
            $key=str_replace("_", ".", $key);
            try {
                if (!isset($vars["_reset_" . $key])) {
                    $item=static::getItemByName($key);
                    $item->setValue($value);
                    $item->update();
                }
            } catch(\ConfigurationException $e) {
                log::msg("Configuration cannot be updated: " .
                    $e->getMessage(), log::ERROR, log::CONFIG);
            }
        }
        static::$loaded=true;
    }

    /**
     * Get a configuration item by name
     * @param string Name of item to return
     * @return conf\item Configuration item
     * @throws \ConfigurationException
     */
    public static function getItemByName($name) {
        $nameArr=explode(".", $name);
        $group=array_shift($nameArr);
        if (isset(static::$groups[$group])) {
            $items=static::$groups[$group]->getItems();
            if (isset($items[$name])) {
                return $items[$name];
            }
        }
        throw new \ConfigurationException("Unknown configuration item " . $name);
    }

    /**
     * Get the value of a configuration item
     * @param string Name of item to return
     * @return string Value of parameter
     */
    public static function get($key) {
        if (!static::$loaded) {
            static::loadFromDB();
        }
        $item=static::getItemByName($key);
        return $item->getValue();

    }

    /**
     * Set the value of a configuration item
     * Does not store this value in the database as this is mainly
     * used for runtime-overriding a stored value. This function returns
     * the object so the calling function can do a $item->update() if
     * it should be stored in the db.
     * @param string Name of item to change
     * @param string Value to set
     * @return conf\item the item that has been updated
     */
    public static function set($key, $value) {
        $item=static::getItemByName($key);
        $item->setValue($value);
        return $item;
    }

    /**
     * Get all configuration items (in groups)
     * @return array Array of group objects
     */
    public static function getAll() {
        if (!static::$loaded) {
            static::loadFromDB();
        }
        return static::$groups;
    }

    /**
     * Create a new conf\group and add it to the list
     * @param collection collection to add as group
     * @param string name
     * @param string label
     * @param string description
     */
    public static function addGroup(collection $collection, $name, $label, $desc = "") {
        $group = new group($collection);

        $group->setName($name);
        $group->setLabel($label);
        $group->setDesc($desc);


        static::$groups[$name]=$group;
        return $group;
    }

    /**
     * Return warnings generated while loading configuration
     */
    public static function getWarnings() {
        return static::$warnings;
    }
}
