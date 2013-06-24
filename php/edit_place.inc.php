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
<?php
     echo check_js($user);
?>
    <div class="main">
      <form action="place.php" method="GET">
        <input type="hidden" name="_action" value="<?php echo $action ?>">
        <input type="hidden" name="place_id" value="<?php echo $place->get("place_id") ?>">
        <label for="title"><?php echo translate("title") ?></label>
        <?php echo create_text_input("title", $place->get("title"), 40, 64) ?>
        <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></span><br>
        <label for="parent_place_id"><?php echo translate("parent location") ?></label>
<?php
        if($place->isRoot()) {
            echo translate("places");
        } else {
?>
        <?php echo place::createPulldown("parent_place_id", $place->get("parent_place_id"), $user) ?>
<?php
        }
?>
        <br>
        <label for="address"><?php echo translate("address") ?></label>
        <?php echo create_text_input("address", $place->get("address"), 40, 40) ?>
        <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></span><br>
        <label for="address2"><?php echo translate("address continued") ?></label>
        <?php echo create_text_input("address2", $place->get("address2"), 40, 40) ?>
        <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></span><br>
        <label for="city"><?php echo translate("city") ?></label>
        <?php echo create_text_input("city", $place->get("city"), 32, 32) ?>
        <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
        <label for="state"><?php echo translate("state") ?></label>
        <?php echo create_text_input("state", $place->get("state"), 16, 32) ?>
        <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
        <label for="zip"><?php echo translate("zip") ?></label>
        <?php echo create_text_input("zip", $place->get("zip"), 10, 10) ?>
        <span class="inputhint"><?php echo translate("zip or zip+4") ?></span><br>
        <label for="country"><?php echo translate("country") ?></label>
        <?php echo create_text_input("country", $place->get("country"), 32, 32) ?>
         <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
         <label for="url"><?php echo translate("url") ?></label>
         <?php echo create_text_input("url", $place->get("url"), 32, 1024) ?>
         <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "1024") ?></span><br>
         <label for="urldesc"><?php echo translate("url description") ?></label>

         <?php echo create_text_input("urldesc", $place->get("urldesc"), 32, 32) ?>
         <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
         <label for="pageset"><?php echo translate("pageset") ?></label>
         <?php echo template::createPulldown("pageset", $place->get("pageset"), get_pageset_select_array()) ?><br>
         <fieldset class="map">
            <legend><?php echo translate("map") ?></legend>
            <label for="lat"><?php echo translate("latitude") ?></label>
            <?php echo create_text_input("lat", $place->get("lat"), 10, 10) ?><br>
            <label for="lat"><?php echo translate("longitude") ?></label>
            <?php echo create_text_input("lon", $place->get("lon"), 10, 10) ?><br>
            <label for="mapzoom"><?php echo translate("zoom level") ?></label>
            <?php echo place::createZoomPulldown($place->get("mapzoom")) ?><br>
        <?php if(conf::get("maps.geocode")): ?>
            <div class="geocode">
                <input id="geocode" class="geocode" type="button" value="<?php echo translate("search", false) ?>">
                <div id="geocoderesults"></div>
                <script type="text/javascript">
                    var translate={
                        "An error occurred": "<?php echo trim(translate("An error occurred.", false)); ?>",
                        "Nothing found": "<?php echo trim(translate("Nothing found", false)); ?>"
                    };
                    zGeocode.checkGeocode();
                </script>
            </div>
         <?php endif; ?>
         </fieldset>
<?php
        if(conf::get("date.guesstz")) {
            $tz=e($place->guessTZ());
            if(!empty($tz)) {
?>
            <ul class="actionlink">
                <li><a href="place.php?_action=update&place_id=<?php echo (int) $place->getId() ?>&timezone=<?php echo $tz ?>"><?php echo $tz ?></a></li>
            </ul>
<?php
            } 
        }
        if($place->get("timezone")) {
?>
            <span class="actionlink">
                <a href="place.php?_action=settzchildren&place_id=<?php echo $place->get("place_id") ?>"><?php printf(translate("set %s for children"), $place->get("timezone"))?></a>
            </span>
<?php
        }
?>

         <label for="timezone_id"><?php echo translate("timezone") ?></label>
         <?php echo TimeZone::createPulldown("timezone_id", $place->get("timezone"), $user); ?>

         <label for="notes"><?php echo translate("notes") ?></label>
         <textarea name="notes" cols="40" rows="4"><?php echo $place->get("notes") ?></textarea>
         <input type="submit" value="<?php echo translate($action, 0) ?>">
</form>
<!-- end edit_place.inc !-->
