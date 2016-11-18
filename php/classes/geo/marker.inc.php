<?php
/**
 * Marker to be displayed on map
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

namespace geo;

/**
 * Class to display markers on a map
 * @author Jeroen Roos
 * @package Zoph
 */
class marker {

    public $lat=0;
    public $lon=0;
    public $icon=0;
    public $title="";
    public $quicklook="";

    public function __construct($lat, $lon, $icon, $title, $quicklook) {
        $this->lat=$lat;
        $this->lon=$lon;
        $this->icon=$icon;
        $this->title=$title;
        $this->quicklook=$quicklook;
    }

    /**
     * Get marker from object
     * @param photo|place Object to get marker from
     * @param string Icon to use
     * @return marker created marker.
     * @todo A "mapable" interface should be created to make sure
             only certain objects can get passed to this function.
     */
    public static function getFromObj($obj, $icon) {
        $lat=$obj->get("lat");
        $lon=$obj->get("lon");
        if ($lat && $lon) {
            $title=$obj->get("title");
            $quicklook=$obj->getQuicklook();
            return new self($lat, $lon, $icon, $title, $quicklook);
        } else {
            return null;
        }
    }

}
