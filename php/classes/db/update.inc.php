<?php
/**
 * Database query class for UPDATE queries
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
 * The insert object is used to create UPDATE queries
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class update extends query {

    /** @var bool Set to true to allow UPDATE query without WHERE
             this will delete all data from the table
             There currently is no way of setting this, because it
             is a protection against accidently running a query like
             this during development or due to a bug */
    private $updateAll=false;
    /** @var array Array of fields to be SET in UPDATE query */
    private $set=array();

    /**
     * Add a field to be SET in UPDATE query
     * @param string field to be SET
     * @param string name of param to be used to SET this field
     */
    public function addSet($field, $param) {
        $this->set[]=$field . "=:" . $param;
    }

    /**
     * Add a field to be SET in UPDATE query, using a function to set it
     * @param string field=function() expression
     */
    public function addSetFunction($function) {
        $this->set[]=$function;
    }

    /**
     * Get array of SET statements for this query
     * @return array SET statements
     */
    public function getSet() {
        return $this->set;
    }

    /**
     * Create UPDATE query
     * @return string SQL query
     */
    public function __toString() {
        $sql = "UPDATE " . $this->table . " SET ";

        if (is_array($this->set)) {
            $sql.=implode(", ", $this->set);
        } else {
         //   throw new databaseException("UPDATE with no SET");
        }

        if ($this->where instanceof clause) {
            $sql .= " WHERE " . $this->where;
        } else if (!$this->updateAll) {
            die("UPDATE query without WHERE");
        }
        return $sql . ";";
    }

}

