<!---- begin edit_user.inc ---->
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("add/edit user") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">[
            <a href="users.php"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a> |
            <a href="user.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <td>
<form action="user.php" method="POST">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<?php echo translate("user name") ?>
          </td>
          <td><?php echo create_text_input("user_name", $this_user->get("user_name"), 16, 16) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "16") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("person") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("person_id", $action == "insert" ? "1" : $this_user->get("person_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("password") ?></td>
          <td><input type="password" name="password" value="" size="16" maxlength="32"></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("class") ?></td>
          <td colspan="2">
<?php echo create_pulldown("user_class", $this_user->get("user_class"), array("1" => translate("User",0), "0" => translate("Admin",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("can browse people") ?></td>
          <td colspan="2">
<?php echo create_pulldown("browse_people", $this_user->get("browse_people"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("can browse places") ?></td>
          <td colspan="2">
<?php echo create_pulldown("browse_places", $this_user->get("browse_places"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("can view details of people") ?></td>
          <td colspan="2">
<?php echo create_pulldown("detailed_people", $this_user->get("detailed_people"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("can view details of places") ?></td>
          <td colspan="2">
<?php echo create_pulldown("detailed_places", $this_user->get("detailed_places"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("can import") ?></td>
          <td colspan="2">
<?php echo create_pulldown("import", $this_user->get("import"), array("0" => translate("No",0), "1" => translate("Yes",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td><?php echo translate("lightbox album") ?></td>
          <td colspan="2">
<?php
    $lightbox_array = get_albums_select_array();
    $lightbox_array["null"] = "[none]";
?>
<?php echo create_smart_pulldown("lightbox_id", $this_user->get("lightbox_id"), $lightbox_array) ?>
          </td>
        </tr>
        <tr>
          <td colspan="3" align="center">
            <input type="submit" value="<?php echo translate($action, 0) ?>">
</form>
          </td>
        </tr> 
      </table>
    </td>
  </tr>
<!---- end edit_user.inc ---->
