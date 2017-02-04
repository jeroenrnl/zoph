<?php
/**
 * Process variables
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
 * @author David Baldwin
 * @author Jeroen Roos
 *
 * @todo The functionality in this file has been moved to the web\request and
 *       generic\varible functions, this remains until all files have been
 *       moved to accessing those instead of the functions defined here.
 */

use generic\variable;
use web\request;

$request=request::create();

function getvar($var) {
    return $GLOBALS["request"][$var];
}

function i($value) {
    $var=new variable($value);
    return $var->input();
}

function e($value) {
    $var=new variable($value);
    return $var->escape();
}

$request_vars=$request->getRequestvars();

?>
