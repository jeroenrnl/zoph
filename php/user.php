<?php
/**
 * Display and modify users
 *
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
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */

require_once "include.inc.php";

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

$user_id = getvar("user_id");
$album_id_new = getvar("album_id_new");

$this_user = new user($user_id);

$obj = &$this_user;
$redirect = "users.php";
require_once "actions.inc.php";

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

require_once "header.inc.php";

if ($action == "display") {
    $actionlinks=array(
        "edit"      => "user.php?_action=edit&amp;user_id=" . $this_user->getId(),
        "delete"    => "user.php?_action=delete&amp;user_id=" . $this_user->getId(),
        "new"       => "user.php?_action=new"
    );
    $comments=$this_user->getComments();

    $ratingGraph = new block("graph_bar", array(
        "title"         => translate("photo ratings"),
        "class"         => "ratings",
        "value_label"   => translate("rating", 0),
        "count_label"   => translate("count", 0),
        "rows"          => $this_user->getRatingGraph()
    ));

    $tpl=new template("displayUser", array(
        "title"         => $title,
        "actionlinks"   => $actionlinks,
        "obj"           => $this_user,
        "fields"        => $this_user->getDisplayArray(),
        "hasComments"   => (bool) (sizeOf($comments) > 0),
        "comments"      => $comments,
        "ratingGraph"   => $ratingGraph
    ));

    echo $tpl;

} else if ($action == "confirm") {
    ?>
    <h1>
      <span class="actionlink">
        <a href="user.php?_action=display&amp;user_id=<?php echo $this_user->get("user_id") ?>">
          <?php echo translate("cancel") ?>
        </a>
      </span>
      <?php echo translate("delete user") ?>
    </h1>
    <div class="main">
      <span class="actionlink">
        <a href="user.php?_action=confirm&amp;user_id=<?php echo $this_user->get("user_id") ?>">
          <?php echo translate("delete") ?>
        </a> |
        <a href="user.php?_action=display&amp;user_id=<?php echo $this_user->get("user_id") ?>">
          <?php echo translate("cancel") ?>
        </a>
      </span>
      <?php echo sprintf(translate("Confirm deletion of '%s'"), $this_user->get("user_name")) ?>
    <?php
} else {
    require_once "edit_user.inc.php";
}
?>
</div>

<!--
    // NOTIFY USER, to be rewritten
    $url = getZophURL() . "login.php";

    $this_user->lookupPerson();
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
      <input class="bigbutton" type="submit" name="_button"
        value="<?php echo translate("Notify User", 0) ?>">
    </form>
!-->
<?php require_once "footer.inc.php"; ?>
