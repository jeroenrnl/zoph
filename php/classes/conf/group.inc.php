<?php
/**
 * A conf\group groups several configuration items (@see conf\item) together.
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

namespace conf;

use conf\item\item;
use template\block;

/**
 * Group of @see conf\Item objects
 * @package Zoph
 * @author Jeroen Roos
 */
class group {
    /** @var string Name of group */
    private $name;
    /** @var string Label */
    private $label;
    /** @var string Description */
    private $desc;
    /** @var collection Holds collection of configuration items */
    private $collection;

    public function __construct(collection $collection) {
        $this->collection=$collection;
    }

    /**
     * Set the name of the group
     * @param string Name
     */
    public function setName($name) {
        $this->name=$name;
    }

    /**
     * Set the description of the group
     * @param string Description
     */
    public function setDesc($desc) {
        $this->desc=$desc;
    }

    /**
     * Set the label of the group
     * @param string Label
     */
    public function setLabel($label) {
        $this->label=$label;
    }

    /**
     * Get name
     * @return string Name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get description
     * @return string Description
     */
    public function getDesc() {
        return $this->desc;
    }

    /**
     * Get label
     * @return string Label
     */
    public function getLabel() {
        return $this->label;
    }

    public function getItems() {
        return $this->collection;
    }

    /**
     * Display group
     * @return block template block
     */
    public function display() {
        return new block("confGroup", array(
            "title" => translate($this->getLabel(), 0),
            "desc"  => translate($this->getDesc(), 0),
            "items" => $this->getItems()
        ));
    }
}
