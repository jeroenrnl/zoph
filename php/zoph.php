<?php
/**
 * Zoph main page
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

use conf\conf;

use photo\collection;

require_once "include.inc.php";
$title = translate("Home");
require_once "header.inc.php";

// get one random photo

$thumnails;
$photoCollection=collection::createFromVars(array(
    "_random"       => 1,
    "rating"        => user::getCurrent()->prefs->get("random_photo_min_rating"),
    "_rating-op"    => ">="
));

if (sizeof($photoCollection) >= 1) {
    $random=$photoCollection->shift()->getThumbnailLink();
} else {
    $random="";
}

$album = album::getRoot();
$album_count = album::getCount();
$album_photoCount = $album->getTotalPhotoCount();
$category = category::getRoot();
$category_count = category::getCountForUser();
$category_photoCount = $category->getTotalPhotoCount();

?>

<h1><?php echo conf::get("interface.title"); ?></h1>
<div class="main">
    <div class="thumbnail" id="random">
        <?= $random ?>
    </div>

    <div class="intro" id="first">
      <?php echo sprintf(translate("Welcome %s. %s currently contains"),
          $user->person->getLink(),
          conf::get("interface.title")) . "\n"; ?>
      <ul class="intro">
        <li>
          <?php echo sprintf(translate("%s photos in %s"), $album_photoCount, $album_count) ?>
          <a href="albums.php">
            <?php echo $album_count == 1 ? translate("album") : translate("albums") ?>
          </a>
        </li>
        <li>
          <?php echo sprintf(translate("%s photos in %s"), $category_photoCount,
              $category_count) ?>
            <a href="categories.php">
              <?php echo $category_count == 1 ? translate("category") : translate("categories") ?>
            </a>
          </li>
<?php
if ($user->canBrowsePeople()) {
    $person_count = person::getCountForUser();
    ?>
    <li>
      <?php echo $person_count ?>
      <a href="people.php">
        <?php echo $person_count == 1 ? translate("person", 0) : translate("people", 0) ?>
      </a>
    </li>
    <?php
}
if ($user->canBrowsePlaces()) {
    $place_count = place::getCount();
    ?>
    <li>
      <?php echo $place_count ?>
      <a href="places.php">
        <?php echo $place_count == 1 ? translate("place", 0) : translate("places", 0) ?>
      </a>
    </li>
    <?php
}
?>
  </ul>
</div>
<p class="intro">
<?php
$recent = new Time();
$sub_days = (int) $user->prefs->get("recent_photo_days");
$min_rating = (int) $user->prefs->get("random_photo_min_rating");
$recent->sub(new DateInterval("P" . (int) $sub_days . "D"));
$timestamp=$recent->format("Y-m-d");

$welcomeText=sprintf(translate("You may search for photos %s taken %s or %s modified %s in " .
    "the past %s days."), "<a href=\"photos.php?_date-op=%3E%3D&amp;date=" . $timestamp . "\">",
    "</a>", "<a href=\"photos.php?_timestamp-op=%3E%3D&amp;timestamp=" . $timestamp . "\">",
    "</a>", $sub_days) . "\n";
$welcomeText.=sprintf(translate("Or you may use the %s search page %s to find photos using " .
    "multiple criteria. You may also view a %s randomly chosen photo %s like the one above."),
    "<a href=\"search.php\">", "</a>",
    "<a href=\"photos.php?_random=1&amp;_rating-op=%3E%3D&amp;rating=" . $min_rating . "\">",
    "</a>") . "\n";
$welcomeText.="<p class=\"intro\">\n";
$welcomeText.=sprintf(translate("These options are always available in the tabs on the upper right. " .
    "Use the %s home %s link to return here. Click on any thumbnail to see a larger version " .
    "along with information about that photo."),"<a href=\"zoph.php\">","</a>");
$welcomeText.="\n        </p>\n";
if ($user->get("user_id") != conf::get("interface.user.default")) {
    $welcomeText.="<p class=\"intro\">";
    $welcomeText.=sprintf(translate("To edit your preferences or change your password, " .
        "click %s here %s."),"<a href=\"prefs.php\">","</a>");
    $welcomeText.="\n        </p>\n";
}
// Remove the rather ugly trailing space on the links
echo str_replace(" </a", "</a", $welcomeText);
?>
    <p class="version">
        Zoph <?php echo VERSION . "\n" ?>
    </p>
</div>
<?php
require_once "footer.inc.php";
?>
