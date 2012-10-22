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

    require_once("include.inc.php");

    if (!$user->is_admin()) {
        redirect(add_sid("zoph.php"));
    }

    $user_id = getvar("user_id");
    $album_id_new = getvar("album_id_new");

    $this_user = new user($user_id);

    $obj = &$this_user;
    $redirect = "users.php";
    require_once("actions.inc.php");

    if ($_action == "update" &&
        $user->get("user_id") == $this_user->get("user_id")) {
        $user->setFields($request_vars);
    }

    // edit after insert to add album permissions
    if ($_action == "insert") {
        $action = "update";
    }

    if ($action != "insert") {
        $this_user->lookup();
        $title = e($this_user->get("user_name"));
    } else {
        $title = translate("New User");
    }

    require_once("header.inc.php");
?>
<?php
    if ($action == "display") {
?>
         <h1>
          <span class="actionlink">
            <a href="user.php?_action=edit&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="user.php?_action=delete&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="user.php?_action=new"><?php echo translate("new") ?></a>
          </span>
          <?php echo translate("user") ?>
        </h1>
        <div class="main">
          <h2><?php echo e($this_user->get("user_name")) ?></h2>
            <dl class='users'>
                <?php echo create_field_html($this_user->getDisplayArray(), 3) ?>
            </dl>
            <br>
<?php
        $url = getZophURL() . "login.php";

        $this_user->lookup_person();
        $name = $this_user->person->getName();

        $subject = translate("Your Zoph Account", 0);
        $message =
            translate("Hi",0) . " " . e($name) .  ",\n\n" .
            translate("I have created a Zoph account for you", 0) .
            ":\n\n" .  e($url) . "\n" .
            translate("user name", 0) . ": " .
            e($this_user->get("user_name")) . "\n";

        if ($_action == "insert") {
            $message .=
                translate("password", 0) . ": " .
                e($this_user->get("password")) . "\n";
        }
        $message .=
            "\n" . translate("Regards,",0) . "\n" .
            e($user->person->getName());
?>
<form action="notify.php" method="POST">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="subject" value="<?php echo $subject ?>">
<input type="hidden" name="message" value="<?php echo $message ?>">
<input class="bigbutton" type="submit" name="_button" value="<?php echo translate("Notify User", 0) ?>">
</form><br>
<?php
        echo $this_user->get_rating_graph();

        $comments=$this_user->get_comments();
        if(!empty($comments)) {
?>
<h3><?php echo translate("comments by this user") ?></h3>
<?php
            echo format_comments($user,$comments);
        }
    } else if ($action == "confirm") {
?>
          <h1>
            <span class="actionlink">
              <a href="user.php?_action=display&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("cancel") ?></a>
            </span>
            <?php echo translate("delete user") ?>
          </h1>
      <div class="main">
          <span class="actionlink">
            <a href="user.php?_action=confirm&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="user.php?_action=display&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("cancel") ?></a>
          </span>
          <?php echo sprintf(translate("Confirm deletion of '%s'"), $this_user->get("user_name")) ?>
<?php
    }
    else {
        require_once("edit_user.inc.php");
    } 
?>
</div>
<?php require_once("footer.inc.php"); ?>
