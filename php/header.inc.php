<?php
/**
 * Display the header of the page
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

header("Content-Type: text/html; charset=utf-8");
$user=user::getCurrent();

$icons=array(
    "count"     => template::getImage("icons/photo.png"),
    "taken"     => template::getImage("icons/date.png"),
    "modified"  => template::getImage("icons/modified.png"),
    "rated"     => template::getImage("icons/rating.png"),
    "children"  => template::getImage("icons/folder.png"),
    "geo-photo" => template::getImage("icons/geo-photo.png"),
    "geo-place" => template::getImage("icons/geo-place.png"),
    "resize"    => template::getImage("icons/resize.png"),
    "unpack"    => template::getImage("icons/unpack.png"),
    "remove"    => template::getImage("icons/remove.png"),
    "down2"     => template::getImage("down2.gif"),
    "pleasewait"=> template::getImage("pleasewait.gif")
);

$javascript=array();

$scripts=array(
    "js/util.js",
    "js/xml.js",
    "js/thumbview.js"
    );

switch(basename($_SERVER["SCRIPT_NAME"])) {
case "import.php":
    $scripts[]="js/import.js";
    $scripts[]="js/formhelper.js";
    break;
case "config.php":
    $scripts[]="js/conf.js";
    break;
}

if (conf::get("interface.autocomplete")) {
    $scripts[]="js/autocomplete.js";
}

if (conf::get("maps.provider")) {
    $scripts[]="js/mxn/mxn.js?(" . conf::get("maps.provider") .")";
    $scripts[]="js/maps.js";
    $scripts[]="js/custommaps.js";
    if (conf::get("maps.geocode")) {
        $scripts[]="js/geocode.js";
    }
    switch (strtolower(conf::get("maps.provider"))) {
    case "googlev3":
        $scripts[]="https://maps.google.com/maps/api/js?sensor=false";
        break;
    case "yahoo":
        $scripts[]="http://api.maps.yahoo.com/ajaxymap?v=3.0&appid=Zoph";
        break;
    case "openlayers":
        $scripts[]="http://openlayers.org/api/OpenLayers.js";
        break;
    case "cloudmade":
        $scripts[]="http://tile.cloudmade.com/wml/0.2/web-maps-lite.js";
        $javascript[]="var cloudmade_key = \"" . conf::get("maps.key.cloudmade") . "\"";
        break;
    }
}

$html_title=conf::get("interface.title");
if (isset($title)) {
    $html_title.=" - " . $title;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<?php
$tpl=new block("header", array(
    "icons"         => $icons,
    "scripts"       => $scripts,
    "javascript"    => $javascript,
    "extrastyle"    => isset($extrastyle) ? $extrastyle : null,
    "title"         => $html_title
));

echo $tpl;
?>
<body>
<?php
$tabs = array(
    translate("home", 0) => "zoph.php",
    translate("albums", 0) => "albums.php",
    translate("categories", 0) => "categories.php"
);

if ($user->canBrowsePeople()) {
    $tabs[translate("people", 0)] = "people.php";
}

if ($user->canBrowsePlaces()) {
    $tabs[translate("places", 0)] = "places.php";
}

$tabs[translate("photos", 0)] = "photos.php";

if ($user->get("lightbox_id")) {
    $tabs[translate("lightbox", 0)] = "photos.php?album_id=" .
        $user->get("lightbox_id");
}

$tabs[translate("search",0)] = "search.php";

if (conf::get("import.enable") &&
    ($user->isAdmin() || $user->get("import"))) {

    $tabs[translate("import", 0)] = "import.php";
}

if ($user->isAdmin()) {
    $tabs[translate("admin", 0)] = "admin.php";
}

$tabs += array(
    translate("reports", 0) => "reports.php",
    translate("prefs", 0) => "prefs.php",
    translate("about", 0) => "info.php"
);

if ($user->get("user_id") == conf::get("interface.user.default")) {
    $tabs[translate("logon", 0)] = "zoph.php?_action=logout";
} else {
    $tabs[translate("logout", 0)] = "zoph.php?_action=logout";
}

if (strpos($_SERVER["PHP_SELF"], "/") === false) {
    $self = $_SERVER["PHP_SELF"];
} else {
    $self = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
}

$tpl=new block("menu", array(
    "tabs"  => $tabs,
    "self"  => $self
));
echo $tpl;

require_once "breadcrumbs.inc.php";
?>
