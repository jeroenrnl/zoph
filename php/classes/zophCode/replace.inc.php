<?php
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

namespace zophCode;

/**
 * Replace problematic code in zophcode with escaped code
 * @author Jeroen Roos
 * @package Zoph
 */
class replace {
    /** @var string to replace */
    public $find;
    /** @var string to replace with */
    public $replace;
    /** @var array List of all replaces */
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
     * Run the replaces on a message
     * @param string Message
     * @return string Message with problematic code changed
     */
    public static function processMessage($msg) {
        $find=array();
        $replace=array();

        foreach (static::getArray() as $repl) {
            array_push($find, "/" . preg_quote($repl->find) . "/");
            array_push($replace, $repl->replace);
        }
        return preg_replace($find, $replace, $msg);
    }

    /**
     * Get an array of all replace objects
     */
    private static function getArray() {
        if (empty(static::$replaces)) {
            static::createArray();
        }
        return static::$replaces;
    }

    /**
     * Fill the static $replaces.
     */
    private static function createArray() {
        // Watch the order of these... putting &amp; at the end of the array
        // will make you end up with things like "&amp;lt;"...
        static::$replaces=array(
            // The first two are needed to revert anti SQL injection-code
            new replace("&#40;", "("),
            new replace("&#41;", ")"),
            new replace("&", "&amp;"),
            new replace("<", "&lt;"),
            new replace(">", "&gt;"),
            new replace("\n", "<br>")
        );
    }
}
?>
