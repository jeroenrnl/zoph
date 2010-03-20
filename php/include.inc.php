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

    require_once("variables.inc.php");
    require_once("log.inc.php");

    require_once("config.inc.php");
    require_once("requirements.inc.php");
    require_once("util.inc.php");
    require_once("validator.inc.php");

    require_once("translation.inc.php");

    require_once("zoph_table.inc.php");
    require_once("zoph_tree_table.inc.php");

    require_once("zoph_calendar.inc.php");

    require_once("place.inc.php");
    require_once("person.inc.php");

    require_once("group_permissions.inc.php");
    require_once("color_scheme.inc.php");
    require_once("prefs.inc.php");
    require_once("user.inc.php");
    require_once("group.inc.php");

    require_once("database.inc.php");
    require_once("auth.inc.php");

    require_once("album.inc.php");
    require_once("category.inc.php");
    require_once("code.inc.php");
    require_once("comment.inc.php");

    require_once("page.inc.php");
    require_once("pageset.inc.php");

    require_once("file.inc.php");
    require_once("import.inc.php");
    require_once("template.inc.php");

    if(minimum_version("5.2.0")) {
        require_once("timezone.inc.php");
    }
    require_once("photo.inc.php");
    require_once("saved_search.inc.php");
    require_once("photo_search.inc.php");

?>
