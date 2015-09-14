<?php
/**
 * Database parameter class
 * This class is used to define parameters for stored procedures
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
 * The param object contains a parameter that is sent to the database
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class param {

    /** @var string name */
    private $name;
    /** @var string value to store in db */
    private $value;
    /** @var int type of param, should be one of:
        - null
        - PDO::PARAM_BOOL
        - PDO::PARAM_NULL
        - PDO::PARAM_INT
        - PDO::PARAM_STR
        - PDO::PARAM_LOB
    */
    private $type;

    /**
     * Create new param
     * @param string name
     * @param string value
     * @param int type
     */
    public function __construct($name, $value, $type=null) {
        if(is_array($value)) {
            $this->name=array();
            for($n=0; $n<sizeof($value); $n++) {
                $this->name[]=$name . "_" . $n;
            }
        } else {
            $this->name=$name;
        }
        $this->value=$value;
        $this->type=$type;
    }

    /**
     * Get the name of the param
     * @return string name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the value of the param
     * @return string value
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Get the type of the param
     * @return int type
     */
    public function getType() {
        return $this->type;
    }

}

