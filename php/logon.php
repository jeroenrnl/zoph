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
    define("LOGON", true);
    require_once("include.inc.php");

    if(isset($_GET["redirect"])) {
        $redirect = urlencode($_GET["redirect"]);
    } else {
        $redirect = "";
    }
    if (conf::get("ssl.force") != "never") {
        if (!array_key_exists('HTTPS', $_SERVER)) {
            redirect(getZophURL("https") . "/logon.php?redirect=" . $redirect, "https required");
        }
    }

    $user = new user();
    $lang=$user->load_language();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link TYPE="text/css" REL="stylesheet" HREF="css.php?logged_on=no">
<title><?php echo conf::get("interface.title") . ' - ' . translate("logon",0) ?></title>
</head>
<body>
    <h1><?php echo translate("logon",0) ?></h1>
    <div class="main" id="logon">
        <form action="zoph.php" method="POST">
            <h2 class="logon"><?php echo conf::get("interface.title") ?></h2>
            <label for="uname"><?php echo translate("username",0) ?></label>
            <input type="text" name="uname" id="uname"><br>
            <label for="pword"><?php echo translate("password",0) ?></label>
            <input type="password" name="pword" id="pword"><br>
            <input type="hidden" name="redirect" value="<?php echo $redirect ?>">
            <input type="submit" value="<?php echo translate("submit",0); ?>">
        </form>
    </div>
</body>
</html>
