<?php
/**
 * Various operations on variables such as escaping
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
 * @author Jason Geiger
 * @author David Baldwin
 */

namespace generic;

/**
 * Variable handling
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class variable {
    /** @var holds value of variable */
    private $value;

    /**
     * Create object
     * @param value to assign
     */
    public function __construct($value) {
        $this->value=$value;
    }

    /**
     * Get value
     */
    public function __toString() {
        return (string) $this->value;
    }

    /**
     * Get value
     */
    public function get() {
        return $this->value;
    }

    /**
     * This function will escape the user input and remove HTML tags
     */
    public function input() {
        $var=$this->value;
        if ($var === "<" || $var === "<=" || $var === ">=" || $var === ">") {
            // Strip tags breaks some searches
            $value=$var;
        } else if (is_array($var)) {
            $value=array();
            foreach ($var as $key => $arrayValue) {
                $keyVar=new variable($key);
                $valueVar=new variable($arrayValue);
                $value[$keyVar->input()]=$valueVar->input();
            }
        } else {
            $value=strip_tags(html_entity_decode($var));
        }
        return $value;
    }

    /**
     * Return escaped output
     * @param array|string value to be escaped
     */
    public function escape($var=null) {
        if (!$var) {
            $var=$this->value;
        }

        if (is_array($var)) {
            $return=array();
            foreach ($var as $key => $arrayValue) {
                $return[static::escape($key)]=static::escape($arrayValue);
            }
        } else {
            $return=htmlspecialchars($var);
            /* Extra escape for a few chars that may cause troubles but are
               not escaped by htmlspecialchars. */
            $return=str_replace(array("<", ">", "\"", "(", ")", "'", "[",  "]", "{", "}", "~", "`"),
                array("&lt;", "&gt;", "&quot;", "&#40;", "&#41;", "&#39;","&#91;", "&#93;", "&#123;",
                  "&#125;", "&#126;", "&#96;"), $return);
        }
        return $return;
    }
}
