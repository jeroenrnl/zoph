<?php

/**
 * A class corresponding to the color_themes table.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 */

/**
 * A class corresponding to the color_themes table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class color_scheme extends zophTable {

    private static $current=null;
    
    /**
     * Create a color_scheme object
     * @param int color_scheme id
     */
    public function __construct($id = 0) {
        if($id && !is_numeric($id)) { die("color_scheme_id must be numeric"); }
        parent::__construct("color_schemes", array("color_scheme_id"), array("name"));
        $this->set("color_scheme_id", $id);
    }

    /**
     * Get color from current color scheme
     * or fall back to default
     * @param string Name of color to retrieve
     * @param string #xxxxxx HTML color code
     */
    public static function getColor($color) {
        if(!is_null(self::$current)) {
            return "#" . self::$current->get($color);
        } else {
            return self::getDefault($color);
        }
    }

    /**
     * Define a default for each color
     * for now, this is a fallback for whenever no color scheme has been loaded,
     * e.g. when the user is not logged in yet. Eventually, it will be possible
     * to define a "default" color scheme, and then this will only be used in 
     * a worst case fall back (for example when an admin deletes *all* color 
     * schemes.
     * @param string Name of color to retrieve
     * @param string #xxxxxx HTML color code
     * @throws Exception
     * @todo Maybe a custom Exception should be created.
     */
    private static function getDefault($color) {
        $cs=array(
            "page_bg_color"             => "#ffffff",
            "text_color"                => "#000000", 
            "link_color"                => "#111111", 
            "vlink_color"               => "#444444",
            "table_bg_color"            => "#ffffff",
            "table_border_color"        => "#000000",
            "breadcrumb_bg_color"       => "#ffffff",
            "title_bg_color"            => "#f0f0f0", 
            "title_font_color"          => "#000000",
            "tab_bg_color"              => "#000000",
            "tab_font_color"            => "#ffffff",
            "selected_tab_bg_color"     => "#c0c0c0",
            "selected_tab_font_color"   => "#000000" 
        );

        if(array_key_exists($color, $cs)) {
            return $cs[$color];
        } else {
            throw new Exception("Undefined Color: " . e($color));
        }
    }

    /**
     * Set current color scheme
     * @param color_scheme the color scheme to use
     */
    public static function setCurrent(color_scheme $cs) {
        self::$current=$cs;
    }
}
?>
