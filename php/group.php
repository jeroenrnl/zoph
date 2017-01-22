<?php
/**
 * Define and modify a group of users
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

use conf\conf;

use group\controller as groupController;

use template\block;
use template\form;
use template\template;

use web\request;


require_once "include.inc.php";

if (!user::getCurrent()->isAdmin()) {
    redirect("zoph.php");
}

$controller = new groupController(request::create());

$group=$controller->getObject();

if ($controller->getView() == "insert") {
    $title = translate("New Group");
} else {
    $title = $group->get("group_name");
}

require_once "header.inc.php";

if ($controller->getView() == "display") {
    $actionlinks=array(
        "edit"      => "group.php?_action=edit&amp;group_id=" . $group->getId(),
        "delete"    => "group.php?_action=delete&amp;group_id=" . $group->getId(),
        "new"       => "group.php?_action=new",
        "return"    => "groups.php"
    );

    $tpl=new template("displayGroup", array(
        "title"         => $title,
        "actionlinks"   => $actionlinks,
        "obj"           => $group,
        "view"          => "album",
        "fields"        => $group->getDisplayArray(),
        "watermark"     => conf::get("watermark.enable"),
        "permissions"   => $group->getPermissionArray()
    ));
} else if ($controller->getView() == "confirm") {
    $actionlinks=array(
        translate("delete") => "group.php?_action=confirm&amp;group_id=" . $group->getId(),
        translate("cancel") => "group.php?_action=display&amp;group_id=" . $group->getId(),
    );
    $tpl=new template("confirm", array(
        "title"             => translate("delete group"),
        "actionlinks"       => null,
        "mainActionlinks"   => $actionlinks,
        "obj"               => $group
    ));
} else if ($controller->getView() == "redirect") {
    redirect($controller->redirect);
} else {
    $actionlinks=array(
        translate("return") => "group.php?group_id=" . $group->getId(),
        translate("new")    => "group.php?_action=new"
    );

    $tpl=new template("edit", array(
        "title"             => $title,
        "actionlinks"       => $actionlinks,
        "mainActionlinks"   => null,
        "obj"               => $group,
    ));

    $form=new form("form", array(
        "formAction"        => "group.php",
        "onsubmit"          => null,
        "action"            => $controller->getView(),
        "submit"            => translate("submit")
    ));

    $form->addInputHidden("group_id", $group->getId());

    $form->addInputText("group_name", $group->getName(), translate("group name"),
        sprintf(translate("%s chars max"), 32), 32);

    $form->addInputText("description", $group->get("description"),
        translate("description"), sprintf(translate("%s chars max"), 128), 128, 32);

    if ($controller->getView()!="insert") {
        $curMembers=$group->getMembers();
        $members=new block("members", array(
            "members"   => $curMembers,
            "group"     => $group
        ));
        $form->addBlock($members);
    }

    $tpl->addBlock($form);

    if ($controller->getView() == "insert") {
        $tpl->addBlock(new block("message", array(
            "class" => "info",
            "text" => translate("After this group is created it can be given access to albums."
        ))));
    } else {
        $view=new permissions\view\edit($group);
        $tpl->addBlock($view->view());
    }
}
echo $tpl;
require_once "footer.inc.php";
