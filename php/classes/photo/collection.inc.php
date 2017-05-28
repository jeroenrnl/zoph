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

use web\request;
use user;

/**
 * Collection of photo objects
 * @package Zoph
 * @author Jeroen Roos
 */
class collection extends \generic\collection {

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
}
