        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("photo") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">[
            <a href="photo.php?_action=display&photo_id=<?= $photo->get("photo_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a>
<?php
        if ($user->is_admin()) {
?>
            | <a href="photo.php?_action=delete&photo_id=<?= $photo->get("photo_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a>
<?php
        }
?>
            ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<form action="photo.php" method="POST">
<input type="hidden" name="_action" value="<?= $action ?>">
<?php
    if ($action == "insert") {
?>
        <tr>
          <td><?php echo translate("file name") ?></td>
          <td><?= create_text_input("name", $photo->get("name"), 40, 64) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
<?php
    }
    else {
?>
<input type="hidden" name="photo_id" value="<?= $photo->get("photo_id") ?>">
        <tr>
          <td colspan="3" align="center">
            <table width="100%">
              <tr>
                <td align="left"><?= $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></td>
                <td align="center">
                  <font size="-1">
                  <?= $photo->get_fullsize_link($photo->get("name")) ?> :
                  <?= $photo->get("width") ?> x <?= $photo->get("height") ?>,
                  <?= $photo->get("size") ?> <?php echo translate("bytes") ?>
                  </font>
                </td>
                <td align="right"><?= $next_link ? "[ $next_link ]" : "&nbsp;" ?></td>
              </tr>
            </table>
        </tr>
        <tr>
          <td colspan="3" align="center">
            <?= $photo->get_fullsize_link($photo->get_midsize_img()) ?>
          </td>
        </tr>
<?php
    }
?>
        <tr>
          <td colspan="3" align="right"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr>
        <tr>
          <td><?php echo translate("title") ?></td>
          <td><?= create_text_input("title", $photo->get("title"), 40, 64) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("location") ?></td>
          <td colspan="2">
