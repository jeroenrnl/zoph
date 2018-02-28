<?php
/**
 * Modify configuration
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
 * @package Zoph
 * @author Jeroen Roos
 */

use conf\conf;
use template\template;

require_once "include.inc.php";
$title=translate("Configuration");

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

// Configuration setting depends on POST
if (!empty($_GET)) {
    redirect("config.php");
}

$_action=getvar("_action");
if ($_action == "setconfig") {
    conf::loadFromRequestVars($request_vars);
}

$tpl=new template("config", array(
    "title" => $title,
));

// this doesn't work yet, because the page is not fully template-generated
// it is also included in header.inc.php, but header.inc.php should be
// phased out soon.
$tpl->js[]="js/conf.js";
foreach (conf::getAll() as $name=>$item) {
    $tpl->addBlock($item->display());
}
echo $tpl;
?>
