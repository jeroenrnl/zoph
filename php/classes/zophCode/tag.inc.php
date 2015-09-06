<?php
namespace zophCode;

/**
 * This class is a helper class for zophCode
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
 * zophcode tags
 * @author Jeroen Roos
 * @package Zoph
 */
class tag {
    public $find;
    public $replace;
    public $regexp;
    public $param;
    public $close=true;

    private static $tags=array();

    /**
     * Create a new tag object
     *
     * @param string The tag in zophCode, without [ ]
     * @param string The tag in HTML without < >
     * @param string How to check the parameter
     * @param string How to translate parameter
     * @param bool True if this tags needs closure, false if it does not
     * @todo regexp check of param not implemented
     */
    public function __construct($find, $replace, $regexp = null, $param = null, $close=true) {
        $this->find=$find;
        $this->replace=$replace;
        $this->regexp=$regexp;
        $this->param=$param;
        $this->close=$close;
    }

    /**
     * Get an array of defined tags
     */
    public static function getArray() {
        if (empty(static::$tags)) {
            static::createArray();
        }
        return static::$tags;
    }

    /**
     * Fill static $tags
     */
    private static function createArray() {
        static::$tags=array(
            new tag("b", "b"),
            new tag("i", "i"),
            new tag("u", "u"),
            new tag("h1", "h1"),
            new tag("h2", "h2"),
            new tag("h3", "h3"),
            new tag("color", "span", "", "style=\"color: [param];\""),
            new tag("font", "span", "", "style=\"font-family: [param];\""),
            new tag("br", "br", null, null, false),
            new tag("background", "div", "", "class='background' style=\"background: [param];\""),
            new tag("photo", "a", "", "href=\"photo.php?photo_id=[param]\""),
            new tag("album", "a", "", "href=\"album.php?album_id=[param]\""),
            new tag("person", "a", "", "href=\"people.php?person_id=[param]\""),
            new tag("cat", "a", "", "href=\"category.php?category_id=[param]\""),
            new tag("link", "a", "", "href=\"[param]\""),
            new tag("place", "a", "", "href=\"places.php?parent_place_id=[param]\""),
            new tag("thumb", "img", "", "src=\"image.php?photo_id=[param]&type=thumb\"", false),
            new tag("mid", "img", "", "src=\"image.php?photo_id=[param]&type=mid\"", false)
        );
    }

    /**
     * Find a tag by name
     * @param string name
     * @return tag found tag
     */
    public static function getFromName($name) {
        // Check if tag is a valid tag.
        foreach (static::getArray() as $tag) {
            if ($tag->find == $name) {
                return $tag;
            }
        }
    }
    /**
     * Check whether a given value conforms to the requirement
     * @param string Param value to check
     * @todo currently not used
     * @return bool true: validates, false: does not validate
     */
    private function checkParam($value) {
        if (!empty($this->regexp)) {
            return preg_match($this->regexp, $value);
        } else {
            return true;
        }
    }

    /**
     * Insert parameter value into tag
     * @param string value to insert into tag
     * @return string parameter with value inserted in place of [param] placeholder
     */
    public function addParam($value) {
        if ($this->checkParam($value)) {
            return " " . str_replace("[param]", $value, $this->param);
        }
    }
}
?>
