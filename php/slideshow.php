<?php
/**
 * Display a slideshow of photos
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
 * @author Jason Geiger
 * @author Jeroen Roos
 */

require_once "include.inc.php";
$_off = getvar("_off");
$_pause = getvar("_pause");
$_random = getvar("_random");

if (!$_off)  { $_off = 0; }
$offset = $_off;

$thumbnails;
$clean_vars = clean_request_vars($request_vars);
$num_photos = get_photos($clean_vars, $offset, 1, $thumbnails, $user);
header("Content-Type: text/html; charset=utf-8");

$num_thumbnails = sizeof($thumbnails);
if  ($num_thumbnails) {
    if ($_random) {
        $title = translate("random photo ") . ($offset + 1);
    } else {
        $title = sprintf(translate("photo %s of %s"),  ($offset + 1) , $num_photos);
    }
} else {
    redirect(html_entity_decode("photos.php?" . update_query_string($clean_vars, "_off", 0)),
        "No photos");
}

$newoffset = $offset + 1;

$qs = implode("&amp;", explode("&", $_SERVER["QUERY_STRING"]));
$clean_qs=update_query_string($clean_vars, "", 0);
$new_qs = $qs;
if (strpos($_SERVER["QUERY_STRING"], "_off=") !== false) {
    $new_qs = str_replace("_off=$offset", "_off=$newoffset", $new_qs);
} else {
    if ($new_qs) {
        $new_qs .= "&amp;";
    }
    $new_qs .= "_off=$newoffset";
}

$header = "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link TYPE="text/css" REL="stylesheet" HREF="css.php">
<?php
if (!$_pause) {
    $header = "<meta http-equiv=\"refresh\" content=\"" .
        $user->prefs->get("slideshow_time") . ";URL=" .
        $_SERVER["PHP_SELF"] . "?" . $new_qs. "\">\n";
} else {
    $header="";
    $new_qs = str_replace("&amp;_pause=1", "", $new_qs);
}

?>
<?php echo $header ?>
<title>Zoph - Slideshow</title>
</head>
<body>
<h1>
<span class="actionlink">
<?php
if ($_pause) {
    ?>
      <a href="<?php echo $_SERVER["PHP_SELF"] . '?' . $new_qs ?>">I
        <?php echo translate("continue") ?>
      </a> |
    <?php
} else {
    ?>
      <a href="<?php echo $_SERVER["PHP_SELF"] . '?' . $qs . '&amp;' . "_pause=1" ?>">
        <?php echo translate("pause") ?>
      </a> |
    <?php
}
?>
  <a href="photos.php?<?php echo str_replace("_off=$offset", "_off=0", $clean_qs) ?>">
    <?php echo translate("stop") ?>
  </a> |
  <a href="photo.php?<?php echo $clean_qs ?>"><?php echo translate("open") ?></a>
</span>
<?php echo $title ?>
</h1>
<div class="main">
<?php
if ($num_thumbnails <= 0) {
    echo translate("No photos were found for this slideshow.");
} else {
    $photo = $thumbnails[0];
    $photo->lookup();
    ?>
    <div class="prev">&nbsp;</div>
    <div class="photohdr">
        <?php echo $photo->getFullsizeLink($photo->get("name"))?>:
        <?php echo $photo->get("width") ?> x <?php echo $photo->get("height")?>,
        <?php echo $photo->get("size") ?> <?php echo translate("bytes")?>
    </div>
    <div class="next">&nbsp;</div>
    <?php echo $photo->getFullsizeLink($photo->getImageTag(MID_PREFIX))?>
    <?php
    if ($people_links = $photo->getPeopleLinks()) {
        ?>
        <div id="personlink"><?php echo $people_links ?></div>
        <?php
    }
    ?>
    <br>
    <dl>
    <?php echo create_field_html($photo->getDisplayArray(), 2) ?>
    <?php
    if ($photo->get("description")) {
        ?>
        <dt><?php echo translate("description") ?></dt>
        <dd>
            <?php echo $photo->get("description") ?>
        </dd>
        <?php
    }
} // if photos
?>
  </dl>
  <br>
</div>
<?php require_once "footer.inc.php"; ?>
