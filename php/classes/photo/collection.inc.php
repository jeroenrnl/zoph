<?php
/**
 * A photo\collection is a collection of photos (@see photo).
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

namespace photo;

use photo;

use user;

use web\request;

/**
 * Collection of photo objects
 * @package Zoph
 * @author Jeroen Roos
 */
class collection extends \generic\collection {

    /**
     * Remove all photos that have no valid timezone
     * @todo this is now a wrapper around the static photo::removePhotosWithNoValidTZ() function
     *       once all references to that function have been moved to this, the implementation
     *       should move as well
     */
    public function removeNoValidTZ() {
        $this->items=photo::removePhotosWithNoValidTZ($this->items);
        return $this;
    }

    /**
     * Remove all photos that have lat/lon set
     * @todo this is now a wrapper around the static photo::removePhotosWithLatLon() function
     *       once all references to that function have been moved to this, the implementation
     *       should move as well
     */
    public function removeWithLatLon() {
        $this->items=photo::removePhotosWithLatLon($this->items);
        return $this;
    }

    /**
     * Get a subset of photos to do geotagging test on
     */
    public function getSubsetForGeotagging(array $subset, $count) {
        $begin=0;

        $max=count($this);

        $count = min($max, $count);
        $return = new self;
        if (in_array("first", $subset)) {
            $first=$this->subset(0, $count);
            $max=$max-$count;
            $begin=$count;
            $return = $first;
        }
        if (in_array("last", $subset)) {
            $last=$this->subset(-$count);
            $max=$max-$count;
            $return = $return->merge($last);
        }

        if (in_array("random", $subset) && ($max > 0)) {
            $center=$this->subset($begin, $max);

            $max=count($center);

            if ($max!=0) {
                $random = $center->random($count);
                $return = $return->merge($random);
            }
        }


        return $return->renumber();
    }
    /**
     * Create a new photo\collection from request
     * for now, this is just a wrapper around the get_photos() global function
     * but this will change soon
     */
    public static function createFromRequest(request $request) {
        $photos=array();
        get_photos($request->getRequestVarsClean(), 0, 999999, $photos, user::getCurrent());

        $collection = new self;
        foreach ($photos as $photo) {
            $collection[$photo->getId()]=$photo;
        }
        return $collection;
    }

    /**
     * Create a new photo\collection from request
     * for now, this is just a wrapper around the get_photos() global function
     * but this will change soon
     * for now, this function is very similar to createFromRequest(), eventually one
     * of these functions should disappear
     */
    public static function createFromConstraints($constraints) {
        $photos=array();
        get_photos($constraints, 0, 999999, $photos, user::getCurrent());

        $collection = new self;
        foreach ($photos as $photo) {
            $collection[$photo->getId()]=$photo;
        }
        return $collection;
    }
}
