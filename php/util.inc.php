<?php

function create_field_html($fields, $cols = 2, $split = null) {

    $html = "";
    if ($split) {
        $left_tag = "          <td align=\"right\" width=\"$split\">";
        $right_tag = "          <td width=\"$split\">";
    }
    else {
        $left_tag = "          <td align=\"right\">";
        $right_tag = "          <td>";
    }

    while (list($key, $val) = each($fields)) {
        if ($val) {
            $html .=
            "        <tr>\n" .
            "$left_tag$key</td>\n" .
            "$right_tag$val</td>\n";
            for ($i = 2; $i < $cols; $i++) {
                $html .= "$right_tag&nbsp;</td>\n";
            }
            $html .= "        </tr>\n";
        }
    }

    return $html;
}

function create_text_input($name, $value, $size = 20, $max = 32) {
    return "<input type=\"text\" name=\"$name\" value=\"$value\" size=\"$size\" maxlength=\"$max\">\n";
}

function create_pulldown($name, $value, $value_array) {
    $html = "<select name=\"$name\">\n";
    while (list($val, $label) = each($value_array)) {
        if ($val == $value) { $selected = " selected"; }
        else { $selected  = ""; }
        $html .= "  <option value=\"$val\"$selected>$label</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}

function create_grouped_pulldown($name, $value, $value_array) {
    $html = "<select name=\"$name\">\n";

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

function create_smart_pulldown($name, $value, $value_array) {

    $size = sizeof($value_array);

    if ($size <= GROUPED_PULLDOWN_SIZE) {
        return create_pulldown($name, $value, $value_array);
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

function create_rating_pulldown($val = "3") {
    $rating_array = array(
        "" => "",
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
    return create_pulldown("rating", $val, $rating_array);
}

function create_conjunction_pulldown($var) {
    return create_pulldown($var . "-conj", "",
        array("" => "", "and" => translate("and",0), "or" => translate("or",0)));
}

function create_operator_pulldown($var, $op = "=") {
    return create_pulldown($var . "-op", $op,
        array(
            "=" => "=", "!=" => "!=",
            ">" => "&gt;", ">=" => "&gt;=",
            "<" => "&lt;", "<=" => "&lt;=",
            "like" => translate("like",0), "not like" => translate("not like",0)));
}

function create_binary_operator_pulldown($var, $op = "=") {
    return create_pulldown($var . "-op", $op,
        array("=" => "=", "!=" => "!="));
}

function create_present_operator_pulldown($var, $op = "=") {
    return create_pulldown($var . "-op", $op,
        array("=" => translate("is in photo",0), "!=" => translate("is not in photo",0)));
}

function create_inequality_operator_pulldown($var, $op = ">") {
    return create_pulldown($var . "-op", $op,
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

function update_query_string($vars, $new_key, $new_val, $ignore = null) {

    if (!$ignore) { $ignore = array(); }
    $ignore[] = "PHPSESSID";
    $ignore[] = "_crumb";

    while (list($key, $val) = each($vars)) {
        if (in_array($key, $ignore)) { continue; }
        if ($key == $new_key) { $continue; }
        if ($qstr) { $qstr .= "&"; }
        $qstr .= "$key=$val";
    }

    if ($qstr) { $qstr .= "&"; }
    return $qstr . "$new_key=$new_val";
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

function create_date_link($date) {
    if ($date) {
        return "<a href=\"calendar.php?date=$date\">$date</a>";
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
    return create_date_link($date) . ' ' . $time;
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

?>
