<?php
/**
 * Show albums
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
use template\block;
use template\template;

require_once "include.inc.php";

$_view=getvar("_view");
if (empty($_view)) {
    $_view=$user->prefs->get("view");
}
$_autothumb=getvar("_autothumb");
if (empty($_autothumb)) {
    $_autothumb=$user->prefs->get("autothumb");
}

$parent_album_id = getvar("parent_album_id");
if (!$parent_album_id) {
    $album = album::getRoot();
} else {
    $album = new album($parent_album_id);
}

try {
    $selection=new selection($_SESSION, array(
        "coverphoto"    => "album.php?_action=update&amp;album_id=" . $album->getId() . "&amp;coverphoto=",
        "return"        => "_return=albums.php&amp;_qs=parent_album_id=" . $album->getId()
    ));
} catch (PhotoNoSelectionException $e) {
    $selection=null;
}

$pagenum = getvar("_pageset_page");

$album->lookup();
$obj=&$album;
$ancestors = $album->getAncestors();

$title = $album->get("parent_album_id") ? $album->get("album") : translate("Albums");

$ancLinks=array();
if ($ancestors) {
    while ($parent = array_pop($ancestors)) {
        $ancLinks[$parent->getName()] = $parent->getURL();
    }
}

require_once "header.inc.php";

try {
    $pageset=$album->getPageset();
    $page=$album->getPage($request_vars, $pagenum);
    $showOrig=$album->showOrig($pagenum);
} catch (pageException $e) {
    $showOrig=true;
    $page=null;
}

$tpl=new template("organizer", array(
    "page"          => $page,
    "pageTop"       => $album->showPageOnTop(),
    "pageBottom"    => $album->showPageOnBottom(),
    "showMain"      => $showOrig,
    "title"         => $title,
    "ancLinks"      => $ancLinks,
    "selection"     => $selection,
    "coverphoto"    => $album->displayCoverPhoto(),
    "description"   => $album->get("description"),
    "view"          => $_view,
    "view_name"     => "Album view",
    "view_hidden"   => null,
    "autothumb"     => $_autothumb
));

$actionlinks=array();

if ($user->canEditOrganizers()) {
    $actionlinks=array(
        translate("new") => "album.php?_action=new&amp;parent_album_id=" . (int) $album->getId(),
        translate("edit") => "album.php?_action=edit&amp;album_id=" . (int) $album->getId(),
    );
    if ($album->get("coverphoto")) {
        $actionlinks["unset coverphoto"]="album.php?_action=update&amp;album_id=" . (int) $album->getId() .
            "&amp;coverphoto=NULL";
    }
}

$tpl->addActionlinks($actionlinks);

$sortorder = $album->get("sortorder");
$sort = $sortorder ? $sortorder : "";

$tpl->addBlock(new block("photoCount", array(
    "tpc"       => $album->getTotalPhotoCount(),
    "totalUrl"  => "photos.php?album_id=" . $album->getBranchIds() . $sort,
    "pc"        => $album->getPhotoCount(),
    "url"       => "photos.php?album_id=" . $album->getId() . $sort
)));

$order = $user->prefs->get("child_sortorder");
$children = $album->getChildren($order);
if ($children) {
    $tpl->addBlock(new block("view_" . $_view, array(
        "id" => $_view . "view",
        "items" => $children,
        "autothumb" => $_autothumb,
        "topnode" => true,
        "links" => array(
            translate("view photos") => "photos.php?album_id="
        )
    )));
}
echo $tpl;
require_once "footer.inc.php";
?>
