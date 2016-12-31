<?php
/**
 * Edit places.
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

/** @todo get rid of difference between "action" and "_action" */

use conf\conf;

use template\block;
use template\fieldset;
use template\form;
use template\template;

$actionlinks=array(
    "return"    => "places.php",
    "new"       => "place.php?_action=new"
);

$tpl=new template("edit", array(
    "title"         => translate($_action) . " " . translate("place"),
    "actionlinks"   => $actionlinks
));

$tpl->addBlock(template::showJSwarning());

if ($place->isRoot()) {
    $parentPlace=translate("places");
} else {
    $parentPlace=place::createPulldown("parent_place_id", $place->get("parent_place_id"));
}

$form=new form("form", array(
    "formAction"    => "place.php",
    "onsubmit"      => null,
    "action"        => $action,
    "submit"        => translate($action, 0)
));

$form->addInputHidden("place_id", $place->getId());
$form->addInputText("title", $place->get("title"), translate("title"),
    sprintf(translate("%s chars max"), "64"), 64, 40);

if (!$place->isRoot()) {
    $parentPlace=place::createPulldown("parent_place_id", $place->get("parent_place_id"));
    $form->addPulldown("parent_place_id", $parentPlace, translate("parent location"));
}

$form->addInputText("address", $place->get("address"), translate("address"),
    sprintf(translate("%s chars max"), "64"), 64, 40);
$form->addInputText("address2", $place->get("address2"), translate("address continued"),
    sprintf(translate("%s chars max"), "64"), 64, 40);
$form->addInputText("city", $place->get("city"), translate("city"),
    sprintf(translate("%s chars max"), "32"), 32);
$form->addInputText("state", $place->get("state"), translate("state"),
    sprintf(translate("%s chars max"), "32"), 32, 16);
$form->addInputText("zip", $place->get("zip"), translate("zip"),
    translate("zip or zip+4"), 10);
$form->addInputText("country", $place->get("country"), translate("country"),
    sprintf(translate("%s chars max"), "32"), 32);
$form->addInputText("url", $place->get("url"), translate("url"),
    sprintf(translate("%s chars max"), "1024"), 1024, 32);
$form->addInputText("urldesc", $place->get("urldesc"), translate("urldesc"),
    sprintf(translate("%s chars max"), "32"), 32);

$pageset=template::createPulldown("pageset", $place->get("pageset"),
    template::createSelectArray(pageset::getRecords("title"), array("title"), true));
$form->addPulldown("pageset", $pageset, translate("pageset"));

$fieldset=new fieldset("formFieldset", array(
    "class"     => "map",
    "legend"    => translate("map")
));

$fieldset->addInputText("lat", $place->get("lat"), translate("latitude"), null, 10);
$fieldset->addInputText("lon", $place->get("lon"), translate("longitude"), null, 10);
$mapzoom=place::createZoomPulldown($place->get("mapzoom"));
$fieldset->addPulldown("mapzoom", $mapzoom, translate("zoom level"));

if (conf::get("maps.geocode")) {
    $fieldset->addBlock(new block("geocode"));
}

$form->addBlock($fieldset);

$tzActionlinks=array();
if (conf::get("date.guesstz")) {
    $tz=e($place->guessTZ());
    if (!empty($tz)) {
        $tzActionlinks[$tz] = "place.php?_action=update&place_id=" . $place->getId() . "&timezone=" . $tz;
    }
}

if ($place->get("timezone")) {
    $tzActionlinks[sprintf(translate("set %s for children"), $place->get("timezone"))] =
        "place.php?_action=settzchildren&place_id=" . $place->getId();
}

if (!empty($tzActionlinks)) {
    $form->addBlock(new block("actionlinks", array(
        "actionlinks" => $tzActionlinks
    )));
}

$timezone=TimeZone::createPulldown("timezone_id", $place->get("timezone"));
$form->addPulldown("timezone_id", $timezone, translate("timezone"));

$form->addTextarea("notes", $place->get("notes"), translate("notes"), 40, 4);

$tpl->addBlock($form);

echo $tpl;
