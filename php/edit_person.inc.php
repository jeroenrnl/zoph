<!---- begin edit_person.inc ---->
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo $_action ?><?php echo translate("person") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">[
            <a href="people.php"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a> |
            <a href="person.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<form action="person.php" method="GET">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="person_id" value="<?php echo $person->get("person_id") ?>">
        <tr>
          <td><?php echo translate("last name") ?></td>
          <td><?php echo create_text_input("last_name", $person->get("last_name"), 32, 32) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("first name") ?></td>
          <td><?php echo create_text_input("first_name", $person->get("first_name"), 32, 32) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("middle name") ?></td>
          <td><?php echo create_text_input("middle_name", $person->get("middle_name"), 32, 32) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("called") ?></td>
          <td><?php echo create_text_input("called", $person->get("called"), 16, 16) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "16") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("gender") ?></td>
          <td>
<?php echo create_pulldown("gender", $person->get("gender"), array("1" => translate("male",0), "2" => translate("female",0))) ?>
          </td>
          <td>&nbsp</td>
        </tr>
        <tr>
          <td><?php echo translate("date of birth") ?></td>
          <td><?php echo create_text_input("dob", $person->get("dob"), 12, 10) ?></td>
          <td><font size="-1">YYYY-MM-DD</font></td>
        </tr>
        <tr>
          <td><?php echo translate("date of death") ?></td>
          <td><?php echo create_text_input("dod", $person->get("dod"), 12, 10) ?></td>
          <td><font size="-1">YYYY-MM-DD</font></td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("home") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("home_id", $person->get("home_id"), get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("work") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("work_id", $person->get("work_id"), get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("mother") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("mother_id", $person->get("mother_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("father") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("father_id", $person->get("father_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("spouse") ?></td>
          <td colspan="2">
<?php echo create_smart_pulldown("spouse_id", $person->get("spouse_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("notes") ?></td>
          <td colspan="2"><textarea name="notes" cols="40" rows="4"><?php echo $person->get("notes") ?></textarea></td>
        </tr>
        <tr>
          <td colspan="3" align="center"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr> 
</form>
<!---- end edit_person.inc ---->
