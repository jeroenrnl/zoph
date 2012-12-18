<?php
/**
 * A confItemNumber defines a configuration item that is defined using a user-specified number
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

class confItemNumber extends confItemString {
    
    protected $regex="[0-9]+";
    protected $min=0;
    protected $max=99;
    protected $step=1;

    public function display() {
        if($this->internal) {
            return;
        }
        $tpl=new block("confItemNumber", array(
            "label" => e(translate($this->getLabel(),0)),
            "name" => e($this->getName()),
            "value" => e($this->getValue()),
            "desc" => e(translate($this->getDesc(),0)),
            "hint" => e(translate($this->getHint(),0)),
            "regex" => e($this->regex),
            "size" => (int) $this->size,
            "min" => (float) $this->min,
            "max" => (float) $this->max,
            "step" => (float) $this->step,
            "title" => e(translate($this->title),0),
            "req" => ($this->required ? "required" : "")
        ));
        return $tpl;
    }

    public function checkValue($value) {
        if($this->required && $value=="") {
            return false;
        }

        if((isset($this->min) && ($value < $this->min)) || 
           (isset($this->max) && ($value > $this->max)) ||
           (isset($this->step) && ($value % $this->step !== 0))) {
            return false;
        } else if(isset($this->regex)) {
            return preg_match("/" . $this->regex ."/", $value);
        } else {
            return true;
        }
    }

    public function setBounds($min, $max, $step=1) {
        $this->min=$min;
        $this->max=$max;
        $this->step=$step;
    }


}
