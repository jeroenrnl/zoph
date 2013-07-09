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

require_once "include.inc.php";
$_view=getvar("_view");
if(empty($_view)) {
    $_view=$user->prefs->get("view");
}
$_autothumb=getvar("_autothumb");
if(empty($_autothumb)) {
    $_autothumb=$user->prefs->get("autothumb");
}

if (!$user->is_admin() && !$user->get("browse_people")) {
    redirect("zoph.php");
}

$_l = getvar("_l");

if (empty($_l)) {
    $_l = "all";
}
$title = translate("People");
require_once "header.inc.php";
?>
  <h1>
<?php
if ($user->is_admin()) {
    ?>
    <span class="actionlink">
      <a href="person.php?_action=new">
        <?php echo translate("new") ?>
      </a>
    </span>
    <?php
    }
?>
<?php echo translate("people") ?></h1>
    <div class="letter">
<?php
for ($l = 'a'; $l <= 'z' && $l != 'aa'; $l++) {
    $title = $l;
    if ($l == $_l) {
        $title = "<span class=\"selected\">" . strtoupper($title) . "</span>";
    }
    ?>
    <a href="people.php?_l=<?php echo $l ?>"><?php echo $title ?></a> |
    <?php
}
?>
    <a href="people.php?_l=no%20last%20name"><?php echo translate("no last name") ?></a> |
    <a href="people.php?_l=all"><?php echo translate("all") ?></a>
  </div>
  <div class="main">
    <form class="viewsettings" method="get" action="people.php">
      <?php echo create_form($request_vars, array ("_view", "_autothumb",
        "_button")) ?>
      <?php echo translate("Category view", 0) . "\n" ?>
      <?php echo template::createViewPulldown("_view", $_view, true) ?>
      <?php echo translate("Automatic thumbnail", 0) . "\n" ?>
      <?php echo template::createAutothumbPulldown("_autothumb", $_autothumb, true) ?>
    </form>
    <br>
<?php
if ($_l == "all") {
    $first_letter=null;
} else if ($_l == "no last name") {
    $first_letter="";
} else {
    $first_letter = $_l;
}
$ppl = person::getAllPeopleAndPhotographers($first_letter);
if ($ppl) {
    if ($_view=="thumbs") {
        $template="view_thumbs";
    } else {
        $template="view_list";
    }
    $tpl=new template($template, array(
        "id" => $_view . "view",
        "items" => $ppl,
        "autothumb" => $_autothumb,
        "links" => array(
            translate("photos of") => "photos.php?person_id=",
            translate("photos by") => "photos.php?photographer_id="
        )
    ));
    echo $tpl;
} else {
    ?>
      <div class="error">
        <?php echo sprintf(translate("No people were found with a last name beginning with '%s'."),
            htmlentities($_l)) ?></div>
    <?php
}
?>
<br>

</div>
<?php
require_once "footer.inc.php";
?>
