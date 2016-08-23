<?php
/**
 * Template for import page
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
 * @todo This template still creates the h1 and div main tags itself.
 *       this should be done by the main template;
 */

if (!ZOPH) { die("Illegal call"); }
?>
    <script type="text/javascript">
        <?php echo $tpl_javascript; ?>
    </script>

    <h1>
        <?php echo translate("import photos"); ?>
    </h1>
    <div class="main">
        <noscript>
          <div class="message warning">
            <img class="icon" src="<?= template::getImage("icons/warning.png") ?>">
            <?= translate("This page needs Javascript switched on and will not " .
                "function without it."); ?><br>
          </div>
       </noscript>
        <div class="import_uploads">
            <h2><?php echo translate("Upload photo",0);?></h2>
            <iframe class="upload" id="upload_<?php echo $tpl_num; ?>"
                src="import.php?_action=browse&upload_id=<?php
                echo $tpl_upload_id ?>&num=<?php echo $tpl_num ?>"
                allowtransparency=1 frameborder=0>
            </iframe>
        </div>
        <div id="import_details" class="import_details">
            <h2>
                <ul class="actionlink">
                    <li><a href="#" onClick="clr('import_details_text'); return false">
                        <?php echo translate("clear")?></a>
                    </li>
                </ul>
                <?php echo translate("Details",0);?>
            </h2>
            <div id="import_details_text">
            </div>
        </div>
        <div id="import_thumbs" class="import_thumbs">
            <h2>
                <?php echo translate("Uploaded photos",0);?>
            </h2>
            <div id="import_thumbnails">
                <ul class="actionlink">
                    <li><a href="#" onClick="zImport.selectAll(); return false">
                        <?php echo translate("select all")?></a>
                    </li>
                    <li><a href="#" onClick="zImport.toggleSelection(); return false">
                        <?php echo translate("toggle selection")?></a>
                    </li>
                    <li><a href="#" onClick="zImport.deleteSelected(); return false">
                        <?php echo translate("delete selected")?></a>
                    </li>
                </ul>
                <br>
            </div>
        </div>
        <div class="import">
            <h2><?php echo translate("Import",0);?></h2>
            <form id="import_form" class="import" onSubmit="zImport.importPhotos(); return false;">
                <label for="_path"><?php echo translate("path") ?> </label>
                <?php echo create_text_input("_path", "", 40, 64) ?>
                <?php if (conf::get("import.dated")): ?>
                    <span class="inputhint">
                        <?php echo translate("Dated directory will be appended") ?>
                    </span>
                <?php endif ?>
                <br>
                <label for="album"><?php echo translate("albums") ?></label>
                <fieldset class="multiple">
                    <?php echo album::createPulldown("_album_id[0]") ?>
                </fieldset>
                <label for="category"><?php echo translate("categories") ?></label>
                <fieldset class="multiple">
                    <?php echo category::createPulldown("_category_id[0]") ?>
                </fieldset>
                <label for="title"><?php echo translate("title") ?></label>
                <?php echo create_text_input("title", "", 40, 64) ?>
                <span class="inputhint">
                    <?php echo sprintf(translate("%s chars max"), "64") ?>
                </span><br>
                <label for="location"><?php echo translate("location") ?></label>
                <?php echo place::createPulldown("location_id") ?><br>
                <label for="view"><?php echo translate("view") ?></label>
                <?php echo create_text_input("view", "", 40, 64) ?>
                <span class="inputhint">
                  <?php echo sprintf(translate("%s chars max"), "64") ?>
                </span><br>
                <label for="date"><?php echo translate("date") ?></label>
                <?php echo create_text_input("date", "", 12, 10) ?>
                <span class="inputhint">YYYY-MM-DD</span><br>
                <label for="rating"><?php echo translate("rating") ?></label>
                <?php echo create_rating_pulldown("") ?>
                <span class="inputhint">1 - 10</span><br>
                <label for="people"><?php echo translate("people") ?></label>
                <fieldset class="multiple">
                    <?php echo person::createPulldown("_person_id[0]") ?>
                </fieldset>
                <label for="photographer"><?php echo translate("photographer") ?></label>
                <?php echo photographer::createPulldown("photographer_id") ?><br>
                <label for="level"><?php echo translate("level") ?></label>
                <?php echo create_text_input("level", "", 4, 2) ?>
                <span class="inputhint">1 - 10</span><br>
                <label for="extrafields"><?php echo translate("extra fields") ?></label><br>
                <span class="inputhint"><?= translate("These settings will override EXIF data!") ?></span><br>
                <fieldset class="formhelper-multiple">
                    <fieldset class="import-extrafields">
                        <?php echo template::createImportFieldPulldown("_field[]", "") ?>
                        <?php echo create_text_input("field[]", "", 30, 64) ?>
                    </fieldset>
                </fieldset>
                <label for="description"><?php echo translate("description") ?></label>
                <textarea name="description" cols="40" rows="4"></textarea><br>
                <input id="import_submit" type="submit"
                    value="<?php echo translate("import", 0) ?>">
            </form>
        </div>
