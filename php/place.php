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

    if (!$user->is_admin()) {
        $_action = "display";
    }

    $place_id = getvar("place_id");

    $place = new place($place_id);

    $obj = &$place;
    $redirect = "places.php";
    require_once("actions.inc.php");

    if ($action != "insert") {
        $place->lookup();
        $title = $place->get("title") ? $place->get("title") : $place->get("city");
    }
    else {
        $title = translate("New Place");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
<?php
    if ($action == "display") {

        $vars["location_id"] = $place->get("place_id");
        $photos_at = get_photos($vars, 0, 1, $ignore, $user);
?>
        <tr>
          <th><h1><?php echo translate("place") ?></h1></th>
          <td class="actionlink">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="place.php?_action=edit&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="place.php?_action=delete&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="place.php?_action=new"><?php echo translate("new") ?></a>
          ]
<?php
        }
        else {
            echo "&nbsp;";
        }
?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
<?php
    if ($user->get("detailed_places")) {
?>
            <?php echo $place->to_html() ?>
<?php
    }
    else {
?>
            <?php echo $title ?>
<?php
    }
?>
          </td>
          <td class="actionlink">[
            <a href="photos.php?location_id=<?php echo $place->get("place_id") ?>"><?php echo "$photos_at " . translate("photos at") ?></a>
          ]</td>
        </tr>
<?php
        if ($user->get("detailed_places") && $place->get("notes")) {
?>
        <tr>
          <td colspan="2"><?php echo $place->get("notes") ?></td>
        </tr>
<?php
        }
    }
    else if ($action == "confirm") {
?>
        <tr class="titlebar">
          <th><h1><?php echo translate("delete place") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $title) ?>:
          </td>
          <td class="actionlink">[
            <a href="place.php?_action=confirm&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="place.php?_action=display&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
      </table>
<?php
    }
    else {
require_once("edit_place.inc.php");
    }
?>
</table>

<?php require_once("footer.inc.php"); ?>
