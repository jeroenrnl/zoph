<?php
/**
 * Database query class for DELETE queries
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
 * The delete object is used to create DELETE queries
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class delete extends query {
    /** @var bool Set to true to allow DELETE query without WHERE
             this will delete all data from the table
             There currently is no way of setting this, because it
             is a protection against accidently running a query like
             this during development or due to a bug */
    private $deleteAll=false;

    /**
     * Create DELETE query
     * @return string SQL query
     */
    public function __toString() {
        $sql = "DELETE FROM " . $this->table;
        if ($this->where instanceof clause) {
            $sql .= " WHERE " . $this->where;
        } else if (!$this->deleteAll) {
            throw new DatabaseException("DELETE query without WHERE");
        }

        return $sql . ";";
    }

}

