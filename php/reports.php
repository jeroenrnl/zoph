<?php
    require_once("include.inc.php");

    $title = translate("Reports");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("reports") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td class="reports">
<?php
    $top_albums = get_popular_albums($user);
    if ($top_albums) {
?>
            <table class="reports">
              <tr>
                <th colspan="3"><h3><?php echo translate("Most Populated Albums") ?></h3></th>
              </tr>
<?php
        while (list($album, $count) = each($top_albums)) {
?>
              <tr>
                <td><?php echo $album ?></td>
                <td>&nbsp;</td>
                <td><?php echo $count ?></td>
              </tr>
<?php
        }
?>
            </table>
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
          <td  class="reports">
<?php
    $top_categories = get_popular_categories($user);
    if ($top_categories) {
?>
            <table class="reports">
              <tr>
                <th colspan="3"><h3><?php echo translate("Most Populated Categories") ?></h3></th>
              </tr>
<?php
        while (list($category, $count) = each($top_categories)) {
?>
              <tr>
                <td><?php echo $category ?></td>
                <td>&nbsp;</td>
                <td><?php echo $count ?></td>
              </tr>
<?php
        }
?>
            </table>
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
        </tr>
        <tr>
          <td class="reports">
<?php
    $top_people = get_popular_people($user);
    if ($top_people) {
?>
            <table class="reports">
              <tr>
                <th colspan="3"><h3><?php echo translate("Most Photographed People") ?></h3></th>
              </tr>
<?php
        while (list($person, $count) = each($top_people)) {
?>
              <tr>
                <td><?php echo $person ?></td>
                <td>&nbsp;</td>
                <td><?php echo $count ?></td>
              </tr>
<?php
        }
?>
            </table>
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
          <td class="reports">
<?php
    $top_places = get_popular_places($user);
    if ($top_places) {
?>
            <table class="reports">
              <tr>
                <th colspan="3"><h3><?php echo translate("Most Photographed Places") ?></h3></th>
              </tr>
<?php
        while (list($place, $count) = each($top_places)) {
?>
              <tr>
                <td><?php echo $place ?></td>
                <td>&nbsp;</td>
                <td><?php echo $count ?></td>
              </tr>
<?php
        }
?>
            </table>
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
        </tr>
        <tr>
          <td colspan="2">
<?php echo create_rating_graph($user) ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<?php
    require_once("footer.inc.php");
?>
