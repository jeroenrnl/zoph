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

    $styles =
        "<style type=\"text/css\">\n" .
        ".calendarDay { font-weight: bold }\n" .
        "</style>\n";

    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left" colspan="2"><?php echo translate("calendar") ?></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <td align="center">
<?php echo $cal->getMonthView($month, $year, $day) ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</div>

</body>
</html>
