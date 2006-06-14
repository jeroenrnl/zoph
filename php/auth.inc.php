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
     * This file lets a user pass through if one of the following is true:
     * - a valid username/password was given
     * - a $user object was found in the session
     * - a default user has been defined in config.inc.php
     */
    session_start();

    $_action = getvar("_action");

    mysql_pconnect(DB_HOST, DB_USER, DB_PASS)
        or die("Unable to connect to MySQL");
    mysql_select_db(DB_NAME)
        or die("Unable to select database");

    if (minimum_version('4.1.0')) {
        $user = $_SESSION['user'];
    }

    // no user was in the session, try logging in
    if ($_action == "logout") {
        // delete left over temp files
        if($user) {
            delete_temp_annotated_files($user->get("user_id"));
        }
        session_destroy();
        $user = null;
        header("Location: logon.php");
        die;
    } else if (empty($user)) {
        if(FORCE_SSL_LOGIN && !FORCE_SSL) {
            header("Location: " . ZOPH_URL . "/zoph.php");
        }
        $uname = getvar("uname");
        $pword = getvar("pword");
        $redirect = getvar("redirect");

        $validator = new validator($uname, $pword);
        $user = $validator->validate();

        // we have a valid user
        if (!empty($user)) {
            $user->lookup();
            $user->lookup_person();
            $user->lookup_prefs();

            if (!minimum_version('4.1.0')) {
                session_register("user");
            }

            // Update Last Login Fields
            $updated_user = new user($user->get("user_id"));
            $updated_user->set("lastlogin", "now()");
            $updated_user->set("lastip", $_SERVER["REMOTE_ADDR"]);
            $updated_user->update();

            // delete left over temp files
            delete_temp_annotated_files($user->get("user_id"));

            if ($redirect) {
                $redirect="/" . urldecode($redirect);
                // The next line makes sure you are not tricked into deleting a
                // photo by a url pointing you to the "confirm" action. Just
                // to be extra sure, any action, except "search" is replaced by
                // "display".
                $redirect_clean=preg_replace("/action=(?!search).[^&]+/", "action=display", $redirect);
                if(FORCE_SSL_LOGIN && !FORCE_SSL) {
                    $redirect_clean = "http://" . $_SERVER['SERVER_NAME'] . "/" . $redirect_clean;
                }
                header("Location: " . $redirect_clean);
            }
        }
        else {
            $this_page=urlencode(preg_replace("/^\//", "", $_SERVER['REQUEST_URI']));
            header("Location: logon.php?redirect=" . $this_page);
            die;
        }

    }

    if (!empty($user)) {
        $user->prefs->load();
        $rtplang = $user->load_language();
            
        if (minimum_version('4.1.0')) {
            $_SESSION['user'] = &$user;
        }
    } else {
        $rtplang = new rtplang("lang", "en", "en", "en");
    }        
?>
