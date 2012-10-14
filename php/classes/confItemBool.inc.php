<?php
/**
 * A confItemBool defines a configuration item that can be true or false
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

/**
 * A confItemBool defines a configuration item that can be true or false
 */
class confItemBool extends confItem {

    /**
     * Set value
     * @param bool value
     */
    public function setValue($value) {
        parent::setValue((bool) $value);
    }

    /** 
     * Check value
     * check if a specific value is legal for this option
     * @param string value
     * @return bool
     */
    public function checkValue($value) {
        return ((bool) $value == $value);
    }

    /**
     * Display this option through template
     * @return block template block
     */
    public function display() {
        $tpl=new block("confItemBool", array(
            "label" => $this->getLabel(),
            "name" => $this->getName(),
            "checked" => $this->getValue() ? "checked" : "",
            "desc" => $this->getDesc(),
            "hint" => $this->getHint(),
        ));
        return $tpl;
     }
}
