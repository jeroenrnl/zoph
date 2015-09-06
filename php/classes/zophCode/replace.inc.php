<?php
namespace zophCode;

/**
 * Replace is a helper class for zophCode
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
 * @author Jeroen Roos
 * @package Zoph
 */


/**
 * Replace problematic code in zophcode with escaped code
 *
 * @todo: this can possibly be integrated in the zophcode class
 * @author Jeroen Roos
 * @package Zoph
 */
class replace {
    public $find;
    public $replace;
    private static $replaces=array();

    /**
     * Create a new replace object
     * @param string to replace
     * @param string to replace with
     */
    private function __construct($find, $replace) {
        $this->find=$find;
        $this->replace=$replace;
    }

    /**
     * Get an array of all replace objects
     */
    public static function getArray() {
        if(empty(self::$replaces)) { 
            self::createArray(); 
        }
        return self::$replaces;
    }
   
    /**
     * Fill the static $replaces.
     */
    private static function createArray() {
        // Watch the order of these... putting &amp; at the end of the array
        // will make you end up with things like "&amp;lt;"...
        self::$replaces=array(
            new replace("&#40;", "("),  # Needed to revert anti
            new replace("&#41;", ")"),  # SQL injection-code
            new replace("&", "&amp;"),
            new replace("<", "&lt;"),
            new replace(">", "&gt;"),
            new replace("\n", "<br>")
        );
    }
}
?>
