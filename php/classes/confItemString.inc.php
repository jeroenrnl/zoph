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

class confItemString extends confItem {
    
    protected $regex=".+";
    protected $title="";
    protected $size=30;

    public function display() {
        $tpl=new block("confItemString", array(
            "label" => e($this->getLabel()),
            "name" => e($this->getName()),
            "value" => e($this->getValue()),
            "desc" => e($this->getDesc()),
            "hint" => e($this->getHint()),
            "regex" => e($this->regex),
            "size" => (int) $this->size,
            "title" => e($this->title)
        ));
        return $tpl;
    }

    public function setRegex($regex) {
        $this->regex=$regex;
    }

    public function setTitle($title) {
        $this->title=$title;
    }

    public function checkValue($value) {
        if(isset($this->regex)) {
            return preg_match("/" . $this->regex ."/", $value);
        } else {
            return true;
        }
    }

    public function setSize($size) {
        $this->size=(int) $size;
    }


}
