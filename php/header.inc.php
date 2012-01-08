<?php
/* This file is part of Zoph.
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

    header("Content-Type: text/html; charset=utf-8");
    global $user;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link type="text/css" rel="stylesheet" href="<?php echo CSS_SHEET ?>">
<?php
    if(JAVASCRIPT) {
?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/xml.js"></script>
        <script type="text/javascript" src="js/thumbview.js"></script>
<?php
        if(basename($_SERVER["SCRIPT_NAME"])=="import.php") {
?>
        <script type="text/javascript" src="js/import.js"></script>
<?php
        }
        if(AUTOCOMPLETE) {
?>
        <script type="text/javascript" src="js/autocomplete.js"></script>
<?php
        }
        if(MAPS) {
?>
        <script type="text/javascript" src="js/mxn/mxn.js?(<?php echo strtolower(MAPS); ?>)"></script>
        <script type="text/javascript" src="js/maps.js"></script>
        <script type="text/javascript" src="js/custommaps.js"></script>
<?php 
        if(GEOCODE) { ?>

        <script type="text/javascript" src="js/geocode.js"></script>
<?php
        }        
            switch (strtolower(MAPS)) {
            case 'google':
?>
        <script src="http://maps.google.com/maps?file=api&v=2&key=<?php echo GOOGLE_KEY ?>" type="text/javascript"></script>
<?php
            break;
            case 'googlev3':
?>
        <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>

<?php
            break;
            case 'yahoo':
?>
        <script type="text/javascript" src="http://api.maps.yahoo.com/ajaxymap?v=3.0&appid=Zoph"></script>
<?php
            break;
            case 'openlayers':
?>
        <script src="http://openlayers.org/api/OpenLayers.js"></script>
<?php
            break;
            case 'cloudmade':
?>
        <script type="text/javascript" src="http://tile.cloudmade.com/wml/0.2/web-maps-lite.js"></script>
        <script type="text/javascript">
            var cloudmade_key = "<?php echo CLOUDMADE_KEY; ?>";
        </script>
<?php
            break;
            }
        }

    }
    if (isset($extrastyle)) {
?>
        <style type="text/css">
            <?php echo $extrastyle ?>
        </style>
<?php
    }
    if (isset($title)) {
        $html_title=ZOPH_TITLE . " - " . $title;
    } else {
        $html_title=ZOPH_TITLE; 
    }
?>
    <title><?php echo $html_title ?></title>
    </head>
    <body>
        <ul class="menu">
<?php
    $tabs = array(
        translate("home", 0) => "zoph.php",
        translate("albums", 0) => "albums.php",
        translate("categories", 0) => "categories.php");
    if ($user->is_admin() || $user->get("browse_people")) {
        $tabs[translate("people", 0)] = "people.php";
    }

    if ($user->is_admin() || $user->get("browse_places")) {
        $tabs[translate("places", 0)] = "places.php";
    }

    $tabs[translate("photos", 0)] = "photos.php";

    if ($user->get("lightbox_id")) {
        $tabs[translate("lightbox", 0)] = "photos.php?album_id=" .
            $user->get("lightbox_id");
    }

    $tabs[translate("search",0)] = "search.php";

    if (IMPORT &&
        ($user->is_admin() || $user->get("import"))) {

        $tabs[translate("import", 0)] = "import.php";
    }

    if ($user->is_admin()) {
        $tabs[translate("admin", 0)] = "admin.php";
    }

    $tabs += array(
        translate("reports", 0) => "reports.php",
        translate("prefs", 0) => "prefs.php",
        translate("about", 0) => "info.php");

    if ($user->get("user_id") == DEFAULT_USER) {
        $tabs[translate("logon", 0)] = "zoph.php?_action=logout";
    }
    else {
        $tabs[translate("logout", 0)] = "zoph.php?_action=logout";
    }

    if (strpos($_SERVER["PHP_SELF"], "/") === false) {
        $self = $_SERVER["PHP_SELF"];
    }
    else {
        $self = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
    }

    while (list($label, $page) = each($tabs)) {
        if ($page == $self) {
            $class = "class=\"selected\"";
        } else {
	$class="";
	}
?><li <?php echo $class ?>><a href="<?php echo $page ?>"><?php echo $label ?></a></li><?php
    }
    echo "\n";
?>
    </ul>
<?php
require_once("breadcrumbs.inc.php");
?>
