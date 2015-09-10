<?php
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

namespace zophCode;

/**
 * zophcode tags
 * @author Jeroen Roos
 * @package Zoph
 */
class tag {
    /** @var string The tag in zophCode, without [ ] */
    private $find;
    /** @var string The tag in HTML without < > */
    private $replace;
    /** @var string How to check the parameter */
    private $regexp;
    /** @var string How to translate parameter */
    private $param;
    /** @var bool True if this tags needs closing, false if it does not */
    private $needsClosing=true;
    /** @var bool Whether or not this is a closing tag */
    private $isClosing=false;
    /** @var array List of allowed tags */
    private static $allowed=array();
    /** @var string Value of the parameter */
    private $paramValue=null;

    /** @var array List of known tags */
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
     * Determines whether this tag can be used
     */
    public function isAllowed() {
        return in_array($this->find, static::$allowed);
    }

    /**
     * Returns the "find" string
     * This is the zophCode tag
     */
    public function getFind() {
        return $this->find;
    }

    /**
     * Returns the "replace" string
     * This is the HTML tag
     */
    public function __toString() {
        if ($this->isClosing()) {
            return "</" . $this->replace . ">";
        } else {
            return "<" . $this->replace . $this->getParam() . ">";
        }
    }

    public function needsClosing() {
        return $this->needsClosing;
    }

    public function isClosing() {
        return $this->isClosing;
    }

    public function setClosing($closing=true) {
        $this->isClosing=$closing;
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
     * Fill the array of allowed tags
     * @param array Array of allowed tags
     */
    public static function setAllowed(array $allowed=null) {
        static::$allowed=array();
        if ($allowed) {
            static::$allowed=$allowed;
        } else {
            foreach (static::getArray() as $tag) {
                static::$allowed[]=$tag->find;
            }
        }
    }

    /**
     * Create tag object from a string
     * @param string Tag [...], [/...], [...=...]
     * @return tag found tag
     */
    public static function getFromString($string) {
        // strip off the [ and ]
        $string=substr($string, 1, -1);

        $newtag = explode("=", $string);
        $tag=$newtag[0];
        if (isset($newtag[1])) {
            $param=$newtag[1];
        }

        $closing=false;
        if (substr($tag, 0, 1) == "/") {
            $closing=true;
            $tag=substr($tag, 1);
        }

        // Check if tag is a valid tag.
        foreach (static::getArray() as $newtag) {
            if ($newtag->find == $tag) {
                $tag=clone $newtag;
                $tag->setClosing($closing);
                if (isset($param)) {
                    $tag->setParamValue($param);
                }
                if ($tag->isClosing() && !$tag->close) {
                    // This is a closing tag for a tag that is not supposed to be closed
                    // such as [br], we will just ignore it.
                    $tag=null;
                }
                return $tag;
            }
        }
    }

    /**
     * Set value of parameter
     */
    private function setParamValue($value) {
        // params in zophCode do not have spaces, so we cut off at the first space
        list($value)=explode(" ", $value, 2);
        $this->paramValue=$value;
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
    private function getParam() {
        if (!empty($this->param) && $this->checkParam($this->paramValue)) {
            return " " . str_replace("[param]", $this->paramValue, $this->param);
        }
    }
}
?>
