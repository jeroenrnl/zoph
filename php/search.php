<?php
/**
 * Search photos
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

use search\controller as searchController;

use template\block;
use template\form;
use template\template;

use web\request;


require_once "include.inc.php";

$controller = new searchController(request::create());

$search=$controller->getObject();
if ($controller->getView() == "insert") {
    $title = translate("New Search");
} else if ($search instanceof search) {
    $title = $search->get("description");
    if (empty($title)) {
        $title = translate("Search");
    }
} else {
    $title = translate("Search");
}

if ($controller->getView() != "photos") {
    require_once "header.inc.php";
}

if ($controller->getView() == "display") {
    $view=new search\view\display($request);
    $tpl=$view->view();
} else if ($controller->getView() == "confirm") {
    $actionlinks=array(
        translate("delete") => "search.php?_action=confirm&amp;search_id=" . $search->getId(),
        translate("cancel") => "search.php",
    );
    $tpl=new template("confirm", array(
        "title"             => translate("Delete saved search"),
        "actionlinks"       => null,
        "mainActionlinks"   => $actionlinks,
        "obj"               => $search
    ));
} else if ($controller->getView() == "redirect") {
    redirect($controller->redirect);
} else if ($controller->getView() == "photos") {
    $view=new search\view\photos($request);
    $tpl=$view->view();
} else {
    $actionlinks=array(
        translate("return") => "search.php",
        translate("new")    => "search.php?_action=new"
    );

    $tpl=new template("edit", array(
        "title"             => $title,
        "actionlinks"       => $actionlinks,
        "mainActionlinks"   => null,
        "obj"               => $search,
    ));

    $form=new form("form", array(
        "formAction"        => "search.php",
        "onsubmit"          => null,
        "action"            => $controller->getView(),
        "submit"            => translate("submit")
    ));

    $form->addInputHidden("search_id", $search->getId());
    $form->addInputHidden("search", $search->get("search"));

    $form->addInputText("name", $search->getName(), translate("Name"),
        sprintf(translate("%s chars max"), 64), 40);
    if (user::getCurrent()->isAdmin()) {
        $form->addPulldown(
            "owner",
            template::createPulldown("owner", $search->get("owner"),
                template::createSelectArray(user::getRecords("user_name"),
                array("user_name"))),
            translate("Owner")
        );
        $form->addPulldown(
            "public",
            template::createYesNoPulldown("public", $search->get("public")),
            translate("Public")
        );
    }

    $tpl->addBlock($form);
}
echo $tpl;
require_once "footer.inc.php";
