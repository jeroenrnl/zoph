<?php

require_once("calendar.inc.php");

/*
 * An extension to the calendar class with a couple Zoph-specific
 * utility functions.
 */
class zoph_calendar extends Calendar {

    function getCalendarLink($month, $year) {
        $script = getenv('SCRIPT_NAME');
        return "$script?month=$month&year=$year";
    }

    function getDateLink($day, $month, $year) {
        $today = date("Ymd");

        if (strlen($month) < 2 && $month < 10) { $month = "0$month"; }
        if (strlen($day) < 2 && $day < 10) { $day = "0$day"; }

        if ("$year$month$day" > $today) {
            return "";
        }

        return "photos.php?date=$year-$month-$day";
    }

}
