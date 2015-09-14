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
 * This code is heavily based on:
 * PHP Calendar Class Version 1.4 (5th March 2001)
 *
 * Copyright David Wilkinson 2000 - 2001. All Rights reserved.
 * This software may be used, modified and distributed freely
 * providing this copyright notice remains intact at the head
 * of the file.
 *
 * This software is freeware. The author accepts no liability for
 * any loss or damages whatsoever incurred directly or indirectly
 * from the use of this script. The author of this software makes
 * no claims as to its fitness for any purpose whatsoever. If you
 * wish to use this software you should first satisfy yourself that
 * it meets your requirements.
 *
 * URL:   http://www.cascade.org.uk/software/php/calendar/
 * Email: davidw@cascade.org.uk
 *
 * @author David Wilkinson
 * @author Jeroen Roos
 * @url http://www.cascade.org.uk/software/php/calendar
 * @copyright David Wilkinson 2000 - 2001
 * @package Zoph
 */

class Calendar {
    /**
     * @var int The start day of the week. This is the day that appears in the first column
     * of the calendar. Sunday = 0.
     */
    private $startDay = 0;

    /**
     * @var int The start month of the year. This is the month that appears in the first slot
     * of the calendar in the year view. January = 1.
     */
    private $startMonth = 1;

    /**
     * @var array The labels to display for the days of the week. The first entry in this array
     * represents Sunday.
     */
    private $dayNames = array("S", "M", "T", "W", "T", "F", "S");

    /**
     * @var string the field to search on when linking back to photos.php
     * (date or timestamp)
     */
    private $searchField ="";

    /**
     * Constructor for the Calendar class
     */
    public function __construct() {
    }

    public function setSearchField($search) {
        $this->searchField=$search;
    }

    /**
     * Get the array of strings used to label the days of the week. This array contains seven
     * elements, one for each day of the week. The first entry in this array represents Sunday.
     */
    public function getDayNames() {
        return $this->dayNames;
    }

    /**
     * Set the array of strings used to label the days of the week. This array must contain seven
     * elements, one for each day of the week. The first entry in this array represents Sunday.
     */
    public function setDayNames($names) {
        $this->dayNames = $names;
    }

    /**
     * Gets the start day of the week. This is the day that appears in the first column
     * of the calendar. Sunday = 0.
     */
    public function getStartDay() {
        return $this->startDay;
    }

    /**
     * Sets the start day of the week. This is the day that appears in the first column
     * of the calendar. Sunday = 0.
     */
    public function setStartDay($day) {
        $this->startDay = $day;
    }


    /**
     * Gets the start month of the year. This is the month that appears first in the year
     * view. January = 1.
     */
    public function getStartMonth() {
        return $this->startMonth;
    }

    /**
     * Sets the start month of the year. This is the month that appears first in the year
     * view. January = 1.
     */
    public function setStartMonth($month) {
        $this->startMonth = $month;
    }

    /**
     * Return the URL to link to in order to display a calendar for a given month/year.
     */
    public function getCalendarLink(Time $date) {
        $script = getenv('SCRIPT_NAME');
        $month=$date->format("m");
        $year=$date->format("Y");
        return "$script?month=$month&amp;year=$year";
    }

    /**
     * Return the URL to link to  for a given date.
     */
    public function getDateLink(Time $date) {
        if($date > new Time) { return; }

        if ($this->searchField == "timestamp") {

            // since timestamps have hms, we have to do
            // timestamp >= today and timestamp < tomorrow
            // Or we could trim the date within Mysql:
            // substring(timestamp, 0, 8) = today

            $today = $date->format("Ymd000000");
            $date_tomorrow = clone $date;
            $date_tomorrow->add(new DateInterval("P1D"));
            $tomorrow = $date_tomorrow->format("Ymd000000");


            $qs =
                rawurlencode("timestamp#1") . "=" . "$today&" .
                rawurlencode("_timestamp-op#1") . "=" . rawurlencode(">=") . "&" .
                rawurlencode("timestamp#2") . "=" . "$tomorrow&" .
                rawurlencode("_timestamp-op#2") . "=" . rawurlencode("<");
        } else {
            $today=$date->format("Y-m-d");
            $qs = "date=$today";
        }

        return "photos.php?$qs";
    }

    /**
     * Return the HTML for a specified month
     * @todo Day names are hardcoded and not localized
     */
    public function getMonthView(Time $date) {
        $prev_date=clone $date;
        $prev = $prev_date->sub(new DateInterval("P1M"));
        $next_date=clone $date;
        $next = $next_date->add(new DateInterval("P1M"));

        $daysInMonth=$date->format("t");
        $firstDay=$date->format("w");
        $today=new Time();
        $today->setTime(0,0,0);
        $header=$date->format("F Y");

        $days=array();

        $titles=array("S", "M", "T", "W", "T", "F", "S");

        for($i=0; $i < $firstDay; $i++) {
            $days[]=array(
                "date"  => "",
                "link"  => "",
                "class" => "calendar",
            );
        }

        for($day=1; $day<=$daysInMonth; $day++) {
            $classes="calendar day";
            if($date == $today) { $classes .= " today"; }
            $days[]=array(
                "date"  => $day,
                "link"  => $this->getDateLink($date),
                "class" => $classes
            );
            $date->add(new dateInterval("P1D"));
        }



        $tpl=new block("calendar", array(
            "prev"      => $this->getCalendarLink($prev),
            "next"      => $this->getCalendarLink($next),
            "header"    => $header,
            "titles"    => $titles,
            "days"      => $days
        ));

        return $tpl;
    }
}

?>
