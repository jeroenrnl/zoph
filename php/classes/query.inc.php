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

    private $table;
    private $fields=null;

    public function __construct($table, array $fields=null) {
        $table=db::getPrefix() . $table;
        if(is_array($fields)) {
            foreach ($fields as $field) {
                $this->fields[]=$table . "." . $field;
            }
        }
        $this->table=$table;

    }

    public function __toString() {
        $sql = "SELECT ";

        if(is_array($this->fields)) {
            $sql.=implode(", ", $this->fields);
        } else {
            $sql.="*";
        }

        $sql .= " FROM " . $this->table;

        return $sql . ";";
    }
}

