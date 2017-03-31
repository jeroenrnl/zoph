<?php
/**
 * Show and modify people
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

use template\block;
use template\template;

require_once "include.inc.php";
$_view=getvar("_view");
$_showhidden=(bool) getvar("_showhidden");

if (empty($_view)) {
    $_view=$user->prefs->get("view");
}
$_autothumb=getvar("_autothumb");
if (empty($_autothumb)) {
    $_autothumb=$user->prefs->get("autothumb");
}

if (!$user->canBrowsePeople()) {
    redirect("zoph.php");
}

$_l = getvar("_l");

if (empty($_l) || $_l=="all") {
    $_l = "all";
    $first_letter=null;
    $msg=translate("No people were found");
} else if ($_l == "no last name") {
    $first_letter=" ";
    $msg=translate("No people with no last name were found");
} else {
    $first_letter = $_l;
    $msg=sprintf(translate("No people were found with a last name beginning with '%s'."), htmlentities($_l));
}

if (getvar("circle_id")) {
    $circle=new circle(getvar("circle_id"));
    $circle->lookup();
    if (!$circle->isVisible()) {
        redirect("people.php");
    }
    $title=$circle->getName();
    try {
        $selection=new selection($_SESSION, array(
            "coverphoto"    => "circle.php?_action=update&amp;circle_id=" . $circle->getId() . "&amp;coverphoto=",
            "return"        => "_return=circle.php&amp;_qs=circle_id=" . $circle->getId()
        ));
    } catch (PhotoNoSelectionException $e) {
        $selection=null;
    }

    if ($circle->isHidden() && !$user->canSeeHiddenCircles()) {
        redirect("people.php");
    }

    $view_hidden=array("circle_id" => $circle->getId());

} else {
    $title = translate("People");
    $selection=null;
    $view_hidden=array();
}

require_once "header.inc.php";

$tpl=new template("organizer", array(
    "pageTop"           => false,
    "pageBottom"        => false,
    "showMain"          => true,
    "title"     => strtolower($title),
    "ancLinks"  => null,
    "coverphoto"    => null,
    "description"   => null,
    "selection" => $selection,
    "view"      => $_view,
    "view_name" => "People view",
    "view_hidden"   => $view_hidden,
    "autothumb" => $_autothumb
));

$actionlinks=array();
if ($user->canEditOrganizers()) {
    $actionlinks=array(
        translate("new") => "person.php?_action=new",
        translate("new circle") => "circle.php?_action=new"
    );
    if (isset($circle) && $circle instanceof circle) {
        $actionlinks[translate("edit circle")]="circle.php?_action=edit&circle_id=" . $circle->getId();
        $actionlinks[translate("delete circle")]="circle.php?_action=delete&circle_id=" . $circle->getId();
    }

}

if (!isset($circle) && ($user->canSeeHiddenCircles())) {
    if ($_showhidden) {
        $actionlinks[translate("hide hidden")]="people.php?_showhidden=0";
    } else {
        $actionlinks[translate("show hidden")]="people.php?_showhidden=1";
    }
}
$tpl->addActionlinks($actionlinks);
$tpl->addBlock(new block("people_letters", array(
    "l"    => $_l
)));

if (isset($circle)) {
    $people=$circle->getMembers();
    $ppl=array();
    foreach ($people as $person) {
        $person->lookup();
        $ppl[]=$person;
     }
} else if (!$first_letter) {
    $circles=circle::getAll($_showhidden);
    if ($circles) {
        $block=new block("view_" . $_view, array(
            "id" => $_view . "circle",
            "items" => $circles,
            "autothumb" => $_autothumb,
            "links" => array(
                translate("photos of") => "photos.php?person_id=",
                translate("photos by") => "photos.php?photographer_id="
            )
        ));
        $tpl->addBlock($block);
    }
    $ppl = person::getAllNoCircle();
} else {
    $ppl = person::getAllPeopleAndPhotographers($first_letter);
}
if ($ppl) {
    if ($_view=="thumbs") {
        $template="view_thumbs";
    } else {
        $template="view_list";
    }
    $block=new block($template, array(
        "id" => $_view . "view",
        "items" => $ppl,
        "autothumb" => $_autothumb,
        "links" => array(
            translate("photos of") => "photos.php?person_id=",
            translate("photos by") => "photos.php?photographer_id="
        )
    ));
    $tpl->addBlock($block);
}

if (!$ppl && !isset($circles) && !isset($circle)) {
    $block=new block("message", array(
        "class" => "error",
        "text"  => $msg
    ));
    $tpl->addBlock($block);
}
echo $tpl;
?>
<br>

</div>
<?php
require_once "footer.inc.php";
?>
