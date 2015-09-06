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


mysql_pconnect(DB_HOST, DB_USER, DB_PASS)
    or die("Unable to connect to MySQL");
mysql_select_db(DB_NAME)
    or die("Unable to select database");


function escape_string($str) {
    return mysql_real_escape_string($str);
}

function query($sql, $error = false) {
    // Simply executes the given query. Will display error if something
    // goes wrong, or nothing at all if $error is false

    log::msg($sql, log::NOTIFY, log::SQL);

    // New DB
    if ($sql instanceof query) {
        return db::query($sql);
    } else {

        if (!$error) {
            $result=mysql_query($sql);
        } else {
            $result=mysql_query($sql) or die_with_db_error($error, $sql);
        }
        return $result;
    }
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

function num_rows($result) {
    return mysql_num_rows($result);
}

function fetch_array($result) {
    return mysql_fetch_array($result);
}

function fetch_assoc($result) {
    return mysql_fetch_assoc($result);
}

function result($result, $row) {
    // $row is never used
    if ($result instanceof PDOStatement) {
        return $result->fetch(PDO::FETCH_BOTH)[0];
    }
    return mysql_result($result, $row);
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
