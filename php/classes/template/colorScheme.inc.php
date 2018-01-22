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

namespace template;

/**
 * A class corresponding to the color_schemes table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class colorScheme extends \zophTable {

    /** @var string The name of the database table */
    protected static $tableName="color_schemes";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("color_scheme_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("name");
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="color_schemes.php?color_scheme_id=";

    /** @var color_scheme the currently loaded scheme */
    private static $current=null;

   /**
    * Update the color scheme in the db
    */
    public function update() {
        foreach ($this->fields as $field => $value) {
            $this->set($field, str_replace("#", "", $value));
        }
        parent::update();
    }

    /**
     * Get color from current color scheme
     * or fall back to default
     * @param string Name of color to retrieve
     * @return string #xxxxxx HTML color code
     */
    public static function getColor($color) {
        if (!is_null(static::$current)) {
            return "#" . static::$current->get($color);
        } else {
            return static::getDefault($color);
        }
    }

    /**
     * Get all colours from the current schem
     * @return array of name => value pairs
     */
    public function getColors() {
        $this->lookup();
        $colors=array();
        foreach ($this->fields as $field => $value) {
            if ($this->isKey($field) || $field=="name") {
                continue;
            }
            $colors[$field]=$this->fields[$field];
        }
        return $colors;
    }

    /**
     * Define a default for each color
     * for now, this is a fallback for whenever no color scheme has been loaded,
     * e.g. when the user is not logged in yet. Eventually, it will be possible
     * to define a "default" color scheme, and then this will only be used in
     * a worst case fall back (for example when an admin deletes *all* color
     * schemes.
     * @param string Name of color to retrieve
     * @return array of name => #xxxxxx HTML color code pairs
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

        if (array_key_exists($color, $cs)) {
            return $cs[$color];
        } else {
            throw new \Exception("Undefined Color: " . e($color));
        }
    }

    /**
     * Set current color scheme
     * @param colorScheme the color scheme to use
     */
    public static function setCurrent(colorScheme $cs) {
        static::$current=$cs;
    }
}
?>
