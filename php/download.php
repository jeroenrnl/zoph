<?php
/**
 * Download a ZIP file with a collection of photos
 *
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
 *
 * @package Zoph
 * @author Jeroen Roos
 *
 */

use conf\conf;

use photo\collection;

use template\template;

use web\request;

require_once "include.inc.php";
$vars=$request->getRequestVarsClean();

$_action=getvar("_action");
if (!conf::get("feature.download") || (!$user->get("download") && !$user->isAdmin())) {
    redirect("zoph.php");
}
if ($_action=="getfile" || $_action=="download") {
    $filename=getvar("_filename");
    if (!$filename) { $filename="zoph"; }
    if (!preg_match("/^[a-zA-Z0-9_-]+$/", $filename)) {
        die("Invalid filename");
    }

    $filenum=getvar("_filenum");
    if (!$filenum) {
        $filenum=1;
    }
}

if ($_action=="download") {
    $zipfile="/tmp/zoph_" . $user->get("user_id") . "_" . $filename ."_" . $filenum . ".zip";
    if (file_exists($zipfile)) {
        header("Content-Length: " . filesize($zipfile));
        header("Content-Disposition: inline; filename=" . $filename . $filenum . ".zip");
        header("Content-type: application/zip");

        readfile($zipfile);
        unlink($zipfile);
    } else {
        echo sprintf(translate("Could not read %s."), $zipfile) . "<br>\n";
    }
    flush();
    exit;
}
$title=translate("Download zipfile");

require_once "header.inc.php";
?>
<h1><?= $title ?></h1>
<div class="main">

<?php

$offset=getvar("_off");
if (!$offset) {
    $offset=0;
}

$maxfiles=getvar("_maxfiles");
if (!$maxfiles) {
    $maxfiles=200;
}
if (!is_numeric($maxfiles)) {
    die("Maximum files must be numeric");
}

$photoCollection = collection::createFromRequest(request::create());
$photos=$photoCollection->subset($offset, $maxfiles);

$totalPhotoCount = sizeof($photoCollection);
$downloadCount = sizeof($photos);

if ($_action=="getfile") {
    $maxsize=getvar("_maxsize");
    if (!$maxsize) { $maxsize=25000000; }

    if (!is_numeric($maxsize)) {
        die("Maximum size must be numeric");
    }
    $dateddirs=getvar("dateddirs");

    if ($downloadCount) {
        echo translate("The zipfile is being created...") . "<br>";
        flush();
        $number=create_zipfile($photos, $maxsize, $filename, $filenum, $user);
        $newoffset=$offset + $number;
        echo "<iframe style=\"border: none; width: 100%; height: 4em\" " .
            "src=download.php?_action=download&_filename=" . $filename .
            "&_filenum=" . $filenum . "></iframe>";

        $new_qs=str_replace("_off=$offset", "_off=$newoffset", $_SERVER["QUERY_STRING"]);
        if ($new_qs==$_SERVER["QUERY_STRING"]) {
            $new_qs=$new_qs . "&_off=$newoffset";
        }
        $qs=$new_qs;
        $new_qs=str_replace("_filenum=$filenum", "_filenum=" . ($filenum + 1), $qs);
        if ($new_qs==$qs) {
            $new_qs=$new_qs . "&_filenum=" . ($filenum + 1);
        }
        if ($newoffset < $totalPhotoCount) {
            echo sprintf(translate("Downloaded %s of %s photos."), $newoffset, $totalPhotoCount);
            ?>
            <ul class="actionlink">
              <li><a href="download.php?<?php echo $new_qs?>">
                <?php echo translate("download next file") ?>
              </a></li>
            </ul>
            <?php
        } else {
            $link = breadcrumb::getLast()->getLink();
            echo sprintf(translate("All photos have been downloaded in %s zipfiles."), $filenum)
            ?>
            <ul class="actionlink">
              <li><a href="<?php echo $link ?>">
                <?php echo translate("Go back")?>
              </a></li>
            </ul>
            <?php
        }
        ?>
        <br>
        </div>
        <?php
        require_once "footer.inc.php";
    } else {
        echo translate("No photos were found matching your search criteria.") . "\n";
    }
} else {
    if ($totalPhotoCount <= 0) {
        echo translate("No photos were found matching your search criteria.") . "\n";
    } else {
        ?>
        <form class="download">
          <p>
            <?php printf(translate("You have requested the download of %s photos," .
                "with a total size of  %s."), $totalPhotoCount,
                template::getHumanReadableBytes(photo::getFilesize($photos))); ?>
          </p>
          <p>
            <?php echo create_form($vars, array("_off", "_action")) ?>
            <input type="hidden" name="_action" value="getfile">
            <label for="filename">
              <?php echo translate("Filename") ?>
            </label>
            <input type="text" id="filename" name="_filename" value="zoph">
            <span class="inputhint">
              <?php echo translate("Use alphanumeric, - and _. Do not provide an extension.") ?>
            </span><br>
            <label for="maxfiles">
              <?php echo translate("Maximum number of files per zipfile") ?>
            </label>
            <?php echo template::createPulldown("_maxfiles", "100",
                array(
                    10 => 10,
                    25 => 25,
                    50 => 50,
                    75 => 75,
                    100 => 100,
                    150 => 150,
                    200 => 200,
                    300 => 300,
                    400 => 400,
                    500 => 500)
                ) ?>
            <br>
            <label for="maxsize">
              <?php echo translate("Maximum size per zipfile") ?>
            </label>
            <?php echo template::createPulldown("_maxsize", "50000000",
                array(
                    "5000000" => "5MiB",
                    "10000000" => "10MiB",
                    "25000000" => "25MiB",
                    "50000000" => "50MiB",
                    "75000000" => "75MiB",
                    "100000000" => "100MiB",
                    "150000000" => "150MiB",
                    "250000000" => "250MiB",
                    "500000000" => "500MiB",
                    "650000000" => "650MiB",
                    "1000000000" => "1GiB",
                    "2000000000" => "2GiB",
                    "4200000000" => "4.2GiB")
                ) ?><br>
            <input type="submit" value="<?php echo translate("download") ?>">
          </p>
        </form>
        <?php
    }
    ?>
    </div>
    <br>
    <?php
    require_once "footer.inc.php";
}
?>
