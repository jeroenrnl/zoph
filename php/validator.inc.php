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
    function validate() {
        $user = null;

        // No username or password are given, and a default user is defined
        // let's login as that...
        if (!$this->username && !$this->password && conf::get("interface.user.default")) {
            $user = new user(conf::get("interface.user.default"));
        } else {

            $query =
                "select user_id from " . DB_PREFIX . "users where " .
                "user_name = '" .  escape_string($this->username) . "' and " .
                "password = password('" . escape_string($this->password) . "')";

            $result = query($query);

            if (num_rows($result) == 1) {
                $row = fetch_array($result);

                $user = new user($row["user_id"]);
            }
        }
        return $user;
    }

}

?>
