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

    $_l = getvar("_l");

    if (empty($_l)) {
        if (DEFAULT_SHOW_ALL) {
            $_l = "all";
        }
        else {
            $_l = "a";
        }
    }

    $title = translate("Places");
    require_once("header.inc.php");
?>
          <h1>
<?php
        if ($user->is_admin()) {
?>
          <span class="actionlink"><a href="place.php?_action=new"><?php echo translate("new") ?></a></span>
<?php
        }
?>
<?php echo translate("places") ?></h1>
          <div class="letter">
<?php
    for ($l = 'a'; $l < 'z'; $l++) {
        $title = $l;
        if ($l == $_l) {
            $title = "<span class=\"selected\">" . strtoupper($title) . "</span>";
        }
?>
            <a href="places.php?_l=<?php echo $l ?>"><?php echo $title ?></a> |
<?php
    }
?>
            <a href="places.php?_l=z"><?php echo $_l == "z" ? "<strong>Z</strong>" : "z" ?></a> |
            <a href="places.php?_l=no%20city"><?php echo translate("no city") ?></a> |
            <a href="places.php?_l=all"><?php echo translate("all") ?></a>
    </div>
      <div class="main">
      <table class="places">
<?php
    $constraints = null;
    if ($_l == "all") {
        // no constraint
    }
    else if ($_l == "no city") {
        $constraints["city#1"] = "null";
        $ops["city#1"] = "is";
        $constraints["city#2"] = "''";
    }
    else {
        $constraints["lower(city)"] = "$_l%";
        $ops["lower(city)"] = "like";
    }

    $plcs = get_places($constraints, "or", $ops);

    if ($plcs) {
        foreach($plcs as $p) {
?>
       <tr>
          <td class="place">
            <?php echo $p->get("city") ? $p->get("city") : "&nbsp;" ?>
          </td>
<?php
        if ($user->is_admin() || $user->get("detailed_people")) {
?>
          <td>
            <?php echo $p->get("address") ? $p->get("address") : "&nbsp;" ?>
          </td>
<?php
        }
?>
          <td>
          <span class="actionlink">
            <a href="place.php?place_id=<?php echo $p->get("place_id") ?>"><?php echo translate("view") ?></a> | <a href="photos.php?location_id=<?php echo $p->get("place_id") ?>"><?php echo translate("photos at") ?></a>
          </span>
            <?php echo $p->get("title") ? "\"" . $p->get("title") . "\"" : "&nbsp;" ?>
          </td>
        </tr>
<?php
        }
?>
</table>
<?php      }
    else {
?>
          <div class="error"><?php echo sprintf(translate("No places were found in a city beginning with '%s'."), $_l) ?></div>
<?php
    }
?>
</div>
<?php
    require_once("footer.inc.php");
?>
