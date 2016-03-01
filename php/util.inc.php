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

function create_field_html($fields) {

    $html = "";
    while (list($key, $val) = each($fields)) {
        if ($val) {
            $html .=
            "<dt>" . e($key) . "</dt>\n" .
            "<dd>" . $val ." </dd>\n";
        }
    }
    return $html;
}

function create_field_html_table($fields) {

    $html = "";
    while (list($key, $val) = each($fields)) {
        if ($val) {
            $html .=
            "<tr>\n  <th>" . e($key) . "</th>\n" .
            "  <td>" . e($val) . "</<td>\n</tr>\n";
        }
    }
    return $html;
}

function create_edit_fields($fields) {
    $html = "";
    while (list($key, $field) = each($fields)) {
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

function get_sort_array() {
    return array(
        "name" => translate("Name",0),
        "sortname" => translate("Sort Name",0),
        "oldest" => translate("Oldest photo",0),
        "newest" => translate("Newest photo",0),
        "first" => translate("Changed least recently",0),
        "last" => translate("Changed most recently",0),
        "lowest" => translate("Lowest ranked",0),
        "highest" => translate("Highest ranked",0),
        "average" => translate("Average ranking",0),
        "random" => translate("Random",0)
    );
}
/*
 * Remove any params without values and operator params without corresponding
 * fields (e.g. _album_id-op when there is no _album_id).  This can be called
 * once after a search is performed.  It allows for shorter urls that are
 * more readable and easier to debug.
 */
function clean_request_vars($vars) {
    $clean_vars = array();
    $interim_vars = array();

    /*
      First pass through vars will flatten out any arrays in the list.
      arrays were used in search.php to make the form extensible. -RB
    */
    while (list($key, $val) = each($vars)) {
        // trim empty values
        if (empty($val)) { continue; }

        // won't need this
        //if ($key == "_action" || $key == "_button") { continue; }
        // keep _action now that the pager links point back to search.php
        if ($key == "_button") { continue; }

        if ( is_array($val) ) {
            while (list($subkey, $subval) = each($val)) {
                if (empty($subval)) { continue; }

                //  change var_op[key] to var#key_op
                if (substr($key, -3) == "_op") {
                    $newkey = substr($key, 0, -3) . '#' . $subkey . '_op';

                    //  change var_conj[key] to var#key_conj
                } elseif (substr($key, -5) == "_conj") {
                    $newkey = substr($key, 0, -5) . '#' . $subkey . '_conj';

                    //  change var_children[key] to var#key_children
                } elseif (substr($key, -9) == "_children") {
                    $newkey = substr($key, 0, -9) . '#' . $subkey . '_children';
                    //  change var[key] to var#key
                } else {
                    $newkey = $key . '#' . $subkey;
                }

                $interim_vars[$newkey] = $subval;
            }
        } else {
            $interim_vars[$key] = $val;
        }
    }

    /*
      Second pass through will get rid of ops and conjs without fields
      and fix the keys for compatability with the rest of zoph.  It will also remove
      "field" entries without a corresponding "_field" type and vice versa.
      A hyphen is not valid as part of a variable name in php so underscore was used
      while processing the form in search.php  -RB
    */

    while (list($key, $val) = each($interim_vars)) {
        // process _var variables
        if (substr($key, 0, 1) == "_") {

            //process _op variables
            if (substr($key, -3) == "_op") {
                // replace _op with -op to be compatable with the rest of application
                $key = substr_replace($key, '-', -3, -2);
                // get rid of ops without fields
                $field = substr($key, 1, -3);
                if (empty($interim_vars[$field]) && empty($interim_vars["_$field"])) { continue; }

                //process _conj variables
            } elseif (substr($key, -5) == "_conj") {
                // replace _conj with -conj to be compatable
                // with the rest of application
                $key = substr_replace($key, '-', -5, -4);
                // get rid of ops without fields
                $field = substr($key, 1, -5);
                if (empty($interim_vars[$field]) && empty($interim_vars["_$field"])) { continue; }
                //process _children variables
            } elseif (substr($key, -9) == "_children") {
                // replace _children with -children to be compatable
                // with the rest of application
                $key = substr_replace($key, '-', -9, -8);
                // get rid of ops without fields
                $field = substr($key, 1, -9);
                if (empty($interim_vars[$field]) && empty($interim_vars["_$field"])) { continue; }
            } else {
                $field = substr($key, 1);
            }

            //process "_field" type variables
            if (substr($field, 0, 5) == "field" &&
                (empty($interim_vars[$field]) &&
                empty($interim_vars["_$field"]))) { continue; }
        } else {
            //process "field" type variables
            if (substr($key, 0, 5) == "field" && empty($interim_vars["_$key"])) { continue; }
        }

        $clean_vars[$key] = $val;
    }

    return $clean_vars;
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
        while (list($key, $val) = each($vars)) {
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
    while (list($key, $val) = each($vars)) {
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

function file_extension($str) {
    return substr($str, strrpos($str, '.') + 1);
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

function get_image_type($name) {
    $ext = strtolower(file_extension($name));
    if ($ext == "jpg" || $ext == "jpeg" || $ext == "jpe") {
        return "image/jpeg";
    }
    else if ($ext == "gif") {
        return "image/gif";
    }
    else if ($ext == "tiff" || $ext == "tif") {
        return "image/tiff";
    }
    else if ($ext == "png") {
        return "image/png";
    }

    return "";
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

/**
 * Cleans up a path, by removing all double slashes, "/./",
 * leading and trailing slashes.
 */
function cleanup_path($path) {
    $search = array ( "/(\/+)/", "/(\/\.\/)/", "/(\/$)/", "/(^\/)/" );
    $replace = array ( "/", "/", "", "" );
    return preg_replace($search,$replace, $path);
}

function create_actionlinks($actionlinks) {
    $bar="";
    if (is_array($actionlinks)) {
        $html="<span class=\"actionlink\">\n";
        while (list($key, $val) = each($actionlinks)) {
            $html .= $bar . "<a href=\"" . $val . "\">" . translate($key, 0) . "</a>";
            $bar=" | ";
        }
        $html.="</span>\n";
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

/**
 * transforms a size in bytes into a human readable format using
 * Ki Mi Gi, etc. prefixes
 * Give me a call if your database grows bigger than 1024 Yobbibytes. :-)
 * @param int bytes number of bytes
 * @return string human readable filesize
 */
function getHuman($bytes) {
    if ($bytes==0) {
        // prevents div by 0
        return "0B";
    } else {
        $prefixes=array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
        $length=floor(log($bytes,2)/10);
        return round($bytes/pow(2,10*($length)),1) . $prefixes[floor($length)] . "B";
    }
}

function check_js($user) {
    if (($user->prefs->get("autocomp_albums")) ||
        ($user->prefs->get("autocomp_categories")) ||
        ($user->prefs->get("autocomp_places")) ||
        ($user->prefs->get("autocomp_people")) ||
        ($user->prefs->get("autocomp_photographer")) &&
        conf::get("interface.autocomplete")) {

        return "<noscript><div class='warning'><img class='icon' src='" .
            template::getImage("icons/warning.png") . "'>" .
            translate("You have enabled autocompletion for one or more dropdown " .
                "boxes on this page, however, you do not seem to have Javascript " .
                "support. You should either enable javascript or turn autocompletion " .
                "off, or this page will not work as expected!") . "</div></noscript>";
    }
}

function remove_empty(array $children) {
    $user=user::getCurrent();
    $clean=array();
    // If user is not admin, remove any children that do not have photos
    if (!$user->isAdmin()) {
        foreach ($children as $child) {
            $count=$child->getTotalPhotoCount();
            if ($count>0) {
                $clean[]=$child;
            }
        }
        return $clean;
    } else {
        return $children;
    }
}

function redirect($url = "zoph.php", $msg = "Access denied") {
    if (!((LOG_SUBJECT & log::REDIRECT) && (LOG_SEVERITY >= log::DEBUG))) {
        header("Location: " . $url);
    }
        echo "<a href='" . $url . "'>" . $msg . "</a>";
    die();
}

function get_filetype($mime) {
    switch ($mime) {
    case "image/jpeg":
    case "image/png":
    case "image/gif":
        $type="image";
        break;
    case "application/x-bzip2":
    case "application/x-gzip":
    case "application/x-tar":
    case "application/zip":
        $type="archive";
        break;
    case "application/xml":
        $type="xml";
        break;
    case "directory":
        $type="directory";
        break;
    default:
        $type=false;
    }
    return $type;
}

function create_dir($directory) {
    if (file_exists($directory) == false) {
        if (@mkdir($directory, octdec(conf::get("import.dirmode")))) {
            if (!defined("CLI") || conf::get("import.cli.verbose")>=1) {
                log::msg(translate("Created directory") . ": $directory", log::NOTIFY, log::GENERAL);
            }
            return true;
        } else {
            throw new FileDirCreationFailedException(
                translate("Could not create directory") . ": $directory<br>\n");
        }
    }
    return 0;
}

function create_dir_recursive($directory){
    $nextdir="";
    $directory="/" . cleanup_path($directory);
    foreach (explode("/",$directory) as $subdir) {
        $nextdir=$nextdir . $subdir . "/";
        try {
            $result=create_dir($nextdir);
        } catch (FileException $e) {
            throw $e;
        }
    }
    return $result;
}

?>
