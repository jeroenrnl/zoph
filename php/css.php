<?php

/* This file is part of Zoph.
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

header("Content-Type: text/css");
if (isset($_GET['logged_on'])) {
    define("LOGON", true);
    echo "/* This is the default CSS, the user is not logged on */";
} else {
    echo "/* This is the customized CSS, user is logged on */";
}
require_once "include.inc.php";
$tpl=conf::get("interface.template");
$css="templates/" . $tpl . "/css.php";
if (!file_exists($css)) {
    $css="templates/default/css.php";
}
require_once $css;

?>
