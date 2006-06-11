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

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $photo_id_1=getvar("photo_id_1");
    $photo_id_2=getvar("photo_id_2");

    $photo_1=new photo($photo_id_1);
    $photo_2=new photo($photo_id_2);
    
    $photo_1->lookup();
    $photo_2->lookup();
    
    if($_action == "insert" || $_action == "new") {
        if($photo_1->check_related($photo_2->get("photo_id"))) {
            $_action="edit";
        }
    }
    
    if($_action != "insert" && $_action != "new" && $_action != "update") {
        $desc_1 = $photo_2->get_relation_desc($photo_1->get("photo_id"));
        $desc_2 = $photo_1->get_relation_desc($photo_2->get("photo_id"));
    }
    
    // These are the same actiona as in actions.inc.php
    // However, that is not usable, as relation is not an object
    if ($_action == "new") {
        $action = "insert";
    } elseif($_action == "insert") {
        $desc_1=getvar("desc_1");
        $desc_2=getvar("desc_2");
        
	    $photo_1->create_relation($photo_id_2, $desc_1, $desc_2);
        $action="display";
    } elseif ($_action == "edit") {
        $action="update";
    } elseif ($_action == "delete") {
        $action="confirm";
    } elseif ($_action == "confirm") {
        $photo_1->delete_relation($photo_id_2);
        $_action = "new";
        $action = "insert"; // in case redirect doesn't work

        $user->eat_crumb();
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = $redirect; }
        header("Location: " . add_sid($link));
    } elseif ($_action == "update") {
        $desc_1=getvar("desc_1");
        $desc_2=getvar("desc_2");
	    $photo_1->update_relation($photo_id_2, $desc_1, $desc_2);
        $action="display";
    } else {
        $action = "display";
    }
    
    if($action=="display") {
        $title=translate("relationship");
    } else {
        $title=translate($action . " relationship");    
    }

    require_once("header.inc.php");

    if ($action == "confirm") {
?>
          <h1><?php echo translate("delete relationship") ?></h1>
          <div class="main">
              <span class="actionlink">
                  <a href="relation.php?_action=confirm&amp;photo_id_1=<?php echo $photo_id_1 ?>&photo_id_2=<?php echo $photo_id_2 ?>"><?php echo translate("delete") ?></a> |
                  <a href="relation.php?_action=edit&amp;photo_id_1=<?php echo $photo_id_1 ?>&photo_id_2=<?php echo $photo_id_2 ?>"><?php echo translate("cancel") ?></a> 
              </span>
              <?php echo translate("Confirm deletion of this relationship") ?>
              <br>
              <div id="relation">
                  <div class="thumbnail">
                      <?php echo $photo_1->get_image_tag("thumb") ?><br>
                      <?php echo $desc_1 ?>
                  </div>
                  <div class="thumbnail">
                      <?php echo $photo_2->get_image_tag("thumb") ?>
                      <?php echo $desc_2 ?>
                  </div>
              </div>
              <br>
          </div>
<?php
    }
    else if ($action == "display") {
?>
          <h1>
            <span class="actionlink">
              <a href="photo.php?photo_id=<?php echo $photo_id_1 ?>"><?php echo translate("return") ?></a> |
              <a href="relation.php?_action=edit&amp;photo_id_1=<?php echo $photo_id_1 ?>&amp;photo_id_2=<?php echo $photo_id_2 ?>"><?php echo translate("edit") ?></a> |
              <a href="relation.php?_action=delete&amp;photo_id_1=<?php echo $photo_id_1 ?>&amp;photo_id_2=<?php echo $photo_id_2 ?>"><?php echo translate("delete") ?></a>
            </span>
<?php
     echo $title;
?>
          </h1>
      <div class="main">
          <br>
          <div id="relation">
              <div class="thumbnail">
                  <?php echo $photo_1->get_image_tag("thumb") ?><br>
                  <?php echo $desc_1 ?>
              </div>
              <div class="thumbnail">
                  <?php echo $photo_2->get_image_tag("thumb") ?><br>
                  <?php echo $desc_2 ?>
              </div>
          </div>
          <br>

      </div>
<?php
    }
   else {
?>
    <h1>
        <?php echo $title ?>
    </h1>
    <div class="main">
    <br>
       <div id="relation">
          <div class="thumbnail">
              <?php echo $photo_1->get_image_tag("thumb") ?><br>
              <?php echo $photo_1->get("name"); ?>
          </div>
          <div class="thumbnail">
              <?php echo $photo_2->get_image_tag("thumb") ?>
              <?php echo $photo_2->get("name"); ?>
          </div>
       </div>
    <br>

        <form action="relation.php">
            <input type="hidden" name="_action" value="<?php echo $action ?>">
            <input type="hidden" name="photo_id_1" value="<?php echo $photo_id_1 ?>">
            <input type="hidden" name="photo_id_2" value="<?php echo $photo_id_2 ?>">
            <label for="desc_1"><?php echo translate("Description for first photo") ?></label>
            <?php echo create_text_input("desc_1", $desc_1) ?><br>
            <label for="desc_2"><?php echo translate("Description for second photo") ?></label>
            <?php echo create_text_input("desc_2", $desc_2) ?><br>
            <input type="submit" value="<?php echo translate($action, 0) ?>">
        </form>
    </div>

<?php
  }
    require_once("footer.inc.php");
?>
