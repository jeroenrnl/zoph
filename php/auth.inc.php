<?php
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
    if (empty($user)) {

        $uname = getvar("uname");
        $pword = getvar("pword");

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
            $user->set("lastlogin", "now()");
            $user->set("lastip", $_SERVER["REMOTE_ADDR"]);
            $user->update();
        }
        else {
            header("Location: logon.php");
        }

    }
    else if ($_action == "logout") {
        session_destroy();
        header("Location: logon.php");
    }

    $user->prefs->load();
    $rtplang = $user->load_language();

    if (minimum_version('4.1.0')) {
        $_SESSION['user'] = &$user;
    }
?>
