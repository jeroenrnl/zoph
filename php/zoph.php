<?php
    require_once("include.inc.php");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    $title = translate("Home");
    require_once("header.inc.php");

    // get one random photo
    $vars["_random"] = 1;
    $vars["rating"] = $RANDOM_PHOTO_MIN_RATING;
    $vars["_rating-op"] = ">=";

    $thumnails;
    $num_photos = get_photos($vars, 0, 1, $thumbnails, $user);
?>

  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo ZOPH_TITLE ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <td align="center" width="140">
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
?>
          </td>
          <td align="left">
      <?php echo sprintf(translate("Welcome %s.  %s currently contains"), $user->person->get_link(), ZOPH_TITLE) ?>
            <li><?php echo sprintf(translate("%s photos in %s"),  $album_photo_count, $album_count) ?> <a href="albums.php"><?php echo $album_count == 1 ? translate("album") : translate("albums") ?></a></li>
            <li><?php echo sprintf(translate("%s photos in %s"), $category_photo_count, $category_count) ?> <a href="categories.php"><?php echo $category_count == 1 ? translate("category") : translate("categories") ?></a></li>
<?php
    if ($user->is_admin() || $user->get("browse_people")) {
        $person_count = get_count("person");
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
          </td>
        </tr>
        <tr>
          <td colspan="2" align="left">
<?php
    $today = date("Y-m-d");
    $sub_days = $user->prefs->get("recent_photo_days");

    echo sprintf(translate("You may search for photos %s taken %s or %s modified %s in the past %s days."), "<a href=\"photos.php?_date-op=%3E%3D&date=" . subtract_days($today, $sub_days) . "\">", "</a>", "<a href=\"photos.php?_timestamp-op=%3E%3D&timestamp=" . subtract_days($today, $sub_days) . "\">", "</a>", $sub_days);
?>
      <?php echo sprintf(translate("Or you may use the %s search page %s to find photos using multiple criteria. You may also view a %s randomly chosen photo %s like the one above."), "<a href=\"search.php\">", "</a>", "<a href=\"photos.php?_random=1&_rating-op=%3E%3D&rating=$RANDOM_PHOTO_MIN_RATING\">","</a>"); ?>
<p>
<?php echo sprintf(translate("These options are always available in the tabs on the upper right.  Use the %s home %s link to return here. Click on any thumbnail to see a larger version along with information about that photo."),"<a href=\"zoph.php\">","</a>"); ?>
</p>
<?php
    if ($user->get("user_id") != DEFAULT_USER) {
?>
<p>
<?php echo sprintf(translate("To edit your preferences or change your password, click %s here %s."),"<a href=\"prefs.php\">","</a>"); ?>
</p>
<?php
    }
?>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <font size="-1">Zoph <?php echo VERSION ?></font>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</div>

<?php
    require_once("footer.inc.php");
?>
