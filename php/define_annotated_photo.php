<?php
  /* **************************************************************************
   * define_annotated_photo.php
   * Creates a form showing all populated fields in tne photos record.
   * Field values are displayed as text boxes so user can edit if desired.
   * Checkboxes determine if field is included, and are of the form field_cb
   *   for easier parsing.
   *
   * Copyright 2003 Nixon P. Childs
   *  License: The same as the rest of Zoph.
   * **************************************************************************/

    require_once("include.inc.php");

    if (!ANNOTATE_PHOTOS) {
        header("Location: " . add_sid("zoph.php"));
    }

    $title = 'Annotate Photo';
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
    $photo_id = getvar("photo_id");
    $photo = new photo($photo_id);
    $found = $photo->lookup($user);
?>
  <tr>
    <td>
      <table class="titlebar"
        <tr>
          <th><h1><?php echo strtolower(translate("Annotate Photo", 0)) ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
        <form action="mail.php" method="POST">
      <table class="main">
<?php
    if (!$found) {
?>
        <tr>
          <td class="error">
            <?php echo sprintf(translate("Could not find photo id %s."), $photo_id); ?>
          </td>
<?php
    }
    else {
?>
        <tr>
          <td colspan="3" class="photo">
            <input type="hidden" name="_action" value="compose">
            <input type="hidden" name="annotate" value="1">
            <input type="hidden" name="photo_id" value="<?= $photo->get("photo_id") ?>">
            <img src="<?= $photo->get_image_href("mid") ?>" ALT="<?= $photo->get("title") ?>">
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <th class="fieldtitle"><?php echo translate("send fullsize") ?></th>
          <td class="field"><?php echo create_pulldown("_size", "mid", array("full" => translate("Yes",0), "mid" => translate("No",0)) ) ?></td>
        </tr>
        <tr>
          <td class="checkbox"><input type="checkbox" name="photo_title_cb"></td>
          <th class="fieldtitle"><?php echo translate("title") ?></th>
          <td class="field"><?= create_text_input("photo_title", $photo->get("title"), 35, 50) ?></td>
        </tr>

<?php
        $location = "";
        $place_id = $photo->get("location_id");
        if($place_id) {
           $place = new place($place_id);
           $place->lookup();
           $location = $place->get("title") ? $place->get("title") : $place->get("city");
        }
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="location_cb"></td>
          <th class="fieldtitle"><?php echo translate("location") ?></th>
          <td class="field"><?= create_text_input("location", $location, 35, 50) ?></td>
        </tr>
<?php
        //}
        if($photo->get("date")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="date_cb"></td>
          <th class="fieldtitle"><?php echo translate("date") ?></th>
          <td class="field"><?= create_text_input("date", $photo->get("date"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("time")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="time_cb"></td>
          <th class="fieldtitle"><?php echo translate("time") ?></th>
          <td class="field"><?= create_text_input("time", $photo->get("time"), 35, 50) ?></td>
        </tr>
<?php
        }
        //if($photo->get("view")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="view_cb"></td>
          <th class="fieldtitle"><?php echo translate("view") ?></th>
          <td class="field"><?= create_text_input("view", $photo->get("view"), 35, 50) ?></td>
        </tr>
<?php
        //}
        $photographer = "";
        $p_id = $photo->get("photographer_id");
        if($p_id) {
            $person = new person($p_id);
            $person->lookup();
            $photographer = $person->get_name();
        }
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="photographer_cb"></td>
          <th class="fieldtitle"><?php echo translate("photographer") ?></th>
          <td class="field"><?= create_text_input("photographer", $photographer, 15, 28) ?></td>
        </tr>
<?php
        //}
        //if($photo->get("description")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="description_cb"></td>
          <th class="fieldtitle"><?php echo translate("description") ?></th>
          <td class="field"><textarea name="description" rows="2" cols="35"><?= $photo->get("description") ?></textarea></td>
        </tr>
<?php
        //}
        $people_string = "";
        $people = $photo->lookup_people();
        if ($people) {
            $count = 0;
            foreach ($people as $person) {
               if ($count > 0) { $people_string .= ", "; }
               $count++;
               $people_string .= $person->get_name();
            }
        }
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="people_cb"></td>
          <th class="fieldtitle"><?php echo translate("people") ?></th>
          <td class="field"><textarea name="people" rows="2" cols="35"><?= $people_string ?></textarea></td>
        </tr>
<?php
        //}
        if($photo->get("camera_make")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="camera_make_cb"></td>
          <th class="fieldtitle"><?php echo translate("camera make") ?></th>
          <td class="field"><?= create_text_input("camera_make", $photo->get("camera_make"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("camera_model")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="camera_model_cb"></td>
          <th class="fieldtitle"><?php echo translate("camera model") ?></th>
          <td class="field"><?= create_text_input("camera_model", $photo->get("camera_model"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("flash_used")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="flash_used_cb"></td>
          <th class="fieldtitle"><?php echo translate("flash used") ?></th>
          <td class="field"><?= create_text_input("flash_used", $photo->get("flash_used"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("focal_length")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="focal_length_cb"></td>
          <th class="fieldtitle"><?php echo translate("focal length") ?></th>
          <td class="field"><?= create_text_input("focal_length", $photo->get("focal_length"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("exposure")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="exposure_cb"></td>
          <th class="fieldtitle"><?php echo translate("exposure") ?></th>
          <td class="field"><?= create_text_input("exposure", $photo->get("exposure"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("aperature")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="aperture_cb"></td>
          <th class="fieldtitle"><?php echo translate("aperture") ?></th>
          <td class="field"><?= create_text_input("aperture", $photo->get("aperture"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("compression")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="compression_cb"></td>
          <th class="fieldtitle"><?php echo translate("compression") ?></th>
          <td class="field"><?= create_text_input("compression", $photo->get("compression"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("iso_equiv")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="iso_equiv_cb"></td>
          <th class="fieldtitle"><?php echo translate("iso equiv") ?></th>
          <td class="field"><?= create_text_input("iso_equiv", $photo->get("iso_equiv"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("metering_mode")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="metering mode_cb"></td>
          <th class="fieldtitle"><?php echo translate("metering mode") ?></th>
          <td class="field"><?= create_text_input("metering_mode", $photo->get("metering_mode"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("focus_dist")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="focus_dist_cb"></td>
          <th class="fieldtitle"><?php echo translate("focus distance") ?></th>
          <td class="field"><?= create_text_input("focus_dist", $photo->get("focus_dist"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("ccd_width")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="ccd_width_cb"></td>
          <th class="fieldtitle"><?php echo translate("ccd width") ?></th>
          <td class="field"><?= create_text_input("ccd_width", $photo->get("ccd_width"), 35, 50) ?></td>
        </tr>
<?php
        }
        if($photo->get("comment")) {
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="comment_cb"></td>
          <th class="fieldtitle"><?php echo translate("comment") ?></th>
          <td class="field"><?= create_text_input("comment", $photo->get("comment"), 35, 50) ?></td>
        </tr>
<?php
        }
?>
        <tr>
          <td class="checkbox"><input type="checkbox" name="extra_cb"></td>
          <td class="field"><?= create_text_input("extra_name", "", 15, 50) ?></td>
          <td class="field"><?= create_text_input("extra", "", 35, 50) ?></td>
        </tr>
        <tr>
          <td colspan="3" class="center">
            <input class="bigbutton" type="submit" value="<?php echo translate("Annotate Photo") ?>">
          </td>
        </tr>
<?php
    } // if found
?>
      </table>
        </form>
    </td>
  </tr>
</table>
<?php require_once("footer.inc.php"); ?>
