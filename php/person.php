<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        $_action = "display";
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

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
<?php
    if ($action == "display") {

        $ignore; // don't need the thumbnails, only get 1

        $vars["person_id"] = $person->get("person_id");
        $photos_of = get_photos($vars, 0, 1, $ignore, $user);

        $vars = null;
        $vars["photographer_id"] = $person->get("person_id");
        $photos_by = get_photos($vars, 0, 1, $ignore, $user);
?>
        <tr>
          <th><h1><?php echo translate("person") ?></h1></th>
          <td class="actionlink">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="person.php?_action=edit&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="person.php?_action=delete&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="person.php?_action=new"><?php echo translate("new") ?></a>
          ]
<?php
        }
        else {
            echo "&nbsp;";
        }
?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <th colspan="2"><h2>
            <?php echo $person->get("first_name") ?>
            <?php echo $person->get("middle_name") ?>
            <?php echo $person->get("last_name") ?>
            </h2>
          </th>
          <td class="actionlink">[
            <a href="photos.php?person_id=<?php echo $person->get("person_id") ?>"><?php echo "$photos_of " . translate("photos of") ?></a> |
            <a href="photos.php?photographer_id=<?php echo $person->get("person_id") ?>"><?php echo "$photos_by " . translate("photos by") ?></a>
          ]</td>
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
          <td class="field"><?php echo $person->home->get_address() ?></td>
          <td class="actionlink">[ <a href="place.php?place_id=<?php echo $person->get("home_id") ?>"><?php echo translate("view") ?></a> ]</td>
        </tr>
<?php
        }

        if ($person->work) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("work") ?></td>
          <td class="field">
             <?php echo $person->work->get("title") ? $person->work->get("title") . "<br>" : "" ?>
             <?php echo $person->work->get_address() ?>
          </td>
          <td class="actionlink">[ <a href="place.php?place_id=<?php echo $person->get("work_id") ?>"><?php echo translate("view") ?></a> ]</td>
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

    } // display
    else if ($action == "confirm") {
?>
        <tr class="titlebar">
          <th><h1><?php echo translate("delete person") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $person->get_name()) ?>:
          </td>
          <td class="actionlink">[
            <a href="person.php?_action=confirm&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="person.php?_action=display&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
      </table>
<?php
    }
    else {
require_once("edit_person.inc.php");
    }
?>
</table>


<?php require_once("footer.inc.php"); ?>
