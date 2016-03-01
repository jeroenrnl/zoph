<?php
/**
 * Display circle details
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
 * @author Jeroen Roos
 */
require_once "include.inc.php";

if (!$user->isAdmin()) {
    $_action = "display";
}

if (!$user->get("browse_people") && !$user->isAdmin()) {
    redirect("zoph.php");
}

$circleId = (int) getvar("circle_id");
$circle = new circle($circleId);
$obj = &$circle;
$redirect = "people.php";
require_once "actions.inc.php";
if ($action != "insert") {
    $circle->lookup();
    $title = e($circle->getName());
} else {
    $title = translate("New circle");
}

if ($circle->isHidden() && !$user->canSeeHiddenCircles()) {
    redirect("people.php");
}

try {
    $selection=new selection($_SESSION, array(
        "coverphoto"    => "circle.php?_action=update&amp;circle_id=" . $circle->getId() . "&amp;coverphoto=",
        "return"        => "_return=circle.php&amp;_qs=circle_id=" . $circle->getId()
    ));
} catch (PhotoNoSelectionException $e) {
    $selection=null;
}


require_once "header.inc.php";
if ($action == "display") {
    $actionlinks=array();

    if ($user->isAdmin()) {
        $actionlinks=array(
            translate("edit")   => "circle.php?_action=edit&amp;circle_id=" . $circle->getId(),
            translate("delete") => "circle.php?_action=delete&amp;circle_id=" . $circle->getId(),
            translate("new")    => "circle.php?_action=new"
        );
        if ($circle->get("coverphoto")) {
            $actionlinks[translate("unset coverphoto")]=
                "circle.php?_action=update&amp;circle_id=" . $circle->getId() . "&amp;coverphoto=NULL";
        }
    }
    $tpl=new template("display", array(
        "title"             => $title,
        "actionlinks"       => $actionlinks,
        "mainActionlinks"   => null,
        "obj"               => $circle,
        "selection"         => $selection,
        "pageTop"           => null,
        "pageBottom"        => null,
        "page"              => null,
        "showMain"          => true
    ));

    if ($user->get("detailed_people") || $user->isAdmin()) {
        $tpl->addBlock(new block("definitionlist", array(
            "class" => "display circle",
            "dl"    => $circle->getDisplayArray()
        )));
    }
} else if ($action == "confirm") {
    $actionlinks=array(
        translate("delete") => "circle.php?_action=confirm&amp;circle_id=" . $circle->getId(),
        translate("cancel") => "circle.php?_action=display&amp;circle_id=" . $circle->getId(),
    );
    $tpl=new template("confirm", array(
        "title"             => translate("delete circle"),
        "actionlinks"       => null,
        "mainActionlinks"   => $actionlinks,
        "obj"               => $circle
    ));
} else {
    $actionlinks=array(
        translate("return") => "circle.php?circle_id=" . $circle->getId(),
        translate("new")    => "circle.php?_action=new"
    );

    $tpl=new template("edit", array(
        "title"             => $title,
        "actionlinks"       => $actionlinks,
        "mainActionlinks"   => null,
        "obj"               => $circle
    ));

    $form=new block("form", array(
        "formAction"    => "circle.php",
        "onsubmit"      => null,
        "action"        => $action,
    ));
    $form->addBlocks(array(
        template::createFormInputHidden("circle_id", $circle->getId()),
        template::createFormInputText("circle_name", $circle->getName(), translate("Name"), "", 32),
        template::createFormTextArea("description", $circle->get("description"), translate("Description"), 40, 4),
        template::createFormInputCheckbox("hidden", $circle->isHidden(), translate("Hide in overviews"))
    ));

    $tpl->addBlock($form);
}
echo $tpl;
?>

<?php require_once "footer.inc.php"; ?>
