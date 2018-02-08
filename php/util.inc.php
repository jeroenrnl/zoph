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

use conf\conf;
use template\template;

function create_field_html($fields) {

    $html = "";
    foreach ($fields as $key => $val) {
        if ($val) {
            $html .=
            "<dt>" . e($key) . "</dt>\n" .
            "<dd>" . $val ." </dd>\n";
        }
    }
    return $html;
}

function create_edit_fields($fields) {
    $html = "";
    foreach ($fields as $key => $val) {
        $html.=
            "<label for=\"$key\">$field[0]</label>\n" . $field[1] ."<br>";
    }
    return $html;
}

function create_text_input($name, $value, $size = 20, $max = 32, $type="text") {
    if ($type=="time") {
        $step="step=\"1\"";
    } else {
        $step="";
    }
    $id=preg_replace("/^_+/", "", $name);
    return "<input type=\"$type\" $step name=\"$name\" id=\"$id\" value=\"" . e($value) ."\"
        size=\"$size\" maxlength=\"$max\">\n";
}

function create_integer_pulldown($name, $value, $min, $max) {
    for ($i = $min; $i <= $max; $i++) {
        $integer_array["$i"] = $i;
    }
    return template::createPulldown($name, $value, $integer_array);
}

function create_rating_pulldown($val = "", $name = "rating") {
    $rating_array = array(
        "1" => translate("1 - close your eyes",0),
        "2" => translate("2",0),
        "3" => translate("3",0),
        "4" => translate("4",0),
        "5" => translate("5 - so so",0),
        "6" => translate("6",0),
        "7" => translate("7",0),
        "8" => translate("8",0),
        "9" => translate("9",0),
        "10" => translate("10 - museum",0));

    if (empty($val)) {
        $tmp_array = array_reverse($rating_array, true);
        $tmp_array["0"] = translate("not rated", 0);
        $rating_array = array_reverse($tmp_array, true);
    }

    return template::createPulldown($name, $val, $rating_array);
}

function create_conjunction_pulldown($var, $val = "") {
    return template::createPulldown($var, $val,
        array("" => "", "and" => translate("and",0), "or" => translate("or",0)));
}

function create_operator_pulldown($var, $op = "=") {
    return template::createPulldown($var, $op,
        array(
            "=" => "=", "!=" => "!=",
            ">" => ">", ">=" => ">=",
            "<" => "<", "<=" => "<=",
            "like" => translate("like",0), "not like" => translate("not like",0)));
}

function create_binary_operator_pulldown($var, $op = "=") {
    return template::createPulldown($var, $op,
        array("=" => "=", "!=" => "!="));
}

function create_present_operator_pulldown($var, $op = "=") {
    return template::createPulldown($var, $op,
        array("=" => translate("is in photo",0), "!=" => translate("is not in photo",0)));
}

function create_inequality_operator_pulldown($var, $op = ">") {
    return template::createPulldown($var, $op,
        array(">" => translate("less than"), "<" => translate("more than")));
}

function create_photo_text_pulldown($var, $name = null) {
    return template::createPulldown($var, $name, array(
        "" => "",
        "album" => translate("album",0),
        "category" => translate("category",0),
        "person" => translate("person",0),
        "photographer" => translate("photographer",0)));
}

/*
 * Updates a query string, replacing (or inserting) a key.
 * A list of keys to ignore can also be specified.
 */
function update_query_string($vars, $new_key, $new_val, $ignore = null) {

    if (!$ignore) { $ignore = array(); }
    $ignore[] = "PHPSESSID";
    $ignore[] = "_crumb";
    $qstr="";
    if ($vars) {
        foreach ($vars as $key => $val) {
            if (in_array($key, $ignore)) { continue; }
            if ($key == $new_key) { continue; }

            if (!empty($qstr)) { $qstr .= "&amp;"; }
            if (is_array($val)) {
                $qstr .= rawurlencode_array($key, $val);
            } else {
                $qstr .= rawurlencode($key) . "=" . rawurlencode($val);
            }
        }
    }

    if ($new_key && isset($new_val)) {
        if ($qstr) { $qstr .= "&amp;"; }
        if (is_array($new_val)) {
            $qstr .= rawurlencode_array($new_key, $new_val);
        } else {
            $qstr .= rawurlencode($new_key) . "=" . rawurlencode($new_val);
        }
    }

    return $qstr;
}

function create_form($vars, $ignore = array()) {

    $ignore[] = "PHPSESSID";
    $ignore[] = "_crumb";

    $form = "";
    foreach ($vars as $key => $val) {
        if (in_array($key, $ignore)) { continue; }
        $form .= "<input type=\"hidden\" name=\"$key\" value=\"" . e($val) . "\">\n";
    }

    return $form;
}

