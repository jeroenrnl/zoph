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
    /** @var array GROUP BY clause */
    private $groupby=array();
    /** @var array ORDER clause */
    private $order=array();
    /** @var array count for LIMIT clause */
    private $count=null;
    /** @var array offset for LIMIT clause */
    private $offset=null;

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

    /**
     * Add one or more fields to a query that is calculated using an SQL function
     */
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
    public function addParam(param $param) {
        $this->params[]=$param;
    }

    /**
     * Get array of params
     */
    public function getParams() {
        $params=array();

        if(!is_array($this->params)) {
            return $params;
        }

        foreach($this->params as $param) {
            if(!$param instanceof param) {
                continue;
            }
            $value=$param->getValue();

            if(is_array($value)) {
                $name=$param->getName();
                $type=$param->getType();
                for($n=0; $n<sizeof($value); $n++) {
                    $params[]=new param($name[$n], $value[$n], $type);
                }
            } else {
                $params[]=$param;
            }
        } 
        return $params;
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
     * Add GROUP BY clause to query
     * @param string GRPUP BY to add
     * @return query return the query to enable chaining
     */
    public function addGroupBy($group) {
        $this->groupby[]=$group;
        return $this;
    }

    /** 
     * Get GROUP BY for query
     * @return string GROUP clause
     */
    private function getGroupBy() {
        $groupby=$this->groupby;
        if(is_array($groupby) && sizeof($groupby) > 0) {
            return " GROUP BY " . implode(", ", $groupby);
        }
        return "";
    }

    /** 
     * Add ORDER BY clause to query
     * @param string order to add
     * @example $qry->addOrder("name DESC");
     * @return query return the query to enable chaining
     */
    public function addOrder($order) {
        $this->order[]=$order;
        return $this;
    }

    /** 
     * Get ORDER BY for query
     * @return string ORDER clause
     */
    private function getOrder() {
        $order=$this->order;
        if(is_array($order) && sizeof($order) > 0) {
            return " ORDER BY " . implode(", ", $order);
        }
        return "";
    }

    /** 
     * Add LIMIT clause to query
     * Be warned that count and offset are reversed compared to how they appear
     * in the query!
     * @param int count 
     * @param int offset
     * @example $qry->addLimit(1,3);
     * @return query return the query to enable chaining
     */
    public function addLimit($count, $offset=null) {
        $this->count=$count;
        $this->offset=$offset;
        return $this;
    }

    /** 
     * Get LIMIT clause for query
     * @return string LIMIT clause
     */
    private function getLimit() {
        if(!is_null($this->offset)) {
            $limit=" LIMIT " . (int) $this->offset;
            if (is_null($this->count)) {
                $limit.= ", " . 999999999999;
            } else {
                $limit.=", " . (int) $this->count;
            }
        } else {
            if (!is_null($this->count)) {
                $limit=" LIMIT " . (int) $this->count;
            } else {
                $limit="";
            }
        }
        return $limit;
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
       
        $groupby=trim($this->getGroupBy());
        if(!empty($groupby)) {
            $sql .= " " . $groupby;
        }

        $order=trim($this->getOrder());
        if(!empty($order)) {
            $sql .= " " . $order;
        }

        $limit=trim($this->getLimit());
        if(!empty($limit)) {
            $sql .= " " . $limit;
        }

        return $sql . ";";
    }
}

