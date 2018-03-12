<?php
/**
 * Show overview of tracks
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
 */
require_once "include.inc.php";

use geo\track;

use photo\collection;

use template\block;
use template\template;

$title=translate("Geotag");

$_action=getvar("_action");
$test=getvar("_test");
$map=null;

if (!$user->isAdmin()) {
    if ($user->canBrowseTracks()) {
        $_action="display";
    } else {
        redirect("zoph.php");
    }
} else {
    $vars=$request->getRequestVarsClean();
    $new_vars=update_query_string($vars, "_action", "do_geotag", array("_test", "_testcount"));
    $photos=collection::createFromRequest($request);
    $photoCount = sizeof($photos);
}

if ($_action=="" || $_action=="display") {
    $title = translate("Tracks");
    $tracks=track::getAll();
    if (count($tracks>0)) {
        $content=new block("tracks_table", array(
            "tracks" => $tracks
        ));
    } else {
        $content=new block("message", array(
            "class" => "warning",
            "text" => translate("No tracks found, you should import a GPX file.")
        ));
    }
} else if ($_action=="geotag") {
    if ($photoCount <= 0) {
        $content=new block("message", array(
            "class" => "error",
            "text" => translate("No photos were found matching your search criteria.")
        ));
    } else {
        $hidden=$vars;
        unset($hidden["_off"]);
        $hidden["_action"]="do_geotag";

        $content=new block("geotag_form", array(
            "photoCount"    => $photoCount,
            "hidden"        => $hidden,
            "tracks"        => track::getRecords("track_id")
        ));
    }

} else if ($_action=="do_geotag") {
    $validtz=getvar("_validtz");
    $overwrite=getvar("_overwrite");
    $count=intval(getvar("_testcount"));
    $track_id=null;
    $tracks=getvar("_tracks");
    $maxtime=getvar("_maxtime");
    $interpolate=getvar("_interpolate");
    $int_maxdist=getvar("_int_maxdist");
    $int_maxtime=getvar("_int_maxtime");
    $entity=getvar("_entity");
    $tphotos=array();

    if ($tracks!="all") {
        $track_id=getvar("_track");
        $track=new track($track_id);
        $track->lookup();
    } else {
        $track=null;
    }

    if ($validtz) {
        $photos->removeNoValidTZ();
    }
    if (!$overwrite) {
        $photos->removeWithLatLon();
    }

    $total=count($photos);

    if ($total>0) {
        $photos=$photos->getSubsetForGeotagging((array) $test, $count);

        foreach ($photos as $photo) {
            $point=$photo->getLatLon($track, $maxtime, $interpolate, $int_maxdist,
                $entity, $int_maxtime);
            if ($point instanceof point) {
                $photo->setLatLon($point);
                if (!is_array($test)) {
                    $photo->update();
                }
                $tphotos[]=$photo;
            }
        }
        $tagged=count($tphotos);
        $map=new map();
        $map->addMarkers($tphotos, $user);
    } else {
        $tagged=0;
    }
    $content=new block("tracks_geotag_results", array(
        "count"         => $total,
        "actionlinks"   =>
            array(translate("geotag") => "tracks.php?" . html_entity_decode($new_vars)),
        "test"          => (bool) is_array($test),
        "tagged_count"  => (int) $tagged,
        "total_count"   => (int) $total
    ));
}
$tpl=new template("main", array(
    "title" => $title,
));
if ($content instanceof block) {
    $tpl->addBlock($content);
}
if ($map instanceof block) {
    $tpl->addBlock($map);
}
echo $tpl;
?>
