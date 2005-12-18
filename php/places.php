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

    if (!$user->is_admin() && !$user->get("browse_places")) {
        header("Location: " . add_sid("zoph.php"));
    }
    $parent_place_id = getvar("parent_place_id");
    if (!$parent_place_id) {
        $place = get_root_place();
    }
    else {
        $place = new place($parent_place_id);
    }
    $place->lookup();
    $ancestors = $place->get_ancestors();
    $children = $place->get_children();

    $photo_count = $place->get_total_photo_count($user);

    $title = $place->get("parent_place_id") ? $place->get("title") : translate("Places");

    require_once("header.inc.php");
?>
    <h1>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink"><a href="place.php?_action=new&amp;parent_place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("new") ?></a></span>
<?php
    }
?>
        <?php echo translate("places") . "\n" ?>
    </h1>
    <div class="main">
        <h2>
<?php
    if ($ancestors) {
        while ($parent = array_pop($ancestors)) {
?>
            <?php echo $parent->get_link() ?> &gt;
<?php
        }
    }
?>
             <?php echo $title . "\n" ?>
        </h2>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink"><a href="place.php?_action=edit&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("edit") ?></a></span>
<?php
    }
    if ($place->get("place_description")) {
?>
        <div class="description">
            <?php echo $place->get("place_description") ?>
        </div>
<?php
    }
?>
<?php
    $fragment = translate("in this place");
    if ($photo_count > 0) {
        if (!$place->get("parent_place_id")) { // root place
            $fragment = translate("available");
        }
        else {
            if ($children) {
                $fragment .= " " . translate("or its children");
            }
        }

    if ($photo_count > 1) {
      echo sprintf(translate("There are %s photos"), $photo_count);
      echo " $fragment.\n";
    }
    else {
      echo sprintf(translate("There is %s photo"), $photo_count);
      echo " $fragment.\n";
    }
?>
        <span class="actionlink">
            <a href="photos.php?location_id=<?php echo $place->get_branch_ids($user) ?>"><?php echo translate("view photos") ?></a>
        </span>
<?php
    }
    else {
?>
        <?php echo translate("There are no photos") ?> <?php echo $fragment . ".\n"; 
    }
    if ($children) {
?>
        <ul>
<?php
        foreach($children as $a) {
?>
            <li><a href="places.php?parent_place_id=<?php echo $a->get("place_id") ?>"><?php echo $a->get("title") ?></a></li>
<?php
        }
?>
        </ul>
<?php
    }
?>
    </div>
<?php
    require_once("footer.inc.php");
?>
