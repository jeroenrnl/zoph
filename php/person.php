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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
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
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("person") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="person.php?_action=edit&person_id=<?= $person->get("person_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("edit") ?></font></a> |
            <a href="person.php?_action=delete&person_id=<?= $person->get("person_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a> |
            <a href="person.php?_action=new"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]
<?php
        }
        else {
            echo "&nbsp;";
        }
?>
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td colspan="3">
            <table width="100%">
              <tr>
          <th align="left" colspan="2">
            <?= $person->get("first_name") ?>
            <?= $person->get("middle_name") ?>
            <?= $person->get("last_name") ?>
          </th>
          <td align="right">[
            <a href="photos.php?person_id=<?= $person->get("person_id") ?>"><?php echo "$photos_of " . translate("photos of") ?></a> |
            <a href="photos.php?photographer_id=<?= $person->get("person_id") ?>"><?php echo "$photos_by " . translate("photos by") ?></a>
          ]</td>
        </tr>
            </tr>
          </table>
        </td>
<?php
    if ($user->get("detailed_people")) {
?>
<?= create_field_html($person->get_display_array(), 3) ?>
<?php
        if ($person->home) {
?>
        <tr>
          <td align="right" valign="top"><?php echo translate("Home") ?></td>
          <td><?= $person->home->get_address() ?></td>
          <td align="right" valign="top">[ <a href="place.php?place_id=<?= $person->get("home_id") ?>"><?php echo translate("view") ?></a> ]</td>
        </tr>
<?php
        }

        if ($person->work) {
?>
        <tr>
          <td align="right" valign="top"><?php echo translate("Work") ?></td>
          <td>
             <?= $person->work->get("title") ? $person->work->get("title") . "<br>" : "" ?>
             <?= $person->work->get_address() ?>
          </td>
          <td align="right" valign="top">[ <a href="place.php?place_id=<?= $person->get("work_id") ?>"><?php echo translate("view") ?></a> ]</td>
        </tr>
<?php
        }

        if ($person->get("notes")) {
?>
        <tr>
          <td align="right" valign="top">notes</td>
          <td colspan="2"><?= $person->get("notes") ?></td>
        </tr>
<?php
        }

    } // detailed_people

    } // display
    else if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete person") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $person->get_name()) ?>:
          </td>
          <td align="right">[
            <a href="person.php?_action=confirm&person_id=<?= $person->get("person_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="person.php?_action=display&person_id=<?= $person->get("person_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
<?php
    }
    else {
require_once("edit_person.inc.php");
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>

<?php require_once("footer.inc.php"); ?>
