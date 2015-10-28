<?php
/*
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
 */

/*
 * A class to validate a user.
 */
class validator {

    private $username;
    private $password;

    /*
     * The constructor.
     */
    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /*
     * Validate a user.
     */
    public function validate() {
        $user = null;

        // No username or password are given, and a default user is defined
        // let's login as that...
        if (!$this->username && !$this->password && conf::get("interface.user.default")) {
            $user = new user(conf::get("interface.user.default"));
            $user->lookup();
        } else {

            $qry = new select(array("users"));
            $qry->addFields(array("user_id"));
            $where=new clause("user_name=:username");
            $where->addAnd(new clause("password=password(:password)"));
            $qry->where($where);
            $qry->addParams(array(
                new param(":username", $this->username, PDO::PARAM_STR),
                new param(":password", $this->password, PDO::PARAM_STR)
            ));

            $stmt=$qry->execute();

            if($stmt->rowCount() == 1) {
                $user = new user($stmt->fetchColumn());
                $user->lookup();
            }
        }
        return $user;
    }

}

?>
