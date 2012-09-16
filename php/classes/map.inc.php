<?php
/**
 * Map. Create and display a map using the mapstraction library. 
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
 * @author Jason Geiger, Jeroen Roos
 * @package Zoph
 */

/**
 * Mapping class.
 */
class map extends block {

    private $provider = MAPS;
    private $map = "map";

    protected $clat;
    protected $clon;
    protected $zoom;

    private $tracks=array();
    private $markers=array();

    protected $edit=false;

    function __construct($template="map", $vars=null) {
        if(!is_array($vars)) {
            $vars=array();
        }
        if(!array_key_exists("id", $vars)) {
            $vars["id"]=$this->map;
        }
        if(!array_key_exists("provider", $vars)) {
            $vars["provider"]=$this->provider;
        }
        parent::__construct($template, $vars);

    } 

    public function addMarker(marker $marker) {
        $this->markers[]=$marker;
    }

    public function addMarkers(array $objs, user $user) {
        $markers=array();
        foreach($objs as $obj) {
            $marker=$obj->getMarker($user);
            if($marker instanceof marker) {
                $this->addMarker($marker);
            }
        }
    }

    public static function getMarkerFromObj($obj, user $user, $icon) {
        $lat=$obj->get("lat");
        $lon=$obj->get("lon");
        if($lat && $lon) {
            $title=$obj->get("title");
            $quicklook=$obj->get_quicklook($user);
            return new marker($lat, $lon, $icon, $title, $quicklook);
        } else {
            return null;
        }
    }

    /**
     * Get markers for this map 
     */
    public function getMarkers() {
        // if multiple photos are taken in the same place, that place 
        // is multiple times in the array, let's remove doubles:
        $markers=array_unique($this->markers, SORT_REGULAR);
        return $markers;
    }

    public function hasMarkers() {
        return !empty($this->markers);
    }

    public function addTrack(track $track) {
        $this->tracks[]=$track;
    }

    public function getTracks() {
        return $this->tracks;
    }

    public function hasTracks() {
        return !empty($this->tracks);
    }

    public function setCenterAndZoom($lat, $lon, $zoom) {
        $this->clat=(float) $lat;
        $this->clon=(float) $lon;
        $this->zoom=(int) $zoom;
    }

    public function setCenterAndZoomFromObj($obj) {
        $lat=$obj->get("lat");
        $lon=$obj->get("lon");
        $zoom=$obj->get("mapzoom");
        if(!$lat && !$lon) {
            if($obj instanceof photo && $obj->location instanceof place) {
                $lat=$obj->location->get("lat");
                $lon=$obj->location->get("lon");
                $zoom=$obj->location->get("mapzoom");
            } else if ($obj instanceof place) {
                foreach($obj->get_ancestors() as $parent) {
                    $lat=$parent->get("lat");
                    $lon=$parent->get("lon");
                    $zoom=$parent->get("mapzoom");
                    if($lat && $lon) {
                        break;
                    }
                }
            }
        }
        if(!$lat) { $lat=0; }
        if(!$lon) { $lon=0; }
        if(!$zoom) { $zoom=2; }
        $this->setCenterAndZoom($lat, $lon, $zoom);
    }

    public function setEditable($edit=true) {
        $this->edit=(bool) $edit;
    }

}

