<?php
/**
 * A confItemString defines a configuration item that is defined using a user-specified string
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

class confItemSelect extends confItem {
    private $options=array();

    public function addOption($key, $desc) {
        $this->options[$key]=$desc;
    }

    public function addOptions(array $options) {
        foreach($options as $key=>$desc) {
            $this->addOption($key, $desc);
        }
    }
   
    public function getOptions() {
        return $this->options;
    }

    public function checkValue($value) {
        return array_key_exists($value, $this->options);
    }

    public function display() {
        $tpl=new block("confItemSelect", array(
            "label" => $this->getLabel(),
            "name" => $this->getName(),
            "value" => $this->getValue(),
            "desc" => $this->getDesc(),
            "hint" => $this->getHint(),
            "options" => $this->getOptions()

        ));
        return $tpl;
     }



}
