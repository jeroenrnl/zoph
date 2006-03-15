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
?>
<!-- begin edit_place.inc !-->
    <h1>
      <span class="actionlink">
        <a href="places.php"><?php echo translate("return") ?></a> |
        <a href="place.php?_action=new"><?php echo translate("new") ?></a>
      </span>
      <?php echo translate($_action) ?> <?php echo translate("place") ?>
    </h1>
    <div class="main">
      <form action="place.php" method="GET">
        <table id="place">
          <tr>
            <td class="fieldtitle">
              <input type="hidden" name="_action" value="<?php echo $action ?>">
            <input type="hidden" name="place_id" value="<?php echo $place->get("place_id") ?>">
            <?php echo translate("title") ?>
          </td>
          <td class="field"><?php echo create_text_input("title", $place->get("title"), 40, 40) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle">
            <?php echo translate("parent location") ?>
          </td>
          <td class="field">
            <?php echo create_pulldown("parent_place_id",
                    $place->get("parent_place_id"), get_places_select_array()) ?>
          </td>
        </tr>

        <tr>
          <td class="fieldtitle"><?php echo translate("address") ?></td>
          <td class="field"><?php echo create_text_input("address", $place->get("address"), 40, 40) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("address continued") ?></td>
          <td class="field"><?php echo create_text_input("address2", $place->get("address2"), 40, 40) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("city") ?></td>
          <td class="field"><?php echo create_text_input("city", $place->get("city"), 32, 32) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("state") ?></td>
          <td class="field"><?php echo create_text_input("state", $place->get("state"), 16, 32) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("zip") ?></td>
          <td class="field"><?php echo create_text_input("zip", $place->get("zip"), 10, 10) ?></td>
          <td class="inputhint"><?php echo translate("zip or zip+4") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("country") ?></td>
          <td class="field"><?php echo create_text_input("country", $place->get("country"), 32, 32) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("notes") ?></td>
          <td class="field" colspan="2"><textarea name="notes" cols="40" rows="4"><?php echo $place->get("notes") ?></textarea></td>
        </tr>
        <tr>
          <td colspan="3"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr>
      </table>
</form>
<!-- end edit_person.inc !-->
