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


    $place_id = getvar("place_id");

    $place = new place($place_id);

    $obj = &$place;
    $redirect = "places.php";
    if($_action=="settzchildren") {
        if($user->is_admin()) {
            $place->lookup();
            $place->set_tz_children($place->get("timezone"));
        }
        $action="display";
    }
    require_once("actions.inc.php");
    if (!$user->is_admin() || $action == "display") {
        redirect(add_sid("places.php?parent_place_id=" . $place->get("place_id")), "Redirect");
    }
    if ($action != "insert") {
        $place->lookup();
        $title = $place->get("title") ? $place->get("title") : $place->get("city");
    }
    else {
        $title = translate("New Place");
    }

    require_once("header.inc.php");
?>
<?php
    if ($action == "confirm") {
?>
          <h1><?php echo translate("delete place") ?></h1>
      <div class="main">
          <span class="actionlink">
            <a href="place.php?_action=confirm&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="place.php?_action=display&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("cancel") ?></a>
          </span>
          <?php echo sprintf(translate("Confirm deletion of '%s'"), $title) ?>:

<?php
    } else {
require_once("edit_place.inc.php");
    }
?>
</div>
<?php
    if(JAVASCRIPT && conf::get("maps.provider")) {
        $map=new map();
        $marker=$place->getMarker($user);
        $map->setCenterAndZoomFromObj($place);
        if($marker instanceof marker) {
            $map->addMarker($marker);
        }
        if($_action == "edit") {
            $map->setEditable();
        }
        echo $map;
    }


?>
<?php require_once("footer.inc.php"); ?>
