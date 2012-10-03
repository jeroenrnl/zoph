<?php
/**
 * A confItem defines a configuration item
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

abstract class confItem extends zophTable {

    protected $label;
    protected $desc;
    protected $default;
    protected $hint;
    public $fields=array();

    function __construct($id = 0) {
        $this->keepKeys=true;
        if($id === 0 || preg_match("/^[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/", $id)) {
            parent::__construct("conf", array("conf_id"), array(""));
            $this->set("conf_id", $id);

        } else {
            log::msg("Illegal configuration id", log::FATAL, log::VARS);
        }

    }

    final public function update() {
        $sql="SELECT COUNT(conf_id) FROM " . DB_PREFIX . "conf WHERE conf_id=";
        $sql.="\"" . escape_string($this->fields["conf_id"]) . "\"";

        if(self::getCountFromQuery($sql) > 0) {
            parent::update();
        } else {
            parent::insert();
        }

    }
    
    final public function getName() {
        return $this->fields["conf_id"];
    }

    
    final public function getLabel() {
        return $this->label;
    }

    final public function getDesc() {
        return $this->desc;
    }
    
    final public function getValue() {
        if(!isset($this->fields["value"]) || $this->fields["value"]===null) {
            return $this->getDefault();
        } else {
            return $this->fields["value"];
        }
    }
    
    public function setValue($value) {
        if($this->checkValue($value)) {
            $this->fields["value"]=$value;
        } else {
            throw new ConfigurationException("Configuration value for " . $this->getName() . " is illegal");
        }
    }
    
    final public function getDefault() {
        return $this->default;
    }
    
    final public function getHint() {
        return $this->hint;
    }

    final public function setName($name) {
        $this->fields["conf_id"]=$name;
    }

    final public function setLabel($label) {
        $this->label=$label;
    }

    final public function setDesc($desc) {
        $this->desc=$desc;
    }
    
    final public function setHint($hint) {
        $this->hint=$hint;
    }
    
    final public function setDefault($default) {
        $this->default=$default;
    }

    abstract public function display();
    
    abstract public function checkValue($value);

}
