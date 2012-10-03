<?php
/**
 * A confGroup is groups several configurationitems (@see confItem) together.
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
 */

class confGroup implements ArrayAccess, IteratorAggregate {

    private $desc;
    private $name;
    private $items=array();


    function __construct() {

    }

    public function setName($name) {
        $this->name=$name;
    }

    public function setDesc($desc) {
        $this->desc=$desc;
    }

    public function getName() {
        return $this->name;
    }

    public function getDesc() {
        return $this->desc;
    }


    public function offsetExists($off) {
        return isset($this->items[$off]);
    }

    public function offsetGet($off) {
        return $this->items[$off];
    }

    public function offsetSet($off, $value) {
        if(is_null($off)) {
            if($value instanceof confItem) {
                $off=$value->getName();
            }
        }
        if(!is_null($off)) {
            $this->items[$off]=$value;
        } else {
            $this->items[]=$value;
        }
    }

    public function offsetUnset($off) {
        unset($this->items[$off]);
    }

    /**
     * For IteratorAggregate interface
     * allow us to do foreach() on this object
     */
    public function getIterator() {
        return new ArrayIterator($this->items);
    }

    private function readFromDB() {

    }

    public function display() {
        $tpl=new block("confGroup", array(
            "title" => $this->getName(),
            "desc"  => $this->getDesc(),
            "items" => $this->items
        ));
        return $tpl;
    }


}
