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

require_once "include.inc.php";

header("Content-Type: text/xml");
session_write_close();
flush();
$object=getvar("object");
$search=getvar("search");

$obj_array=explode("_", $object);
if($obj_array[0]=="details") {
    $obj_name=$obj_array[1];
    $obj=new $obj_name((int) $obj_array[2]);

    echo $obj->getDetailsXML();
} else {
    if($object=="location" || $object=="home" || $object=="work") {
        $object="place";
    } else if ($object=="father" || $object=="mother" || $object=="spouse") {
        $object="person";
    } else if ($object=="timezone") {
        $object="TimeZone";
    } else if ($object=="import_progress") {
        $object="WebImport";
    } else if ($object=="import_thumbs") {
        $object="WebImport";
        $search="thumbs";
    }

    echo $object::getXML($search)->SaveXML();
}
?>
