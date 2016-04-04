<?php
/**
 * Template for logon screen
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
 * @author Jeroen Roos
 * @package ZophTemplates
 */
if (!ZOPH) {
    die("Illegal call");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link TYPE="text/css" REL="stylesheet" HREF="css.php?logged_on=no">
<title><?= $tpl_title . ' - ' . translate("logon",0) ?></title>
</head>
<body>
<img class="background" srcset="image.php?type=background 2x, image.php?type=background 1x">
<h1><?= $tpl_title ?></h1>
<div class="logon">
    <h1><?= translate("logon",0)?></h1>
    <form action="zoph.php" method="POST">
        <label for="uname"><?= translate("username",0) ?></label>
        <input type="text" name="uname" id="uname"><br>
        <label for="pword"><?= translate("password",0) ?></label>
        <input type="password" name="pword" id="pword"><br>
        <input type="hidden" name="redirect" value="<?= $tpl_redirect ?>">
        <input type="submit" value="<?= translate("submit",0); ?>">
    </form>
</div>
</body>
</html>

