<?php
/**
 * Include necessary files
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
 * @author Jason Geiger
 * @author Jeroen Roos
 */

require_once "autoload.inc.php";

require_once "exception.inc.php";
require_once "variables.inc.php";
require_once "log.inc.php";

require_once "config.inc.php";
require_once "settings.inc.php";
require_once "requirements.inc.php";
require_once "util.inc.php";

require_once "track.inc.php";
require_once "point.inc.php";


require_once "color_scheme.inc.php";

if (!defined("LOGON")) {
    if (!defined("TEST")) {
        require_once "auth.inc.php";
    }

    require_once "photo_search.inc.php";
    require_once "exif.inc.php";
}
?>
