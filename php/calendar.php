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
require_once "include.inc.php";

$date = getvar("date");
$year = (int) getvar("year");
$month = (int) getvar("month");
$search_field = getvar("search_field");

$cal = new calendar();
$cal->setSearchField($search_field);

if ($year && $month) {
    $date=new Time($year . "-" . $month . "-01");
} else if ($date) {
    list($year, $month, $day) = explode("-", $date);
    $date=new Time($year . "-" . $month . "-01");
} else {
    $date=new Time("first day of this month");

}

$title=$date->format("F Y");
$header=translate("calendar");

$calendar=$cal->getMonthView($date);

$tpl=new template("calendar", array(
    "title"     => $title,
    "header"    => $header
));

$tpl->addBlock($calendar);

echo $tpl;
