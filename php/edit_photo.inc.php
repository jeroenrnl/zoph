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
    $return_qs = getvar("_qs");

    if (empty($return_qs)) {
       if ($user->prefs->get("auto_edit")) {
           $return_qs=htmlentities(urldecode($qs));
       } else {
           $return_qs = "_action=display&amp;photo_id=" . $photo->get("photo_id");
       }
    }
?>
          <h1>
          <span class="actionlink">
<?php
    if ($user->prefs->get("auto_edit")) {
?>
            <a href="photo.php?_action=display&amp;<?php echo $return_qs ?>"><?php echo translate("return") ?></a>
<?php
    } else {
?>
            <a href="photo.php?<?php echo $return_qs ?>"><?php echo translate("return") ?></a>
<?php
    }
        if ($user->is_admin()) {
/*            | <a href="photo.php?_action=delete&amp;photo_id=<?php echo $photo->get("photo_id") ?>"><?php echo translate("delete") ?></a> */
?>

            | <a href="photo.php?_action=delete&amp;photo_id=<?php echo $photo->get("photo_id") ?>&amp;_qs=<?php echo $encoded_qs ?>"><?php echo translate("delete") ?></a>
            
<?php
        }
?>
            </span>
          <?php echo translate("photo") ?>
          </h1>
      <div class="main">
      <form action="photo.php" method="POST">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="_qs" value="<?php echo $return_qs ?>">

