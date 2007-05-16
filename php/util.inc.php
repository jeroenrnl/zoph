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

function get_url() {
    // set to _SERVER in variables.inc.php for versions >= 4.1
    global $HTTP_SERVER_VARS;

    $script = $HTTP_SERVER_VARS['PHP_SELF'];

    $url =
        "http" . ($HTTP_SERVER_VARS['HTTPS'] == 'on' ? 's' : '') . '://' .
        $HTTP_SERVER_VARS['SERVER_NAME'] . '/' .
        substr($script, 1, strrpos($script, '/'));

    return $url;
}

function create_field_html($fields) {

    $html = "";
    while (list($key, $val) = each($fields)) {
        if ($val) {
            $html .=
            "<dt>$key</dt>\n" .
            "<dd>$val</dd>\n";
        }
    }
    return $html;
}

function create_field_html_table($fields) {

    $html = "";
    while (list($key, $val) = each($fields)) {
        if ($val) {
            $html .=
            "<tr>\n  <th>$key</th>\n" .
            "  <td>$val</<td>\n</tr>\n";
        }
    }
    return $html;
}

function create_edit_fields($fields) {
    $html = "";
    while(list($key, $field) = each($fields)) {
        $html.=
            "<label for=\"$key\">$field[0]</label>\n$field[1]<br>";
    }
    return $html;
}

function create_text_input($name, $value, $size = 20, $max = 32) {
    $id=ereg_replace("^_+", "", $name);
    return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"" . $value ."\" size=\"$size\" maxlength=\"$max\">\n";
}

