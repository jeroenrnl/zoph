<?php
/**
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
 * @author Jeroen Roos
 * @package Zoph
 */


if(!is_callable("finfo_file")) {
    log::msg("fileinfo PHP extension is missing", log::FATAL);
    die();
}

if(!file_exists(conf::get("path.magic")) && strlen(conf::get("path.magic"))>0) {
    log::msg(conf::get("path.magic") . " does not exist. Set the location of your " .
        "magic file in admin -> config to your MIME magic file.", log::FATAL);
    die();
}

if(!ini_get("date.timezone")) {
    @$tz=date("e");
    log::msg("You should set your timezone in php.ini, guessing it should be $tz", 
        log::WARN, log::GENERAL);
    date_default_timezone_set($tz);
}

ini_set("magic_quotes_sybase", false);
ini_set("magic_quotes_runtime", false);
ini_set("magic_quotes_gpc", false);
ini_set("session.use_only_cookies", true);

if(PHP_VERSION_ID < 50300) {
    die("You should run at least PHP 5.3 to use Zoph");
}

