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
 * @author Jeroen Roos
 * @package Zoph
 */

/**
 * Mapping class.
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class map extends block {

    /** @var div id for map */
    private $map = "map";

    /** @var center lattitude */
    protected $clat;
    /** @var center longitude */
    protected $clon;
    /** @var zoom level */
    protected $zoom;

    /** @var array of tracks to display on this map */
    private $tracks=array();
    /** @var array of markers to display on this map */
    private $markers=array();

    /** @var whether or not this map can be changed. i.e. used to add a marker */
    protected $edit=false;

    /**
     * Create a new map object
     * @param string template to use
     * @param array variables to pass to template
     * @return map new object
     */
    function __construct($template="map", $vars=null) {
        if (!is_array($vars)) {
            $vars=array();
        }
        if (!array_key_exists("id", $vars)) {
            $vars["id"]=$this->map;
        }
        if (!array_key_exists("provider", $vars)) {
            $vars["provider"]=conf::get("maps.provider");
        }
        parent::__construct($template, $vars);

    }

    /**
     * Add a marker to the map
     * @param marker marker to add
     */
    public function addMarker(marker $marker) {
        $this->markers[]=$marker;
    }

    /**
     * Add multiple markers from objects
     * @param array Array of objects to get markers from
     */
    public function addMarkers(array $objs) {
        foreach ($objs as $obj) {
            $marker=$obj->getMarker();
            if ($marker instanceof marker) {
                $this->addMarker($marker);
            }
        }
    }

    /**
     * Get marker from object
     * @param photo|place Object to get marker from
     * @param string Icon to use
     * @return marker created marker.
     * @todo A "mapable" interface should be created to make sure
             only certain objects can get passed to this function.
     */
    public static function getMarkerFromObj($obj, $icon) {
        $lat=$obj->get("lat");
        $lon=$obj->get("lon");
        if ($lat && $lon) {
            $title=$obj->get("title");
            $quicklook=$obj->getQuicklook();
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

    /**
     * Checks whether this maps has markers
     * @return bool
     */
    public function hasMarkers() {
        return !empty($this->markers);
    }

    /**
     * Add a track
     * @param track
     */
    public function addTrack(track $track) {
        $this->tracks[]=$track;
    }

    /**
     * Get tracks
     * @return Array tracks
     */
    public function getTracks() {
        return $this->tracks;
    }

    /**
     * Checks whether this maps has tracks
     * @return bool
     */
    public function hasTracks() {
        return !empty($this->tracks);
    }

    /**
     * Set center and zoom
     * This sets the center point and zoom level for the map
     * @param float latitude
     * @param float longitude
     * @param int zoom level
     */
    public function setCenterAndZoom($lat, $lon, $zoom) {
        $this->clat=(float) $lat;
        $this->clon=(float) $lon;
        $this->zoom=(int) $zoom;
    }

    /**
     * Set center and zoom from object
     * Can take a location object and determine center and zoom from there
     * it can also take a photo object to determine c&s.
     * If a photo object does not have c&z, it will see if the photo has
     * a location set, and determine it from there.
     * If a location does not have c&z, it can go up in the tree until
     * it find an ancestor with c&z set.
     * @param photo|place object to get location from
     * @todo mapable interface should be created
     */
    public function setCenterAndZoomFromObj($obj) {
        $lat=$obj->get("lat");
        $lon=$obj->get("lon");
        $zoom=$obj->get("mapzoom");
        if (!$lat && !$lon) {
            if ($obj instanceof photo && $obj->location instanceof place) {
                $lat=$obj->location->get("lat");
                $lon=$obj->location->get("lon");
                $zoom=$obj->location->get("mapzoom");
            } else if ($obj instanceof place) {
                foreach ($obj->get_ancestors() as $parent) {
                    $lat=$parent->get("lat");
                    $lon=$parent->get("lon");
                    $zoom=$parent->get("mapzoom");
                    if ($lat && $lon) {
                        break;
                    }
                }
            }
        }
        if (!$lat) { $lat=0; }
        if (!$lon) { $lon=0; }
        if (!$zoom) { $zoom=2; }
        $this->setCenterAndZoom($lat, $lon, $zoom);
    }

    /**
     * Set whether or not this map can be changed
     * (used to add markers to a place or photo)
     * @param bool
     */
    public function setEditable($edit=true) {
        $this->edit=(bool) $edit;
    }

}

