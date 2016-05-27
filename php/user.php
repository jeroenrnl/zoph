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

    $notifyForm=new form("form", array(
        "formAction"        => "notify.php",
        "onsubmit"          => null,
        "action"            => "notifyuser",
        "submit"            => translate("notify user")
    ));

    $notifyForm->addInputHidden("user_id", $this_user->getId());

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
        "notifyForm"    => $notifyForm,
        "hasComments"   => (bool) (sizeOf($comments) > 0),
        "comments"      => $comments,
        "ratingGraph"   => $ratingGraph
    ));
} else if ($action == "confirm") {
    $actionlinks=array(
        translate("delete") => "user.php?_action=confirm&amp;user_id=" . $this_user->getId(),
        translate("cancel") => "user.php?_action=display&amp;user_id=" . $this_user->getId(),
    );
    $tpl=new template("confirm", array(
        "title"             => translate("delete user"),
        "actionlinks"       => null,
        "mainActionlinks"   => $actionlinks,
        "obj"               => $this_user
    ));
} else {
    $actionlinks=array(
        "return"      => "users.php",
        "new"       => "user.php?_action=new"
    );

    if ($_action != "new") {
        $actionlinks[translate("change password")]="password.php?userid=" . $this_user->getId();
    }

    $tpl=new template("edit", array(
        "title"             => $title,
        "actionlinks"       => $actionlinks,
        "mainActionlinks"   => null,
        "obj"               => $this_user,
    ));

    $form=new form("form", array(
        "formAction"        => "user.php",
        "onsubmit"          => null,
        "action"            => $action,
        "submit"            => translate("submit")
    ));

    $personPulldown=template::createPulldown("person_id",
        ($action == "insert" ? "1" : $this_user->get("person_id")),
        person::getSelectArray());
    $userClassPulldown=template::createPulldown("user_class", $this_user->get("user_class"),
        array("1" => translate("User", 0), "0" => translate("Admin", 0)));

    $form->addInputHidden("user_id", $this_user->getId());
    $form->addInputText("user_name", $this_user->getName(), translate("user name"),
        sprintf(translate("%s chars max"), 16), 16);
    $form->addPulldown("person_id", $personPulldown, translate("person"));

    if ($_action == "new") {
        $form->addInputPassword("password", translate("password"), 32, sprintf(translate("%s chars max"), 32));
    }

    $form->addPulldown("user_class", $userClassPulldown, translate("class"));

    $desc=$this_user->getAccessRightsDescription();

    foreach ($this_user->getAccessRightsArray() as $field => $value) {
        $pulldown=template::createPulldown($field, $value, array(
            "1" => translate("Yes"),
            "0" => translate("No")
        ));
        $form->addPulldown($field, $pulldown, translate($desc[$field]));
    }

    $tpl->addBlock($form);
}
echo $tpl;
?>
<?php require_once "footer.inc.php"; ?>
