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
            "<dt>" . e($key) . "</dt>\n" .
            "<dd>" . e($val) ." </dd>\n";
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
    while(list($key, $field) = each($fields)) {
        $html.=
            "<label for=\"$key\">$field[0]</label>\n" . $field[1] ."<br>";
    }
    return $html;
}

function create_text_input($name, $value, $size = 20, $max = 32) {
    $id=ereg_replace("^_+", "", $name);
    return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"" . e($value) ."\" size=\"$size\" maxlength=\"$max\">\n";
}

function create_pulldown($name, $value, $value_array, $extraopt = null) {
    $id=ereg_replace("^_+", "", $name);

    $html = "<select name=\"$name\" id=\"$id\" $extraopt>\n";
    while (list($val, $label) = each($value_array)) {
        if ($val == $value) { $selected = " selected"; }
        else { $selected  = ""; }
        $html .= "  <option value=\"$val\"$selected >" . ($label?e($label):"&nbsp;") ."</option>\n";
    }
    $html .= "</select>\n";
    return $html;
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
        "" => "",
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
        "" => "",
        "album" => translate("album",0),
        "category" => translate("category",0),
        "person" => translate("person",0),
        "photographer" => translate("photographer",0)));
}

function create_view_pulldown($name, $value, $extraopt=null) {
    return create_pulldown($name, $value, array(
        "list" => translate("List",0), 
        "tree" => translate("Tree",0), 
        "thumbs" => translate("Thumbnails",0)), $extraopt);
}

