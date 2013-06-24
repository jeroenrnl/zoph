<?php
/**
 * Template for geotagging settings
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
 * @package ZophTemplates
 * @author Jeroen Roos
 */
?>
        <form class="geotag" action="tracks.php">
            <p>
                <?php sprintf(translate("Geotagging will make Zoph use GPS tracks to determine the location where a photo was taken. You should import a GPX file using the import function before using the Geotagging option. Zoph will try to geotag %s photos."), $tpl_num_photos); ?>
            </p>
            <p>
                <?php foreach ($tpl_hidden as $key => $value): ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                <?php endforeach; ?>
            </p>
            <fieldset class="geotag">
                <legend><?php echo translate("Options"); ?></legend>
                <label for="maxtime">
                    <?php echo translate("Maximum time") ?>
                </label>
                <input type="text" id="maxtime" name="_maxtime" value="300">
                <?php echo translate("seconds"); ?>
                <div class="inputhint"><?php echo translate("Maximum time difference between photo and GPS timestamp"); ?></div><br>
                <label for="validtz"><?php echo translate("Valid timezone"); ?></label>
                <fieldset class="checkboxlist">
                    <legend><?php echo translate("Valid timezone"); ?></legend>

                    <input type="checkbox" id="validtz" name="_validtz" value="1" checked><?php echo translate("Only photos with a valid timezone"); ?><br>
                </fieldset>
                <label for="overwrite"><?php echo translate("Overwrite"); ?></label>
                <fieldset class="checkboxlist">
                    <legend><?php echo translate("Overwrite"); ?></legend>
                    <input type="checkbox" id="overwrite" name="_overwrite" value="1"><?php echo translate("Overwrite existing geo-information"); ?><br>
                </fieldset>
                <label for="tracks"><?php echo translate("Tracks"); ?></label>
                <fieldset class="checkboxlist">
                    <legend><?php echo translate("Tracks"); ?></legend>
                    <input type="radio" name="_tracks" id="tracks" value="all" checked><?php echo translate("All tracks"); ?><br>
                    <input type="radio" name="_tracks" id="tracks2" value="specific"><?php echo translate("Specific track") . ": " ?>
                    <?php echo template::createPulldown("_track", "", template::createSelectArray(track::getRecords("track_id"), array("name"))) ?>
                </fieldset>
            </fieldset>
            <fieldset class="geotag">
                <legend><?php echo translate("Interpolation"); ?></legend>
                <label for="interpolate">
                    <?php echo translate("Interpolation") ?>
                </label>
                <fieldset class="checkboxlist">
                    <legend><?php echo translate("Interpolation"); ?></legend>
                    <input type="radio" name="_interpolate" id="interpolate" value="yes" checked><?php echo translate("Interpolate between points"); ?><br>
                    <input type="radio" name="_interpolate" id="interpolate2" value="nearest"><?php echo translate("Use nearest point");  ?>
                </fieldset>
                <label for="intmaxdist">
                    <?php echo translate("Maximum distance") ?>
                </label>
                <input type="text" name="_int_maxdist" id="intmaxdist" value="1">
                <?php echo template::createPulldown("_entity","km", array("km" => "km", "miles" => "miles")) ?>
                <div class="inputhint">
                    <?php echo translate("Do not interpolate if distance between points is more than this"); ?>
                </div><br>
                <label for="intmaxtime">
                    <?php echo translate("Maximum time") ?>
                </label>
                <input type="text" name="_int_maxtime" id="intmaxtime" value="300">
                <?php echo translate("seconds"); ?>
                <div class="inputhint">
                    <?php echo translate("Do not interpolate if time between points is more than this"); ?>
                </div>
            </fieldset>
            <fieldset class="geotag">
                <legend><?php echo translate("Test"); ?></legend>
                <div class="formtext">
                    <?php echo translate("To ensure that the geotagging operation goes well, you can check the results of the geotagging before storing them in the database."); ?>
                </div>
                <label for="test">
                    <?php echo translate("Photos to test") ?>
                </label>
                <fieldset class="checkboxlist">
                    <legend><?php echo translate("Test"); ?></legend>
                    <input type="checkbox" name="_test[]" id="test" value="first"><?php echo translate("first"); ?><br>
                    <input type="checkbox" name="_test[]" id="test2" value="last"><?php echo translate("last"); ?><br>
                    <input type="checkbox" name="_test[]" id="test3" value="random"><?php echo translate("random"); ?><br>
                </fieldset>
                <label for="testcount">
                    <?php echo translate("Number of each") ?>
                </label>
                <input type="text" id="testcount" name="_testcount" value="5">
            </fieldset>
            <br>
        <p>
        <input type="submit" value="<?php echo translate("geotag") ?>">
        </p>
    </form>
