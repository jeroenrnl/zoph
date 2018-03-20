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
use conf\conf;
use template\template;

if ($num_photos) {
    $title = sprintf(translate("photo %s of %s"),  ($offset + 1) , $num_photos);
} else {
    $title = translate("photo");
}

try {
    $selection=new selection($_SESSION, array(
        "relate"        => "relation.php?_action=new&amp;photo_id_1=" . $photo->getId() .
                           "&amp;photo_id_2=",
        "return"        => "_return=photo.php&amp;_qs=" . $encoded_qs
    ), $photo);
} catch (photoNoSelectionException $e) {
    $selection=null;
}

if ($action == "insert") {
    unset($actionlinks["email"]);
    unset($actionlinks["edit"]);
    unset($actionlinks["add comment"]);
    unset($actionlinks["select"]);
    unset($actionlinks["delete"]);
}

$rotate=conf::get("rotate.enable") && ($user->isAdmin() || $permissions->get("writable"));

$full=$photo->getFullsizeLink($photo->get("name"));
$width=$photo->get("width");
$height=$photo->get("height");
$size=template::getHumanReadableBytes($photo->get("size"));
if (conf::get("share.enable") && ($user->isAdmin() || $user->get("allow_share"))) {
    $hash=$photo->getHash();
    $full_hash=sha1(conf::get("share.salt.full") . $hash);
    $mid_hash=sha1(conf::get("share.salt.mid") . $hash);
    $full_link=getZophURL() . "image.php?hash=" . $full_hash;
    $mid_link=getZophURL() . "image.php?hash=" . $mid_hash;

    $share=new template("photo_share", array(
        "hash" => $hash,
        "full_link" => $full_link,
        "mid_link" => $mid_link
    ));
} else {
    $share=null;
}

$tpl=new template("editPhoto", array(
    "photo"             => $photo,
    "title"             => $title,
    "selection"         => $selection,
    "admin"             => (bool) $user->isAdmin(),
    "action"            => $action,
    "actionlinks"       => $actionlinks,
    "return_qs"         => $return_qs,
    "rotate"            => $rotate,
    "up"                => $up_link,
    "prev"              => $prev_link,
    "next"              => $next_link,
    "full"              => $full,
    "width"             => $width,
    "height"            => $height,
    "size"              => $size,
    "share"             => $share,
    "image"             => $photo->getFullsizeLink($photo->getImageTag(MID_PREFIX)),
    "people"            => $photo->getPeople(),
    "albums"            => $photo->getAlbums($user),
    "categories"        => $photo->getCategories($user),
    "locPulldown"       => place::createPulldown("location_id", $photo->get("location_id")),
    "pgPulldown"        => photographer::createPulldown("photographer_id", $photo->get("photographer_id")),
    "personPulldown"    => person::createPulldown("_person_id[0]"),
    "albumPulldown"     => album::createPulldown("_album_id[0]"),
    "catPulldown"       => category::createPulldown("_category_id[0]", ""),
    "zoomPulldown"      => place::createZoomPulldown($photo->get("mapzoom")),
    "show"              => getvar("_show")
));
echo $tpl;
