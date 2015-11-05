<?php
/**
 * Database query class for SELECT queries
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
 * The select object is used to create SELECT queries
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class select extends query {

    /** @var array JOIN statements to add to this query */
    private $joins=null;
    /** @var array GROUP BY clause */
    private $groupby=array();
    /** @var array UNION clause */
    protected $union=array();
    /** @var select subquery */
    protected $subquery=null;

    /**
     * Create new query
     * @param string Table to query
     */
    public function __construct($table) {
        if (is_array($table)) {
            $tbl = reset($table);
        } else {
            $tbl = $table;
        }

        if ($tbl instanceof select) {
            $this->subquery=$table;
            foreach ($tbl->getParams() as $param) {
                $this->addParam($param);
            }
        } else {
            return parent::__construct($table);
        }
    }
    /**
     * Add a JOIN clause to the query
     * @param string table to join
     * @param string ON clause
     * @param string join type
     * @return query return the query to enable chaining
     */
    public function join($table, $on, $jointype="INNER") {
        if (is_array($table)) {
            $tbl=reset($table);
            $as=key($table);
            $table=$tbl . " AS " . $as;
            $this->tables[$as]=$tbl;
        } else {
            $this->tables[$table]=$table;
        }

        $table=db::getPrefix() . $table;

        if (!in_array($jointype, array("INNER", "LEFT", "RIGHT"))) {
            throw new DatabaseException("Unknown JOIN type");
        }
        $this->joins[]=$jointype . " JOIN " . $table . " ON " . $on;
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
        if (is_array($groupby) && sizeof($groupby) > 0) {
            return " GROUP BY " . implode(", ", $groupby);
        }
        return "";
    }

    /**
     * Add a UNION clause to the query
     * @param select SELECT query to UNION with this one
     * @return query return the query to enable chaining
     */
    public function union(select $qry) {
        $this->union[]=$qry;

        $this->addParams($qry->getParams());
        return $this;
    }


    /**
     * Execute query
     */
    public function execute() {
        return db::query($this);
    }

    /**
     * Create SELECT query
     * @return string SQL query
     */
    public function __toString() {
        $sql = "SELECT ";

        if (is_array($this->fields)) {
            $sql.=implode(", ", $this->fields);
        } else {
            $sql.="*";
        }

        if (isset($this->table)) {
            $sql .= " FROM " . $this->table;
        } else if (isset($this->subquery)) {
            if (is_array($this->subquery)) {
                $subqry = (string) reset($this->subquery);
                $alias = key($this->subquery);
                // We need to take off the ;
                $sql .= " FROM (" . rtrim($subqry, ";") . ") AS " . $alias;
            } else {
                // We need to take off the ;
                $sql .= " FROM (" . (string) rtrim($this->subquery. ";") . ")";
            }
        } else {
            die("No from clause in query");
        }

        if (isset($this->alias)) {
            $sql.=" AS " . $this->alias;
        }

        if (is_array($this->joins)) {
            $sql.=" " . implode(" ", $this->joins);
        }

        if ($this->where instanceof clause) {
            $sql .= " WHERE " . $this->where;
        }

        $groupby=trim($this->getGroupBy());
        if (!empty($groupby)) {
            $sql .= " " . $groupby;
        }

        if ($this->having instanceof clause) {
            $sql .= " HAVING " . $this->having;
        }

        $order=trim($this->getOrder());
        if (!empty($order)) {
            $sql .= " " . $order;
        }

        $limit=trim($this->getLimit());
        if (!empty($limit)) {
            $sql .= " " . $limit;
        }

        // This does not cover all use cases for UNION queries
        // it is, for example not possible to use ORDER or LIMIT statements
        // on the combined query, but currently Zoph doesn't use those
        if (sizeof($this->union) > 0) {
            foreach ($this->union as $union) {
                // We need to take off the ;
                $sql .= " UNION (" . rtrim($union, ";") . ")";
            }
        }

        return $sql . ";";
    }

    /**
     * Return the first column from the query as an array
     * This function should only be run on queries with a single column,
     * or it will make little sense
     * @return Array array of values
     */
    public function toArray() {
        $stmt=$this->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Executes a "SELECT COUNT(*) FROM ..." query and returns the counter
     * @return int count
     */
    public function getCount() {
        try {
            $result = db::query($this);
        } catch (PDOException $e) {
            log::msg("Unable to get count", log::FATAL, log::DB);
        }

        return $result->fetch(PDO::FETCH_BOTH)[0];
    }
}

