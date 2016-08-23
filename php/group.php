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

require_once "include.inc.php";

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

$group_id = getvar("group_id");
$album_id_new = getvar("album_id_new");

$group = new group($group_id);

if ($_action == "update_albums") {
    // Check if the "Grant access to all albums" checkbox is ticked
    $_access_level_all_checkbox = getvar("_access_level_all_checkbox");

    if ($_access_level_all_checkbox) {
        $albums = album::getAll();
        foreach ($albums as $alb) {
            $permissions = new group_permissions($group_id, $alb->get("album_id"));
            $permissions->setFields($request_vars, "", "_all");
            if (!conf::get("watermark.enable")) {
                $permissions->set("watermark_level", 0);
            }
            $permissions->insert();
        }
    }

    $albums = $group->getAlbums();
    foreach ($albums as $album) {
        $album->lookup();
        $id=$album->getId();
        $name=$album->getName();

        if (isset($request_vars["_remove_permission_album__$id"])) {
            $remove_permission_album = $request_vars["_remove_permission_album__$id"];
            // first check if album needs to be revoked
            if ($remove_permission_album) {
                $permissions = new group_permissions($group_id, $id);
                $permissions->delete();
            }
        } else {
            $permissions = new group_permissions();
            $permissions->setFields($request_vars, "", "__$id");
            $permissions->update();
        }
    }
    // Check if new album should be added
    if ($album_id_new) {
        $permissions = new group_permissions();
        $permissions->setFields($request_vars,"","_new");
        if (!conf::get("watermark.enable")) {
            $permissions->set("watermark_level", 0);
        }
        $permissions->insert();
    }

    $action = "update";
} else if ($_action=="update") {
    $group->setFields($request_vars);
    if (isset($request_vars["_member"]) && ((int) $request_vars["_member"] > 0)) {
        $group->addMember(new user((int) $request_vars["_member"]));
    }

    if (is_array(getvar("_removeMember"))) {
        foreach (getvar("_removeMember") as $user_id) {
            $group->removeMember(new user((int) $user_id));
        }
    }
    $group->update();
    $action = "update";
} else {
    $obj = &$group;
    $redirect = "groups.php";
    require_once "actions.inc.php";
}

// edit after insert to add album permissions
if ($_action == "insert") {
    $action = "update";
}

if ($action != "insert") {
    $group->lookup();
    $title = $group->get("group_name");
} else {
    $title = translate("New Group");
}

require_once "header.inc.php";

if ($action == "display") {
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
        "fields"        => $group->getDisplayArray(),
        "watermark"     => conf::get("watermark.enable"),
        "permissions"   => $group->getPermissionArray()
    ));
} else if ($action == "confirm") {
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
        "action"            => $action,
        "submit"            => translate("submit")
    ));

    $form->addInputHidden("group_id", $group->getId());

    $form->addInputText("group_name", $group->getName(), translate("group name"),
        sprintf(translate("%s chars max"), 32), 32);

    $form->addInputText("description", $group->get("description"),
        translate("description"), sprintf(translate("%s chars max"), 128), 128, 32);

    if ($action!="insert") {
        $curMembers=$group->getMembers();
        $members=new block("members", array(
            "members"   => $curMembers,
            "group"     => $group
        ));
        $form->addBlock($members);
    }

    $tpl->addBlock($form);

    if ($action == "insert") {
        $tpl->addBlock(new block("message", array(
            "class" => "info",
            "text" => translate("After this group is created it can be given access to albums."
        ))));
    } else {
        $accessLevelAll=new block("formInputText", array(
            "label" => null,
            "name"  => "access_level_all",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));
        $wmLevelAll=new block("formInputText", array(
            "label" => null,
            "name"  => "watermark_level_all",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));
        $accessLevelNew=new block("formInputText", array(
            "label" => null,
            "name"  => "access_level_new",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));
        $wmLevelNew=new block("formInputText", array(
            "label" => null,
            "name"  => "watermark_level_new",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));

        $gp = new block("editGroupPermissions", array(
            "watermark"         => conf::get("watermark.enable"),
            "group_id"          => $group->getId(),
            "accessLevelAll"    => $accessLevelAll,
            "wmLevelAll"        => $wmLevelAll,
            "accessLevelNew"    => $accessLevelNew,
            "wmLevelNew"        => $wmLevelNew,
            "permissions"       => $group->getPermissionArray()
        ));
        $tpl->addBlock($gp);
    }
}
echo $tpl;
require_once "footer.inc.php";
