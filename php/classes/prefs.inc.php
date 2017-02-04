<?php
/**
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
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */

use template\colorScheme;

/**
 * A class representing a set of user preferences
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class prefs extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="prefs";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("user_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array();
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = true;
    /** @var string URL for this class */
    protected static $url="prefs.php#";


    private $colorScheme;

    private function lookupColorScheme($force = 0) {

        // avoid unnecessary lookups
        if ($this->colorScheme && $this->colorScheme->get("name") != null && !$force) {
            return $this->colorScheme;
        }

        if ($this->get("color_scheme_id")) {
            $this->colorScheme = new colorScheme($this->get("color_scheme_id"));
            $this->colorScheme->lookup();

            // make sure it was actually found
            if ($this->colorScheme->get("name") != null) {
                return $this->colorScheme;
            }
        }

        return 0;
    }

    public function load($force = 0) {
        if ($this->lookupColorScheme($force)) {
            colorScheme::setCurrent($this->colorScheme);
        }
    }
}

?>
