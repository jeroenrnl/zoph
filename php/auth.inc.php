<?php
/**
 * Check if user is logged in, or perform authentication
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
 * This file lets a user pass through if one of the following is true:
 * - a valid username/password was given
 * - a $user object was found in the session
 * - a default user has been defined in config.inc.php
 * @todo Should be moved inside a class
 *
 * @package Zoph
 * @author Jason Geiger and Jeroen Roos
 */
    $_action="display";
    if(!defined("CLI")) {
        session_start();
        if (array_key_exists('user', $_SESSION)) {
            $user = $_SESSION['user'];

            if($user instanceof anonymousUser) {
                if(!defined("IMAGE_PHP")) {
                    unset($user);
                    $_action=("logout");
                }
            } else {
                $_action = getvar("_action");
            }
        }

    } else {
        if(defined("CLI_USER")) {
            if (CLI_USER != 0) {
                $user=new user(CLI_USER);
            } else {
                $username=$_SERVER["USER"];
                $user=user::getByName($username);
                if(!$user) {
                    log::$stopOnFatal=false;
                    log::msg("$username is not a valid user", log::FATAL, log::LOGIN);
                    exit(EXIT_CLI_USER_NOT_VALID);
                }    
            }
        } else {
            log::$stopOnFatal=false;
            log::msg("CLI_USER is not defined in config.inc.php", log::FATAL, log::LOGIN);
            exit(EXIT_CLI_USER_NOT_DEFINED);
        }
        $user->lookup();
        $user->lookup_person();
        $user->lookup_prefs();
    }


    // no user was in the session, try logging in
    if ($_action == "logout") {
        // delete left over temp files
        if($user) {
            delete_temp_annotated_files($user->get("user_id"));
        }
        session_destroy();
        $user = null;
        redirect("logon.php", "Logout");
    } else if (empty($user)) {
        $hash=getvar("hash");
        if(defined("IMAGE_PHP") && conf::get("interface.share") && !empty($hash)) {
            require_once("classes/anonymousUser.inc.php");
            $user = new anonymousUser();
        } else {
            $uname = getvar("uname");
            $pword = getvar("pword");
            $redirect = getvar("redirect");

            $validator = new validator($uname, $pword);
            $user = $validator->validate();
        }

        // we have a valid user
        if (!empty($user)) {
            $user->lookup();
            $user->lookup_person();
            $user->lookup_prefs();

            // Update Last Login Fields
            $updated_user = new user($user->get("user_id"));
            $updated_user->set("lastlogin", "now()");
            $updated_user->set("lastip", $_SERVER["REMOTE_ADDR"]);
            $updated_user->update();

            // delete left over temp files
            delete_temp_annotated_files($user->get("user_id"));

        } else {
            $this_page=urlencode(preg_replace("/^\//", "", $_SERVER['REQUEST_URI']));
            redirect("logon.php?redirect=" . $this_page);
        }

    }

    if (!empty($user)) {
        $user->prefs->load();
        $lang=$user->load_language();
            
        if (!defined("CLI")) {
            $_SESSION['user'] = &$user;
        }
        if (!empty($redirect)) {
            $redirect="/" . urldecode($redirect);
            // The next line makes sure you are not tricked into deleting a
            // photo by a url pointing you to the "confirm" action. Just
            // to be extra sure, any action, except "search" is replaced by
            // "display".
            $redirect_clean=preg_replace("/action=(?!search).[^&]+/", "action=display", $redirect);
            if (array_key_exists('HTTPS', $_SERVER) && (FORCE_SSL_LOGIN && !FORCE_SSL)) {
                $redirect_clean = "http://" . $_SERVER['SERVER_NAME'] . $redirect_clean;
            }
            redirect($redirect_clean, "Redirect");
        } 
        if (array_key_exists('HTTPS', $_SERVER) && (FORCE_SSL_LOGIN && !FORCE_SSL)) {
            redirect(ZOPH_URL . "/zoph.php", "switch back from https to http");
        }
    } else {
        $lang = new language(DEFAULT_LANG);
    }        

?>
