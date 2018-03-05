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
 * Uses PHP's read_exif_data() function to parse EXIF headers.
 *
 * I've tried to match some of the normalizations/conversions done by jhead
 * (some code borrowed from exif.c). (License from jhead explicitly allows this).
 *
 * Jason
 */
function process_exif($image) {
    $exifdata = array();
    $file=new file($image);
    $mime=$file->getMime();

    if ($mime == "image/jpeg") {
        $exif = exif_read_data($image);
    } else {
        $exif = false;
    }

    if ($exif === false) {
        echo "<b>" . basename($image) . "</b>" . ": ";
        echo translate("No EXIF header found.") . "<br>\n";

        // Set date and time to file date/time
        list($exifdata["date"],$exifdata["time"])=
            explode(" ",date("Y-m-d H:i:s", filemtime($image)));

        return $exifdata;
    }

    if (isset($exif["DateTimeOriginal"])) {
        $datetime = $exif["DateTimeOriginal"];
    }
    else if (isset($exif["DateTimeDigitized"])) {
        $datetime = $exif["DateTimeDigitized"];
    }
    else if (isset($exif["DateTime"])) {
        $datetime = $exif["DateTime"];
    }

    if (!isset($datetime)) {
        $datetime = date ("Y-m-d H:i:s", filemtime($image));
    }
    list($date, $time) = explode(' ', $datetime);
    $date = str_replace(':', '-', $date);

    $exifdata["date"] = $date;
    $exifdata["time"] = $time;

    if (isset($exif["Make"])) {
        $exifdata["camera_make"] = ucwords(strtolower($exif["Make"]));
    }

    if (isset($exif["Model"])) {
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
        $fYN="N";

        switch ($exif["Flash"]) {

        // Flash Not Fired
        case 16:
        case 0: $fYN="N"; break;

        // Flash Fired
        case 9:
        default: $fYN="Y"; break;
        }

        $exifdata["flash_used"] = $fYN;
    }

    if (isset($exif["FocalLength"])) {
        list($a, $b) = explode('/', $exif["FocalLength"]);
        if ($b>0) {
            $exifdata["focal_length"] = sprintf("%.1fmm", $a / $b);
        }
    }
    $exifdata["exposure"]="";
    if (isset($exif["ExposureTime"])) {
        list($a, $b) = explode('/', $exif["ExposureTime"]);
        if ($b>0) {
            $val = $a / $b;
            $exifdata["exposure"] = sprintf("%.3f s", $val);
            if ($val <= 0.5) {
                $exifdata["exposure"] .= sprintf("  (1/%d)", (int)(0.5 + 1 / $val));
            }
        }
    }

    if (isset($exif["ExposureProgram"])) {
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

    if (isset($exif["FNumber"])) {
        list($a, $b) = explode('/', $exif["FNumber"]);
        if ($b>0) {
            $exifdata["aperture"] = sprintf("f/%.1f", $a / $b);
        }
    }
    else if (isset($exif["ApertureValue"])) {
        list($a, $b) = explode('/', $exif["ApertureValue"]);
        if ($b>0) {
            $exifdata["aperture"] = sprintf("f/%.1f", pow(2,($a / $b)/2));
        }
    }
    else if (isset($exif["MaxApertureValue"])) {
        list($a, $b) = explode('/', $exif["MaxApertureValue"]);
        if ($b>0) {
            $exifdata["aperture"] = sprintf("f/%.1f", pow(2,($a / $b)/2));
        }
    }

    if (isset($exif["FocusDistance"])) {
        $exifdata["focus_dist"] = $exif["FocusDistance"];
    }

    if (isset($exif["MeteringMode"])) {
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

    if (isset($exif["ISOSpeedRatings"])) {
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

    if (isset($exif["CompressedBitsPerPixel"])) {
        list($a, $b) = explode('/', $exif["CompressedBitsPerPixel"]);
        if ($b>0) {
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
    }

    if (isset($exif["Comment"])) {
        $exifdata["comment"] = $exif["Comment"];
    }

    if (isset($exif["GPSLatitudeRef"]) && isset($exif["GPSLatitude"]) &&
        isset($exif["GPSLongitudeRef"]) && isset($exif["GPSLongitude"])) {
        $latarray=$exif["GPSLatitude"];

        // This is an array that looks like this
        // array(3) {
        //            [0]=>string(5) "150/1"  (degrees)
        //            [1]=>string(4) "47/1"   (minutes)
        //            [2]=>string(8) "1239/100" (seconds)

        $latdegarray=explode("/", $latarray[0]);
        $latminarray=explode("/", $latarray[1]);
        $latsecarray=explode("/", $latarray[2]);

        $latdeg=$latdegarray[0] / $latdegarray[1];
        $latmin=$latminarray[0] / $latminarray[1];
        $latsec=$latsecarray[0] / $latsecarray[1];

        $lat=$latdeg + ($latmin / 60) + ($latsec / 3600);

        if ($exif["GPSLatitudeRef"] == "S") {
            $lat = $lat * -1;
        }
        $exifdata["lat"]=$lat;

        $lonarray=$exif["GPSLongitude"];

        $londegarray=explode("/", $lonarray[0]);
        $lonminarray=explode("/", $lonarray[1]);
        $lonsecarray=explode("/", $lonarray[2]);

        $londeg=$londegarray[0] / $londegarray[1];
        $lonmin=$lonminarray[0] / $lonminarray[1];
        $lonsec=$lonsecarray[0] / $lonsecarray[1];

        $lon=$londeg + ($lonmin / 60) + ($lonsec / 3600);

        if ($exif["GPSLongitudeRef"] == "W") {
            $lon = $lon * -1;
        }
        $exifdata["lon"]=$lon;
        /*
        // No alt in db yet
        if (isset($exif["GPSAltitude"])) {
            $altarray=explode("/", $exif["GPSAltitude"]);
            $alt=$altarray[0] / $altarray[1];
            $exifdata["alt"]=$alt;
        }
        */
    }

    return $exifdata;
}

?>
