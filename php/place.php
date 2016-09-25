<?php
/**
 * Define and modify places
 *
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
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */

use conf\conf;

require_once "include.inc.php";

$place_id = getvar("place_id");
$place = new place($place_id);
$obj = &$place;
$redirect = "places.php";
if ($_action=="settzchildren") {
    if ($user->canEditOrganizers()) {
        $place->lookup();
        $place->setTzForChildren();
    }
    $action="display";
}
require_once "actions.inc.php";
if (!$user->canEditOrganizers() || $action == "display") {
    redirect("places.php?parent_place_id=" . $place->get("place_id"), "Redirect");
}
if ($action != "insert") {
    $place->lookup();
    if (!$place->isVisible()) {
        redirect("place.php");
    }
    $title = $place->get("title") ? $place->get("title") : $place->get("city");
} else {
    $title = translate("New Place");
}

require_once "header.inc.php";
if ($action == "confirm") {
    ?>
    <h1><?php echo translate("delete place") ?></h1>
    <div class="main">
      <ul class="actionlink">
        <li><a href="place.php?_action=confirm&amp;place_id=<?php
            echo $place->getId() ?>">
          <?php echo translate("delete") ?>
        </a></li>
        <li><a href="place.php?_action=display&amp;place_id=<?php
            echo $place->getId() ?>">
          <?php echo translate("cancel") ?>
        </a></li>
      </ul>
      <?php echo sprintf(translate("Confirm deletion of '%s'"), $title) ?>:
    <?php
} else {
    require_once "edit_place.inc.php";
}
?>
</div>
<?php
if (conf::get("maps.provider")) {
    $map=new map();
    $marker=$place->getMarker();
    $map->setCenterAndZoomFromObj($place);
    if ($marker instanceof marker) {
        $map->addMarker($marker);
    }
    if ($_action == "edit" || $_action == "new") {
        $map->setEditable();
    }
    echo $map;
}

?>
<?php require_once "footer.inc.php"; ?>
