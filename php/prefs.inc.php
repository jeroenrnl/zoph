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
    /** @var string The name of the database table */
    protected static $table_name="prefs";
    /** @var array List of primary keys */
    protected static $primary_keys=array("user_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array();
    /** @var bool keep keys with insert. In most cases the keys are set by the db with auto_increment */
    protected static $keepKeys = true;
    /** @var string URL for this class */
    protected static $url="prefs.php#";


    private $color_scheme;

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
        if ($this->lookup_color_scheme($force)) {
            color_scheme::setCurrent($this->color_scheme);
        }
    }
}

?>
