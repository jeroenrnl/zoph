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

namespace db;

use \PDO;
use \log;

/**
 * The db object is used to connect to the database
 * Example code:
 * $qry=new select("photos");
 * var_dump(db::query($qry));

 *
 * @package Zoph
 * @author Jeroen Roos
 */
class db {
    /** @var holds connection */
    private static $connection=false;

    /** @var database host */
    private static $dbhost;
    /** @var database name */
    private static $dbname;
    /** @var database user */
    private static $dbuser;
    /** @var database password */
    private static $dbpass;
    /** @var table prefix */
    private static $dbprefix;

    /**
     * Make database connection
     * @param string DSN
     * @param string username
     * @param string password
     */
    private function __construct($dsn, $dbuser, $dbpass) {
        static::$connection=new PDO($dsn,$dbuser,$dbpass);
        static::$connection->setAttribute(
            PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        static::$connection->setAttribute(
            PDO::ATTR_EMULATE_PREPARES,false);
    }

    /**
     * Get handle to database
     * Make connection first, if it has not been made
     */
    public static function getHandle() {
        if (!static::$connection) {
            static::connect();
        }
        return static::$connection;
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
        static::$dbhost=$dbhost;
        static::$dbname=$dbname;
        static::$dbuser=$dbuser;
        static::$dbpass=$dbpass;
        static::$dbprefix=$dbprefix;
    }

    /**
     * Get table prefix
     */
    public static function getPrefix() {
        return static::$dbprefix;
    }

    /**
     * Connect to database
     * @param string PDO DSN
     */
    private static function connect($dsn=null) {
        if (!$dsn) {
            $dsn=static::getDSN();
        }
        new db($dsn, static::$dbuser, static::$dbpass);
    }

    /**
     * Get the Data Source Name for the database connection
     * Currently hardcoded to MySQL, in the future this might change
     */

    private static function getDSN() {
        $db="mysql";

        return sprintf("%s:host=%s;dbname=%s", $db, static::$dbhost, static::$dbname);
    }

    /**
     * Run a query
     * @param query Query to run
     */
    public static function query(query $query) {
        $db=static::getHandle();

        try {
            log::msg("SQL Query: " . (string) $query, log::DEBUG, log::SQL);
            $stmt=$db->prepare($query);
            foreach($query->getParams() as $param) {
                if ($param instanceof param) {
                    log::msg("Param: <b>" . $param->getName() . "</b>: " . $param->getValue(), log::DEBUG, log::SQL);
                    $stmt->bindValue($param->getName(), $param->getValue(), $param->getType());
                }
            }
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            log::msg("SQL failed", log::FATAL, log::DB);
        }

        return $stmt;
    }

    /**
     * Execute an SQL query
     * This is meant to execute queries that cannot be handled via the query builder
     * it should not be used for SELECT, UPDATE, DELETE or INSERT queries,
     * these can be handled via their respective objects
     */
    public static function SQL($sql) {
        try {
            $db=static::getHandle();
            $stmt=$db->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            log::msg("SQL failed", log::FATAL, log::DB);
        }
    }

}

