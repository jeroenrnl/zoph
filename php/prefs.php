<?php
/**
 * Change preferences
 * Preferences are user-changeble configuration options that are
 * mostly related to how things are displayed in Zoph
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

$user=user::getCurrent();
$title=translate("Preferences");

if (($_action == "update") && !$user->isDefault()) {
    $user->prefs->setFields($request_vars);
    $user->prefs->update();
    $user->prefs->load(1);
    $lang = $user->loadLanguage(1);
}


require_once "header.inc.php";

if ($user->isDefault()) {
    $defaultWarning = sprintf(translate("The user %s is currently defined as the default user " .
        "and does not have permission to change its preferences. The current values are " .
        "shown below but any changes made will be ignored until a different default user " .
        "is defined."), $user->get("user_name"));
} else {
    $defaultWarning = "";
}

$langs = language::getAll();
$languages=array();
$languages[null] = translate("Browser Default");
foreach ($langs as $language) {
    $languages[$language->iso] = $language->name;
}

$sortorder=array(
    "name"      => translate("Name", 0),
    "sortname"  => translate("Sort Name", 0),
    "oldest"    => translate("Oldest photo", 0),
    "newest"    => translate("Newest photo", 0),
    "first"     => translate("Changed least recently", 0),
    "last"      => translate("Changed most recently", 0),
    "lowest"    => translate("Lowest ranked", 0),
    "highest"   => translate("Highest ranked", 0),
    "average"   => translate("Average ranking", 0),
    "random"    => translate("Random", 0)
);

$tpl=new template("prefs", array(
    "title"             => $title,
    "prefs"             => $user->prefs,
    "userId"            => $user->getId(),
    "userName"          => $user->getName(),
    "isAdmin"           => $user->isAdmin(),
    "languages"         => $languages,
    "sortorder"         => $sortorder,
    "defaultWarning"    => $defaultWarning,
    "autocomplete"      => conf::get("interface.autocomplete")
));

$tpl->addActionlinks(array(
    translate("change password")    => "password.php"
));

echo $tpl;
require_once "footer.inc.php";
