<?php

/*
 * A class corresponding to the prefs table.  A row of prefs is mapped
 * to a user_id.
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
 */

class prefs extends zophTable {

    private $color_scheme;

    function __construct($id = 0) {
        if($id && !is_numeric($id)) { die("user_id must be numeric"); }
        parent::__construct("prefs", array("user_id"), array(""));
        $this->set("user_id", $id);
        $this->keepKeys = true;
    }

    function lookup_color_scheme($force = 0) {

        // avoid unnecessary lookups
        if ($this->color_scheme && $this->color_scheme->get("name") != null
            && !$force) {

            return $this->color_scheme;
        }

        if ($this->get("color_scheme_id")) {
            $this->color_scheme =
                new color_scheme($this->get("color_scheme_id"));
            $this->color_scheme->lookup();

            // make sure it was actually found
            if ($this->color_scheme->get("name") != null) {
                return $this->color_scheme;
            }
        }

        return 0;
    }

    function load($force = 0) {

        // these are global vars because originally they were set in
        // config.inc.php instead of stored in the db
        global $MAX_PAGER_SIZE;
        global $RANDOM_PHOTO_MIN_RATING;
        global $TOP_N;
        global $SLIDESHOW_TIME;
        global $FULLSIZE_NEW_WIN;
        
        $MAX_PAGER_SIZE = intval($this->get("max_pager_size"));
        $RANDOM_PHOTO_MIN_RATING = intval($this->get("random_photo_min_rating"));
        $TOP_N = intval($this->get("reports_top_n"));
        $SLIDESHOW_TIME = intval($this->get("slideshow_time"));
        $FULLSIZE_NEW_WIN = $this->get("fullsize_new_win");

        if ($this->lookup_color_scheme($force)) {
            color_scheme::setCurrent($this->color_scheme);
        }
    }
}

?>
