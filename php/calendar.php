<?php
    require_once("include.inc.php");

    $date = getvar("date");
    $year = getvar("year");
    $month = getvar("month");
    $search_field = getvar("search_field");

    $cal = new zoph_calendar($search_field);

    if ($year && $month) {
        // ok
    }
    else if ($date) {
        list($year, $month, $day) = explode("-", $date);
    }
    else {
        $date = getdate();
        $year = $date["year"];
        $month = $date["mon"];
        $day = 0; // so that the today style will be used
    }

    $month_array = $cal->getMonthNames();
    $monthName = $month_array[$month-1];

    $title = "$monthName $year";
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";

    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
        <tr>
          <th colspan="2"><h1><?php echo translate("calendar") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td class="calendar">
<?php echo $cal->getMonthView($month, $year, $day) ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</body>
</html>
