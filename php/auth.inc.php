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

        $query =
            "select user_id from users where user_name = '" .
            escape_string($uname) . "' and " .
            "password = password('" . escape_string($pword) . "')";

        $result = mysql_query($query);

        if(mysql_num_rows($result) == 1) {
            $row = mysql_fetch_array($result);

            $user = new user($row["user_id"]);
        }
        // couldn't log in, but is there a default user?
        else if (DEFAULT_USER) {
            $user = new user(DEFAULT_USER);
        }

        // we have a valid user
        if (!empty($user)) {
            $user->lookup();
            $user->lookup_person();
            $user->lookup_prefs();

            if (!minimum_version('4.1.0')) {
                session_register("user");
            }
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
