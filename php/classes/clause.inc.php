<?php
/**
 * Database clause class, to build WHERE-clauses
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
 * The clause object is used to build WHERE-clauses
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class clause {

    private $clause;
    private $params;
    private $subclauses;

    public function __construct($clause, array $params) {
        $this->clause=$clause;
        $this->params=$params;
    }

    public function addParam($param, $value) {
        $this->params[$param]=$value;
    }

    public function addAnd(clause $clause) {
        $this->subclauses[]=array(
            "conj" => "AND",
            "subc" => $clause
        );
        return $this;
    }

    public function addOr(clause $clause) {
        $this->subclauses[]=array(
            "conj" => "OR",
            "subc" => $clause
        );

        return $this;
    }

    public function addNot(clause $clause) {
        $this->subclauses[]=array(
            "conj" => "NOT",
            "subc" => $clause
        );

        return $this;
    }

    public function addIn(clause $clause, query $query) {

        return $this;
    }

    public function __toString() {
        $sql="(" . $this->clause . ")";

        if(is_array($this->subclauses)) {
            foreach($this->subclauses as $subclause) {
                $conj=$subclause["conj"];
                $subc=$subclause["subc"];
                $sql.= " " . $conj . " " . $subc;
            }
        }
        return $sql;
    }
}

