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
abstract class collection implements \ArrayAccess, \IteratorAggregate, \Countable {
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

    /**
     * For Countable interface
     * return size of this collection
     */
    public function count() {
        return count($this->items);
    }

    /**
     * Return a subset of this collection as a new collection
     * @param int start of subset
     * @param int size of subset
     */
    public function subset($start, $count=null) {
        return static::createFromArray(array_slice($this->items, $start, $count, true));
    }

    /**
     * Pop last element off the collection
     * @return last object of the collection
     */
    public function pop() {
        return array_pop($this->items);
    }

    /**
     * Shift first element off the collection
     * @return first object of the collection
     */
    public function shift() {
        return array_shift($this->items);
    }

    /**
     * Get random element(s) from the collection
     */
    public function random($count = 1) {
        $count = min(sizeof($this), $count);
        $rndKeys=(array) array_rand($this->items, $count);
        $rndColl = new static();
        foreach ($rndKeys as $key) {
            $rndColl[$key] = $this[$key];
        }
        return $rndColl;
    }

    /**
     * Merge this collection with other collection(s)
     * @param collection to merge with [, collection to merge with [ , ... ]]
     * return collection
     */
    public function merge(self ...$toMerge) {
        $merged=array();
        array_unshift($toMerge, $this);
        foreach ($toMerge as $collection) {
            $merged=array_merge($merged, $collection->toArray());
        }
        return static::createFromArray($merged);
    }

    /**
     * Renumber the items so that each item has it's key as
     * it's key in the array
     * @param callable alternate function to determine key (default ->getId() )
     * @return collection
     */
    public function renumber(callable $function=null) {
        // Default for callable can only be null
        if (!$function) {
            $function="getId";
        }
        $return = new static;
        foreach ($this->items as $item) {
            $id = call_user_func(array($item, $function));
            $return[$id]=$item;
        }
        return $return;
    }

    /**
     * Turn this collection into an array
     */
    protected function toArray() {
        return $this->items;
    }

    /**
     * Create a new collection from an array
     * @param array Items to put in new collection
     */
    public static function createFromArray(array $items, $withKeys = false) {
        $collection = new static();
        if ($withKeys) {
            foreach ($items as $item) {
                $collection[$item->getId()]=$item;
            }
        } else {
            $collection->items = $items;
        }
        return $collection;
    }
}
