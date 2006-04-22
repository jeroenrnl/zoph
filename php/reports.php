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
    require_once("include.inc.php");

    $title = translate("Reports");
    require_once("header.inc.php");
?>
    <h1><?php echo translate("reports") ?></h1>
      <div class="main">
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
<br>
<?php echo create_rating_graph($user) ?>
</div>
<?php
    require_once("footer.inc.php");
?>
