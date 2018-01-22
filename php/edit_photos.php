<?php
/**
 * Edit multiple photos at once
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

use template\template;
use template\pager;

use web\request;

require_once "include.inc.php";

$_cols = (int) getvar("_cols");
$_rows = (int) getvar("_rows");
$_off = (int) getvar("_off");
$_order = getvar("_order");
$_dir = getvar("_dir");

if (!preg_match("/^[a-zA-Z_]*$/", $_order)) {
    die("Illegal characters in _order");
}

if (!$_cols) { $_cols = $user->prefs->get("num_cols"); }
if (!$_rows) { $_rows = $user->prefs->get("num_rows"); }
if (!$_off)  { $_off = 0; }

if (!$_order) { $_order = conf::get("interface.sort.order"); }
if (!$_dir)   { $_dir = conf::get("interface.sort.dir"); }

$cells = $_cols * $_rows;
$offset = $_off;

$thumbnails;
$clean_vars=$request->getRequestVarsClean();

$_qs=getvar("_qs");

$qs = preg_replace('/_crumb=\d+&?/', '', $_SERVER["QUERY_STRING"]);
$qs = preg_replace('/_action=\w+&?/', '', $qs);
$encoded_qs = urlencode(htmlentities($_qs));
if (empty($encoded_qs)) {
    $encoded_qs = urlencode(htmlentities($qs));
}
/* if page is called via a HTTP POST, the $QUERY_STRING variable is empty
   so we need to fill $qs differently... */
if (empty($qs)) {
    $qs=$_qs;
}

$actionlinks["return"]="photos.php?" .  $qs;

$photoCollection = collection::createFromRequest(request::create());
$toDisplay = $photoCollection->subset($offset, $cells);

$photoCount=sizeof($photoCollection);
$displayCount=sizeof($toDisplay);

if  ($displayCount) {
    $pageCount = ceil($photoCount / $cells);
    $currentPage = floor($offset / $cells) + 1;

    $num = min($cells, $displayCount);

    $title = sprintf(translate("Edit Photos (Page %s/%s)", 0), $currentPage, $pageCount);
    $title_bar = sprintf(translate("edit photos %s to %s of %s"),
        ($offset + 1), ($offset + $num), $photoCount);
} else {
    $title = translate("No Photos Found");
    $title_bar = translate("edit photos");
}

require_once "header.inc.php";
?>
  <h1>
<?php
echo create_actionlinks($actionlinks);
echo $title_bar
?>
  </h1>
  <div class="main">
    <form action="edit_photos.php" method="POST">
      <p>
