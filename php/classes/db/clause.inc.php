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

namespace db;

/**
 * The clause object is used to build WHERE-clauses
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class clause {
    /** @var string contains the WHERE clause */
    private $clause;
    /** @var array contains any subclauses */
    private $subclauses;

    /**
     * Create a new WHERE clause
     * @param string clause to be created
     */
    public function __construct($clause) {
        $this->clause=$clause;
    }

    /**
     * Add a subclause with AND conjunction
     * @param clause subclause to be added
     */
    public function addAnd(clause $clause) {
        $this->subclauses[]=array(
            "conj" => "AND",
            "subc" => $clause
        );
        return $this;
    }

    /**
     * Add a subclause with OR conjunction
     * @param clause subclause to be added
     */
    public function addOr(clause $clause) {
        $this->subclauses[]=array(
            "conj" => "OR",
            "subc" => $clause
        );

        return $this;
    }

    /**
     * Add a subclause with NOT conjunction
     * @param clause subclause to be added
     */
    public function addNot(clause $clause) {
        $this->subclauses[]=array(
            "conj" => "NOT",
            "subc" => $clause
        );

        return $this;
    }

    /**
     * Create a WHERE ... IN (..., ..., ...) clause
     * @param string variable
     * @param param parameters
     * @return clause WHERE clause
     */
    public static function InClause($var, param $param) {
        return new self($var . " IN (" . implode(", ", $param->getName()) . ")");
    }

    /**
     * Create a WHERE ... IN (SELECT ...) clause
     * @param string variable
     * @param select subquery
     * @return clause WHERE clause
     */

    public static function inSubQry($var, select $subqry) {
        return new self($var . " IN (" . rtrim($subqry, ";") . ")");
    }

    /**
     * Create a WHERE ... NOT IN (..., ..., ...) clause
     * @param string variable
     * @param param parameters
     * @return clause WHERE clause
     */
    public static function NotInClause($var, param $param) {
        return new self($var . " NOT IN (" . implode(", ", $param->getName()) . ")");
    }

    /**
     * Build the clause
     * @return string clause
     */
    public function __toString() {
        $sql="(" . $this->clause . ")";

        if (is_array($this->subclauses)) {
            foreach ($this->subclauses as $subclause) {
                $conj=$subclause["conj"];
                $subc=$subclause["subc"];
                $sql.= " " . $conj . " (" . $subc . ")";
            }
        }
        return $sql;
    }
}

