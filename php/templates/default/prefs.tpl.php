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
    <form action="prefs.php" method="GET">
    <dl class="prefs">
        <dt>
            <input type="hidden" name="_action" value="update">
            <input type="hidden" name="user_id" value="<?= $tpl_userId ?>">
            <?= translate("user name") ?>
        </dt>
        <dd><?= $tpl_userName ?></dd>
        <dt><?= translate("show breadcrumbs") ?></dt>
        <dd><?= template::createYesNoPulldown("show_breadcrumbs", $tpl_prefs->get("show_breadcrumbs")) ?></dd>
        <dt><?= translate("number of breadcrumbs to show") ?></dt>
        <dd><input type="number" name="num_breadcrumbs" min=1 max=20 value="<?= $tpl_prefs->get("num_breadcrumbs") ?>"></dd>
        <dt><?= translate("default number of rows on results page") ?></dt>
        <dd><input type="number" name="num_rows" min=1 max=10 value="<?= $tpl_prefs->get("num_rows") ?>"></dd>
        <dt><?= translate("default number of columns on results page") ?></dt>
        <dd><input type="number" name="num_cols" min=1 max=10 value="<?= $tpl_prefs->get("num_cols") ?>"></dd>
        <dt><?= translate("size of pager on results page") ?></dt>
        <dd><input type="number" name="max_pager_size" min=1 max=20 value="<?= $tpl_prefs->get("max_pager_size") ?>"></dd>
        <dt><?= translate("minimum rating for random photos") ?></dt>
        <dd><input type="number" name="random_photo_min_rating" min=0 max=10 value="<?= $tpl_prefs->get("random_photo_min_rating") ?>"></dd>
        <dt><?= translate("number of results to display on reports page") ?></dt>
        <dd><input type="number" name="reports_top_n" min=1 max=20 value="<?= $tpl_prefs->get("reports_top_n") ?>"></dd>
        <dt><?= translate("time to display each photo in a slideshow") ?>
        <dd><input type="number" name="slideshow_time" min=1 value="<?= $tpl_prefs->get("slideshow_time") ?>"><?= translate("seconds") ?></dd>
        <dt><?= translate("days past for recent photos links") ?></dt>
        <dd><input type="number" name="recent_photo_days" min=1 value="<?= $tpl_prefs->get("recent_photo_days") ?>"></dd>
        <dt><?= translate("open fullsize photo in new window") ?></dt>
        <dd><?= template::createYesNoPulldown("fullsize_new_win", $tpl_prefs->get("fullsize_new_win")) ?></dd>
        <dt><?= translate("display camera info") ?></dt>
        <dd><?= template::createYesNoPulldown("camera_info", $tpl_prefs->get("camera_info")) ?></dd>
        <dt><?= translate("display all EXIF info") ?></dt>
        <dd><?= template::createYesNoPulldown("allexif", $tpl_prefs->get("allexif")) ?></dd>
        <dt><?= translate("automatically edit photos") ?></dt>
        <dd><?= template::createYesNoPulldown("auto_edit", $tpl_prefs->get("auto_edit")) ?></dd>
        <dt>
            <?php if ($tpl_isAdmin): ?>
                <a href="color_schemes.php"><?= translate("color scheme") ?></a>
            <?php else: ?>
                <?= translate("color scheme"); ?>
            <?php endif ?>
        </dt>
        <dd>
            <?= template::createPulldown("color_scheme_id", $tpl_prefs->get("color_scheme_id"),
                template::createSelectArray(color_scheme::getRecords("name"), array("name"))) ?>
        </dd>
        <dt><?= translate("language") ?></dt>
        <dd><?= template::createPulldown("language", $tpl_prefs->get("language"), $tpl_languages) ?></dd>
        <dt><?= translate("Default view") ?></dt>
        <dd><?= template::createViewPulldown("view", $tpl_prefs->get("view")) ?></dd>
        <dt><?= translate("Automatic coverphoto") ?></dt>
        <dd><?= template::createAutothumbPulldown("autothumb", $tpl_prefs->get("autothumb")) ?></dd>
        <dt><?= translate("Sort order for subalbums and categories") ?></dt>
        <dd><?= template::createPulldown("child_sortorder", $tpl_prefs->get("child_sortorder"), $tpl_sortorder); ?></dd>
    </dl>
    <br>
    <?php if ($tpl_autocomplete): ?>
        <h2><?= translate("Autocomplete")?></h2>
        <dl class="prefs">
            <dt><?= translate("albums") ?></dt>
            <dd><?= template::createYesNoPulldown("autocomp_albums", $tpl_prefs->get("autocomp_albums")) ?></dd>
            <dt><?= translate("categories") ?></dt>
            <dd><?= template::createYesNoPulldown("autocomp_categories", $tpl_prefs->get("autocomp_categories")) ?></dd>
            <dt><?= translate("people") ?></dt>
            <dd><?= template::createYesNoPulldown("autocomp_people", $tpl_prefs->get("autocomp_people")) ?></dd>
            <dt><?= translate("places") ?></dt>
            <dd><?= template::createYesNoPulldown("autocomp_places", $tpl_prefs->get("autocomp_places")) ?></dd>
            <dt><?= translate("photographer") ?></dt>
            <dd><?= template::createYesNoPulldown("autocomp_photographer", $tpl_prefs->get("autocomp_photographer")) ?></dd>
        </dl>
    <?php endif ?>
    <br>
    <input type="submit" value="<?= translate("update") ?>">
  </form>
</div>
