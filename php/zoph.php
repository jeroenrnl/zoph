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
    $title = translate("Home");
    require_once("header.inc.php");

    // get one random photo
    $vars["_random"] = 1;
    $vars["rating"] = $RANDOM_PHOTO_MIN_RATING;
    $vars["_rating-op"] = ">=";

    $thumnails;
    $num_photos = get_photos($vars, 0, 1, $thumbnails, $user);
?>

    <h1><?php echo ZOPH_TITLE ?></h1>
    <div class="main">
        <div class="thumbnail" id="random">
<?php
    if (sizeof($thumbnails) == 1) {
        echo $thumbnails[0]->get_thumbnail_link();
    }

    $album = get_root_album();
    $album_count = get_album_count($user);
    $album_photo_count = $album->get_total_photo_count($user);
    $category = get_root_category();
    $category_count = get_count("category");
    $category_photo_count = $category->get_total_photo_count($user);
    echo "\n";
?>
        </div>
        <div class="intro" id="first">
            <?php echo sprintf(translate("Welcome %s. %s currently contains"), $user->person->get_link(), ZOPH_TITLE);
    echo "\n"; ?>
            <ul class="intro">
              <li><?php echo sprintf(translate("%s photos in %s"),  $album_photo_count, $album_count) ?> <a href="albums.php"><?php echo $album_count == 1 ? translate("album") : translate("albums") ?></a></li>
                <li><?php echo sprintf(translate("%s photos in %s"), $category_photo_count, $category_count) ?> <a href="categories.php"><?php echo $category_count == 1 ? translate("category") : translate("categories") ?></a></li>
<?php
    if ($user->is_admin() || $user->get("browse_people")) {
        $person_count = get_people_count($user);
?>
                <li><?php echo $person_count ?> <a href="people.php"><?php echo $person_count == 1 ? translate("person", 0) : translate("people", 0) ?></a></li>
<?php
    }
    if ($user->is_admin() || $user->get("browse_places")) {
        $place_count = get_count("place");
?>
                <li><?php echo $place_count ?> <a href="places.php"><?php echo $place_count == 1 ? translate("place", 0) : translate("places", 0) ?></a></li>
<?php
    }
?>
            </ul>
        </div>
        <p class="intro">
<?php
    $today = date("Y-m-d");
    $sub_days = $user->prefs->get("recent_photo_days");

    echo sprintf(translate("You may search for photos %s taken %s or %s modified %s in the past %s days."), "<a href=\"photos.php?_date-op=%3E%3D&amp;date=" . subtract_days($today, $sub_days) . "\">", "</a>", "<a href=\"photos.php?_timestamp-op=%3E%3D&amp;timestamp=" . subtract_days($today, $sub_days) . "\">", "</a>", $sub_days);
    echo "\n";
    echo sprintf(translate("Or you may use the %s search page %s to find photos using multiple criteria. You may also view a %s randomly chosen photo %s like the one above."), "<a href=\"search.php\">", "</a>", "<a href=\"photos.php?_random=1&amp;_rating-op=%3E%3D&amp;rating=$RANDOM_PHOTO_MIN_RATING\">","</a>");
    echo "\n        <p class=\"intro\">\n";
    echo sprintf(translate("These options are always available in the tabs on the upper right. Use the %s home %s link to return here. Click on any thumbnail to see a larger version along with information about that photo."),"<a href=\"zoph.php\">","</a>"); 
    echo "\n        </p>\n";
    if ($user->get("user_id") != DEFAULT_USER) {
?>
        <p class="intro">
<?php echo sprintf(translate("To edit your preferences or change your password, click %s here %s."),"<a href=\"prefs.php\">","</a>");
    echo "\n        </p>\n"; 
    }
?>
        <p class="version">
            Zoph <?php echo VERSION . "\n" ?>
        </p>
    </div>
<?php
    require_once("footer.inc.php");
?>
