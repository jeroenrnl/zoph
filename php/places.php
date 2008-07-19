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

    if (!$user->is_admin() && !$user->get("browse_places")) {
        header("Location: " . add_sid("zoph.php"));
    }
    $parent_place_id = getvar("parent_place_id");
    if (!$parent_place_id) {
        $place = get_root_place();
    }
    else {
        $place = new place($parent_place_id);
    }
    $place->lookup();
    $ancestors = $place->get_ancestors();
    $children = $place->get_children(null, "title");

    $total_photo_count = $place->get_total_photo_count($user);
    $photo_count = $place->get_photo_count($user);

    $title = $place->get("parent_place_id") ? $place->get("title") : translate("Places");

    require_once("header.inc.php");
?>
    <h1>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink"><a href="place.php?_action=new&amp;parent_place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("new") ?></a></span>
<?php
    }
?>
        <?php echo translate("places") . "\n" ?>
    </h1>
<?php
    if ($user->is_admin()) {
        include("selection.inc.php");
    }
?>
    <div class="main">
<?php
    if(JAVASCRIPT) {
?>
        <form class="viewsettings" method="get" action="places.php">
<?php
            echo create_pulldown("parent_place_id", 0, get_places_select_array($user), "onChange='form.submit()'");
?>
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

    if ($user->is_admin()) {
?>
        <span class="actionlink">
            <a href="place.php?_action=edit&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("edit") ?></a> | 
            <a href="place.php?_action=delete&amp;place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("delete") ?></a>
<?php
            if($place->get("coverphoto")) {
?>
                |
                <a href="place.php?_action=update&amp;place_id=<?php echo $place->get("place_id") ?>&amp;coverphoto=NULL"><?php echo translate("unset coverphoto") ?></a>
<?php
            }
?>
        </span>
        
        <h2>
<?php
    if ($ancestors) {
        while ($parent = array_pop($ancestors)) {
?>
            <?php echo $parent->get_link() ?> &gt;
<?php
        }
    }
?>
        </h2>
        <p>
<?php
    }
    echo $place->get_coverphoto($user);
?>
        </p>
<?php
    if ($user->get("detailed_places") || $user->is_admin()) {
        echo $place->to_html();
        if ($place->get("notes")) {
            echo "<p>";
            echo $place->get("notes");
            echo "</p>";
        }
    }
    else {
        echo "<h2>" . $title . "</h2>\n";
    }
    if ($place->get("place_description")) {
       echo $place->get("place_description");
    }
?>
    <br><br>
<?php
    $fragment = translate("in this place");
    if($total_photo_count > 0) {
        if ($total_photo_count > $photo_count && $children) {
?>
        <span class="actionlink">
            <a href="photos.php?location_id=<?php echo $place->get_branch_ids($user) ?>"><?php echo translate("view photos") ?></a>
        </span>
<?php   
            $fragment .= " " . translate("or its children");
            if ($total_photo_count > 1) {
                echo sprintf(translate("There are %s photos"), $total_photo_count);
                echo " $fragment.<br>\n";
            }
            else {
                echo sprintf(translate("There is %s photo"), $total_photo_count);
                echo " $fragment.<br>\n";
            }
        }
        $fragment = translate("in this place");
        if (!$place->get("parent_place_id")) { // root place
            $fragment = translate("available");
        }
        if ($photo_count > 0) {
?>
            <span class="actionlink">
                <a href="photos.php?location_id=<?php echo $place->get("place_id") ?>"><?php echo translate("view photos")?></a>
            </span>
<?php

            if ($photo_count > 1) {
                echo sprintf(translate("There are %s photos"), $photo_count);
                echo " $fragment.<br>\n";
            }
            else {
                echo sprintf(translate("There is %s photo"), $photo_count);
                echo " $fragment.<br>\n";
            }
        }
    }
    else {
?>
        <?php echo translate("There are no photos") ?> <?php echo $fragment . ".<br>\n"; 
    }
    if ($children) {
?>
        <ul class="<?php echo $_view ?>">
<?php
        if($_view!="tree") {
            foreach($children as $a) {
                $photo_count=$a->get_photo_count($user);
                $total_photo_count=$a->get_total_photo_count($user);
                if($photo_count==$total_photo_count) {
                    $count=" <span class=\"photocount\">(" . $photo_count . ")</span>";
                } else {
                    $count=" <span class=\"photocount\">(" . $photo_count ."/" . $total_photo_count . ")</span>";
                }
?>
                <li>
                    <a href="places.php?parent_place_id=<?php echo $a->get("place_id") ?>">
<?php
                    if ($_view=="thumbs") {
?>
                        <p>
                            <?php echo $a->get_coverphoto($user,$_autothumb); ?>
                            &nbsp;
                        </p>
                        <div>
<?php
                    }
                    echo $a->get("title");
                    echo $count;
                    if ($_view=="thumbs") {
?>
                        </div>
<?php
                    }
?>
                </a>
            </li>

<?php
        }
    } else {
        echo $place->get_html_tree();
    }
?>
        </ul>
        <br>
<?php
    }
?>
    </div>
<?php
    require_once("footer.inc.php");
?>
