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

    if (!$parent_album_id) {
        $album = get_root_album();
    }
    else {
        $album = new album($parent_album_id);
    }
    $album->lookup($user);
    $ancestors = $album->get_ancestors();
    $children = $album->get_children($user);

    $photo_count = $album->get_total_photo_count($user);

    $title = $album->get("parent_album_id") ? $album->get("album") : translate("Albums");

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
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
    <div class="main">
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
<?php
    }
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
    if ($photo_count > 0) {
        if (!$album->get("parent_album_id")) { // root album
            $fragment = translate("available");
        }
        else {
            if ($children) {
                $fragment .= " " . translate("or its children");
            }
        }

    if ($photo_count > 1) {
      echo sprintf(translate("There are %s photos"), $photo_count);
      echo " $fragment.\n";
    }
    else {
      echo sprintf(translate("There is %s photo"), $photo_count);
      echo " $fragment.\n";
    }
?>
        <span class="actionlink">
            <a href="photos.php?album_id=<?php echo $album->get_branch_ids($user) ?>"><?php echo translate("view photos") ?></a>
        </span>
<?php
    }
    else {
?>
        <?php echo translate("There are no photos") ?> <?php echo $fragment . ".\n"; 
    }
    if ($children) {
?>
        <ul>
<?php
        foreach($children as $a) {
?>
            <li><a href="albums.php?parent_album_id=<?php echo $a->get("album_id") ?>"><?php echo $a->get("album") ?></a></li>
<?php
        }
?>
        </ul>
<?php
    }
?>
    </div>
<?php
    require_once("footer.inc.php");
?>
