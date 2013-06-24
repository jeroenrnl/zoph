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
     * Since the same sort of actions (inserting/updating/deleting) are done
     * on many pages I have extracted some of the common code here.  The $obj
     * variable just needs to be set to the object to act on before this file
     * is included.
     */
    if ($_action == "edit") {
        $action = "update";
    }
    else if ($_action == "update") {
        $obj->setFields($request_vars);
        $obj->update();
        $action = "display";
    }
    else if ($_action == "new") {
        $obj->setFields($request_vars);
        $action = "insert";
    }
    else if ($_action == "insert") {
        $obj->setFields($request_vars);
        $obj->insert();
        $action = "display";
    }
    else if ($_action == "delete") {
        $action = "confirm";
    }
    else if ($_action == "confirm") {
        $obj->delete();
        $_action = "new";
        $action = "insert"; // in case redirect doesn't work

        $user->eat_crumb();
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = $redirect; }
        redirect($link, "Redirect");
    }
    else {
        $action = "display";
    }
?>
