<!-- begin edit_user.inc !-->
        <tr>
          <th><h1><?php echo translate("add/edit user") ?></h1></th>
          <td class="actionlink">[
            <a href="users.php"><?php echo translate("return") ?></a> |
            <a href="user.php?_action=new"><?php echo translate("new") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="user.php" method="POST">
      <table class="main">
        <tr>
          <td class="fieldtitle">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<?php
    if ($action == "insert") {
?>
<input type="hidden" name="lastnotify" value="now()">
<?php
    }
?>

<?php echo translate("user name") ?>
          </td>
          <td class="field"><?php echo create_text_input("user_name", $this_user->get("user_name"), 16, 16) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "16") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("person") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("person_id", $action == "insert" ? "1" : $this_user->get("person_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("password") ?></td>
          <td><input type="password" name="password" value="" size="16" maxlength="32"></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("class") ?></td>
          <td colspan="2">
<?php echo create_pulldown("user_class", $this_user->get("user_class"), array("1" => translate("User",0), "0" => translate("Admin",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("can browse people") ?></td>
          <td colspan="2">
<?php echo create_pulldown("browse_people", $this_user->get("browse_people"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("can browse places") ?></td>
          <td colspan="2">
<?php echo create_pulldown("browse_places", $this_user->get("browse_places"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("can view details of people") ?></td>
          <td colspan="2">
<?php echo create_pulldown("detailed_people", $this_user->get("detailed_people"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("can view details of places") ?></td>
          <td colspan="2">
<?php echo create_pulldown("detailed_places", $this_user->get("detailed_places"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("can import") ?></td>
          <td colspan="2">
<?php echo create_pulldown("import", $this_user->get("import"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("lightbox album") ?></td>
          <td colspan="2">
<?php
    $lightbox_array = get_albums_select_array();
    $lightbox_array["null"] = "[none]";
?>
<?php echo create_smart_pulldown("lightbox_id", $this_user->get("lightbox_id"), $lightbox_array) ?>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="center">
            <input type="submit" value="<?php echo translate($action, 0) ?>">
          </td>
        </tr>
      </table>
</form>
    </td>
  </tr>
<!-- end edit_user.inc !-->
