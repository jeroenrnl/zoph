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
        $_action = "display";
    }
    if (!$user->get("browse_people") && !$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }
    $name = getvar("person");
    if ($name) {
        list($last_name, $first_name) = explode(',', $name);
        $people = get_person_by_name($first_name, $last_name);
        if ($people && count($people) == 1) {
            $person = array_shift($people);
        }
        else {
            $person = new person();
        }
    }
    else {
        $person_id = getvar("person_id");
        $person = new person($person_id);
    }

    $obj = &$person;
    $redirect = "people.php";
    require_once("actions.inc.php");

    if ($action != "insert") {
        $person->lookup();
        $title = $person->get_name();
    }
    else {
        $title = translate("New Person");
    }

    require_once("header.inc.php");
    if ($action == "display") {

        $ignore; // don't need the thumbnails, only get 1

        $vars["person_id"] = $person->get("person_id");
        $photos_of = get_photos($vars, 0, 1, $ignore, $user);

        $vars = null;
        $vars["photographer_id"] = $person->get("person_id");
        $photos_by = get_photos($vars, 0, 1, $ignore, $user);
?>
      <h1>
<?php
        if ($user->is_admin()) {
?>
          <span class="actionlink">
            <a href="person.php?_action=edit&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="person.php?_action=delete&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="person.php?_action=new"><?php echo translate("new") ?></a>
          </span>
<?php
        }
?>
        <?php echo translate("person") ?>
     </h1>
      <div class="main">
          <span class="actionlink">
            <a href="photos.php?person_id=<?php echo $person->get("person_id") ?>"><?php echo "$photos_of " . translate("photos of") ?></a> |
            <a href="photos.php?photographer_id=<?php echo $person->get("person_id") ?>"><?php echo "$photos_by " . translate("photos by") ?></a>
          </span>
          <h2>
            <?php echo $person->get("first_name") ?>
            <?php echo $person->get("middle_name") ?>
            <?php echo $person->get("last_name") ?>
          </h2>
          <table id="person">
<?php
    if ($user->get("detailed_people")) {
?>
<?php echo create_field_html($person->get_display_array(), 3) ?>
<?php
        if ($person->get_email()) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("email") ?></td>
          <td class="field"><a href="mailto:<?php echo $person->get_email() ?>"><?php echo $person->get_email() ?></a></td>
        </tr>
<?php
        }
        if ($person->home) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("home") ?></td>
          <td class="field">
          <span class="actionlink"><a href="place.php?place_id=<?php echo $person->get("home_id") ?>"><?php echo translate("view") ?></a></span>
          <?php echo $person->home->get_address() ?></td>
        </tr>
<?php
        }

        if ($person->work) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("work") ?></td>
          <td class="field">
          <span class="actionlink"><a href="place.php?place_id=<?php echo $person->get("work_id") ?>"><?php echo translate("view") ?></a></span>
             <?php echo $person->work->get("title") ? $person->work->get("title") . "<br>" : "" ?>
             <?php echo $person->work->get_address() ?>
          </td>
        </tr>
<?php
        }

        if ($person->get("notes")) {
?>
        <tr>
          <td class="fieldtitle">notes</td>
          <td colspan="2" class="field"><?php echo $person->get("notes") ?></td>
        </tr>
<?php
        }

    } // detailed_people

?>
</table>
<?php

    } // display
    else if ($action == "confirm") {
?>
          <h1>
            <?php echo translate("delete person") ?>
          </h1>
      <div class="main">
          <span class="actionlink">
            <a href="person.php?_action=confirm&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="person.php?_action=display&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("cancel") ?></a>
          </span>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $person->get_name()) ?>:
<?php
    }
    else {
require_once("edit_person.inc.php");
    }
?>
</div>

<?php require_once("footer.inc.php"); ?>
