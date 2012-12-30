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
        redirect(add_sid("zoph.php"));
    }
    $name = getvar("person");
    if ($name) {
        $people = person::getByName($name);
        if ($people && count($people) == 1) {
            $person = array_shift($people);
        } else {
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
        $title = e($person->getName());
    }
    else {
        $title = translate("New Person");
    }

    require_once("header.inc.php");
    if ($action == "display") {
        $photos_of = $person->getPhotoCount();
        $photos_by = $person->getPhotographerCount();
?>
      <h1>
<?php
        if ($user->is_admin()) {
?>
          <span class="actionlink">
            <a href="person.php?_action=edit&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="person.php?_action=delete&amp;person_id=<?php echo $person->get("person_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="person.php?_action=new"><?php echo translate("new") ?></a>
<?php
            if($person->get("coverphoto")) {
?>
                |
                <a href="person.php?_action=update&amp;person_id=<?php echo $person->get("person_id") ?>&amp;coverphoto=NULL"><?php echo translate("unset coverphoto") ?></a>
<?php
            }
?>
          </span>
<?php
        }
?>
        <?php echo translate("person") ?>
     </h1>
<?php
    if($user->is_admin()) {
        include("selection.inc.php");
    }
    include("show_page.inc.php");
    if($show_orig) {
?>
      <div class="main">
          <span class="actionlink">
            <a href="photos.php?person_id=<?php echo $person->get("person_id") ?>"><?php echo "$photos_of " . translate("photos of") ?></a> |
            <a href="photos.php?photographer_id=<?php echo $person->get("person_id") ?>"><?php echo "$photos_by " . translate("photos by") ?></a>
          </span>
          <h2>
            <?php echo e($person->get("first_name")) ?>
            <?php echo e($person->get("middle_name")) ?>
            <?php echo e($person->get("last_name")) ?>
          </h2>
          <p>
<?php
    echo $person->getCoverphoto();
?>
          </p>
          <dl>
<?php
if ($user->get("detailed_people") || $user->is_admin()) {
?>
<?php echo create_field_html($person->getDisplayArray()) ?>
<?php
        if ($person->get_email()) {
?>
          <dt><?php echo translate("email") ?></dt>
          <dd><a href="mailto:<?php echo e($person->get_email()) ?>"><?php echo e($person->get_email()) ?></a></dd>
<?php
        }
        if ($person->home) {
?>
          <dt><?php echo translate("home location") ?></dt>
          <dd>
          <span class="actionlink"><a href="place.php?place_id=<?php echo $person->get("home_id") ?>"><?php echo translate("view") ?></a></span>
          <?php echo $person->home->get("title") ? $person->home->get("title") . "<br>" : "" ?>
          <?php echo $person->home->get_address() ?></dd>
<?php
        }

        if ($person->work) {
?>
          <dt><?php echo translate("work") ?></dt>
          <dd>
          <span class="actionlink"><a href="place.php?place_id=<?php echo $person->get("work_id") ?>"><?php echo translate("view") ?></a></span>
             <?php echo $person->work->get("title") ? $person->work->get("title") . "<br>" : "" ?>
             <?php echo $person->work->get_address() ?>
          </dd>
<?php
        }

        if ($person->get("notes")) {
?>
          <dt>notes</dt>
          <dd><?php echo $person->get("notes") ?></dd>
<?php
        }

    } // detailed_people


?>
    </dl><br>
    </div>
<?php
     } // show_orig
     echo $page_html;


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
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $person->getName()) ?>:
         <br>
       </div>
<?php
    }
    else {
require_once("edit_person.inc.php");
    }
?>

<?php require_once("footer.inc.php"); ?>
