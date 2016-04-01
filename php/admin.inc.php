<?php

/**
 * Functions used in the admin page
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

/**
 * This is a class to generate the admin page
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class admin {
    public $name;
    public $url;
    public $desc;
    public $icon;
    private static $pages=array();

    /**
     * Create a new entry in the admin page
     * @param string Name
     * @param string Description
     * @param string URL to point to
     * @param string Icon name (only <filename>.png, no path)
     */
    function __construct($name, $desc, $url, $icon) {
        $this->name=$name;
        $this->url=$url;
        $this->desc=$desc;
        $this->icon=template::getImage("icons/" . $icon);
    }

    /**
     * Get an array of all entries in the admin page
     */
    public static function getArray() {
        if (empty(static::$pages)) {
            static::createArray();
        }
        return static::$pages;
    }

    /**
     * Fill the static array containing the entries for the admin page
     */
    private static function createArray() {
        static::$pages=array(
            new admin("users", "create or modify user accounts", "users.php", "users.png"),
            new admin("groups", "create or modify user groups", "groups.php", "groups.png"),
            new admin("pages", "create or modify zoph pages", "pages.php", "pages.png"),
            new admin("pagesets", "create or modify pagesets", "pagesets.php", "pagesets.png"),
            new admin("tracks", "create or modify GPS tracks", "tracks.php", "tracks.png"),
            new admin("config", "modify configuration items", "config.php", "configure.png")
        );
    }
}

