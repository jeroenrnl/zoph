<?php

/*
 * Uses PHP's read_exif_data() function to parse EXIF headers.
 *
 * I've tried to match some of the normalizations/conversions done by jhead
 * (some code borrowed from exif.c).
 *
 * Jason
 */
function process_exif($image) {

    $exifdata = array();

    $exif = read_exif_data($image);

    if ($exif === false) {
        echo translate("No EXIF header found.") . "<br>\n";
        return $exifdata;
    }

    if ($exif["DateTime"]) {
        $datetime = $exif["DateTime"];
    }
    else if ($exif["DateTimeOriginal"]) {
        $datetime = $exif["DateTimeOriginal"];
    }
    else if ($exif["DateTimeDigitized"]) {
        $datetime = $exif["DateTimeDigitized"];
    }

    if ($datetime) {
        list($date, $time) = explode(' ', $datetime);
        $date = str_replace(':', '-', $date);

        $exifdata["date"] = $date;
        $exifdata["time"] = $time;
    }

    if ($exif["Make"]) {
        $exifdata["camera_make"] = ucwords(strtolower($exif["Make"]));
    }

    if ($exif["Model"]) {
        $exifdata["camera_model"] = ucwords(strtolower($exif["Model"]));
    }

    if (isset($exif["Flash"])) {
        /*
           bug#671023 from mail2061 <AT> deys <DOT> org

           "The code in exif.inc.php that tests $exif["Flash"] in order
           to determine whether or not the flash was fired is getting
           wrong values. My FujiFilm S602 is returning '9' for 'Fired'
           and '16' for 'Not Fired(compulsory)'. I reworked the boolean
           test into a switch statement that handles this. However, I
           suspect that this field can have additional values besides
           the two I've identified."
        */
        //$exifdata["flash_used"] = $exif["Flash"] ? "Yes" : "No";

        // Revamped to handled more expressive flash indications
        $fYN="No";

        switch ($exif["Flash"]) {

        // Flash Not Fired
        case 16:
        case 0: $fYN="No"; break;

        // Flash Fired
        case 9:
        default: $fYN="Yes"; break;
        }

        $exifdata["flash_used"] = $fYN;
    }

    if ($exif["FocalLength"]) {
        list($a, $b) = explode('/', $exif["FocalLength"]);
        $exifdata["focal_length"] = sprintf("%.1fmm", $a / $b);
    }

    if ($exif["ExposureTime"]) {
        list($a, $b) = explode('/', $exif["ExposureTime"]);
        $val = $a / $b;
        $exifdata["exposure"] = sprintf("%.3f s", $val);
        if ($val <= 0.5) {
            $exifdata["exposure"] .= sprintf("  (1/%d)", (int)(0.5 + 1 / $val));
        }
    }

    if ($exif["ExposureProgram"]) {
        $ep = $exif["ExposureProgram"];
        switch ($ep) {
        case 2:
            $exifdata["exposure"] .= " [program (auto)]";
            break;
        case 3:
            $exifdata["exposure"] .= " [aperture priority (semi-auto)]";
            break;
        case 4:
            $exifdata["exposure"] .= " [shutter priority (semi-auto)]";
            break;
        }
    }

    if ($exif["FNumber"]) {
        list($a, $b) = explode('/', $exif["FNumber"]);
        $exifdata["aperture"] = sprintf("f/%.1f", $a / $b);
    }
    else if ($exif["ApertureValue"]) {
        list($a, $b) = explode('/', $exif["ApertureValue"]);
        $exifdata["aperture"] = sprintf("f/%.1f", $a / $b * log(2) * 0.5);
    }
    else if ($exif["MaxApertureValue"]) {
        list($a, $b) = explode('/', $exif["MaxApertureValue"]);
        $exifdata["aperture"] = sprintf("f/%.1f", $a / $b * log(2) * 0.5);
    }

    if ($exif["FocusDistance"]) {
        $exifdata["focus_dist"] = $exif["FocusDistance"];
    }

    if ($exif["MeteringMode"]) {
        $mm = $exif["MeteringMode"];
        switch ($mm) {
        case 2:
            $exifdata["metering_mode"] = "center weight";
            break;
        case 3:
            $exifdata["metering_mode"] = "spot";
            break;
        case 5:
            $exifdata["metering_mode"] = "matrix";
            break;
        }
    }

    if ($exif["ISOSpeedRatings"]) {
        $a = $exif["ISOSpeedRatings"];
        if ($a < 50) { $a *= 200; }
        $exifdata["iso_equiv"] = $a;
    }

    /* something is not quite right here
    if ($exif["FocalPlaneXResolution"] && $exif["FocalPlaneResolutionUnit"]) {
        $width = $exif["ExifImageWidth"];
        list($a, $b) = explode('/', $exif["FocalPlaneXResolution"]);
        $fpxr = $a / $b;
        $fpru = $exif["FocalPlaneResolutionUnit"];

        $exifdata["ccd_width"] = sprintf("%.2fmm", $width * $fpru / $fpxr);
    }
    */

    if ($exif["CompressedBitsPerPixel"]) {
        list($a, $b) = explode('/', $exif["CompressedBitsPerPixel"]);
        $val = round($a / $b);
        switch ($val) {
        case 1:
            $exifdata["compression"] = "jpeg quality: basic";
            break;
        case 2:
            $exifdata["compression"] = "jpeg quality: normal";
            break;
        case 4:
            $exifdata["compression"] = "jpeg quality: fine";
            break;
        }
    }

    if ($exif["Comment"]) {
        $exifdata["comment"] = $exif["Comment"];
    }

    return $exifdata;
}

?>
