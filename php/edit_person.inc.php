<!-- begin edit_person.inc !-->
        <tr class="titlebar">
          <th><h1><?php echo $_action ?> <?php echo translate("person") ?></h1></th>
          <td class="actionlink">[
            <a href="people.php"><?php echo translate("return") ?></a> |
            <a href="person.php?_action=new"><?php echo translate("new") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="person.php" method="GET">
      <table class="main">
        <tr>
          <td class="fieldtitle">
          <input type="hidden" name="_action" value="<?php echo $action ?>">
          <input type="hidden" name="person_id" value="<?php echo $person->get("person_id") ?>">
          <?php echo translate("last name") ?></td>
          <td class="field"><?php echo create_text_input("last_name", $person->get("last_name"), 32, 32) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("first name") ?></td>
          <td class="field"><?php echo create_text_input("first_name", $person->get("first_name"), 32, 32) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("middle name") ?></td>
          <td class="field"><?php echo create_text_input("middle_name", $person->get("middle_name"), 32, 32) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("called") ?></td>
          <td class="field"><?php echo create_text_input("called", $person->get("called"), 16, 16) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "16") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("gender") ?></td>
          <td class="field">
<?php echo create_pulldown("gender", $person->get("gender"), array("1" => translate("male",0), "2" => translate("female",0))) ?>
          </td>
          <td>&nbsp</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("date of birth") ?></td>
          <td><?php echo create_text_input("dob", $person->get("dob"), 12, 10) ?></td>
          <td class="inputhint">YYYY-MM-DD</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("date of death") ?></td>
          <td class="field"><?php echo create_text_input("dod", $person->get("dod"), 12, 10) ?></td>
          <td class="inputhint">YYYY-MM-DD</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("email") ?></td>
          <td class="field"><?php echo create_text_input("email", $person->get("email"), 32, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("home") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("home_id", $person->get("home_id"), get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("work") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("work_id", $person->get("work_id"), get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("mother") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("mother_id", $person->get("mother_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("father") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("father_id", $person->get("father_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("spouse") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("spouse_id", $person->get("spouse_id"), get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("notes") ?></td>
          <td class="field" colspan="2"><textarea name="notes" cols="40" rows="4"><?php echo $person->get("notes") ?></textarea></td>
        </tr>
        <tr>
          <td colspan="3"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr>
</table>
</form>
<!-- end edit_person.inc -->
