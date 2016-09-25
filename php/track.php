<?php
/**
 * This file is the controller part for the track (geotagging) functions
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
 * @author Jeroen Roos
 * @package Zoph
 */

use conf\conf;

require_once "include.inc.php";

$title="Zoph";
$content="";
$mapping_js="";

if (!$user->isAdmin()) {
    $_action="display";
    if (!$user->canBrowseTracks()) {
        redirect("zoph.php");
    }
}

$track_id = getvar("track_id");
$track = new track($track_id);
if ($track_id) {
    if ($track->lookup()) {
        $title = $track->get("name");
        if (empty($title)) {
            $title=translate("Track");
        }
    } else {
        redirect("tracks.php");
    }
} else {
    redirect("tracks.php");
}

$obj = &$track;

require_once "actions.inc.php";

$tpl=new template("main", array(
    "title"     => $title
));

if ($action == "confirm") {
    $q=new block("question", array(
        "question"  => translate("confirm deletion of this track"),
        "title"     => translate("delete track")
    ));
    $q->addActionlinks(array(
        translate("delete") => "track.php?_action=confirm&amp;track_id=" . $track_id,
        translate("cancel") => "track.php?_action=display&amp;track_id=" . $track->get("track_id"),
    ));
    $tpl->addBlock($q);
} else if ($action == "display") {
    $tpl->addActionlinks(array(
        translate("return") => "tracks.php",
        translate("edit") => "track.php?_action=edit&amp;track_id=" . $track->get("track_id"),
        translate("delete") => "track.php?_action=delete&amp;track_id=" . $track->get("track_id")
    ));
    $dl=new block("definitionlist",array(
        "class" => "display track",
        "dl" => $track->getDisplayArray()
    ));
    $tpl->addBlock($dl);
    if (!is_null(conf::get("maps.provider"))) {
        $map=new map();
        $map->addTrack($track);
        $tpl->addBlock($map);
    }
} else {
    $form=new block("track_form", array(
        "action"    => $action,
        "track_id"  => $track->getId(),
        "name"      => $track->get("name")
    ));
    $tpl->addBlock($form);
}
echo $tpl;
?>
