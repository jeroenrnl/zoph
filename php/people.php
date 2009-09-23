<?php
 /*
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
 */   

    require_once("include.inc.php");

    $_view=getvar("_view");
    if(empty($_view)) {
        $_view=$user->prefs->get("view");
    }
    $_autothumb=getvar("_autothumb");
    if(empty($_autothumb)) {
        $_autothumb=$user->prefs->get("autothumb");
    }

    if (!$user->is_admin() && !$user->get("browse_people")) {
        redirect(add_sid("zoph.php"));
    }

    $_l = getvar("_l");

    if (empty($_l)) {
        if (DEFAULT_SHOW_ALL) {
            $_l = "all";
        }
        else {
            $_l = "a";
        }
    }

    $title = translate("People");
    require_once("header.inc.php");
?>
          <h1>
<?php
        if ($user->is_admin()) {
?>
            <span class="actionlink"><a href="person.php?_action=new"><?php echo translate("new") ?></a></span>
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

<?php
    if(JAVASCRIPT) {
?>
            <form class="viewsettings" method="get" action="people.php">
                <?php echo create_form($request_vars, array ("_view", "_autothumb",
"_button")) ?>
                <?php echo translate("Category view", 0) . "\n" ?>
                <?php echo create_view_pulldown("_view", $_view, "onChange='form.submit()'") ?>
                <?php echo translate("Automatic thumbnail", 0) . "\n" ?>
                <?php echo create_autothumb_pulldown("_autothumb", $_autothumb, "onChange='form.submit()'") ?>

            </form>
            <br>
<?php
    }

    $constraints = null;
    $ops = null;
    if ($_l == "all") {
        // no contraint
    }
    else if ($_l == "no last name") {
        $constraints["last_name#1"] = "null";
        $ops["last_name#1"] = "is";
        $constraints["last_name#2"] = "''";
    }
    else {
        $constraints["lower(last_name)"] = "$_l%";
        $ops["lower(last_name)"] = "like";
    }

    $ppl = get_people($constraints, "or", $ops);
?>
        <ul class="<?php echo $_view ?>">
<?php
    if ($ppl) {
        foreach($ppl as $p) {
?> 
        <li>
            <span class="actionlink"><a href="person.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo translate("display") ?></a> | <a href="photos.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo translate("photos of") ?></a> | <a href="photos.php?photographer_id=<?php echo $p->get("person_id") ?>"><?php echo translate("photos by") ?></a></span>
<?php
        if ($_view=="thumbs") {
?>
            <p>
                <?php echo $p->get_coverphoto($user,$_autothumb); ?>
                &nbsp;
            </p>
           <div>
<?php
        }
?>
            <a class="person" href="person.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo $p->get("last_name") ? $p->get("last_name") . ", " : "" ?><?php echo $p->get("first_name") ?></a>
        </li>
<?php
        }
    }
    else {
?>
          <div class="error"><?php echo sprintf(translate("No people were found with a last name beginning with '%s'."), htmlentities($_l)) ?></div>
<?php
    }
?>
    </ul>
    <br>

</div>
<?php
    require_once("footer.inc.php");
?>
