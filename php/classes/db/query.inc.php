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

namespace db;
use \PDO;

/**
 * The query object is used to create queries
 *
 * @package Zoph
 * @author Jeroen Roos
 */
abstract class query {

    /** @var string db table to query */
    protected $table;
    /** @var tables in this query */
    protected $tables=array();
    /** @var string alias of db table to query */
    protected $alias;
    /** @var array fields to query */
    protected $fields=null;
    /** @var array parameters for prepared queries */
    protected $params=null;
    /** @var string WHERE clause */
    protected $where=null;
    /** @var string HAVING clause */
    protected $having=null;
    /** @var array ORDER clause */
    protected $order=array();
    /** @var array count for LIMIT clause */
    protected $count=null;
    /** @var array offset for LIMIT clause */
    protected $offset=null;

    /**
     * Create new query
     * @param string Table to query
     */
    public function __construct($table) {
        if (is_array($table)) {
            $tbl=reset($table);
            $alias=key($table);
            if (!is_numeric($alias)) {
                $this->alias=$alias;
            }
            $table=$tbl;

            $this->tables[$alias]=$tbl;
        } else {
            $this->tables[$table]=$table;
        }
        $table=db::getPrefix() . $table;
        $this->table=$table;
    }

    /**
     * Add one or more fields to a query
     * @param array list of fields [ "alias" => "field"]
     * @return query
     */
    public function addFields(array $fields, $distinct=false) {
        $table=$this->table;
        foreach ($fields as $alias => $field) {

            if (!isset($this->table) || strpos($field, ".")) {
                $field=$field;
            } else if (!isset($this->alias)) {
                $field=$table . "." . $field;
            } else {
                $field=$this->alias . "." . $field;
            }

            if ($distinct) {
                $field="DISTINCT " . $field;
            }

            if (!is_numeric($alias)) {
                $field .= " AS " . $alias;
            }

            $this->fields[]=$field;

        }
    }

    /**
     * Add one or more fields to a query that is calculated using an SQL function
     */
    public function addFunction(array $functions) {
        foreach ($functions as $alias => $function) {
            $this->fields[]=$function . " AS " . $alias;
        }
    }

    /**
     * Add a parameter for a prepared query
     * @param param parameter object
     */
    public function addParam(param $param) {
        $this->params[]=$param;
    }

    /**
     * Add parameters for a prepared query
     * @param array parameters
     */
    public function addParams(array $params) {
        foreach ($params as $param) {
            $this->addParam($param);
        }
    }

    /**
     * Get array of params
     */
    public function getParams() {
        $params=array();

        if (!is_array($this->params)) {
            return $params;
        }

        foreach ($this->params as $param) {
            if (!$param instanceof param) {
                continue;
            }
            $value=$param->getValue();

            if (is_array($value)) {
                $value=array_values($value);
                $name=array_values($param->getName());
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
        $this->where=$clause;
        return $this;
    }

    /**
     * Add a subclause to the WHERE, or set the clause as a WHERE if it is not yet set
     * @param clause clause to add
     * @param string AND|OR
     * @return query return the query to enable chaining
     */
    public function addClause(clause $clause, $conj="AND") {
        if ($this->where instanceof clause) {
            if (strtoupper($conj) == "AND") {
                $this->where->addAnd($clause);
            } else if (strtoupper($conj) == "OR") {
                $this->where->addOr($clause);
            } else {
                throw new DatabaseException("Unknown conjunction: " . e($conj));
            }
        } else {
            $this->where($clause);
        }
        return $this;
    }

    /**
     * Add a HAVING clause to the query
     * @param clause HAVING clause
     * @return query return the query to enable chaining
     */
    public function having(clause $hclause) {
        $this->having=$hclause;
        return $this;
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
    protected function getOrder() {
        $order=$this->order;
        if (is_array($order) && sizeof($order) > 0) {
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
    protected function getLimit() {
        if (!is_null($this->offset)) {
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
     * Check if a table is already included in this query
     * @param string query;
     */
    public function hasTable($table) {
        return in_array($table, $this->tables);
    }

    /**
     * Execute a query
     */
    public function execute() {
        return db::query($this);
    }

    /**
     * Add WHERE clause, by building it from a constraints array
     * @param array Constraints, conditions that the records must comply to
     * @param array Conjunctions, and/or
     * @param array Operators =, !=, >, <, >= or <=
     * @return query $this
     */
    public function addWhereFromConstraints(array $constraints, $conj = "AND", $ops = null) {
        $where=null;
        while (list($name, $value) = each($constraints)) {
            $op = "=";
            if ($ops && !empty($ops["$name"])) {
                $op = $ops["$name"];
            }

            $n = strpos($name, "#");
            if ($n > 1) {
                $paramNumber=substr($name, $n + 1);
                $name = substr($name, 0, $n);
                $paramName=":" . $name . "_" . $paramNumber;
            } else {
                $paramName=":" . $name;
            }


            if ($value == "null" || $value == "''") {
                $value = null;
            }

            $clause=new clause($name . " " . $op . " " . $paramName);
            $this->addParam(new param($paramName, $value, PDO::PARAM_STR));

            if ($where instanceof clause) {
                if ($conj == "AND") {
                    $where->addAnd($clause);
                } else if ($conj == "OR") {
                    $where->addOr($clause);
                } else {
                    throw new zophException("Illegal conjunction (" . e($conj) .
                        ") should be AND or OR, please file a bug");
                    }
            } else {
                $where = $clause;
            }

        }
        if ($where instanceof clause) {
            $this->where($where);
        }
        return $this;
    }

    /**
     * The __toString() magic function creates the query to be fed to the db
     * each inheritance of this class will have to implement it.
     * @return string SQL query
     */
    abstract public function __toString();
}

