<?php
/**
 * Change preferences
 * Preferences are user-changeble configuration options that are
 * mostly related to how things are displayed in Zoph
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
require_once "include.inc.php";

if (($_action == "update") && (conf::get("interface.user.default") != $user->get("user_id"))) {
    $user->prefs->setFields($request_vars);
    $user->prefs->update();
    $user->prefs->load(1);
    $lang = $user->loadLanguage(1);
}
$action = "update";

$title = translate("Preferences");

require_once "header.inc.php";
?>
      <h1>
        <span class="actionlink">
          <a href="password.php">
            <?php echo translate("change password") ?>
          </a>
        </span>
        <?php echo translate("edit preferences") ?>
      </h1>
  <div class="main">
  <form action="prefs.php" method="GET">
<?php
if ($user->get("user_id") == conf::get("interface.user.default")) {
    echo sprintf(translate("The user %s is currently defined as the default user " .
        "and does not have permission to change its preferences. The current values are " .
        "shown below but any changes made will be ignored until a different default user " .
        "is defined."), $user->get("user_name"));
}
?>
<dl class="prefs">
    <dt>
      <input type="hidden" name="_action" value="<?php echo $action ?>">
      <input type="hidden" name="user_id" value="<?php echo $user->prefs->get("user_id") ?>">
      <?php echo translate("user name") ?>
    </dt>
    <dd>
       <?php echo $user->get("user_name") ?>
    </dd>
    <dt>
      <?php echo translate("show breadcrumbs") ?>
    </dt>
    <dd>
      <?php echo template::createYesNoPulldown("show_breadcrumbs",
        $user->prefs->get("show_breadcrumbs")) ?>
    </dd>
    <dt>
      <?php echo translate("number of breadcrumbs to show") ?>
    </dt>
    <dd>
      <?php echo create_integer_pulldown("num_breadcrumbs",
        $user->prefs->get("num_breadcrumbs"), 1, 20) ?>
    </dd>
    <dt>
        <?php echo translate("default number of rows on results page") ?>
    </dt>
    <dd>
        <?php echo create_integer_pulldown("num_rows",
          $user->prefs->get("num_rows"), 1, 10) ?>
    </dd>
    <dt>
        <?php echo translate("default number of columns on results page") ?>
    </dt>
    <dd>
    <?php echo create_integer_pulldown("num_cols",
        $user->prefs->get("num_cols"), 1, 10) ?>
    </dd>
    <dt>
        <?php echo translate("size of pager on results page") ?>
    </dt>
    <dd>
        <?php echo create_integer_pulldown("max_pager_size",
            $user->prefs->get("max_pager_size"), 1, 20) ?>
    </dd>
    <dt>
        <?php echo translate("minimum rating for random photos") ?>
    </dt>
    <dd>
        <?php echo create_integer_pulldown("random_photo_min_rating",
            $user->prefs->get("random_photo_min_rating"), 0, 10) ?>
    </dd>
    <dt>
        <?php echo translate("number of results to display on reports page") ?>
    </dt>
    <dd>
        <?php echo create_integer_pulldown("reports_top_n",
            $user->prefs->get("reports_top_n"), 1, 20) ?>
    </dd>
    <dt>
        <?php echo translate("time to display each photo in a slideshow") ?>
    </dt>
    <dd>
    <?php echo create_text_input("slideshow_time",
        $user->prefs->get("slideshow_time"), 4, 4) ?> <?php echo translate("seconds") ?>
    </dd>
    <dt>
        <?php echo translate("days past for recent photos links") ?>
    </dt>
    <dd>
        <?php echo create_text_input("recent_photo_days",
            $user->prefs->get("recent_photo_days"), 4, 4) ?>
    </dd>
    <dt>
        <?php echo translate("open fullsize photo in new window") ?>
    </dt>
    <dd>
        <?php echo template::createYesNoPulldown("fullsize_new_win",
            $user->prefs->get("fullsize_new_win")) ?>
    </dd>
    <dt>
        <?php echo translate("display camera info") ?>
    </dt>
    <dd>
        <?php echo template::createYesNoPulldown("camera_info",
            $user->prefs->get("camera_info")) ?>
    </dd>
    <dt>
        <?php echo translate("display all EXIF info") ?>
    </dt>
    <dd>
    <?php echo template::createYesNoPulldown("allexif",
        $user->prefs->get("allexif")) ?>
    </dd>
    <dt>
        <?php echo translate("automatically edit photos") ?>
    </dt>
    <dd>
        <?php echo template::createYesNoPulldown("auto_edit",
            $user->prefs->get("auto_edit")) ?>
    </dd>
    <dt>
<?php
if ($user->isAdmin()) {
    ?>
    <a href="color_schemes.php"><?php echo translate("color scheme") ?></a>
    <?php
} else {
    echo translate("color scheme");
}
?>
    </dt>
    <dd>
      <?php echo template::createPulldown("color_scheme_id",
          $user->prefs->get("color_scheme_id"),
          template::createSelectArray(color_scheme::getRecords("name"), array("name"))) ?>
    </dd>
<?php
$langs = language::getAll();
$lang_select_array[null] = translate("Browser Default");
foreach ($langs as $language) {
    $lang_select_array[$language->iso] = $language->name;
}
?>
    <dt><?php echo translate("language") ?>
    </dt>
    <dd>
        <?php echo template::createPulldown("language",
            $user->prefs->get("language"), $lang_select_array) ?>
    </dd>
    <dt>
        <?php echo translate("Default view") ?>
    </dt>
    <dd>
        <?php echo template::createViewPulldown("view",
            $user->prefs->get("view")) ?>
    </dd>
    <dt>
        <?php echo translate("Automatic coverphoto") ?>
    </dt>
    <dd>
        <?php echo template::createAutothumbPulldown("autothumb",
            $user->prefs->get("autothumb")) ?>
    </dd>
    <dt><?php echo translate("Sort order for subalbums and categories") ?></dt>
    <dd>
        <?php echo template::createPulldown("child_sortorder",
            $user->prefs->get("child_sortorder"), array(
                "name"      => translate("Name", 0),
                "sortname"  => translate("Sort Name", 0),
                "oldest"    => translate("Oldest photo", 0),
                "newest"    => translate("Newest photo", 0),
                "first"     => translate("Changed least recently", 0),
                "last"      => translate("Changed most recently", 0),
                "lowest"    => translate("Lowest ranked", 0),
                "highest"   => translate("Highest ranked", 0),
                "average"   => translate("Average ranking", 0),
                "random"    => translate("Random", 0)
            )); ?>
    </dd>
</dl>
<?php
if (conf::get("interface.autocomplete")) {
    ?>
    <br><h2><?php echo translate("Autocomplete")?></h2>
    <dl class="prefs">
      <dt><?php echo translate("albums") ?></dt>
      <dd>
        <?php echo template::createYesNoPulldown("autocomp_albums",
          $user->prefs->get("autocomp_albums")) ?>
      </dd>
      <dt><?php echo translate("categories") ?></dt>
      <dd>
        <?php echo template::createYesNoPulldown("autocomp_categories",
          $user->prefs->get("autocomp_categories")) ?>
      </dd>
      <dt><?php echo translate("people") ?></dt>
      <dd>
        <?php echo template::createYesNoPulldown("autocomp_people",
          $user->prefs->get("autocomp_people")) ?>
      </dd>
      <dt><?php echo translate("places") ?></dt>
      <dd>
        <?php echo template::createYesNoPulldown("autocomp_places",
          $user->prefs->get("autocomp_places")) ?>
      </dd>
      <dt><?php echo translate("photographer") ?></dt>
      <dd>
        <?php echo template::createYesNoPulldown("autocomp_photographer",
            $user->prefs->get("autocomp_photographer")) ?>
      </dd>
    </dl>
    <?php
}
?>
    <br>
    <input type="submit" value="<?php echo translate("update") ?>">
  </form>
</div>

<?php require_once "footer.inc.php"; ?>