function create_pulldown($name, $value, $value_array, $extraopt = null) {
    $id=ereg_replace("^_+", "", $name);

    $html = "<select name=\"$name\" id=\"$id\" $extraopt>\n";
    while (list($val, $label) = each($value_array)) {
        if ($val == $value) { $selected = " selected"; }
        else { $selected  = ""; }
        $html .= "  <option value=\"$val\"$selected >" . ($label?$label:"&nbsp;") ."</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}

function create_grouped_pulldown($name, $value, $value_array) {
    $id=ereg_replace("^_+", "", $name);
    $html = "<select name=\"$name\" id=\"$id\">\n";

    $current_label = "";
    $optgroup;
    while (list($val, $label) = each($value_array)) {
        list($left, $right) = explode(', ', $label);

        if (!$right && $left) {
            $right = $left;
            $left = "[]";
            $label = "$left, $right";
        }

        if ($left != $current_label) {
            $current_label = $left;
            if ($optgroup) {
                $html .= "  </optgroup>\n";
            }
            $html .= "  <optgroup label=\"$current_label\">\n";
            $optgroup = 1;
        }

        if ($val == $value) { $selected = " selected"; }
        else { $selected  = ""; }
        $html .= "  <option label=\"$right\" value=\"$val\"$selected>$label</option>\n";
    }

    if ($optgroup) {
        $html .= "  </optgroup>\n";
    }

    $html .= "</select>\n";
    return $html;
}

function create_smart_pulldown($name, $value, $value_array, $extraopt = null) {

    $size = sizeof($value_array);

    if ($size <= GROUPED_PULLDOWN_SIZE) {
        return create_pulldown($name, $value, $value_array, $extraopt);
    }
    else if ($size <= MAX_PULLDOWN_SIZE) {
        return create_grouped_pulldown($name, $value, $value_array);
    }
    else {
        return create_text_input($name, $value, 10, 10);
    }
}

function create_integer_pulldown($name, $value, $min, $max) {
    for ($i = $min; $i <= $max; $i++) {
        $integer_array["$i"] = $i;
    }
    return create_pulldown($name, $value, $integer_array);
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

    return create_pulldown($name, $val, $rating_array);
}

function create_conjunction_pulldown($var, $val = "") {
    return create_pulldown($var, $val,
        array("" => "", "and" => translate("and",0), "or" => translate("or",0)));
}

function create_operator_pulldown($var, $op = "=") {
    return create_pulldown($var, $op,
        array(
            "=" => "=", "!=" => "!=",
            ">" => "&gt;", ">=" => "&gt;=",
            "<" => "&lt;", "<=" => "&lt;=",
            "like" => translate("like",0), "not like" => translate("not like",0)));
}

function create_binary_operator_pulldown($var, $op = "=") {
    return create_pulldown($var, $op,
        array("=" => "=", "!=" => "!="));
}

function create_present_operator_pulldown($var, $op = "=") {
    return create_pulldown($var, $op,
        array("=" => translate("is in photo",0), "!=" => translate("is not in photo",0)));
}

function create_inequality_operator_pulldown($var, $op = ">") {
    return create_pulldown($var, $op,
        array(">" => translate("less than"), "<" => translate("more than")));
}

function create_photo_field_pulldown($var, $name = null) {
    return create_pulldown($var, $name, array(
        "" => "&nbsp;",
        "date" => translate("date",0),
        "time" => translate("time",0),
        "timestamp" => translate("timestamp",0),
        "name" => translate("file name",0),
        "path" => translate("path",0),
        "title" => translate("title",0),
        "view" => translate("view",0),
        "description" => translate("description",0),
        "width" => translate("width",0),
        "height" => translate("height",0),
        "size" => translate("size",0),
        "aperture" => translate("aperture",0),
        "camera_make" => translate("camera make",0),
        "camera_model" => translate("camera model",0),
        "compression" => translate("compression",0),
        "exposure" => translate("exposure",0),
        "flash_used" => translate("flash used",0),
        "focal_length" => translate("focal length",0),
        "iso_equiv" => translate("iso equiv",0),
        "metering_mode" => translate("metering mode",0)));
}

function create_photo_text_pulldown($var, $name = null) {
    return create_pulldown($var, $name, array(
        "" => "&nbsp;",
        "album" => translate("album",0),
        "category" => translate("category",0),
        "person" => translate("person",0),
        "photographer" => translate("photographer",0)));
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
            if (substr($field, 0, 5) == "field" && (empty($interim_vars[$field]) && empty($interim_vars["_$field"]))) { continue; }
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
    if($vars) {
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
        $form .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
    }

    return $form;
}

function add_sid($url) {
    if (SID) {
        if (strpos($url, "?") > 0) {
            $url .= "&";
        }
        else {
            $url .= "?";
        }

        $url .= SID;
    }
    return $url;
}

function make_title($string) {
    $string = str_replace("_", " ", $string);
    $string = preg_replace("/\b(\w)/e", "strtoupper('\\1')", $string);
    return $string;
}

function create_date_link($date, $search_field = "date") {
    if ($date) {
        return "<a href=\"calendar.php?date=$date&amp;search_field=$search_field\">$date</a>";
    }
}

function parse_date($date) {
    // expects either YYYY-MM-DD or YYYYMMDDHHMMSS

    $date_array = null;

    if (preg_match("/^\d\d\d\d-\d\d-\d\d/", $date)) {
        $date_array['year'] = substr($date, 0, 4);
        $date_array['mon'] = substr($date, 5, 2);
        $date_array['day'] = substr($date, 8, 2);
    }
    else if (preg_match("/^\d{14}/", $date)) {
        $date_array['year'] = substr($date, 0, 4);
        $date_array['mon'] = substr($date, 4, 2);
        $date_array['day'] = substr($date, 6, 2);
        $date_array['hour'] = substr($date, 8, 2);
        $date_array['min'] = substr($date, 10, 2);
        $date_array['sec'] = substr($date, 12, 2);
    }

    return $date_array;
}

function format_timestamp($ts) {
    $da = parse_date($ts);
    $date = $da['year'] . '-' . $da['mon'] . '-' . $da['day'];
    $time = $da['hour'] . ':' . $da['min'] . ':' . $da['sec'];
    return create_date_link($date, "timestamp") . ' ' . $time;
}

function subtract_days($date, $days) {
    $da = parse_date($date);
    $time = mktime(0, 0, 0, $da['mon'], $da['day'] - $days, $da['year']);

    /*
    MySQL's timestamp seems smart enough to do convertions so that
    timestamp >= '2002-09-01' does work.

    if (strpos($date, '-')) {
        $new_date = strftime("%Y-%m-%d", $time);
    }
    else {
        $new_date = strftime("%Y%m%d", $time);
        $new_date .= $da['hour'] . $da['min'] . $da['sec'];
    }

    return $new_date;
    */

    return strftime("%Y-%m-%d", $time);
}

function get_date_select_array($date, $days) {
    $da = parse_date($date);

    $date_array[""] = "";
    for ($i = 1; $i <= $days; $i++) {
        $time = mktime(0, 0, 0, $da['mon'], $da['day'] - $i, $da['year']);
        $date_array[strftime("%Y-%m-%d", $time)] = $i;
    }

    return $date_array;
}

function encode_href($str) {
    $encoded_href = '';
    foreach (explode('/', $str) as $path) {
        if ($path) {
            $encoded_href .= '/' . rawurlencode($path);
        }
    }

    if (strpos(" $str", '/') != 1) {
        $encoded_href = substr($encoded_href, 1);
    }

    return $encoded_href;
}

function strip_href($str) {
    if ($str) {
        return preg_replace("/<a href=\"([^\"]+)\">.*/", "\\1", $str);
    }
    return $str;
}

function file_extension($str) {
    return substr($str, strrpos($str, '.') + 1);
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

function valid_image($name) {
    $ext = strtolower(file_extension($name));
    if ($ext == "jpg" ||
        $ext == "jpeg" ||
        $ext == "jpe" ||
        $ext == "gif" ||
        $ext == "tiff" ||
        $ext == "tif" ||
        $ext == "png") {

        return true;
    }
    return false;
}

function get_converted_image_name($name) {

    $extension = file_extension($name);

    // if you used a version of Zoph prior to 0.3 AND have thumbnails
    // for image types other than jpegs, you may want to define
    // MIXED_THUMBNAILS in config.inc.php to avoid having to regenerate
    // your thumbnails.

    if (MIXED_THUMBNAILS && valid_image($name)) {
        return $name;
    }

    // zophImport.pl should have generated jpg thumbnails for other image types
    return preg_replace("/" . $extension . "$/", THUMB_EXTENSION, $name);
}

function delete_temp_annotated_files($user_id) {
    if (!ANNOTATE_PHOTOS) {
        return;
    }

    $tmp_dir = dir(ANNOTATE_TEMP_DIR);
    $search_str = ANNOTATE_TEMP_PREFIX . $user_id;
    while ($entry = $tmp_dir->read()) {
        if (strpos(" $entry", $search_str) == 1) {
            unlink(ANNOTATE_TEMP_DIR . "/" . $entry);
        }
    }
}
/* based on urlencode_array
   By linus at flowingcreativity dot net
   from: http://www.php.net/manual/en/function.urlencode.php
*/
function rawurlencode_array(
   $var,                // the array value
   $varName,            // variable name to be used in the query string
   $separator = '&'    // what separating character to use in the query string
) {
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

function cleanup_path($path) {
   // Cleans up a path, by removing all double slashes, "/./",
   // leading and trailing slashes.
   $search = array ( "/(\/+)/", "/(\/\.\/)/", "/(\/$)/", "/(^\/)/" );
   $replace = array ( "/", "/", "", "" );
   return preg_replace($search,$replace, $path);
}

function create_actionlinks($actionlinks) {
    if(is_array($actionlinks)) {
        $html="<span class=\"actionlink\">\n";
        while (list($key, $val) = each($actionlinks)) {
            $html .= $bar . "<a href=\"" . $val . "\">" . translate($key, 0) . "</a>";
            $bar=" | ";
        }
        $html.="</span>\n";
        return $html;
    }
}

function running_on_windows() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return true;
    } else {
        return false;
    }
}

function get_autothumb_order($autothumb) {
    switch ($autothumb) {
    case "oldest":
        $order="ORDER BY p.date, p.time DESC LIMIT 1";
        break;
    case "newest":
        $order="ORDER BY p.date DESC, p.time DESC LIMIT 1";
        break;
    case "first":
        $order="ORDER BY p.timestamp LIMIT 1";
        break;
    case "last":
        $order="ORDER BY p.timestamp DESC LIMIT 1";
        break;
    case "random":    
        $order="ORDER BY rand() LIMIT 1";
        break;
    default:
    case "highest":
        $order="ORDER BY p.rating DESC LIMIT 1";
        break;
    }
    return $order;
}

?>
