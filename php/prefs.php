<?php
/* This file is part of Zoph.
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

    if ($_action == "edit") {
        $action = "update";
    }
    else if ($_action == "update") {
        if (DEFAULT_USER != $user->get("user_id")) {
            $user->prefs->set_fields($request_vars);
            $user->prefs->update();
            $user->prefs->load(1);
            $rtplang = $user->load_language(1);
        }
        $action = "update";
    }
    else {
        $action = "update";
    }

    $title = translate("Preferences");

    require_once("header.inc.php");
?>
          <h1>
            <span class="actionlink"><a href="password.php"><?php echo translate("change password") ?></a></span>
            <?php echo translate("edit preferences") ?>
          </h1>
      <div class="main">
      <form action="prefs.php" method="GET">
<?php
    if ($user->get("user_id") == DEFAULT_USER) {
?>
        <?php echo sprintf(translate("The user %s is currently defined as the default user and does not have permission to change its preferences. The current values are shown below but any changes made will be ignored until a different default user is defined."), $user->get("user_name")); ?>
<?php
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
            <?php echo create_pulldown("show_breadcrumbs", $user->prefs->get("show_breadcrumbs"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt>
            <?php echo translate("number of breadcrumbs to show") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("num_breadcrumbs", $user->prefs->get("num_breadcrumbs"), 1, 20) ?>
        </dd>
        <dt>
            <?php echo translate("default number of rows on results page") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("num_rows", $user->prefs->get("num_rows"), 1, 10) ?>
        </dd>
        <dt>
            <?php echo translate("default number of columns on results page") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("num_cols", $user->prefs->get("num_cols"), 1, 10) ?>
        </dd>
        <dt>
            <?php echo translate("size of pager on results page") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("max_pager_size", $user->prefs->get("max_pager_size"), 1, 20) ?>
        </dd>
        <dt>
            <?php echo translate("minimum rating for random photos") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("random_photo_min_rating", $user->prefs->get("random_photo_min_rating"), 0, 10) ?>
        </dd>
        <dt>
            <?php echo translate("number of results to display on reports page") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("reports_top_n", $user->prefs->get("reports_top_n"), 1, 20) ?>
        </dd>
        <dt>
            <?php echo translate("time to display each photo in a slideshow") ?>
        </dt>
        <dd>
            <?php echo create_text_input("slideshow_time", $user->prefs->get("slideshow_time"), 4, 4) ?> <?php echo translate("seconds") ?>
        </dd>
        <dt>
            <?php echo translate("days past for recent photos links") ?>
        </dt>
        <dd>
            <?php echo create_text_input("recent_photo_days", $user->prefs->get("recent_photo_days"), 4, 4) ?>
        </dd>
        <dt>
            <?php echo translate("number of people to add at once") ?>
        </dt>
        <dd>
            <?php echo create_integer_pulldown("people_slots", $user->prefs->get("people_slots"), 1, MAX_PEOPLE_SLOTS) ?>
        </dd>
<?php
    if (MAX_THUMB_DESC) {
?>
        <dt>
            <?php echo translate("show descriptions under thumbnails") ?>
        </dt>
        <dd>
            <?php echo create_pulldown("desc_thumbnails", $user->prefs->get("desc_thumbnails"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
<?php
    }

    if (JAVASCRIPT) {
?>
        <dt>
            <?php echo translate("open fullsize photo in new window") ?>
        </dt>
        <dd>
            <?php echo create_pulldown("fullsize_new_win", $user->prefs->get("fullsize_new_win"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
<?php
    }
?>
        <dt>
            <?php echo translate("display camera info") ?>
        </dt>
        <dd>
            <?php echo create_pulldown("camera_info", $user->prefs->get("camera_info"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt>
            <?php echo translate("display all EXIF info") ?>
        </dt>
        <dd>
            <?php echo create_pulldown("allexif", $user->prefs->get("allexif"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt>
            <?php echo translate("automatically edit photos") ?>
        </dt>
        <dd>
            <?php echo create_pulldown("auto_edit", $user->prefs->get("auto_edit"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt>
<?php
    if ($user->is_admin()) {
?>
        <a href="color_schemes.php"><?php echo translate("color scheme") ?></a>
<?php
    }
    else {
?>
        <?php echo translate("color scheme") ?>
<?php
    }
?>
          </dt>
          <dd>
<?php echo create_smart_pulldown("color_scheme_id", $user->prefs->get("color_scheme_id"), create_select_array(get_records("color_scheme", "name"), array("name"))) ?>
          </dd>
<?php
    $lang_array = $rtplang->get_available_languages();
    $lang_select_array['null'] = translate("Browser Default");
    while (list($code, $code_to_name) = each($lang_array)) {
        $lang_select_array[$code] = $code_to_name[$code];
    }
?>
        <dt><?php echo translate("language") ?>
        </dt>
        <dd>
<?php echo create_smart_pulldown("language", $user->prefs->get("language"), $lang_select_array) ?>
        </dd>
        <dt>
            <?php echo translate("Default view") ?>
        </dt>
        <dd>
            <?php echo create_view_pulldown("view", $user->prefs->get("view")) ?>
        </dd>
        <dt>
            <?php echo translate("Automatic coverphoto") ?>
        </dt>
        <dd>
            <?php echo create_autothumb_pulldown("autothumb", $user->prefs->get("autothumb")) ?>

        </dd>
        <dt><?php echo translate("Sort order for subalbums and categories") ?></dt>
        <dd>
            <?php echo create_pulldown("child_sortorder", $user->prefs->get("child_sortorder"), get_sort_array()); ?>
        </dd>
    </dl>
<?php
    if (JAVASCRIPT && AUTOCOMPLETE) {
?>
    <br><h2><?php echo translate("Autocomplete")?></h2");
    <dl class="prefs">
        <dt><?php echo translate("albums") ?></dt>
        <dd>
            <?php echo create_pulldown("autocomp_albums", $user->prefs->get("autocomp_albums"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt><?php echo translate("categories") ?></dt>
        <dd>
            <?php echo create_pulldown("autocomp_categories", $user->prefs->get("autocomp_categories"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt><?php echo translate("people") ?></dt>
        <dd>
            <?php echo create_pulldown("autocomp_people", $user->prefs->get("autocomp_people"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt><?php echo translate("places") ?></dt>
        <dd>
            <?php echo create_pulldown("autocomp_places", $user->prefs->get("autocomp_places"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
        <dt><?php echo translate("photographer") ?></dt>
        <dd>
            <?php echo create_pulldown("autocomp_photographer", $user->prefs->get("autocomp_photographer"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
        </dd>
     </dl>
<?php
    }
?>
<br>
<input type="submit" value="<?php echo translate("update") ?>">
  </form>
</div>

<?php require_once("footer.inc.php"); ?>
