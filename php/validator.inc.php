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

    var $username;
    var $password;

    /*
     * The constructor.
     */
    function validator($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /*
     * Validate a user.
     */
    function validate() {
        global $VALIDATOR;
        return $this->$VALIDATOR();
    }

    /*
     * Validate users using Zoph's database.
     */
    function default_validate() {
        $user = null;

        // No username or password are given, and a default user is defined
        // let's login as that...
        if (!$this->username && !$this->password && DEFAULT_USER) {
            $user = new user(DEFAULT_USER);
        } else {

            $query =
                "select user_id from " . DB_PREFIX . "users where " .
                "user_name = '" .  escape_string($this->username) . "' and " .
                "password = password('" . escape_string($this->password) . "')";

            $result = query($query);

            if (num_rows($result) == 1) {
                $row = fetch_array($result);

                $user = new user($row["user_id"]);
            } else {
                if (DEBUG>=5) {
                    echo "No valid user found... trying old_password...<br>\n";
                }
                /*
                 * No valid user has been found. It could be that we've upgraded
                 * MySQL to a post-4.1 version and the password is still in 
                 * the old format. 
                 * Let's find out, first, we will determine if we're indeed
                 * running running a newer version than 4.1:
                 */
            
                if(db_server=="mysql" && db_min_version("4.1")) { 
                    $query =
                        "select user_id from " . DB_PREFIX . "users where " .
                        "user_name = '" .  escape_string($this->username) . 
                        "' and " .  "password = old_password('" . 
                        escape_string($this->password) . "')";
                    
                    $result = query($query);

                    if (num_rows($result) == 1) {
                        $row = fetch_array($result);
                        $user = new user($row["user_id"]);
                        /* Ok...we found the user, let's make sure 
                         * this won't happen again...
                         */
                     
                        $query = 
                            "update " . DB_PREFIX . "users " .
                            "set password=password('" . 
                            escape_string($this->password) . "') " .
                            "where user_name = '" . 
                            escape_string($this->username) . "' and " .
                            "password = old_password('" . 
                            escape_string($this->password) . "')";
                        
                        $result = query($query);
                    }
                }
            }
        }
        return $user;
    }

    /*
     * Validate users using Zoph's database against the
     * PHP_AUTH_USER and PHP_AUTH_PW variables.
     *
     * Contributed by Samuel Keim
     */
    function php_validate() {
        $user = null;

        if (empty($this->username)) {
            $this->username = $_SERVER['PHP_AUTH_USER'];

            if (empty($pword)) {
                $this->password = $_SERVER['PHP_AUTH_PW'];
            }
        }

        return $this->default_validate();
    }

    /*
     * Validate using an htpasswd file.
     * (C) and GPL Asheesh Laroia, 2002
     * Uses code by Jason Geiger and include()s "cdi@thewebmasters.net"'s
     * Htpasswd PHP class
     */
    function htpasswd_validate() {
        /*
            Due to licensing issues, the Htpaswd class is not distrubuted with
            Zoph.  You must download it before making use of this feature.
        */
        $user = null;
        include("Passwd.php");
        $htpass = new File_Passwd(HTPASS_FILE);

        if ($htpass->verifyPassword($this->username, $this->password)) {
            $query =
                "select user_id from " . DB_PREFIX . "users where " .
                "user_name = '" .  escape_string($this->username) . "'";

            $result = query($query);

            if (num_rows($result) == 0) {
                // make a new user
                $tmpUser = new user();
                $tmpUser->set('user_name', $this->username);
                $tmpUser->set('password', $this->password);

                // make a new person
                $tmpPerson = new person();
                $tmpPerson->set('first_name', $this->username);

                // put him in DB
                $tmpPerson->insert();
                $tmpUser->set('person_id', $tmpPerson->get('person_id'));

                if (DEFAULT_USER) {
                    // Give user same privileges as Guest:
                    $guestUser = new user(DEFAULT_USER);

                    $privNames = array(
                        'browse_people',
                        'browse_places',
                        'detailed_people',
                        'import',
                        'lightbox_id');

                     foreach ($privNames as $q) {
                         $tmpUser->set($q, $guestUser->get($q));
                     }

                     // Now, grant special privileges of being registered
                     $privNames = array(
                         'detailed_people' => 1,
                         'import' => 1);

                     foreach ($privNames as $k => $v) {
                         $tmpUser->set($k, $v);
                     }
                }

                // Put a row in the DB with this cool dude's info
                $tmpUser->insert();

                // And return a new user of that row number
                $user = new user($tmpUser->get('user_id'));
            }
            else if ((num_rows($result) == 1)) {
                $row = fetch_array($result);
                $user = new user($row["user_id"]);
            }
        }
        // Fall back to DEFAULT_USER
        // Would it have been better to just fall back to default_validate()?
        // For code reuse's sake, perhaps this should be its own function,
        // and in each we just say "$user = default_user();"
        else if (DEFAULT_USER) {
            $user = new user(DEFAULT_USER);
        }

        return $user;
    }

}

?>
