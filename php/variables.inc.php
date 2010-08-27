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

function minimum_version($vercheck) {
    $minver = (int)str_replace('.', '', $vercheck);
    $curver = (int)str_replace('.', '', phpversion());
    if($curver >= $minver)
        return true;
    return false;
}

function getvar($var) {
    global $HTTP_GET_VARS;
    global $HTTP_POST_VARS;
    $val = "";
    if (minimum_version('4.1.0')) { // when global variables changed
        if (in_array($var,array_keys($_GET))) {
            $val = $_GET[$var];
        } else if (in_array($var,array_keys($_POST))) {
            $val = $_POST[$var];
        }
    } else {
        if (in_array($var,array_keys($HTTP_GET_VARS))) {
            $val = $HTTP_GET_VARS[$var];
        } else if (in_array($var,array_keys($HTTP_POST_VARS))) {
            $val = $HTTP_POST_VARS[$var];
        }
    }

    remove_magic_quotes($val);
    return i($val);
}

/*
 * This function removes slashes that may have been automatically
 * inserted by PHP if one of magic_quotes_* is On.
 *
 * Code by gordon at kanazawa dot ac dot jp as posted on php.net.
 * http://www.php.net/manual/en/function.get-magic-quotes-gpc.php
 */
function remove_magic_quotes(&$x) {
    if (is_array($x)) {
        while (list($key,$value) = each($x)) {
            $oldkey=$key;
            remove_magic_quotes($key);
            if($key!=$oldkey) {
                $x[$key]=$x[$oldkey];
                unset($x[$oldkey]);
            }
            if ($value) remove_magic_quotes($x[$key]);
        }
    }else if (ini_get('magic_quotes_sybase')) {
        $x = preg_replace("/''/", "'", $x);
    } else if (get_magic_quotes_runtime()) {
        $x = preg_replace("/\\\"/", '"', $x);
    } else if (get_magic_quotes_gpc()) {
        $x = stripslashes($x);
    }
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

if (minimum_version('4.1.0')) {
    $HTTP_SERVER_VARS = &$_SERVER;

    $PHP_SELF = &$_SERVER["PHP_SELF"];
    $QUERY_STRING = &$_SERVER["QUERY_STRING"];
    $REQUEST_URI = &$_SERVER["REQUEST_URI"];
    $HTTP_POST_FILES = &$_FILES;
    $HTTP_ACCEPT_LANGUAGE = &$_SERVER["HTTP_ACCEPT_LANGUAGE"];

    if ($_GET) { $request_vars = &$_GET; }
    else       { $request_vars = &$_POST; }
} else {
    if ($HTTP_GET_VARS) { $request_vars = &$$HTTP_GET_VARS; }
    else                { $request_vars = &$HTTP_POST_VARS; }
}
remove_magic_quotes($request_vars);
$request_vars=i($request_vars);
    
?>
