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

    require_once("include.inc.php");
    $_off = getvar("_off");
    $_pause = getvar("_pause");
    $_random = getvar("_random");

    if (!$_off)  { $_off = 0; }
    $offset = $_off;

    $thumbnails;
    $clean_vars = clean_request_vars($request_vars);
    $num_photos = get_photos($clean_vars, $offset, 1, $thumbnails, $user);
    $charset = $rtplang->get_encoding();
    header("Content-Type: text/html; charset=" . $charset);

    $num_thumbnails = sizeof($thumbnails);
    if  ($num_thumbnails) {
        if ($_random) {
            $title = translate("random photo ") . ($offset + 1);
        }
        else {
            $title = sprintf(translate("photo %s of %s"),  ($offset + 1) , $num_photos);
        }
    }
    else {
        redirect(html_entity_decode(add_sid("photos.php?" . update_query_string($clean_vars, "_off", 0))), "No photos");
    }

    $newoffset = $offset + 1;

    $qs = implode("&amp;", explode("&", $QUERY_STRING));
    $clean_qs=update_query_string($clean_vars, "", 0);
    $new_qs = $qs;
    if (strpos($QUERY_STRING, "_off=") !== false ) {
        $new_qs = str_replace("_off=$offset", "_off=$newoffset", $new_qs);
    }
    else {
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
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>">
<link TYPE="text/css" REL="stylesheet" HREF="<?php echo CSS_SHEET ?>">
<?php
if (!$_pause) {
        // bug#667480: header() didn't work with IE on Mac
        // manually set http-equiv instead
        //header("Refresh: $SLIDESHOW_TIME;URL=$PHP_SELF?$new_qs");
        $header = "<meta http-equiv=\"refresh\" content=\"$SLIDESHOW_TIME;URL=$PHP_SELF?$new_qs\">\n";
    }
    else {
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
      <a href="<?php echo $PHP_SELF . '?' . $new_qs ?>"><?php echo translate("continue") ?></a> |
<?php
    }
    else {
?>
      <a href="<?php echo $PHP_SELF . '?' . $qs . '&amp;' . "_pause=1" ?>"><?php echo translate("pause") ?></a> |
<?php
    }
?>
      <a href="photos.php?<?php echo str_replace("_off=$offset", "_off=0", $clean_qs) ?>"><?php echo translate("stop") ?></a> |
      <a href="photo.php?<?php echo $clean_qs ?>"><?php echo translate("open") ?></a>
    </span>
    <?php echo $title ?>
  </h1>
  <div class="main">
<?php
    if ($num_thumbnails <= 0) {
       echo translate("No photos were found for this slideshow.");
    }
    else {
        $photo = $thumbnails[0];
        $photo->lookup();
	?>
        <div class="prev">&nbsp;</div>
        <div class="photohdr">
            <?php echo $photo->get_fullsize_link($photo->get("name"),$FULLSIZE_NEW_WIN)?>: 
            <?php echo $photo->get("width") ?> x <?php echo $photo->get("height")?>,
            <?php echo $photo->get("size") ?> <?php echo translate("bytes")?>
        </div>    
        <div class="next">&nbsp;</div>
        <?php echo $photo->get_fullsize_link($photo->get_midsize_img(),$FULLSIZE_NEW_WIN)?>
        <?php
        if ($people_links = get_photo_person_links($photo)) {
?>	
            <div id="personlink"><?php echo $people_links ?></div>
<?php
        }
?>
        <br>
     <dl>
<?php echo create_field_html($photo->get_display_array(), 2) ?>
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
<?php require_once("footer.inc.php"); ?>
