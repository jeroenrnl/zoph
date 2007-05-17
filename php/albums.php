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

    $parent_album_id = getvar("parent_album_id");
    $_view=getvar("_view");
    if(empty($_view)) {
        $_view=$user->prefs->get("view");
    }
    $_autothumb=getvar("_autothumb");
    if(empty($_autothumb)) {
        $_autothumb=$user->prefs->get("autothumb");
    }
    if (!$parent_album_id) {
        $album = get_root_album();
    }
    else {
        $album = new album($parent_album_id);
    }
    $album->lookup($user);
    $ancestors = $album->get_ancestors();
    $children = $album->get_children($user);

    $total_photo_count = $album->get_total_photo_count($user);
    $photo_count = $album->get_photo_count($user);

    $title = $album->get("parent_album_id") ? $album->get("album") : translate("Albums");

    require_once("header.inc.php");
?>
    <h1>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink"><a href="album.php?_action=new&amp;parent_album_id=<?php echo $album->get("album_id") ?>"><?php echo translate("new") ?></a></span>
<?php
    }
?>
        <?php echo translate("albums") . "\n" ?>
    </h1>
<?php
    if($user->is_admin()) {
        include("selection.inc.php");
    }
?>
    <div class="main">
        <form class="viewsettings" method="get" action="albums.php">
        <div class="viewtype">
            <?php echo create_form($request_vars, array ("_view", "_autothumb", "_button")) ?>
            <?php echo translate("Album view", 0) . "\n" ?>
            <?php echo create_view_pulldown("_view", $_view) ?>
        </div>
        <div class="autothumb">
            <?php echo translate("Automatic Thumbnail", 0) . "\n" ?>
            <?php echo create_autothumb_pulldown("_autothumb", $_autothumb) ?>

            <input type="submit" name="_button" value="<?php echo translate("go", 0) ?>">

        </div>
        </form>
        <br>
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
             <?php echo $title . "\n" ?>
        </h2>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink"><a href="album.php?_action=edit&amp;album_id=<?php echo $album->get("album_id") ?>"><?php echo translate("edit") ?></a></span>
        <br>
        <p>
<?php
    }
    echo $album->get_coverphoto();
?>
        </p>
<?php
    if ($album->get("album_description")) {
?>
        <div class="description">
            <?php echo $album->get("album_description") ?>
        </div>
<?php
    }
?>
<?php
    $fragment = translate("in this album");
    $sortorder = $album->get("sortorder");
    if ($sortorder) {
        $sort = "&amp;_order=" . $sortorder;
    }
    if ($total_photo_count > 0) {
        if ($total_photo_count > $photo_count && $children) {
?>
            <span class="actionlink">
                <a href="photos.php?album_id=<?php echo $album->get_branch_ids($user) . $sort ?>"><?php echo translate("view photos") ?></a>
            </span>
<?php
            $fragment .= " " . translate("or its children");
            if($total_photo_count>1) {
                echo sprintf(translate("There are %s photos"), $total_photo_count);
                echo " $fragment.<br>\n";
            } else {
                echo sprintf(translate("There is %s photo"), $total_photo_count);
                echo " $fragment.<br>\n";
            }
            $fragment = translate("in this album");
            if (!$album->get("parent_album_id")) { // root album
                $fragment = translate("available");
            }

        }
    }
    if ($photo_count > 0) {
?>
        <span class="actionlink">
            <a href="photos.php?album_id=<?php echo $album->get("album_id") . $sort ?>"><?php echo translate("view photos")?></a>
        </span>
<?php
        if ($photo_count > 1) {
            echo sprintf(translate("There are %s photos"), $photo_count);
            echo " $fragment.\n";
        } else {
            echo sprintf(translate("There is %s photo"), $photo_count);
            echo " $fragment.\n";
        }
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
                <a href="albums.php?parent_album_id=<?php echo $a->get("album_id") ?>">
<?php
                if ($_view=="thumbs") {
?>
                    <p>
                        <?php echo $a->get_coverphoto($_autothumb); ?>
                        &nbsp;
                    </p>
                    <div>
<?php
                }
                echo $a->get("album");
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
            echo $album->get_html_tree();
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
