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
    David Baldwin kindly provided most of this code to help transition to
    PHP 4.2.x.

    14 August 2002
 */

function getvar($var) {
    $val = "";
    if (in_array($var,array_keys($_GET))) {
        $val = $_GET[$var];
    } else if (in_array($var,array_keys($_POST))) {
        $val = $_POST[$var];
    }

    return i($val);
}

function i($var) {
    if($var === "<" || $var === "<=" || $var === ">=" || $var === ">") {
    	// Strip tags breaks some searches
	return $var;
    }
    if(is_array($var)) {
        $return=array();
        foreach($var as $key => $value) {
            $return[i($key)]=i($value);
        }
    } else {
        $return=strip_tags(html_entity_decode($var));
    }
    return $return;
}

function e($var) {
    if(is_array($var)) {
        $return=array();
        foreach($var as $key => $value) {
            $return[e($key)]=e($value);
        }
    } else {
        $return=htmlspecialchars($var);
        # Extra escape for a few chars that may cause troubles but are
        # not escaped by htmlspecialchars.
        $return=str_replace(array("<", ">", "\"", "(", ")", "'", "[",  "]", "{", "}", "~", "`"), array("&lt;", "&gt;", "&quot;", "&#40;", "&#41;", "&#39;","&#91;", "&#93;", "&#123;", "&#125;", "&#126;", "&#96;"), $return);
    }
    return $return;
}

if ($_GET) { $request_vars = &$_GET; }
else       { $request_vars = &$_POST; }
$request_vars=i($request_vars);
    
?>
