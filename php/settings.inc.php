<?php
/**
 * Class that takes care of configuration
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
 * @author Jeroen Roos
 * @package Zoph
 */

/**
 * This class takes care of loading and processing settings
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class settings {

    public static $php_loc;
   /**
    * Load ini file, as defined in the INI_FILE constant
    * Check if these settings are still made in config.inc.php
    * and figure out which of the settings should be used.
    */
    public static function loadINI($instance="") {
        if(!defined("INI_FILE")) {
            define("INI_FILE", "/etc/zoph.ini");
        }
        if(defined("DB_HOST") || 
           defined("DB_NAME") || 
           defined ("DB_USER") || 
           defined("DB_PASS") || 
           defined("DB_PREFIX")) {

            log::msg("Remove DB_ settings from config.inc.php and define them in " . INI_FILE, log::FATAL, log::GENERAL);
        } else {

            if(file_exists(INI_FILE)) {
                $ini=parse_ini_file(INI_FILE, true);
                if(!empty($instance)) {
                    if(!isset($ini[$instance])) {
                        echo "Instance " . $instance . " not found in " . INI_FILE;
                        exit(EXIT_INSTANCE_NOT_FOUND);
                    }
                } else {
                    // No instance given, autodetect 
                    $instance=self::detectInstance($ini);
                }
                return $ini[$instance];
            } else {
                log::msg(INI_FILE . " not found.", log::FATAL, log::GENERAL);
            }
        }
    }



    /**
     * Detect which Zoph instance is used from php path
     * @param array ini file as parsed by php function parse_ini_file()
     */

    public static function detectInstance(array $ini) {
        $php_loc=dirname($_SERVER['SCRIPT_FILENAME']);
        foreach($ini as $instance=>$i) {
            if(!isset($i["php_location"])) {
                log::msg("php_location setting missing from " . $instance . " in " . INI_FILE, log::FATAL, log::GENERAL);
            } else if($php_loc==$i["php_location"]) {
                return $instance;
            }
        }
        // No corresponding settings found.
        log::msg("No php_location setting in " . INI_FILE . " found that matches " . $php_loc, log::FATAL, log::GENERAL);
    }

   /**
    * Parse values from ini file.
    * @param array section from ini file
    * @todo get rid of constants.
    */
    public static function parseINI($i) {
        if(!isset($i["php_location"])) {
            log::msg("No php_location setting in " . INI_FILE . " found that matches " . $php_loc, log::FATAL, log::GENERAL);
        } else {
            self::$php_loc=$i["php_location"];
        }
        if(!isset($i["db_host"]) || !isset($i["db_name"]) ||
          !isset($i["db_user"]) || !isset($i["db_pass"]) ||
          !isset($i["db_prefix"])) {
            log::msg("db_host, db_name, db_user, db_pass or db_prefix setting missing from " . INI_FILE, log::FATAL, log::GENERAL);
        } else {
            define("DB_HOST", $i["db_host"]);
            define("DB_NAME", $i["db_name"]);
            define("DB_USER", $i["db_user"]);
            define("DB_PASS", $i["db_pass"]);
            define("DB_PREFIX", $i["db_prefix"]);
            return true;
        }
    } 
}
if(!defined("CLI")) {
    if(defined("TEST")) {
        /* unittest code cannot use autodetection of PHP location
           because the code is executed from PHPUnit context */
        $i=settings::loadINI(INSTANCE);
        set_include_path(get_include_path() . PATH_SEPARATOR . $i["php_location"]);
    } else {
        $i=settings::loadINI();
    }
    settings::parseINI($i);
}
