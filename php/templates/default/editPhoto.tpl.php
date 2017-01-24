<?php
/**
 * Template for edit photo page
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
 * @author Jeroen Roos
 * @package ZophTemplates
 */
if (!ZOPH) {
    die("Illegal call");
}

use conf\conf;
use template\template;

?>
<h1>
    <?= $this->getActionlinks($tpl_actionlinks) ?>
    <?= $tpl_title ?>
</h1>
<?php if ($tpl_selection): ?>
    <?= $tpl_selection ?>
<?php endif ?>

<div class="main">
    <?= template::showJSwarning() ?>
    <form action="photo.php" method="POST">
        <input type="hidden" name="_action" value="<?= $tpl_action ?>">
        <input type="hidden" name="_qs" value="<?= $tpl_return_qs ?>">
        <?php if ($tpl_action == "insert"): ?>
            <?= template::createInput("name", $tpl_photo->get("name"), 40,
                translate("file name"), 64, sprintf(translate("%s chars max"), "64")) ?>
        <?php else: ?>
            <input type="hidden" name="photo_id" value="<?= $tpl_photo->getId() ?>">
            <?php if ($tpl_rotate): ?>
                <div class="rotate">
                    <?= translate("rotate", 0) ?>
                    <select name="_deg">
                        <option>&nbsp;</option>
                        <option>90</option>
                        <option>180</option>
                        <option>270</option>
                    </select>
                    <br>
                    <?= translate("recreate thumbnails", 0) ?>
                    <input type="radio" name="_thumbnail" value="1">
                    <?= translate("yes") ?>
                    <input type="radio" name="_thumbnail" value="0" checked>
                    <?= translate("no") ?>
                </div>
            <?php endif ?>
            <div class="prev"><?php echo $tpl_prev ? "[ $tpl_prev ]" : "&nbsp;" ?></div>
            <div class="photohdr">
                <?= $tpl_full ?>:
                <?= $tpl_width ?> x <?= $tpl_height ?>,
                <?= $tpl_size ?>
            </div>
            <div class="next"><?= $tpl_next ? "[ $tpl_next ]" : "&nbsp;" ?></div>
            <ul class="tabs">
                <?= $tpl_share ?>
            </ul>
            <?= $tpl_image ?>
        <?php endif ?>
        <input class="updatebutton" type="submit" value="<?= translate($tpl_action, 0) ?>"><br>
        <?= template::createInput("title", $tpl_photo->get("title"), 64, translate("title"), 40,
            sprintf(translate("%s chars max"), "64")) ?>
        <label for="_location_id">
            <?= translate("location") ?>
        </label>
        <?= $tpl_locPulldown ?>
        <br>
        <fieldset class="map">
            <legend><?= translate("map") ?></legend>
            <?= template::createInput("lat", $tpl_photo->get("lat"), 10, translate("latitude")) ?><br>
            <?= template::createInput("lon", $tpl_photo->get("lon"), 10, translate("longitude")) ?><br>
            <label for="mapzoom">
                <?= translate("zoom level") ?>
            </label>
            <?= $tpl_zoomPulldown ?><br>
        </fieldset>
        <?= template::createInput("date", $tpl_photo->get("date"), 12, translate("date"), 10, "YYYY-MM-DD") ?>
        <?= template::createInput("time", $tpl_photo->get("time"), 8, translate("time"), 10, "HH:MM:SS") ?>
        <?= template::createInput("time_corr", $tpl_photo->get("time_corr"), 8,
            translate("time correction"), 10, translate("in minutes")) ?>
        <?= template::createInput("view", $tpl_photo->get("view"), 64, translate("view"), 40,
            sprintf(translate("%s chars max"), "64")) ?>
        <label for="_photographer_id">
            <?= translate("photographer") ?>
        </label>
        <?= $tpl_pgPulldown ?>
        <br>
        <?php if ($tpl_admin):  ?>
            <?= template::createInput("level", $tpl_photo->get("level"), 2, translate("level"), 4, "1 - 10") ?>
        <?php endif ?>
        <label><?= translate("description") ?></label>
        <textarea name="description" cols="60" rows="4"><?= $tpl_photo->get("description") ?></textarea>
        <br>
        <?php if ($tpl_action != "insert"): ?>
            <label for="person_id[0]">
                <?= translate("people") ?><br>
            </label>
            <span class="inputhint"><?php echo translate("(left to right, front to back).") ?></span>
            <fieldset class="multiple">
                <?php if ($tpl_people): ?>
                    <?php foreach ($tpl_people as $person): ?>
                        <input class="remove" type="checkbox" name="_remove_person_id[]"
                            value="<?= $person->getId()?>"
                        <?= $person->getLink() ?><br>
                    <?php endforeach ?>
                <?php else: ?>
                    <?= translate("No people have been added to this photo.") ?><br>
                <?php endif ?>
                <?= $tpl_personPulldown ?>
            </fieldset>
            <label for="albums">
                <?= translate("albums") ?>
            </label>
            <fieldset class="albums multiple">
                <?php if ($tpl_albums): ?>
                    <?php foreach ($tpl_albums as $album): ?>
                        <input class="remove" type="checkbox" name="_remove_album_id[]"
                            value="<?= $album->getId()?>"
                        <?= $album->getLink() ?><br>
                    <?php endforeach ?>
                <?php else: ?>
                    <?= translate("This photo is not in any albums.") ?><br>
                <?php endif ?>
                <?= $tpl_albumPulldown ?>
            </fieldset>
            <label for="categories">
                <?= translate("categories") ?>
            </label>
            <fieldset class="categories multiple">
                <?php if ($tpl_categories): ?>
                    <?php foreach ($tpl_categories as $category): ?>
                        <input class="remove" type="checkbox" name="_remove_category_id[]"
                            value="<?= $category->getId()?>"
                        <?= $category->getLink() ?><br>
                    <?php endforeach ?>
                <?php else: ?>
                    <?= translate("This photo is not in any categories.") ?><br>
                <?php endif ?>
                <?= $tpl_catPulldown ?>
            </fieldset>
            <br>
            <?php if ($tpl_show): ?>
                <hr>
                <?= template::createInput("path", $tpl_photo->get("path"), 64, translate("path"), 40, sprintf(translate("%s chars max"), "64")) ?>
                <?= template::createInput("width", $tpl_photo->get("width"), 6, translate("width")) ?>
                <?= template::createInput("height", $tpl_photo->get("height"), 6, translate("height")) ?>
                <?= template::createInput("camera_make", $tpl_photo->get("camera_make"), 32, translate("camera make")) ?>
                <?= template::createInput("camera_model", $tpl_photo->get("camera_model"), 32, translate("camera model")) ?>
                <label for="flash_used">
                    <?= translate("flash used") ?>
                </label>
                <?= template::createPulldown("flash_used", $tpl_photo->get("flash_used"),
                    array("" => "", "Y" => translate("Yes", 0), "N" => translate("No", 0))) ?><br>
                <?= template::createInput("focal_length", $tpl_photo->get("focal_length"), 64, translate("focal length"), 10) ?>
                <?= template::createInput("exposure", $tpl_photo->get("exposure"), 64, translate("exposure"), 32) ?>
                <?= template::createInput("aperture", $tpl_photo->get("aperture"), 16, translate("aperture"), 8) ?>
                <?= template::createInput("compression", $tpl_photo->get("compression"), 64, translate("compression"), 32) ?>
                <?= template::createInput("iso_equiv", $tpl_photo->get("iso_equiv"), 8, translate("iso equiv")) ?>
                <?= template::createInput("metering_mode", $tpl_photo->get("metering_mode"), 16, translate("metering mode")) ?>
                <?= template::createInput("focus_dist", $tpl_photo->get("focus_dist"), 16, translate("focus distance")) ?>
                <?= template::createInput("ccd_width", $tpl_photo->get("ccd_width"), 16, translate("ccd width")) ?>
                <?= template::createInput("comment", $tpl_photo->get("comment"), 128, translate("comment"), 40) ?></br>
            <?php else: ?>
                <a href="photo.php?_action=edit&amp;photo_id=<?= $tpl_photo->getId() ?>&amp;_show=all">
                    <?= translate("show additional attributes") ?>
                </a>
            <?php endif ?>
            <br>
            <input type="submit" value="<?php echo translate($tpl_action, 0) ?>">
        <?php endif ?>
    </form>
</div>
