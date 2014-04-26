<?php
/**
 * Database connection class
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

/**
 * The db object is used to connect to the database
 * Example code:
 * $qry=new query("photos", array("photo_id", "name"));
 * var_dump(db::query($qry));

 *
 * @package Zoph
 * @author Jeroen Roos
 */
class db {

    private static $connection=false;

    private static $dbhost;
    private static $dbname;
    private static $dbuser;
    private static $dbpass;
    private static $dbprefix;

    /**
     * Make database connection
     * @param string DSN
     * @param string username 
     * @param string password
     */
    private function __construct($dsn, $dbuser, $dbpass) {
        self::$connection=new PDO($dsn,$dbuser,$dbpass); 
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get handle to database
     * Make connection first, if it has not been made
     */
    public static function getHandle() {
        if(!self::$connection) {
            self::connect();
        }
        return self::$connection;
    }
    
    /**
     * Set login details
     * @param string database hostname
     * @param string database name
     * @param string database user
     * @param string database password
     * @param string database table prefix
     */
    public static function setLoginDetails($dbhost, $dbname, $dbuser, $dbpass, $dbprefix) {
        self::$dbhost=$dbhost;
        self::$dbname=$dbname;
        self::$dbuser=$dbuser;
        self::$dbpass=$dbpass;
        self::$dbprefix=$dbprefix;
    }

    /**
     * Get table prefix
     */
    public static function getPrefix() {
        return self::$dbprefix;
    }

    /**
     * Connect to database
     */
    private static function connect($dsn=null) {
        if(!$dsn) {
            $dsn=self::getDSN();
        }
        new db($dsn, self::$dbuser, self::$dbpass);
    }

    /**
     * Get the Data Source Name for the database connection
     * Currently hardcoded to MySQL, in the future this might change
     */
        
    private static function getDSN() {
        $db="mysql";
        
        $dsn=sprintf("%s:host=%s;dbname=%s", $db, self::$dbhost, self::$dbname);
        return $dsn;
    }

    public static function query(query $query) {
        $db=self::getHandle();
        return $db->query($query);
    }
}

