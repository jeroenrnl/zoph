<?php
/**
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
 * @author Jason Geiger
 */

// Temporary, until mysql_ calls have been migrated to PDO
// Otherwise, php 5.5+ reports deprecation errors:
error_reporting(E_ALL ^ E_DEPRECATED);

// This file should contain the following settings:
// VERSION, RELEASEDATE, INI_FILE, THUMB_SIZE, MID_SIZE, THUMB_PREFIX, MID_PREFIX, LOG_ALWAYS
// LOG_SEVERITY, LOG_SUBJECT.
// All other settings are now made from the webinterface

define('VERSION', '0.9.1');
define('RELEASEDATE', '21-2-2014');
// DB_HOST, DB_NAME, DB_USER, DB_PASS and DB_PREFIX have been moved to
// zoph.ini. The location can be set by the next config item:

// INI FILE is already defined when using CLI and when running UnitTests
if(!defined("INI_FILE")) {
    define('INI_FILE', "/etc/zoph.ini");
}

define('THUMB_SIZE', 120);
define('MID_SIZE', 480);

define('THUMB_PREFIX', 'thumb');
define('MID_PREFIX', 'mid');

// LOG_ALWAYS and LOG_SEVERITY can have the following values:
// log::DEBUG, log::NOTIFY, log::WARN, log::ERROR, log::FATAL, log::MSG, log::NONE

// Always show fatal errors
define('LOG_ALWAYS', log::FATAL);

// Use the next options to show errors on a specific subject
// You can use the following subjects:
// log::VARS, log::LANG, log::LOGIN, log::REDIRECT,
// log::DB, log::SQL, log::XML, log:IMG, log::GENERAL, log::ALL

// Combine several subjects with | and |~
// For example to see SQL and LANG errors, log::SQL | log::LANG
// to see all errors except redirect log::ALL | ~log::REDIRECT
// all erros except SQL and LANG: log::ALL | ~(log::SQL |log::LANG)
define('LOG_SEVERITY', log::NONE);
define('LOG_SUBJECT', log::NONE);

?>
