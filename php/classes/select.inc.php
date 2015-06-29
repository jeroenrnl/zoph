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

    /**
     * Add a JOIN clause to the query
     * @param string table to join
     * @param string ON clause
     * @param string join type
     * @return query return the query to enable chaining
     */
    public function join($table, $on, $jointype="INNER") {
        if(is_array($table)) {
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
        if(is_array($groupby) && sizeof($groupby) > 0) {
            return " GROUP BY " . implode(", ", $groupby);
        }
        return "";
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

        if($this->having instanceof clause) {
            $sql .= " HAVING " . $this->having;
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

