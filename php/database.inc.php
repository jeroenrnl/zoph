<?php
/*
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
 */

 /*
  * All database calls are in this include. If you would like to use another
  * database then mysql, you should only have to change this file.
  * If you do have a working installation with another database, please
  * share your work, so others can use it too!
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
        if (!$error) {
            $result=mysql_query($sql);
        } else {
            $result=mysql_query($sql) or die_with_db_error($error, $sql);
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
        $db_fields = mysql_list_fields(DB_NAME, $table_name);
        $columns = mysql_num_fields($db_fields);
        for ($i = 0; $i < $columns; $i++) {
            $field_lengths[mysql_field_name($db_fields, $i)] =
                mysql_field_len($db_fields, $i);
        }

        return $field_lengths;
    }

    function num_rows($result) {
        return mysql_num_rows($result);
    }

    function fetch_row($result) {
        return mysql_fetch_row($result);
    }

    function fetch_array($result) {
        return mysql_fetch_array($result);
    }

    function fetch_assoc($result) {
        return mysql_fetch_assoc($result);
    }


    function free_result($result) {
        return mysql_free_result($result);
    }

    function insert_id() {
        return mysql_insert_id();
    }

    function result($result, $row, $field = null) {
        return mysql_result($result, $row);
    }

    function data_seek($result, $row) {
        return mysql_data_seek($result, $row);
    }

    function get_db_server_info() {
        return mysql_get_server_info();
    }

    function db_server() {
        return "mysql";
    }

    function db_min_version($version) {
        $dbversion=get_db_server_info();
        list($dbmaj, $dbmin) = split("\.", $dbversion, 2);
        list($maj, $min) = split("\.", $version, 2);

        if (($dbmaj == $maj && $dbmin >= $min) || ($dbmaj >= ($maj + 1)) ) {
            log::msg("Yep, we're running version " . $version . " or later", log::DEBUG, log::DB);
            return true;
        } else {
            return false;
        }
    }

?>
