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
     * This will select a subset of photos containing of the first x, last x and or random x photos
     * from the subset. This is used to give the user a preview of what is going to be geotagged.
     * @param array subset array that can contain "first", "last" and/or "random"
     * @param int number of each to select
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
     * @param request web request
     */
    public static function createFromRequest(request $request) {
        return static::createFromVars($request->getRequestVarsClean());
    }

    /**
     * Create a new photo\collection from request vars
     * @param array http request vars
     */
    public static function createFromVars(array $vars) {
        $search=new search($vars);
        $photos=photo::getRecordsFromQuery($search->getQuery());
        return static::createFromArray($photos, true);
    }

}
