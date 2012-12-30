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

  /* **************************************************************************
   * define_annotated_photo.php
   * Creates a form showing all populated fields in tne photos record.
   * Field values are displayed as text boxes so user can edit if desired.
   * Checkboxes determine if field is included, and are of the form field_cb
   *   for easier parsing.
   *
   * Copyright 2003 Nixon P. Childs
   * **************************************************************************/

    require_once("include.inc.php");

    if (!conf::get("feature.annotate")) {
        redirect(add_sid("zoph.php"));
    }

    $title = translate("Annotate Photo");
    require_once("header.inc.php");
    $photo_id = getvar("photo_id");
    $photo = new photo($photo_id);
    $found = $photo->lookup();
?>
          <h1><?php echo strtolower($title) ?></h1>
      <div class="main">
        <form action="mail.php" method="POST">
<?php
    if (!$found) {
?>
            <?php echo sprintf(translate("Could not find photo id %s."), $photo_id); ?>
<?php
    }
    else {
?>
            <input type="hidden" name="_action" value="compose">
            <input type="hidden" name="annotate" value="1">
            <input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
            <img src="<?php echo $photo->getURL("mid")?>" class="mid" ALT="<?php $photo->get("title") ?>">
          <br>
          <label for="size"><?php echo translate("send fullsize") ?></label>
          <?php echo create_pulldown("_size", "mid", array("full" => translate("Yes",0), "mid" => translate("No",0)) ) ?><br>
          <input type="checkbox" name="photo_title_cb">
          <label for="photo_title"><?php echo translate("title") ?></label>
<?php echo create_text_input("photo_title", $photo->get("title"), 35, 50) ?><br>
<?php
        $location = "";
        $place_id = $photo->get("location_id");
        if($place_id) {
           $place = new place($place_id);
           $place->lookup();
           $location = $place->get("title") ? $place->get("title") : $place->get("city");
        }
?>
          <input type="checkbox" name="location_cb">
          <label for="location"><?php echo translate("location") ?></label>
          <?php echo create_text_input("location", $location, 35, 50) ?><br>
<?php
        //}
        if($photo->get("date")) {
?>
          <input type="checkbox" name="date_cb">
          <label for="date"><?php echo translate("date") ?></label>
          <?php echo create_text_input("date", $photo->get("date"), 35, 50) ?><br>
<?php
        }
        if($photo->get("time")) {
?>
          <input type="checkbox" name="time_cb">
          <label for="time"><?php echo translate("time") ?></label>
          <?php echo create_text_input("time", $photo->get("time"), 35, 50) ?><br>
<?php
        }
        //if($photo->get("view")) {
?>
          <input type="checkbox" name="view_cb">
          <label for="view"><?php echo translate("view") ?></label>
          <?php echo create_text_input("view", $photo->get("view"), 35, 50) ?><br>
<?php
        //}
        $photographer = "";
        $p_id = $photo->get("photographer_id");
        if($p_id) {
            $person = new person($p_id);
            $person->lookup();
            $photographer = $person->getName();
        }
?>
          <input type="checkbox" name="photographer_cb">
          <label for="photographer"><?php echo translate("photographer") ?></label>
          <?php echo create_text_input("photographer", $photographer, 15, 28) ?><br>
<?php
        //}
        //if($photo->get("description")) {
?>
          <input type="checkbox" name="description_cb">
          <label for="description"><?php echo translate("description") ?></label>
          <textarea name="description" rows="2" cols="35"><?php echo $photo->get("description") ?></textarea><br>
<?php
        //}
        $people_string = "";
        $people = $photo->lookup_people();
        if ($people) {
            $count = 0;
            foreach ($people as $person) {
               if ($count > 0) { $people_string .= ", "; }
               $count++;
               $people_string .= $person->getName();
            }
        }
?>
          <input type="checkbox" name="people_cb">
          <label for="people"><?php echo translate("people") ?></label>
          <textarea name="people" rows="2" cols="35"><?php echo $people_string ?></textarea><br>
<?php
        //}
        if($photo->get("camera_make")) {
?>
        
          <input type="checkbox" name="camera_make_cb">
          <label for="camera_make"><?php echo translate("camera make") ?></label>
          <?php echo create_text_input("camera_make", $photo->get("camera_make"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("camera_model")) {
?>
        
          <input type="checkbox" name="camera_model_cb">
          <label for="camera_model"><?php echo translate("camera model") ?></label>
          <?php echo create_text_input("camera_model", $photo->get("camera_model"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("flash_used")) {
?>
        
          <input type="checkbox" name="flash_used_cb">
          <label for="flash_used"><?php echo translate("flash used") ?></label>
          <?php echo create_text_input("flash_used", $photo->get("flash_used"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("focal_length")) {
?>
        
          <input type="checkbox" name="focal_length_cb">
          <label for="focal_length"><?php echo translate("focal length") ?></label>
          <?php echo create_text_input("focal_length", $photo->get("focal_length"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("exposure")) {
?>
        
          <input type="checkbox" name="exposure_cb">
          <label for="exposure"><?php echo translate("exposure") ?></label>
          <?php echo create_text_input("exposure", $photo->get("exposure"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("aperature")) {
?>
        
          <input type="checkbox" name="aperture_cb">
          <label for="aperture"><?php echo translate("aperture") ?></label>
          <?php echo create_text_input("aperture", $photo->get("aperture"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("compression")) {
?>
        
          <input type="checkbox" name="compression_cb">
          <label for="compression"><?php echo translate("compression") ?></label>
          <?php echo create_text_input("compression", $photo->get("compression"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("iso_equiv")) {
?>
        
          <input type="checkbox" name="iso_equiv_cb">
          <label for="iso_equiv"><?php echo translate("iso equiv") ?></label>
          <?php echo create_text_input("iso_equiv", $photo->get("iso_equiv"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("metering_mode")) {
?>
        
          <input type="checkbox" name="metering mode_cb">
          <label for="metering_mode"><?php echo translate("metering mode") ?></label>
          <?php echo create_text_input("metering_mode", $photo->get("metering_mode"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("focus_dist")) {
?>
        
          <input type="checkbox" name="focus_dist_cb">
          <label for="focus_dist"><?php echo translate("focus distance") ?></label>
          <?php echo create_text_input("focus_dist", $photo->get("focus_dist"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("ccd_width")) {
?>
        
          <input type="checkbox" name="ccd_width_cb">
          <label for="ccd_width"><?php echo translate("ccd width") ?></label>
          <?php echo create_text_input("ccd_width", $photo->get("ccd_width"), 35, 50) ?>
        <br>
<?php
        }
        if($photo->get("comment")) {
?>
        
          <input type="checkbox" name="comment_cb">
          <label for="comment"><?php echo translate("comment") ?></label>
          <?php echo create_text_input("comment", $photo->get("comment"), 35, 50) ?>
        <br>
<?php
        }
?>
        
          <input type="checkbox" name="extra_cb">
          <label><?php echo create_text_input("extra_name", "", 15, 50) ?></label>
          <?php echo create_text_input("extra", "", 35, 50) ?>
        <br>
       
            <input class="bigbutton" type="submit" value="<?php echo translate("Annotate Photo") ?>">
          
<?php
    } // if found
?>
        </form>
    
  <br>
</div>
<?php require_once("footer.inc.php"); ?>
