<?php
/**
 * Template for preferences screen
 *
 * Preferences are user-changeble configuration options that are
 * mostly related to how things are displayed in Zoph
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
?>
<h1>
    <?= $this->getActionlinks() ?>
    <?= $tpl_title ?>
</h1>
<div class="main">
    <?= $tpl_defaultWarning ?>
    <form class="prefs" action="prefs.php" method="POST">
        <label>
            <input type="hidden" name="_action" value="update">
            <input type="hidden" name="user_id" value="<?= $tpl_userId ?>">
            <?= translate("user name") ?>
        </label>
        <?= $tpl_userName ?><br>
        <label><?= translate("show breadcrumbs") ?></label>
        <?= template::createYesNoPulldown("show_breadcrumbs", $tpl_prefs->get("show_breadcrumbs")) ?><br>
        <label><?= translate("number of breadcrumbs to show") ?></label>
        <input type="number" name="num_breadcrumbs" min=1 max=20 value="<?= $tpl_prefs->get("num_breadcrumbs") ?>"><br>
        <label><?= translate("default number of rows on results page") ?></label>
        <input type="number" name="num_rows" min=1 max=10 value="<?= $tpl_prefs->get("num_rows") ?>"><br>
        <label><?= translate("default number of columns on results page") ?></label>
        <input type="number" name="num_cols" min=1 max=10 value="<?= $tpl_prefs->get("num_cols") ?>"><br>
        <label><?= translate("size of pager on results page") ?></label>
        <input type="number" name="max_pager_size" min=1 max=20 value="<?= $tpl_prefs->get("max_pager_size") ?>"><br>
        <label><?= translate("minimum rating for random photos") ?></label>
        <input type="number" name="random_photo_min_rating" min=0 max=10 value="<?= $tpl_prefs->get("random_photo_min_rating") ?>"><br>
        <label><?= translate("number of results to display on reports page") ?></label>
        <input type="number" name="reports_top_n" min=1 max=20 value="<?= $tpl_prefs->get("reports_top_n") ?>"><br>
        <label><?= translate("time to display each photo in a slideshow") ?></label>
        <input type="number" name="slideshow_time" min=1 value="<?= $tpl_prefs->get("slideshow_time") ?>"><?= translate("seconds") ?><br>
        <label><?= translate("days past for recent photos links") ?></label>
        <input type="number" name="recent_photo_days" min=1 value="<?= $tpl_prefs->get("recent_photo_days") ?>"><br>
        <label><?= translate("open fullsize photo in new window") ?></label>
        <?= template::createYesNoPulldown("fullsize_new_win", $tpl_prefs->get("fullsize_new_win")) ?><br>
        <label><?= translate("display camera info") ?></label>
        <?= template::createYesNoPulldown("camera_info", $tpl_prefs->get("camera_info")) ?><br>
        <label><?= translate("display all EXIF info") ?></label>
        <?= template::createYesNoPulldown("allexif", $tpl_prefs->get("allexif")) ?><br>
        <label><?= translate("automatically edit photos") ?></label>
        <?= template::createYesNoPulldown("auto_edit", $tpl_prefs->get("auto_edit")) ?><br>
        <label>
            <?php if ($tpl_isAdmin): ?>
                <a href="color_schemes.php"><?= translate("color scheme") ?></a>
            <?php else: ?>
                <?= translate("color scheme"); ?>
            <?php endif ?>
        </label>
        
            <?= template::createPulldown("color_scheme_id", $tpl_prefs->get("color_scheme_id"),
                template::createSelectArray(color_scheme::getRecords("name"), array("name"))) ?>
        <br>
        <label><?= translate("language") ?></label>
        <?= template::createPulldown("language", $tpl_prefs->get("language"), $tpl_languages) ?><br>
        <label><?= translate("Default view") ?></label>
        <?= template::createViewPulldown("view", $tpl_prefs->get("view")) ?><br>
        <label><?= translate("Automatic coverphoto") ?></label>
        <?= template::createAutothumbPulldown("autothumb", $tpl_prefs->get("autothumb")) ?><br>
        <label><?= translate("Sort order for subalbums and categories") ?></label>
        <?= template::createPulldown("child_sortorder", $tpl_prefs->get("child_sortorder"), $tpl_sortorder); ?><br>
    <br>
    <?php if ($tpl_autocomplete): ?>
        <h2><?= translate("Autocomplete")?></h2>
        <label><?= translate("albums") ?></label>
        <?= template::createYesNoPulldown("autocomp_albums", $tpl_prefs->get("autocomp_albums")) ?><br>
        <label><?= translate("categories") ?></label>
        <?= template::createYesNoPulldown("autocomp_categories", $tpl_prefs->get("autocomp_categories")) ?><br>
        <label><?= translate("people") ?></label>
        <?= template::createYesNoPulldown("autocomp_people", $tpl_prefs->get("autocomp_people")) ?><br>
        <label><?= translate("places") ?></label>
        <?= template::createYesNoPulldown("autocomp_places", $tpl_prefs->get("autocomp_places")) ?><br>
        <label><?= translate("photographer") ?></label>
        <?= template::createYesNoPulldown("autocomp_photographer", $tpl_prefs->get("autocomp_photographer")) ?><br>
    <?php endif ?>
    <br>
    <input type="submit" value="<?= translate("update") ?>">
  </form>
</div>
