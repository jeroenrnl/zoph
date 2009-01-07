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
?>

<!-- begin edit_user.inc !-->
          <h1>
            <span class="actionlink">
              <a href="users.php"><?php echo translate("return") ?></a> |
              <a href="user.php?_action=new"><?php echo translate("new") ?></a>
            </span>
            <?php echo translate("add/edit user") ?>
          </h1>
      <div class="main">
        <form action="user.php" method="POST">
          <input type="hidden" name="_action" value="<?php echo $action ?>">
          <input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<?php
    if ($action == "insert") {
?>
           <input type="hidden" name="lastnotify" value="now()">
<?php
    }
?>
           <label for="username"><?php echo translate("user name") ?></label>
           <?php echo create_text_input("user_name", $this_user->get("user_name"), 16, 16) ?>
           <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "16") ?></span>
           <br>
          <label for="personid"><?php echo translate("person") ?></label>
          <?php echo create_pulldown("person_id", $action == "insert" ? "1" : $this_user->get("person_id"), get_people_select_array()) ?><br>
<?php 
    if($_action=="new") { 
?>
          <label for="password"><?php echo translate("password") ?></label>
          <input type="password" name="password" id="password" value="" size="16" maxlength="32">
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
<?php
    } else {
?>
        <span class="actionlink"><a href="password.php?userid=<?php echo $this_user->get("user_id")?>"> 
            <?php echo translate("change password")?>
        </a></span>
<?php
    }
?>
          <label for="userclass"><?php echo translate("class") ?></label>
          <?php echo create_pulldown("user_class", $this_user->get("user_class"), array("1" => translate("User",0), "0" => translate("Admin",0)) ) ?>
          <br>
          <label for="browsepeople"><?php echo translate("can browse people") ?></label>
<?php echo create_pulldown("browse_people", $this_user->get("browse_people"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="browseplaces"><?php echo translate("can browse places") ?></label>
          <?php echo create_pulldown("browse_places", $this_user->get("browse_places"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?><br>
          <label for="detailedpeople"><?php echo translate("can view details of people") ?></label>
          <?php echo create_pulldown("detailed_people", $this_user->get("detailed_people"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="detailedplaces"><?php echo translate("can view details of places") ?></label>
           <?php echo create_pulldown("detailed_places", $this_user->get("detailed_places"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="import"><?php echo translate("can import") ?></label>
          <?php echo create_pulldown("import", $this_user->get("import"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="download"><?php echo translate("can download zipfiles") ?></label>
          <?php echo create_pulldown("download", $this_user->get("download"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="leave_comments"><?php echo translate("can leave comments") ?></label>
          <?php echo create_pulldown("leave_comments", $this_user->get("leave_comments"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="allow_rating"><?php echo translate("can rate photos") ?></label>
          <?php echo create_pulldown("allow_rating", $this_user->get("allow_rating"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="allow_multirating"><?php echo translate("can rate the same photo multiple times") ?></label>
          <?php echo create_pulldown("allow_multirating", $this_user->get("allow_multirating"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          <br>
          <label for="lightboxid"><?php echo translate("lightbox album") ?></label>
<?php
    $lightbox_array = get_albums_select_array();
    $lightbox_array["null"] = "[none]";
?>
<?php echo create_pulldown("lightbox_id", $this_user->get("lightbox_id"), $lightbox_array) ?>
        <br>
            <input type="submit" value="<?php echo translate($action, 0) ?>">
</form>
<!-- end edit_user.inc !-->
