<?php
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
    return $val;
}

if (minimum_version('4.1.0')) {
    $PHP_SELF = &$_SERVER["PHP_SELF"];
    $QUERY_STRING = &$_SERVER["QUERY_STRING"];
    $REQUEST_URI = &$_SERVER["REQUEST_URI"];
    $HTTP_POST_FILES = &$_FILES;
    $HTTP_ACCEPT_LANGUAGE = &$_SERVER["HTTP_ACCEPT_LANGUAGE"];

    if ($_GET) { $request_vars = &$_GET; }
    else       { $request_vars = &$_POST; }
}
else {
    if ($HTTP_GET_VARS) { $request_vars = &$HTTP_GET_VARS; }
    else                { $request_vars = &$HTTP_POST_VARS; }
}

?>
