<?php
/**
 * Database query class
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
 * The query object is used to create queries
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class query {

    /** @var string db table to query */
    private $table;
    /** @var string alias of db table to query */
    private $alias;
    /** @var array fields to query */
    private $fields=null;
    /** @var array parameters for prepared queries */
    private $params=null;
    /** @var array JOIN statements to add to this query */
    private $joins=null;
    /** @var string WHERE clause */
    private $clause=null;

    /**
     * Create new query
     * @param string Table to query
     * @param array Fields to query
     */
    public function __construct($table, array $fields=null) {
        if(is_array($table)) {
            $tbl=reset($table);
            $this->alias=key($table);
            $table=$tbl;
        }
        $table=db::getPrefix() . $table;
        if(is_array($fields)) {
            foreach ($fields as $field) {
                $this->fields[]=$table . "." . $field;
            }
        }
        $this->table=$table;

    }

    public function addFunction(array $functions) {
        foreach($functions as $alias => $function) {
            $this->fields[]=$function . " AS " . $alias;
        }
    }

    /**
     * Add a parameter for a prepared query
     * @param string Parameter name
     * @param string Parameter value
     */
    public function addParam($param, $value) {
        $this->params[$param]=$value;
    }

    /**
     * Get array of params
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Add a WHERE clause to the query
     * @param clause WHERE clause
     * @return query return the query to enable chaining
     */
    public function where(clause $clause) {
        $this->clause=$clause;
        $this->params=array_merge( (array) $this->params, (array) $clause->getParams());
        return $this;
    }

    /**
     * Add a JOIN clause to the query
     * @param array fields to add to the query
     * @param string table to join
     * @param string ON clause
     * @param string join type
     * @return query return the query to enable chaining
     */
    public function join(array $fields, $table, $on, $jointype="INNER") {
        if(is_array($table)) {
            $tbl=reset($table);
            $as=key($table);
            $table=$tbl . " AS " . $as;
        }

        $table=db::getPrefix() . $table;
        
        if (!in_array($jointype, array("INNER", "LEFT", "RIGHT"))) {
            throw new DatabaseException("Unknown JOIN type");
        }
        $this->joins[]=$jointype . " JOIN " . $table . " ON " . $on;
        foreach ($fields as $field) {
            $this->fields[]=$table . "." . $field;
        }
        return $this;
    }

    /**
     * Create query
     * @return string SQL query
     */
    public function __toString() {
        $sql = "SELECT ";

        if(is_array($this->fields)) {
            $sql.=implode(", ", $this->fields);
        } else {
            $sql.="*";
        }

        $sql .= " FROM " . $this->table;

        if(isset($this->alias)) {
            $sql.=" AS " . $this->alias;
        }

        if(is_array($this->joins)) {
            $sql.=" " . implode(" ", $this->joins);
        }

        if($this->clause instanceof clause) {
            $sql .= " WHERE " . $this->clause;
        }

        return $sql . ";";
    }
}

