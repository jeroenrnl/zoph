<?php

/*
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
 */
class admin {
    var $name;
    var $url;
    var $desc;
    var $icon;
    private static $pages=array();

    function __construct($name, $desc, $url, $icon) {
        $this->name=$name;
        $this->url=$url;
        $this->desc=$desc;
        $this->icon=$icon;
    }

    public static function getArray() {
        if(empty(self::$pages)) {
            self::createArray();
        }
        return self::$pages;
    }

    private static function createArray() {
        self::$pages=array(
            new admin("users", "create or modify user accounts", "users.php", "users.png"),
            new admin("groups", "create or modify user groups", "groups.php", "groups.png"),
            new admin("pages", "create or modify zoph pages", "pages.php", "pages.png"),
            new admin("pagesets", "create or modify pagesets", "pagesets.php", "pagesets.png"),
            new admin("tracks", "create or modify GPS tracks", "tracks.php", "tracks.png")
        );
    }

    function tohtml() {
        $html="<li>\n";
        $html.="  <a href='" . $this->url . "'>\n";
        $html.="    <img src='images/icons/" . ICONSET . "/" . 
                        $this->icon . "'>\n";
        $html.="    <br>" . translate($this->name) . "\n"; 
        $html.="  </a>\n";
        $html.="</li>";

        return $html;
    }
}

function get_admin_page($adminpage) {
    $html="<ul class='admin'>";
    foreach ($adminpage as $admin) {
        $html.=$admin->tohtml();
    }
    $html.="</ul><br>";
    return $html;
}