/**
 * Create a link to the calendar page
 * @param string Date in "yyyy-mm-dd" format
 * @param string Search field, the field to search from from the calendar page
 * @return string link.
 * @todo Contains HTML
 * @todo Should be better separated, possibly included in Time object
 */
function create_date_link($date, $search_field = "date") {
    $dt = new Time($date);

    if ($date) {
        $html="<a href=\"calendar.php?date=$date&amp;search_field=$search_field\">";
        $html.=$dt->format(conf::get("date.format"));
        $html.="</a>";
        return $html;
    }
}

/**
 * Format a timestamp
 * Temporary, is really redundant
 */
function format_timestamp($ts) {
    $dt=new Time($ts);
    return create_date_link($dt->format("Y-m-d"), "timestamp") . ' ' .
        $dt->format(conf::get("date.timeformat"));
}

function get_date_select_array($date, $days) {
    $dt=new Time($date);

    $date_array[""] = "";
    $day=new DateInterval("P1D");
    for ($i = 1; $i <= $days; $i++) {
        $dt->sub($day);
        $date_array[$dt->format("Y-m-d")] = $i;
    }

    return $date_array;
}

/**
 * Get the current Zoph URL
 * Autodetect or use the URL set in configuration.
 * @param string Override protocol (http/https) autodetection
 * @return string URL
 */
function getZophURL($proto=null) {
    if (is_null($proto)) {
        if (isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != "off")) {
            $proto="https";
        } else {
            $proto="http";
        }
    } else {
        if (!preg_match("/^http(s?)$/", $proto)) {
            die("illegal protocol");
        }
    }

    $current_url=$_SERVER["SERVER_NAME"] . "/" . $_SERVER["PHP_SELF"];
    $new_url=substr($current_url, 0, strrpos($current_url, "/"));
    $url=$proto . "://" . preg_replace("/\/\//","/", $new_url);

    if (conf::get("url.http") && $proto = "http") {
        $url=conf::get("url.http");
    }

    if (conf::get("url.https") && $proto = "https") {
        $url=conf::get("url.http");
    }

    if (substr($url, -1) != "/") {
        $url.="/";
    }
    return $url;
}

/**
 * Encode URL raw
 * based on urlencode_array
 * By linus at flowingcreativity dot net
 * from: http://www.php.net/manual/en/function.urlencode.php
 * @param array the array value
 * @param string variable name to be used in the query string
 * @param string what separating character to use in the query string
 */
function rawurlencode_array($var, $varName, $separator = '&') {
    $toImplode = array();
    foreach ($var as $key => $value) {
        if (is_array($value)) {
            $toImplode[] = rawurlencode_array($value, "{$varName}[{$key}]", $separator);
        } else {
            $toImplode[] = "{$varName}[{$key}]=".rawurlencode($value);
        }
    }
    return implode($separator, $toImplode);
}

function create_actionlinks($actionlinks) {
    if (is_array($actionlinks)) {
        $html="<ul class=\"actionlink\">\n";
        foreach ($actionlinks as $key => $val) {
            $html .= "<li><a href=\"" . $val . "\">" . translate($key, 0) . "</a></li>";
        }
        $html.="</ul>\n";
        return $html;
    }
}

function create_zipfile($photos, $maxsize, $filename, $filenum, $user) {
    if (class_exists("ZipArchive")) {
        $zip=new ZipArchive();
        $tempfile="/tmp/zoph_" . $user->get("user_id") . "_" . $filename ."_" . $filenum . ".zip";
        @unlink($tempfile);
        if ($zip->open($tempfile, ZIPARCHIVE::CREATE)!==TRUE) {
            die("cannot open $tempfile\n");
        }
        $zipsize=0;
        foreach ($photos as $key => $photo) {
            if ($data=@file_get_contents($photo->getFilePath())) {
                $size=strlen($data);
                $zipsize=$zipsize+$size;
                if ($zipsize>=$maxsize) {
                    break;
                }
                $currentfile=$key;
                $zip->addFromString($photo->get("name"),$data);

            } else {
                echo sprintf(translate("Could not read %s."), $photo->getFilePath()) . "<br>\n";
            }
        }
        $zip->close() or die ("Zipfile creation failed");
        return $currentfile;
    } else {
        echo translate("You need to have ZIP support in PHP to download zip files");
        return FALSE;
    }
}

function redirect($url = "zoph.php", $msg = "Access denied") {
    if (!((LOG_SUBJECT & log::REDIRECT) && (LOG_SEVERITY >= log::DEBUG))) {
        header("Location: " . $url);
    }
        echo "<a href='" . $url . "'>" . $msg . "</a>";
    die();
}


?>