<?php
    if ($action == "insert") {
?>
      <table class="newphoto">
        <tr>
          <td><?php echo translate("file name") ?></td>
          <td><?php echo create_text_input("name", $photo->get("name"), 40, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
</tr>
</table>
<?php
    }
    else {
?>
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
<?php
    if (ALLOW_ROTATIONS && ($user->is_admin() || $permissions->get("writable"))) {
?>
          <div class="rotate">
<?php echo translate("rotate", 0) ?>

<select name="_deg">
<option>&nbsp;</option>
<option>90</option>
<option>180</option>
<option>270</option>
</select>

<br>
<?php echo translate("recreate thumbnails", 0) ?>

<input type="radio" name="_thumbnail" value="1">
<?php echo translate("yes") ?>

<input type="radio" name="_thumbnail" value="0" checked>
<?php echo translate("no") ?>
</div>
<?php
    }
?>

                <div id="prev"><?php echo $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></div>
                <div id="photohdr">
                  <?php echo $photo->get_fullsize_link($photo->get("name"),$FULLSIZE_NEW_WIN) ?> :
                  <?php echo $photo->get("width") ?> x <?php echo $photo->get("height") ?>,
                  <?php echo $photo->get("size") ?> <?php echo translate("bytes") ?>
                </div>
                <div id="next"><?php echo $next_link ? "[ $next_link ]" : "&nbsp;" ?></div>
            <?php echo $photo->get_fullsize_link($photo->get_midsize_img(),$FULLSIZE_NEW_WIN) ?>
<?php
    }
?>
          <input class="updatebutton" type="submit" value="<?php echo translate($action, 0) ?>">
        <table id="editphoto">
        <tr>
          <td class="fieldtitle"><?php echo translate("title") ?></td>
          <td class="field"><?php echo create_text_input("title", $photo->get("title"), 40, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("location") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("location_id", $photo->get("location_id"), get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("date") ?></td>
          <td class="field"><?php echo create_text_input("date", $photo->get("date"), 12, 10) ?></td>
          <td class="inputhint">YYYY-MM-DD</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("time") ?></td>
          <td class="field"><?php echo create_text_input("time", $photo->get("time"), 10, 8) ?></td>
          <td class="inputhint">HH:MM:SS</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("view") ?></td>
          <td class="field"><?php echo create_text_input("view", $photo->get("view"), 40, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
<?php
    // if people are allowed to rate photos, the rating field
    // is an average so don't edit it.
    if (!ALLOW_RATINGS) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("rating") ?></td>
          <td class="field">
            <?php echo create_rating_pulldown($photo->get("rating")) ?>
          </td>
          <td class="inputhint">1 - 10</td>
        </tr>
<?php
    }
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("photographer") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("photographer_id", $photo->get("photographer_id"), get_people_select_array()) ?>
          </td>
        </tr>
<?php
    if ($user->is_admin()) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("level") ?></td>
          <td class="field"><?php echo create_text_input("level", $photo->get("level"), 4, 2) ?></td>
          <td class="inputhint">1 - 10</td>
        </tr>
<?php
    }
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("description") ?></td>
          <td class="field" colspan="2">
            <textarea name="description" cols="60" rows="4"><?php echo $photo->get("description") ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3"><hr></td>
        </tr>
<?php
        if ($action != "insert") {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("people") ?></td>
          <td class="field">
<?php
        $people = $photo->lookup_people();
        $next_pos  = 1;
        $ppl_links = "";
        if ($people) {
            foreach ($people as $person) {
                $next_pos++;
                $ppl_links .= "<input type=\"checkbox\" name=\"_remove_person[]\" value=\"" . $person->get("person_id") . "\">" . translate("remove") . "<br>\n";
?>
              <?php echo $person->get_link() ?><br>
<?php
            }
        }
        else {
?>
              <?php echo translate("No people have been added to this photo.") ?><br>
<?php
        }
        for ($i = 0; $i < $PEOPLE_SLOTS; $i++ ) {
?>
            <?php echo create_smart_pulldown("_person_" . $i, "", get_people_select_array()) ?>
            <?php echo translate("position") ?>:
            <?php echo create_text_input("_position_" . $i, ($next_pos + $i), 2, 2) ?>
            <br>
<?php
        }
?>
            <p class="inputhint"><?php echo translate("(left to right, front to back).") ?></p>
          </td>
          <td class="remove">
            <?php echo $ppl_links ? $ppl_links : "&nbsp;" ?>
          </td>
        </tr>
        <tr>
          <td colspan="3"><hr class="wide"></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("albums") ?></td>
          <td class="field">
<?php
        $albums = $photo->lookup_albums($user);
        $alb_links = "";
        if ($albums) {
            foreach ($albums as $album) {
                $alb_links .= "<input type=\"checkbox\" name=\"_remove_album[]\" value=\"" . $album->get("album_id") . "\">" . translate("remove") . "<br>\n";
?>
              <?php echo $album->get_link() ?><br>
<?php
            }
        }
        else {
            echo translate("This photo is not in any albums.");
            echo "<br>\n";
        }
        echo create_pulldown("_album", "", get_albums_select_array($user)) 
        ?>
          </td>
          <td class="remove">
            <?php echo $alb_links ? $alb_links : "&nbsp;" ?>
          </td>
        </tr>
        <tr>
          <td colspan="3"><hr class="wide"></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("categories") ?></td>
          <td class="field">
<?php
        $categories = $photo->lookup_categories($user);
        $cat_links = "";
        if ($categories) {
            foreach ($categories as $category) {
                $cat_links .= "<input type=\"checkbox\" name=\"_remove_category[]\" value=\"" . $category->get("category_id") . "\">" . translate("remove") . "<br>\n";
?>
              <?php echo $category->get_link() ?><br>
<?php
            }
        }
        else {
?>
              <?php echo translate("This photo is not in any categories.") ?><br>
<?php
        }
?>
            <?php echo create_pulldown("_category", "", get_categories_select_array($user)) ?>
          </td>
          <td class="remove">
            <?php echo $cat_links ? $cat_links : "&nbsp;" ?>
          </td>
        </tr>
<?php
        $_show = getvar("_show");
        if ($_show) {
?>
        <tr>
          <td colspan="3"><hr></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("path") ?></td>
          <td class="field"><?php echo create_text_input("path", $photo->get("path"), 40, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("width") ?></td>
          <td class="field"><?php echo create_text_input("width", $photo->get("width"), 6, 6) ?></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("height") ?></td>
          <td class="field"><?php echo create_text_input("height", $photo->get("height"), 6, 6) ?></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("camera make") ?></td>
          <td class="field"><?php echo create_text_input("camera_make", $photo->get("camera_make"), 32, 32) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("camera model") ?></td>
          <td class="field"><?php echo create_text_input("camera_model", $photo->get("camera_model"), 32, 32) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("flash used") ?></td>
          <td class="field">
<?php echo create_pulldown("flash_used", $photo->get("flash_used"), array("" => "", "Y" => translate("Yes",0), "N" => translate("No",0))) ?>
          </td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("focal length") ?></td>
          <td class="field"><?php echo create_text_input("focal_length", $photo->get("focal_length"), 10, 64) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("exposure") ?></td>
          <td class="field"><?php echo create_text_input("exposure", $photo->get("exposure"), 32, 64) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("aperture") ?></td>
          <td class="field"><?php echo create_text_input("aperture", $photo->get("aperture"), 8, 16) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("compression") ?></td>
          <td class="field"><?php echo create_text_input("compression", $photo->get("compression"), 32, 64) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("iso equiv") ?></td>
          <td class="field"><?php echo create_text_input("iso_equiv", $photo->get("iso_equiv"), 8, 8) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("metering mode") ?></td>
          <td class="field"><?php echo create_text_input("metering_mode", $photo->get("metering_mode"), 16, 16) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("focus distance") ?></td>
          <td class="field"><?php echo create_text_input("focus_dist", $photo->get("focus_dist"), 16, 16) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("ccd width") ?></td>
          <td class="field"><?php echo create_text_input("ccd_width", $photo->get("ccd_width"), 16, 16) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("comment") ?></td>
          <td class="field"><?php echo create_text_input("comment", $photo->get("comment"), 40, 128) ?></td>
          <td class="inputhint">&nbsp;</td>
        </tr>
<?php
        } // additional atts
?>
        <tr>
          <td colspan="2" class="showattr">
<?php
        if (!$_show) {
?>
            <a href="photo.php?_action=edit&amp;photo_id=<?php echo $photo->get("photo_id") ?>&amp;_show=all"><?php echo translate("show additional attributes") ?></a>
<?php
        }
        else {
?>
            &nbsp;
<?php
        }
?>
          <td colspan="1" class="right"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr>
      </table>
</form>
<?php
        }
?>