<?= create_smart_pulldown("location_id", $photo->get("location_id"), get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("date") ?></td>
          <td><?= create_text_input("date", $photo->get("date"), 12, 10) ?></td>
          <td><font size="-1">YYYY-MM-DD</font></td>
        </tr>
        <tr>
          <td><?php echo translate("time") ?></td>
          <td><?= create_text_input("time", $photo->get("time"), 10, 8) ?></td>
          <td><font size="-1">HH:MM:SS</font></td>
        </tr>
        <tr>
          <td><?php echo translate("view") ?></td>
          <td><?= create_text_input("view", $photo->get("view"), 40, 64) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("rating") ?></td>
          <td>
            <?= create_rating_pulldown($photo->get("rating")) ?>
          </td>
          <td><font size="-1">1 - 10</font></td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("photographer") ?></td>
          <td colspan="2">
<?= create_smart_pulldown("photographer_id", $photo->get("photographer_id"), get_people_select_array()) ?>
          </td>
        </tr>
<?php
    if ($user->is_admin()) {
?>
        <tr>
          <td><?php echo translate("level") ?></td>
          <td><?= create_text_input("level", $photo->get("level"), 4, 2) ?></td>
          <td><font size="-1">1 - 10</font></td>
        </tr>
<?php
    }
?>
        <tr>
          <td><?php echo translate("description") ?></td>
          <td colspan="2">
            <textarea name="description" cols="60" rows="4"><?= $photo->get("description") ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center"><hr></td>
        </tr>
<?php
        if ($action != "insert") {
?>
        <tr>
          <td valign="top"><?php echo translate("people") ?></td>
          <td>
<?php
        $people = $photo->lookup_people();
        $next_pos  = 1;
        $ppl_links = "";
        if ($people) {
            foreach ($people as $person) {
                $next_pos++;
                $ppl_links .= "[ <a href=\"photo.php?_action=update&_remove=1&photo_id=" . $photo->get("photo_id") . "&_person=" . $person->get("person_id") . "\">remove</a> ]<br>\n";
?>
              <?= $person->get_link() ?><br>
<?php
            }
        }
        else {
?>
              <?php echo translate("No people have been added to this photo.") ?><br>
<?php
        }
?>
            <?= create_smart_pulldown("_person", "", get_people_select_array()) ?>
            <?php echo translate("position") ?>:
            <?= create_text_input("_position", $next_pos, 2, 2) ?>
            <br>
            <font size="-1"><?php echo translate("(left to right, front to back).") ?></font>
          </td>
          <td valign="top">
            <?= $ppl_links ? $ppl_links : "&nbsp;" ?>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center"><hr width="90%"></td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("albums") ?></td>
          <td>
<?php
        $albums = $photo->lookup_albums($user);
        $alb_links = "";
        if ($albums) {
            foreach ($albums as $album) {
                $alb_links .= "[ <a href=\"photo.php?_action=update&_remove=1&photo_id=" . $photo->get("photo_id") . "&_album=" . $album->get("album_id") . "\">" . translate(remove) . "</a> ]<br>\n";
?>
              <?= $album->get_link() ?><br>
<?php
            }
        }
        else {
?>
              <?php echo translate("This photo is not in any albums.") ?><br>
<?php
        }
?>
            <?= create_pulldown("_album", "", get_albums_select_array($user)) ?>
          </td>
          <td valign="top">
            <?= $alb_links ? $alb_links : "&nbsp;" ?>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center"><hr width="90%"></td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("categories") ?></td>
          <td>
<?php
        $categories = $photo->lookup_categories($user);
        $cat_links = "";
        if ($categories) {
            foreach ($categories as $category) {
                $cat_links .= "[ <a href=\"photo.php?_action=update&_remove=1&photo_id=" . $photo->get("photo_id") . "&_category=" . $category->get("category_id") . "\">" . translate("remove") . "</a> ]<br>\n";
?>
              <?= $category->get_link() ?><br>
<?php
            }
        }
        else {
?>
              <?php echo translate("This photo is not in any categories.") ?><br>
<?php
        }
?>
            <?= create_pulldown("_category", "", get_categories_select_array($user)) ?>
          </td>
          <td valign="top">
            <?= $cat_links ? $cat_links : "&nbsp;" ?>
          </td>
        </tr>
<?php
        $_show = getvar("_show");
        if ($_show) {
?>
        <tr>
          <td colspan="3" align="center"><hr></td>
        </tr>
        <tr>
          <td><?php echo translate("path") ?></td>
          <td><?= create_text_input("path", $photo->get("path"), 40, 64) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("width") ?></td>
          <td><?= create_text_input("width", $photo->get("width"), 6, 6) ?></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><?php echo translate("height") ?></td>
          <td><?= create_text_input("height", $photo->get("height"), 6, 6) ?></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><?php echo translate("camera make") ?></td>
          <td><?= create_text_input("camera_make", $photo->get("camera_make"), 32, 32) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("camera model") ?></td>
          <td><?= create_text_input("camera_model", $photo->get("camera_model"), 32, 32) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("flash used") ?></td>
          <td>
<?= create_pulldown("flash_used", $photo->get("flash_used"), array("" => "", "Y" => translate("Yes",0), "N" => translate("No",0))) ?>
          </td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("focal length") ?></td>
          <td><?= create_text_input("focal_length", $photo->get("focal_length"), 10, 64) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("exposure") ?></td>
          <td><?= create_text_input("exposure", $photo->get("exposure"), 32, 64) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("aperture") ?></td>
          <td><?= create_text_input("aperture", $photo->get("aperture"), 8, 16) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("compression") ?></td>
          <td><?= create_text_input("compression", $photo->get("compression"), 32, 64) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("iso equiv") ?></td>
          <td><?= create_text_input("iso_equiv", $photo->get("iso_equiv"), 8, 8) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("metering mode") ?></td>
          <td><?= create_text_input("metering_mode", $photo->get("metering_mode"), 16, 16) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("focus distance") ?></td>
          <td><?= create_text_input("focus_dist", $photo->get("focus_dist"), 16, 16) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("ccd width") ?></td>
          <td><?= create_text_input("ccd_width", $photo->get("ccd_width"), 16, 16) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
        <tr>
          <td><?php echo translate("comment") ?></td>
          <td><?= create_text_input("comment", $photo->get("comment"), 40, 128) ?></td>
          <td><font size="-1">&nbsp;</font></td>
        </tr>
<?php
        } // additional atts
?>
        <tr>
          <td colspan="2" align="left">
<?php
        if (!$_show) {
?>
            <a href="photo.php?_action=edit&photo_id=<?= $photo->get("photo_id") ?>&_show=all"><?php echo translate("show additional attributes") ?></a>
<?php
        }
        else {
?>
            &nbsp;
<?php
        }
?>
          </td>
          <td colspan="1" align="right"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr>
</form>
<?php
        }
?>
