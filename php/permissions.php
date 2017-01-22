<?php
/**
 * Define and modify album permissions
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

use permissions\controller as permissionsController;

use template\block;
use template\form;
use template\template;

use web\request;


require_once "include.inc.php";

if (!user::getCurrent()->isAdmin()) {
    redirect("zoph.php");
}

$controller = new permissionsController(request::create());

$redirect="zoph.php";
if ($controller->getView() == "group") {
    $group=$controller->getObject();
    $redirect = "group.php?_action=edit&group_id=" . $group->getId();
} else if ($controller->getView() == "album") {
    $album=$controller->getObject();
    $redirect = "album.php?_action=edit&album_id=" . $album->getId();
}

redirect($redirect);
