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
 * @author Jason Geiger
 * @author Jeroen Roos
 */

use conf\conf;

$_action="display";
$error="";
if (!defined("CLI")) {
    ini_set("session.name", settings::$instance);
    session_set_cookie_params(conf::get("interface.cookie.expire"));
    session_start();
    if (array_key_exists('user', $_SESSION)) {
        $user = $_SESSION['user'];

        if ($user instanceof anonymousUser) {
            if (!defined("IMAGE_PHP")) {
                unset($user);
                $_action="logout";
            }
        } else {
            $_action = getvar("_action");
        }
    }
} else {
    if (conf::get("interface.user.cli")!==0) {
        $user=new user(conf::get("interface.user.cli"));
    } else {
        $username=$_SERVER["USER"];
        $user=user::getByName($username);
        if (!$user) {
            throw new cliUserNotValidException($username . " is not a valid user");
        }
    }
    $user->lookup();
    $user->lookupPerson();
    $user->lookupPrefs();
}


// no user was in the session, try logging in
if ($_action == "logout") {
    session_destroy();
    $user = null;
    user::unsetCurrent();
    redirect("logon.php", "Logout");
} else if (empty($user)) {
    $hash=getvar("hash");
    if (defined("IMAGE_PHP") && conf::get("share.enable") && !empty($hash)) {
        require_once "classes/anonymousUser.inc.php";
        $user = new anonymousUser();
    } else if (defined("IMAGE_BG")) {
        require_once "classes/anonymousUser.inc.php";
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
        $user->lookupPerson();
        $user->lookupPrefs();

        // Update Last Login Fields
        $user->set("lastlogin", "now()");
        $user->set("lastip", $_SERVER["REMOTE_ADDR"]);
        $user->update();
        $user->lookup();
    } else {
        if (!empty($uname)) {
            /*  A username was given, but by now, no succesful logon has
                happened so, we'll redirect, with error set to PWDFAIL.
                This will show an error on the login screen.
                There is no indication what the exact problem was (unknown user,
                wrong password, etc.) as this might give an adversary more information
                than we want to give away */
            $error="error=PWDFAIL&";
        }
        $this_page=urlencode(preg_replace("/^\//", "", $_SERVER['REQUEST_URI']));
        redirect("logon.php?" . $error . "redirect=" . $this_page);
    }

}

if (!empty($user) && !($user instanceof anonymousUser)) {
    $user->prefs->load();
    $lang=$user->loadLanguage();
    user::setCurrent($user);

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
        if (array_key_exists('HTTPS', $_SERVER) && (conf::get("ssl.force")=="login")) {
            $redirect_clean = "http://" . $_SERVER['SERVER_NAME'] . $redirect_clean;
        }
        redirect($redirect_clean, "Redirect");
    }
    if (array_key_exists('HTTPS', $_SERVER) && (conf::get("ssl.force")=="login")) {
        redirect(getZophURL("http"), "switch back from https to http");
    }
} else if ($user instanceof anonymousUser) {
    user::setCurrent($user);
} else {
    $lang = new language(conf::get("interface.language"));
}

?>
