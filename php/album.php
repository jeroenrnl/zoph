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

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $album_id = getvar("album_id");

    $album = new album($album_id);

    $obj = &$album;
    $redirect = "albums.php";

    if($_action=="update" && getvar("sortorder")=="") {
    // overiding the default action, to be able to clear the sortorder
        $obj->set_fields($request_vars);
        $obj->set("sortorder", "");
        $obj->update();
        $action = "display";
    } else {
        require_once("actions.inc.php");
    }

    if ($action == "display") {
        header("Location: " . add_sid("albums.php?parent_album_id=" . $album->get("album_id")));
    }

    if ($action != "insert") {
        $album->lookup();
        $title = $album->get("album");
    }
    else {
        $title = translate("New Album");
    }

    require_once("header.inc.php");
?>
<?php
    if ($action == "confirm") {
?>
          <h1><?php echo translate("delete album") ?></h1>
            <div class="main">
               <?php echo sprintf(translate("Confirm deletion of '%s' and its subalbums:"), $album->get("album")) ?>
               <span class="actionlink">
                 <a href="album.php?_action=confirm&amp;album_id=<?php echo $album->get("album_id") ?>"><?php echo translate("delete") ?></a> |
                 <a href="album.php?_action=edit&amp;album_id=<?php echo $album->get("album_id") ?>"><?php echo translate("cancel") ?></a>
               </span>
             </div>
<?php
    }
    else {
?>
          <h1>
            <span class="actionlink">
              <a href="albums.php"><?php echo translate("return") ?></a> |
              <a href="album.php?_action=delete&amp;album_id=<?php echo $album->get("album_id") ?>"><?php echo translate("delete") ?></a>
            </span>
            <?php echo translate("album") ?>
          </h1>
      <div class="main">
      <form action="album.php">
        <input type="hidden" name="_action" value="<?php echo $action ?>">
        <input type="hidden" name="album_id" value="<?php echo $album->get("album_id") ?>">
        <?php echo create_edit_fields($album->get_edit_array($user)) ?>
        <input type="submit" value="<?php echo translate($action, 0) ?>">

      </form>
  </div>
<?php
    }
?>

<?php
    require_once("footer.inc.php");
?>
