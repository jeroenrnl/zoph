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

require_once("include.inc.php");

$title=translate("Geotag");
$mapping_js="";

$_action=getvar("_action");
$test=getvar("_test");

if (!$user->is_admin()) {
    if($user->get("browse_tracks")) {
        $_action="display";
    } else {
        header("Location: " . add_sid("zoph.php"));
    }
} else {
    $vars=clean_request_vars($request_vars);
    $new_vars=update_query_string($vars, "_action", "do_geotag", array("_test", "_testcount"));
    $photos;
    $totalPhotoCount = get_photos($vars, 0, 999999999, $photos, $user);
    $num_photos=sizeof($photos);
}

if($_action=="" || $_action=="display") {
    $title = translate("Tracks");
    $tracks=track::getAll();
    if(count($tracks>0)) {
        $tracks_table=new template("tracks_table", array(
            "tracks" => $tracks
        ));
        $content=$tracks_table->toString();
    } else {
        $content=translate("No tracks found, you should import a GPX file.");
    }


} else if ($_action=="geotag") {
    if ($num_photos<= 0) {
        $content=translate("No photos were found matching your search criteria.") . "\n";
    } else {
        $hidden=$vars;
        unset($hidden["_off"]);
        $hidden["_action"]="do_geotag";

        $form=new template("geotag_form", array(
            "num_photos"    => $num_photos,
            "hidden"        => $hidden));

        $content=$form->toString();
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

    if($tracks!="all") {
        $track_id=getvar("_track");
        $track=new track($track_id);
        $track->lookup();
    } else {
        $track=null;
    }

    if($validtz) {
        $photos=photo::removePhotosWithNoValidTZ($photos);
    }
    if(!$overwrite) {
        $photos=photo::removePhotosWithLatLon($photos);
    }

    $total=count($photos);

    if($total>0) {
        if(is_array($test)) {
            $photos=photo::getSubset($photos, $test, $count);
        }

        foreach($photos as $photo) {
            $point=$photo->getLatLon($track, $maxtime, $interpolate, $int_maxdist, $entity, $int_maxtime);
            if($point instanceof point) {
                $photo->setLatLon($point);
                if(!is_array($test)) {
                    $photo->update();
                }
                $tphotos[]=$photo;
            }
        }
        $tagged=count($tphotos);
        if($tagged>0) {
            $js="";
            foreach ($tphotos as $photo) {
                $js.=$photo->getMarker($user);
            }
            
            $mapping_js=create_map_js() . $js;
        }
    } else {
        $tagged=0;
    }
    $results=new template("tracks_geotag_results", array(
        "count"         => $total,
        "actionlinks"   => 
            array(translate("geotag") => "tracks.php?" . html_entity_decode($new_vars)),
        "test"          => (bool) is_array($test),
        "tagged_count"  => (int) $tagged,
        "total_count"   => (int) $total
    ));
    $content=$results->toString();
}
$tpl=new template("main", array(
    "title" => $title,
    "content" => $content,
    "mapping_js" => $mapping_js,
    "header_actionlinks" => null,
    "main_actionlinks" => null
));
echo $tpl;
?>
