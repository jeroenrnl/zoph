<?php
    require_once("include.inc.php");

    $title = translate("Reports");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("reports") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td valign="top" align="center" width="50%">
<?php
    $top_albums = get_popular_albums($user);
    if ($top_albums) {
?>
            <table>
              <tr>
                <th colspan="3"><?php echo translate("Most Populated Albums") ?></th>
              </tr>
<?php
        while (list($album, $count) = each($top_albums)) {
?>
              <tr>
                <td><?= $album ?></td>
                <td>&nbsp;</td>
                <td><?= $count ?></td>
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
          <td valign="top" align="center" width="50%">
<?php
    $top_categories = get_popular_categories($user);
    if ($top_categories) {
?>
            <table>
              <tr>
                <th colspan="3"><?php echo translate("Most Populated Categories") ?></th>
              </tr>
<?php
        while (list($category, $count) = each($top_categories)) {
?>
              <tr>
                <td><?= $category ?></td>
                <td>&nbsp;</td>
                <td><?= $count ?></td>
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
          <td valign="top" align="center">
<?php
    $top_people = get_popular_people($user);
    if ($top_people) {
?>
            <table>
              <tr>
                <th colspan="3"><?php echo translate("Most Photographed People") ?></th>
              </tr>
<?php
        while (list($person, $count) = each($top_people)) {
?>
              <tr>
                <td><?= $person ?></td>
                <td>&nbsp;</td>
                <td><?= $count ?></td>
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
          <td valign="top" align="center">
<?php
    $top_places = get_popular_places($user);
    if ($top_places) {
?>
            <table>
              <tr>
                <th colspan="3"><?php echo translate("Most Photographed Places") ?></th>
              </tr>
<?php
        while (list($place, $count) = each($top_places)) {
?>
              <tr>
                <td><?= $place ?></td>
                <td>&nbsp;</td>
                <td><?= $count ?></td>
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
          <td colspan="2" align="center">
<?= create_rating_graph($user) ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</div>

<?php
    require_once("footer.inc.php");
?>