function create_autothumb_pulldown($name, $value, $extraopt=null) {
    return  create_pulldown($name, $value, array(
        "oldest" => translate("Oldest photo",0), 
        "newest" => translate("Newest photo",0), 
        "first" => translate("Changed least recently",0), 
        "last" => translate("Changed most recently",0), 
        "highest" => translate("Highest ranked",0), 
        "random" => translate("Random",0)), $extraopt);
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
        $form .= "<input type=\"hidden\" name=\"$key\" value=\"" . escape_string($val) . "\">\n";
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
    // expects either YYYY-MM-DD, YYYY-MM-DD HH:MM:SS or YYYYMMDDHHMMSS

    $date_array = null;

    if (preg_match("/^\d\d\d\d-\d\d-\d\d$/", $date)) {
        $date_array['year'] = substr($date, 0, 4);
        $date_array['mon'] = substr($date, 5, 2);
        $date_array['day'] = substr($date, 8, 2);
    } else if (preg_match("/^\d\d\d\d-\d\d-\d\d\ \d\d:\d\d:\d\d$/", $date)) {
        $date_array['year'] = substr($date, 0, 4);
        $date_array['mon'] = substr($date, 5, 2);
        $date_array['day'] = substr($date, 8, 2);
        $date_array['hour'] = substr($date, 11, 2);
        $date_array['min'] = substr($date, 14, 2);
        $date_array['sec'] = substr($date, 17, 2);
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

function create_zipfile($photos, $maxsize, $filename, $filenum, $user) {
    if(class_exists(ZipArchive)) {
        $zip=new ZipArchive();
        $tempfile="/tmp/zoph_" . $user->get("user_id") . "_" . $filename ."_" . $filenum . ".zip";
        @unlink($tempfile);
        // ZIPARCHIVE::CREATE is not available in PHP4, but resolves to 1
        if ($zip->open($tempfile, 1)!==TRUE) {
            die("cannot open $tempfile\n");
        }
        $count=sizeof($photos);
        foreach($photos as $key => $photo) {
            if($data=@file_get_contents($photo->get_file_path())) {
                $size=strlen($data);
                $zipsize=$zipsize+$size;
                if($zipsize>=$maxsize) {
                    break;
                }
                $currentfile=$key;
                $zip->addFromString($photo->get("name"),$data);
            
            } else {
                echo sprintf(translate("Could not read %s."), $photo->get_file_path()) . "<br>\n";
            }
        }
        $zip->close() or die ("Zipfile creation failed");
        return $currentfile;
    } else {
        echo translate("You need to have ZIP support in PHP to download zip files");
        return FALSE;
    }
}

function get_human($bytes) {
    // transforms a size in bytes into a human readable format using 
    // Ki Mi Gi, etc. prefixes
    // Give me a call if your database grows bigger than 1024 Yobbibytes. :-)
    if($bytes==0) {
        // prevents div by 0
        return "0B";
    } else {
        $prefixes=array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
        $length=floor(log($bytes,2)/10);
        return round($bytes/pow(2,10*($length)),1) . $prefixes[floor($length)] . "B";
    }
}

function watermark_image($orig, $watermark, $positionX = "center", $positionY = "center", $transparency = 50) {

    $wm=imagecreatefromgif($watermark);
    
    $width_orig=ImageSX($orig);
    $height_orig=ImageSY($orig);

    $width_wm=ImageSX($wm);
    $height_wm=ImageSY($wm);

    switch ($positionX) {
    case "left":
        $destX = 5;
        break;
    case "right":
        $destX = $width_orig - $width_wm - 5;
        break;
    default:
        $destX = ($width_orig / 2) - ($width_wm / 2);
        break;
    }

    switch ($positionY) {
    case "top":
        $destY = 5;
        break;
    case "bottom":
        $destY = $height_orig - $height_wm - 5;
        break;
    default:
        $destY = ($height_orig / 2) - ($height_wm / 2);
        break;
    }
    ImageCopyMerge($orig, $wm, $destX, $destY, 0, 0, $width_wm, $height_wm, $transparency);
    imagedestroy($wm);
    return $orig;
}

function pager($current, $total, $num_pages, $page_size, $max_size, $url, $request_vars, $var) {
    $page_num = floor($current / $page_size) + 1;
    if ($current > 0) {
        $new_offset = max(0, $current - $page_size);
        $html="<div class='prev'>\n";
        $html.="[ <a href='" . $url . "?";
        $html.=update_query_string($request_vars, $var, $new_offset);
        $html.="'>" . translate("Prev") . "</a> ]\n";
        $html.="</div>\n";
    } else {
        $html.="<div class='prev'>&nbsp;</div>\n";
    }

    if ($num_pages > 1) {
        $html.="<div class='pagelink'>[ ";
        $mid_page = floor($max_size / 2);
        $page = $page_num - $mid_page;
        if ($page <= 0) { $page = 1; }

        $last_page = $page + $max_size - 1;
        if ($last_page > $num_pages) {
            $page = $page - $last_page + $num_pages;
            if ($page <= 0) { $page = 1; }
            $last_page = $num_pages;
        }

        if ($page > 1) {
            $html.="<a href='" . $url . "?";
            $html.=update_query_string($request_vars, $var, 0). "'>1</a> ...";
        }

        while ($page <= $last_page) {
            $new_offset = ($page - 1) * $page_size;
            $new_query_string=update_query_string($request_vars, $var, $new_offset);
            $currentpage=$page == $page_num ? " class='currentpage'" : "";
            $html.="&nbsp;<a href='" . $url . "?" . $new_query_string . "'>";
            $html.="<span " . $currentpage . ">" . $page . "</span></a>&nbsp;";
            $page++;
        }

        if ($page <= $num_pages) {
            $new_query_string=update_query_string($request_vars, $var, ($num_pages-1) * $page_size);
            $html.="... <a href='" . $url . "?" . $new_query_string . "'>";
            $html.=$num_pages . "</a>";
        }
        $html.=" ]</div>\n\n";
    } else {
        $html.="<div class=\"pagelink\">&nbsp;</div>\n";
    }
    if ($total >  $current + $page_size) {
        $new_offset = $current + $page_size;
        $new_query_string=update_query_string($request_vars, $var, $new_offset);
        $html.="<div class='next'>";
        $html.="[ <a href='" . $url . "?" . $new_query_string . "'>";
        $html.=translate("Next") . "</a> ]";
        $html.="</div>";
    } else {
        $html.="<div class=\"next\">&nbsp;</div>\n";
    }
    return $html;
}
function get_markers($objects, $user) {
    $js="<script type='text/javascript'>\n";
    $markers=array();
    if($objects) {
        foreach($objects as $object) {
            $object->lookup();
            $marker=$object->get_marker($user);
            if($marker) {
                $markers[]=$marker;
            }
        }
        // if multiple photos are taken in the same place, that place 
        // is multiple times in the array, let's remove doubles:
        $markers=array_unique($markers);
        foreach($markers as $marker) {
            $js.=$marker;
        }
        $js.="  mapstraction.autoCenterAndZoom();\n";
        $js.="</script>";
    }
    if(count($markers)>0) {
        // only return the javascript if anything is in there, to
        // prevent an empty <script> tag.
        return $js;
    } else {
        return null;
    }
}

function create_map_js($provider=MAPS, $map="map") {
    $js="<script type='text/javascript'>\n" .
        "  createMap('" . $map . "','" . $provider . "');\n" .
        "</script>";
    return $js;
}

function check_js($user) {
    if (($user->prefs->get("autocomp_albums")) || 
        ($user->prefs->get("autocomp_categories")) || 
        ($user->prefs->get("autocomp_places")) || 
        ($user->prefs->get("autocomp_people")) ||  
        ($user->prefs->get("autocomp_photographer")) 
        && AUTOCOMPLETE && JAVASCRIPT) {
        
        return "<noscript><div class='warning'><img class='icon' src='images/icons/" . ICONSET . "/" . "warning.png'>" . translate("You have enabled autocompletion for one or more dropdown boxes on this page, however, you do not seem to have Javascript support. You should either enable javascript or turn autocompletion off, or this page will not work as expected!") . "</div></noscript>";
    }
}

function remove_empty($children, $user) {
    $clean=array();
    // If user is not admin, remove any children that do not have photos
    if($user && !$user->is_admin()) {
        foreach($children as $child) {
            $count=$child->get_total_photo_count($user);
            if($count>0) {
                $clean[]=$child;
            }
        }
        return $clean;
    } else {
        return $children;
    } 
}

function get_sql_for_order($order) {
    switch ($order) {
    case "oldest":
        $sql="min(ph.date) as oldest ";
        break;
    case "newest":
        $sql="max(ph.date) as newest "; 
        break;
    case "first":
        $sql="min(ph.timestamp) as first ";
        break;
    case "last":
        $sql="max(ph.timestamp) as last ";
        break;
    case "lowest":
        $sql="min(rating) as lowest "; 
        break;
    case "highest":
        $sql="max(rating) as highest ";
        break;
    case "average":
        $sql="avg(rating) as average ";
        break;
    case "random":
        $sql="rand() as random ";
        break;
    }
    if($sql) {
        return ", " . $sql;
    } else {
        return null;
    }
}

function create_bar_graph($legend, $value_array, $scale) {
    # $value_array is an array that contains an array for each line 
    # of the graph. Each of those arrays contains 3 values: 
    # value: The value we're graphing, 
    # link:  Where should it link to. (may be null)
    # count: Count of that value;

    foreach($value_array as $row) {
        $counts[]=$row[2];
    }
    $max=max($counts);
    $pixels=$scale/$max;

    $html="<table class='ratings'>\n";
    $html.="  <tr>\n";
    $html.="    <th>" . $legend[0] ."</th>\n";
    $html.="    <th>" . $legend[1] ."</th>\n";
    $html.="  <tr>\n";

    foreach($value_array as $row) {
        $html.="  <tr>\n";
        $html.="    <td>\n";
        if($row[1]) {
            $html.="   <a href='" . $row[1] . "'>";
        }
        $html.=$row[0];
        if($row[1]) {
            $html.="</a>\n";
        }
        $html.="    </td>\n";
        $html.="    <td>\n";
        $html.="      <div class='ratings' style='width: " . 
            ceil($row[2]*$pixels) . "px'>";
        $html.="&nbsp;</div>&nbsp;";
        $html.=$row[2];
        $html.="    </td>\n";
        $html.="  </tr>\n";
    }
    $html.="</table>";
    return $html;
}

function redirect($url = "zoph.php", $msg = "Access denied") {
    header("Location: " . $url);
    echo "<a href='" . $url . "'>" . $msg . "</a>";
    die();
}

?>
