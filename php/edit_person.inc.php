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
?>
<!-- begin edit_person.inc !-->
          <h1>
          <ul class="actionlink">
            <li><a href="people.php"><?php echo translate("return") ?></a></li>
            <li><a href="person.php?_action=new"><?php echo translate("new") ?></a></li>
          </ul>
            <?php echo translate($_action) ?> <?php echo translate("person") ?>
          </h1>
      <div class="main">
      <?php echo template\template::showJSwarning() ?>
      <form action="person.php" method="GET">
          <input type="hidden" name="_action" value="<?php echo $action ?>">
          <input type="hidden" name="person_id" value="<?php echo $person->get("person_id") ?>">
          <label for="last_name"><?php echo translate("last name") ?></label>
          <?php echo create_text_input("last_name", $person->get("last_name"), 32, 32) ?>
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
          <label for="first_name"><?php echo translate("first name") ?></label>
          <?php echo create_text_input("first_name", $person->get("first_name"), 32, 32) ?>
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
          <label for="middle_name"><?php echo translate("middle name") ?></label>
          <?php echo create_text_input("middle_name", $person->get("middle_name"), 32, 32) ?>
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
          <label for="called"><?php echo translate("called") ?></label>
          <?php echo create_text_input("called", $person->get("called"), 16, 16) ?>
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "16") ?></span><br>
          <label for="gender"><?php echo translate("gender") ?></label>
          <?php echo template::createPulldown("gender", $person->get("gender"),
            array("1" => translate("male",0), "2" => translate("female",0))) ?><br>
          <label for="dob"><?php echo translate("date of birth") ?></label>
          <?php echo create_text_input("dob", $person->get("dob"), 12, 10) ?>
          <span class="inputhint">YYYY-MM-DD</span><br>
          <label for="dod"><?php echo translate("date of death") ?></label>
          <?php echo create_text_input("dod", $person->get("dod"), 12, 10) ?>
          <span class="inputhint">YYYY-MM-DD</span><br>
          <label for="email"><?php echo translate("email") ?></label>
          <?php echo create_text_input("email", $person->get("email"), 32, 64) ?>
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></span><br>
          <label for="home_id"><?php echo translate("home") ?></label>
          <?php echo place::createPulldown("home_id", $person->get("home_id")) ?><br>
          <label for="work_id"><?php echo translate("work") ?></label>
          <?php echo place::createPulldown("work_id", $person->get("work_id")) ?><br>
          <label for="mother_id"><?php echo translate("mother") ?></label>
          <?php echo person::createPulldown("mother_id", $person->get("mother_id")) ?><br>
          <label for="father_id"><?php echo translate("father") ?></label>
          <?php echo person::createPulldown("father_id", $person->get("father_id")) ?><br>
          <label for="spouse"><?php echo translate("spouse") ?></label>
          <?php echo person::createPulldown("spouse_id", $person->get("spouse_id")) ?><br>
          <label for="pageset"><?php echo translate("pageset") ?></label>
          <?php echo template::createPulldown("pageset", $person->get("pageset"),
              template::createSelectArray(pageset::getRecords("title"), array("title"), true)) ?><br>
          <label for="notes"><?php echo translate("notes") ?></label>
          <textarea name="notes" cols="40" rows="4">
            <?php echo $person->get("notes") ?>
          </textarea><br>
          <input type="submit" value="<?php echo translate($action, 0) ?>">
    </form>
    <br>
  </div>
<!-- end edit_person.inc -->
