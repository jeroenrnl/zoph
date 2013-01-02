<?php
/**
 * Report class. Eventually everything report-related will move here.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */

/**
 * Report class. Eventually everything report-related will move here.
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class report {
    
    /**
     * Get an array with data about the Zoph database
     * @return array array with data.
     */
    public static function getInfoArray() {
        $album= album::getRoot();
        $category = category::getRoot();

        $size=get_human(get_photo_sizes_sum());
        return array(
            translate("number of photos") => photo::getCount(),
            translate("size of photos") => "$size",
            translate("number of photos in an album") =>
                $album->getTotalPhotoCount(),
            translate("number of categorized photos") =>
                $category->getTotalPhotoCount(),
            translate("number of people") => person::getCount(),
            translate("number of places") => place::getCount()
        );
    }
}

