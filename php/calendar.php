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
    $year = getvar("year");
    $month = getvar("month");
    $search_field = getvar("search_field");

    $cal = new calendar();
    $cal->setSearchField($search_field);

    if ($year && $month) {
        // ok
    } else if ($date) {
        list($year, $month, $day) = explode("-", $date);
    } else {
        $date = getdate();
        $year = $date["year"];
        $month = $date["mon"];
        $day = 0; // so that the today style will be used
    }

    $month_array = $cal->getMonthNames();
    $monthName = $month_array[$month-1];

    $title = "$monthName $year";

    require_once "header.inc.php";
?>
          <h1><?php echo translate("calendar") ?></h1>
      <div class="main">
<?php echo $cal->getMonthView($month, $year, $day) ?>
      </div>

</body>
</html>
