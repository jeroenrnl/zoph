<?php
/**
 * Generic collection class
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

namespace generic;

use ArrayIterator;

/**
 * Collection class
 *
 * @author Jeroen Roos
 * @package Zoph
 */
abstract class collection implements \ArrayAccess, \IteratorAggregate {
    /** @var array of items in this collection */
    protected $items=array();

    /**
     * Check if item exists
     * For ArrayAccess interface
     * @param string offset
     * @return bool whether or not key $off exists in items array
     */
    public function offsetExists($off) {
        return isset($this->items[$off]);
    }

    /**
     * Return item
     * For ArrayAccess interface
     * @param string offset
     * @return conf\item
     */
    public function offsetGet($off) {
        return $this->items[$off];
    }

    /**
     * Add item
     * For ArrayAccess interface
     * @param string offset
     * @param string value
     */
    public function offsetSet($off, $value) {
        if (!is_null($off)) {
            $this->items[$off]=$value;
        } else {
            $this->items[]=$value;
        }
    }

    /**
     * Unset item (remove)
     * For ArrayAccess interface
     * @param string offset
     */
    public function offsetUnset($off) {
        unset($this->items[$off]);
    }

    /**
     * For IteratorAggregate interface
     * allow us to do foreach () on this object
     */
    public function getIterator() {
        return new ArrayIterator($this->items);
    }
}
