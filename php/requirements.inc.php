<?php
/*
 * This file checks if all the PHP requirements are available.
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
 */

/**
 * Check if all requirements are met...
 * @author Jeroen Roos
 * @package Zoph
 */


if(!is_callable("finfo_file")) {
    log::msg("fileinfo PHP extension is missing", log::FATAL);
    die();
}

if(!file_exists(MAGIC_FILE) && strlen(MAGIC_FILE)>0) {
    log::msg(MAGIC_FILE . " does not exist. Set MAGIC_FILE in config.inc.php to your MIME magic file.", log::FATAL);
    die();
}

if(!ini_get("date.timezone")) {
    @$tz=date("e");
    log::msg("You should set your timezone in php.ini, guessing it should be $tz", log::WARN, log::GENERAL);
    date_default_timezone_set($tz);
}
