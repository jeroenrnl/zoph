<?php

require_once("calendar.inc.php");

/*
 * An extension to the calendar class with a couple Zoph-specific
 * utility functions.
 */
class zoph_calendar extends Calendar {

    // the field to search on when linking back to photos.php
    // (date or timestamp)
    var $search_field;

    // store insead of getting for each call go getDateLink()
    var $today;

    function zoph_calendar($search_field) {
        $this->search_field = $search_field;
        $this->today = date("Ymd");
    }

    function getCalendarLink($month, $year) {
        $script = getenv('SCRIPT_NAME');
        return "$script?month=$month&amp;year=$year";
    }

    function getDateLink($day, $month, $year) {
        if (strlen($month) < 2 && $month < 10) { $month = "0$month"; }
        if (strlen($day) < 2 && $day < 10) { $day = "0$day"; }

        if ("$year$month$day" > $this->today) {
            return "";
        }

        if ($this->search_field == "timestamp") {

            // since timestamps have hms, we have to do
            // timestamp >= today and timestamp < tomorrow
            // Or we could trim the date within Mysql:
            // substring(timestamp, 0, 8) = today

            $today = "$year$month$day" . "000000";
            $tomorrow = date("YmdHms", mktime(0, 0, 0, $month, $day + 1, $year));

            $qs =
                rawurlencode("timestamp#1") . "=" . "$today&" .
                rawurlencode("_timestamp-op#1") . "=" . rawurlencode(">=") . "&" .
                rawurlencode("timestamp#2") . "=" . "$tomorrow&" .
                rawurlencode("_timestamp-op#2") . "=" . rawurlencode("<");
        }
        else {
            $qs = "date=$year-$month-$day";
        }

        return "photos.php?$qs";
    }

}
