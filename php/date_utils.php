<?php
/*
    David Baldwin provided this code for use with the calendar class.
 */

function days_of_february($year)
{
   $day = ($year & 3) ? 28 : ((!($year % 100) && ($year % 400)) ? 28 : 29);
   return $day;
}

function date2num($day, $month, $year)
{
   $mvec = array (0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);

   $mjd=($year-1)*365 + (($year-1)>>2);
   $mjd += floor(($year - 1) / 400);
   $mjd -= floor(($year - 1) / 100);
   $mjd += $mvec[$month-1];
   $mjd += $day;

   if ((days_of_february($year) == 29)
       && ($month > 2))
     $mjd++;
   return $mjd;
}

function weekday_of_date ($day, $month, $year)
/*
   Returns the weekday of a Gregorian/Julian calendar date
     (month must be 1...12) and returns 0...6 (0==su, 1==mo, 2==tu...6==sa).
*/
{
   $mjd = date2num($day, $month, $year) % 7;
   return $mjd;
}
?>
