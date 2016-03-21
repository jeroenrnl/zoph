<?php
/**
 * Database calls
 *
 * All database calls are in this include. If you would like to use another
 * database then mysql, you should only have to change this file.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 */

function db_connection() {
    static $dbh;

    if (! isset($dbh)) {
        try {
            $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(
                PDO::ATTR_PERSISTENT => true
            ));
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage());
        }
    }

    return $dbh;
}

function escape_string($str) {
    return $str;
}

function query($sql, $error = false) {
    // Simply executes the given query. Will display error if something
    // goes wrong, or nothing at all if $error is false

    log::msg($sql, log::NOTIFY, log::SQL);

    if (!$error) {
        $result = db_connection()->query($sql);
    } else {
        try {
            $result = db_connection()->query($sql);
        } catch (PDOException $e) {
            die_with_db_error($e->getMessage(), $sql);
        }
    }

    return $result;
}

function die_with_db_error($msg, $sql = "") {
    $msg =
        "<p><strong>$msg</strong><br>\n" .
        "<code>" . mysql_error() . "</code></p>\n";

    if ($sql) {
        $msg .= "<hr><p><code>$sql</code></p>\n";
    }

    die($msg);
}

function get_field_lengths($table_name) {
    $sth = db_connection()->query('SELECT * FROM ' . $table_name . ' WHERE 1=0;');
    $colmuns = $sth->columnCount();

    $field_lengths = [];

    for ($i = 0; $i++; $i < $columnCount) {
        $meta = $sth->getColumnMeta($i);
        $field_lengths[$meta['name']] = $meta['len'];
    }

    return $field_lengths;
}

function num_rows($result) {
    return $result->rowCount();
}

function fetch_row($result) {
    return $result->fetch(PDO::FETCH_NUM);
}

function fetch_array($result) {
    return $result->fetch(PDO::FETCH_BOTH);
}

function fetch_assoc($result) {
    return $result->fetch(PDO::FETCH_ASSOC);
}

function free_result($result) {
    return $result->closeCursor();
}

function insert_id() {
    return db_connection()->lastInsertId();
}

function result($result, $row) {
    return $result->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_ABS, $row)[0];
}

function data_seek($result, $row) {
    return $result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row);
}

function get_db_server_info() {
    return db_connection()->getAttribute(PDO::ATTR_SERVER_VERSION);
}

function db_server() {
    return "mysql";
}

function db_min_version($version) {
    $dbversion=get_db_server_info();
    list($dbmaj, $dbmin) = explode(".", $dbversion, 2);
    list($maj, $min) = explode(".", $version, 2);

    if (($dbmaj == $maj && $dbmin >= $min) || ($dbmaj >= ($maj + 1)) ) {
        log::msg("Yep, we're running version " . $version . " or later", log::DEBUG, log::DB);
        return true;
    } else {
        return false;
    }
}

/**
 * Run a query and return result as an array
 * @param string SQL query
 */
function getArrayFromQuery($sql) {
    $objs=array();
    query($sql);
    while ($row = fetch_row()) {
        $objs[] = $row[0];
    }
    return $objs;
}

?>