<?php
if ($displayCount <= 0) {
    ?>
       <div class="error">
    <?php echo translate("No photos were found matching your search criteria.") ?>
       </div>
    <?php
} else {
    $category_select_array = null;
    $album_select_array = null;
    $places_select_array = null;
    $people_select_array = null;


    // create once
    if (!conf::get("interface.autocomplete")) {
        if (!$user->prefs->get("autocomp_categories")) {
            category::setSAcache();
        }
        if (!$user->prefs->get("autocomp_albums")) {
            album::setSAcache();
        }
        if (!$user->prefs->get("autocomp_places")) {
            place::setSAcache();
        }
        if (!$user->prefs->get("autocomp_people")) {
            person::setSAcache();
        }
    }

    // used to create hidden fields for recreating the results query
    $queryIgnoreArray[] = '_action';
    $queryIgnoreArray[] = '_overwrite';
    $queryIgnoreArray[] = '__location_id__all';
    $queryIgnoreArray[] = '_rating__all';
    $queryIgnoreArray[] = '_album__all';
    $queryIgnoreArray[] = '_category__all';
    ?>
        <input type="submit" value="<?php echo translate("update", 0) ?>">
        <fieldset class="editphotos">
            <legend><?php echo translate("All photos")?></legend>
              <input type="hidden" name="_action" value="update">
              <label for="overwrite"><?php echo translate("overwrite values below", 0) ?></label>
              <?php echo template::createYesNoPulldown("_overwrite", "0") ?><br>
              <label for="date__all"><?php echo translate("date") ?></label>
              <?php echo create_text_input("__date__all", "" , 12, 10, "date") ?>
              <span class="inputhint">YYYY-MM-DD</span><br>
              <label for="time__all"><?php echo translate("time") ?></label>
              <?php echo create_text_input("__time__all", "", 10, 8, "time") ?>
              <span class="inputhint">HH:MM:SS</span><br>
              <label for="location_id__all"><?php echo translate("location") ?></label>
              <?php echo place::createPulldown("__location_id__all") ?>
              <br>
              <label for="photographer_id__all"><?php echo translate("photographer") ?></label>
              <?php echo person::createPulldown("__photographer_id__all") ?><br>
              <label for="rating__all"><?php echo translate("rating") ?></label>
              <?php echo create_rating_pulldown(null, "_rating__all") ?><br>
              <label for="album__all"><?php echo translate("albums") ?></label>
              <fieldset class="multiple">
                  <?php echo album::createPulldown("_album__all[0]") ?>
              </fieldset><br>
              <label for="category__all"><?php echo translate("categories") ?></label>
              <fieldset class="multiple">
                  <?php echo category::createPulldown("_category__all[0]") ?>
              </fieldset><br>
            </fieldset>
    <?php
    // These are used by the autocomplete script
    // to store the real name of location/album/category/person
    // other parts of Zoph discard them because of the _ prefix,
    // however, bulk edit uses _ prefixes for other purposes.
    unset($request_vars["___location_id__all"]);
    unset($request_vars["___photographer_id__all"]);
    unset($request_vars["__album__all"]);
    unset($request_vars["__category__all"]);
    foreach ($toDisplay as $photo) {
        $photo->lookup();
        $photo_id = $photo->getId();

        unset($request_vars["___location_id__" . $photo_id]);
        unset($request_vars["___photographer_id__" . $photo_id]);
        unset($request_vars["__album__" . $photo_id]);
        unset($request_vars["__category__" . $photo_id]);
        unset($request_vars["__person__" . $photo_id]);
        $permissions = $user->getPhotoPermissions($photo);
        if (!$user->isAdmin() && !$permissions) {
            continue;
        }

        $can_edit = false;
        $action="";
        $can_edit = $user->isAdmin() || $permissions->get("writable");


        if (array_key_exists("_action__" . $photo_id, $request_vars)) {
            $action = $request_vars["_action__" . $photo_id];
        }

        if ($can_edit && $action == 'update') {

            $rating = null;
            if ($request_vars['_overwrite']) {
                // set any specific fields
                $photo->setFields($request_vars, '__', "__$photo_id");

                // set "apply to all" fields
                $photo->setFields($request_vars, '__', '__all', false);

                $rating = $request_vars["_rating__$photo_id"];
                if ($request_vars["_rating__all"]) {
                    $rating = $request_vars["_rating__all"];
                }
            } else { // reverse order
                $photo->setFields($request_vars, '__', '__all');
                $photo->setFields($request_vars, '__', "__$photo_id", false);

                $rating = $request_vars["_rating__all"];
                if ($request_vars["_rating__$photo_id"]) {
                    $rating = $request_vars["_rating__$photo_id"];
                }
            }
            if ($rating != "0") {
                if (conf::get("feature.rating")) {
                    $photo->rate($rating);
                }
            }

            // this will update any specific albums, cats & people
            $photo->update();
            $photo->updateRelations($request_vars, '__' . $photo_id);

            // update "apply to all" albums, cats & people

            $photo->updateRelations($request_vars, '__all');

            if ($can_edit && conf::get("rotate.enable") &&
                ($user->isAdmin() || $permissions->get("writable"))) {
                    $deg = $request_vars["_deg__$photo_id"];
                    if ($deg && $deg != 0) {
                        $photo->lookup();
                        try {
                            $photo->rotate($deg);
                        } catch (Exception $e) {
                            echo $e->getMessage();
                            die;
                        }
                    }
            }
        } else if ($can_edit && $action == 'delete') {
            $photo->delete();
            continue;
        }

        if ($action == "update") {
            $request_vars["_action"]="display";
        }

        $photo->lookup();

        $queryIgnoreArray[] = "__photo_id__$photo_id";
        $queryIgnoreArray[] = "__location_id__$photo_id";
        $queryIgnoreArray[] = "__photographer_id__$photo_id";
        $queryIgnoreArray[] = "__title__$photo_id";
        $queryIgnoreArray[] = "__description__$photo_id";
        $queryIgnoreArray[] = "_rating__$photo_id";
        $queryIgnoreArray[] = "_album__$photo_id";
        $queryIgnoreArray[] = "_remove_album__$photo_id";
        $queryIgnoreArray[] = "_category__$photo_id";
        $queryIgnoreArray[] = "_remove_category__$photo_id";
        $queryIgnoreArray[] = "_remove_person__$photo_id";
        $queryIgnoreArray[] = "_person__" . $photo_id;
        $queryIgnoreArray[] = "_deg__$photo_id";
        $queryIgnoreArray[] = "_action__$photo_id";
        ?>
        <fieldset class="editphotos">
            <legend><?php echo $photo->get('name')?></legend>
            <div class="editchoice">
        <?php
        if ($can_edit) {
            ?>
              <input type="hidden" name="__photo_id__<?php echo $photo_id ?>"
                value="<?php echo $photo_id ?>">
              <input type="radio" name="_action__<?php echo $photo_id ?>"
                value="update" checked><?php echo translate("edit", 0) ?><br>
              <input type="radio" name="_action__<?php echo $photo_id ?>"
                value=""><?php echo translate("skip", 0) ?><br>
              <input type="radio" name="_action__<?php echo $photo_id ?>"
                value="delete"><?php echo translate("delete", 0) ?>
            <?php
        } else {
            echo "&nbsp;";
        }
        ?>
            </div>
            <div class="thumbnail">
              <?php echo $photo->getThumbnailLink() . "\n" ?><br>
        <?php
        if ($can_edit && conf::get("rotate.enable") &&
            ($user->isAdmin() || $permissions->get("writable"))) {
            ?>
              <?php echo translate("rotate", 0) ?>
              <select name="_deg__<?php echo $photo_id ?>">
                <option>&nbsp;</option>
                <option>90</option>
                <option>180</option>
                <option>270</option>
              </select>
            <?php
        }
        ?>
            </div>
        <?php
        if (!$can_edit) {
            ?>
            <?php echo $photo->get('name') ?>:<br>
            <?php echo translate("Insufficient permissions to edit photo", 0)?>.
            <?php
        } else {
            ?>
                <fieldset class="editphotos-fields">
                  <label for="title__<?php echo $photo_id?>">
                    <?php echo translate("title") ?>
                  </label>
                  <?php echo create_text_input("__title__$photo_id",
                    $photo->get("title"), 30, 64) ?>
                  <br>
                  <label for="date__<?php echo $photo_id ?>">
                    <?php echo translate("date") ?>
                  </label>
                  <?php echo create_text_input("__date__$photo_id",
                    $photo->get("date") , 12, 10, "date") ?>
                  <span class="inputhint">YYYY-MM-DD</span><br>
                  <label for="time__<?php echo $photo_id ?>">
                    <?php echo translate("time") ?>
                  </label>
                  <?php echo create_text_input("__time__$photo_id",
                    $photo->get("time"), 10, 8, "time") ?>
                  <span class="inputhint">HH:MM:SS</span><br>
                  <label for="location_id__<?php echo $photo_id ?>">
                    <?php echo translate("location") ?>
                  </label>
                  <?php echo place::createPulldown("__location_id__$photo_id",
                    $photo->get("location_id")) ?><br>
                  <label for="photographer_id__<?php echo $photo_id?>">
                    <?php echo translate("photographer") ?>
                  </label>
                  <?php echo photographer::createPulldown("__photographer_id__$photo_id",
                    $photo->get("photographer_id")) ?><br>
            <?php
            if (conf::get("feature.rating")) {
                $rating = $photo->getRatingForUser($user);
                ?>
                  <label for="rating__<?php echo $photo_id?>">
                    <?php echo translate("rating") ?>
                  </label>
                  <?php echo create_rating_pulldown($rating, "_rating__$photo_id") ?>
                  <br>
                <?php
            }
            ?>
                  <label for="description__<?php echo $photo_id?>">
                    <?php echo translate("description") ?>
                  </label>
                  <textarea name="__description__<?php echo $photo_id ?>"
                    id="description__<?php echo $photo_id?>" class="desc"
                    cols="50" rows="3">
                    <?php echo $photo->get("description") ?>
                  </textarea>
                  <br>
                  <label for="album__<?php echo $photo_id?>">
                    <?php echo translate("albums") ?>
                  </label>
                  <fieldset class="checkboxlist">
            <?php
            $albums = $photo->getAlbums($user);
            if ($albums) {
                $append = "";
                foreach ($albums as $album) {
                    ?>
                    <?php echo $append ?>
                    <input type="checkbox" name="_remove_album__<?php echo $photo_id ?>[]"
                    value="<?php echo $album->get("album_id") ?>">
                    <?php echo $album->getLink() ?>
                    <?php
                    $append = "<br>";
                }
                echo "<br>\n";
            }
            ?>
                  <fieldset class="multiple">
                    <?php echo album::createPulldown("_album__" . $photo_id . "[0]") ?>
                  </fieldset><br>
              </fieldset><br>
                  <label for="category__<?php echo $photo_id?>">
                    <?php echo translate("categories") ?>
                   </label>
                  <fieldset class="checkboxlist">
            <?php
            $categories = $photo->getCategories($user);
            if ($categories) {
                $append = "";
                foreach ($categories as $category) {
                    ?>
                    <?php echo $append ?>
                    <input type="checkbox" name="_remove_category__<?php echo $photo_id ?>[]"
                        value="<?php echo $category->get("category_id") ?>">
                    <?php
                    echo $category->getLink();
                    $append = "<br>\n";
                }
                echo "<br>\n";
            }
            ?>
                  <fieldset class="multiple">
                    <?php echo category::createPulldown("_category__" . $photo_id . "[0]") ?>
                  </fieldset><br>
              </fieldset><br>
              <label for="person_0__<?php echo $photo_id ?>">
                <?php echo translate("people") ?>
              </label>
              <fieldset class="checkboxlist multiple">
            <?php
            $people = $photo->getPeople();
            if ($people) {
                $append = "";
                foreach ($people as $person) {
                    ?>
                    <?php echo $append ?>
                    <input type="checkbox" name="_remove_person__<?php echo $photo_id ?>[]"
                        value="<?php echo $person->get("person_id") ?>">
                    <?php echo $person->getLink() ?>
                    <?php
                    $append = "<br>\n";
                }
                echo "<br>\n";
            }
            ?>
               <?php echo person::createPulldown("_person__" . $photo_id . "[0]") ?>
                  </fieldset><br>
                 <br>
              </fieldset>
              </fieldset>
        <?php
        }
    }
    ?>
    <?php echo create_form($clean_vars, $queryIgnoreArray) ?>
              <input type="submit" value="<?php echo translate("update", 0) ?>">
              </p>
              </form>

    <?php
    // Here we clean out $request_vars, so the pager links will not contain
    // all the edits made on this page.
    while (list($key, $val) = each($clean_vars)) {
        if (in_array($key, $queryIgnoreArray)) { continue; }
        $pager_vars[$key] = $val;
    }
    $request_vars = $pager_vars;
    echo new pager($offset, $photoCount, $pageCount, $cells,
        $user->prefs->get("max_pager_size"), $request_vars, "_off");
} // if photos
?>
<br>
</div>

<?php require_once "footer.inc.php"; ?>
