<?php
/* This file is part of Zoph.
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

    $user_id = getvar("user_id");
    $album_id_new = getvar("album_id_new");

    $this_user = new user($user_id);

    if ($_action == "update_albums") {
        // Check if the "Grant access to all albums" checkbox is ticked
        $_access_level_all_checkbox = getvar("_access_level_all_checkbox");

        if($_access_level_all_checkbox) {
            $albums = get_albums();
            if ($albums) {
                foreach ($albums as $alb) {
                    $permissions = new album_permissions(
                        $user_id, $alb->get("album_id"));
                    $permissions->set_fields($request_vars,"","_all");
                    $permissions->insert();
                }
            }
        }

        $albums = get_albums_select_array($this_user);
        while (list($album_id, $name) = each($albums)) {
            $remove_permission_album = $request_vars["_remove_permission_album__$album_id"];
            // first check if album needs to be revoked
            if ($remove_permission_album) {
                $permissions = new album_permissions($user_id, $album_id);
                $permissions->delete();
            }
        }
        // Check if new album should be added
        if($album_id_new) {
            $permissions = new album_permissions();
            $permissions->set_fields($request_vars,"","_new");
            $permissions->insert();
        }
        // update ablums

        $albums = get_albums_select_array($this_user);
        while (list($album_id, $name) = each($albums)) {
            $permissions = new album_permissions();
            $permissions->set_fields($request_vars,"","__$album_id");
            $permissions->update();
        }

        $action = "update";
    }
    else {
        $obj = &$this_user;
        $redirect = "users.php";
        require_once("actions.inc.php");
    }

    if ($_action == "update" &&
        $user->get("user_id") == $this_user->get("user_id")) {
        $user->set_fields($request_vars);
    }

    // edit after insert to add album permissions
    if ($_action == "insert") {
        $action = "update";
    }

    if ($action != "insert") {
        $this_user->lookup();
        $title = $this_user->get("user_name");
    }
    else {
        $title = translate("New User");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";

    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
<?php
    if ($action == "display") {
?>
        <tr>
          <th><h1><?php echo translate("user") ?></h1></th>
          <td class="actionlink">
          [
            <a href="user.php?_action=edit&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="user.php?_action=delete&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="user.php?_action=new"><?php echo translate("new") ?></a>
          ]
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <th colspan="3">
            <h2><?php echo $this_user->get("user_name") ?></h2>
          </th>
        </tr>
<?php echo create_field_html($this_user->get_display_array(), 3) ?>
        <tr>
          <td colspan="3" class="center">
<?php
        $url = ZOPH_URL;
        if (empty($url)) {
            $url = get_url() . "login.php";
        }

        $this_user->lookup_person();
        $name = $this_user->person->get_name();

        $subject = translate("Your Zoph Account", 0);
        $message =
            translate("Hi",0) . " " . $name .  ",\n\n" .
            translate("I have created a Zoph account for you", 0) .
            ":\n\n" .  "$url\n" .
            translate("user name", 0) . ": " .
            $this_user->get("user_name") . "\n";

        if ($_action == "insert") {
            $message .=
                translate("password", 0) . ": " .
                $this_user->get("password") . "\n";
        }
        $message .=
            "\n" . translate("Regards,",0) . "\n" .
            $user->person->get_name();
?>
<form action="notify.php" method="POST">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="subject" value="<?php echo $subject ?>">
<input type="hidden" name="message" value="<?php echo $message ?>">
<input class="bigbutton" type="submit" name="_button" value="<?php echo translate("Notify User", 0) ?>">
</form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="permissions">
    <tr>
          <th colspan="5"><h3><?php echo translate("Albums") ?></h3></th>
        </tr>
<?php
        if ($this_user->is_admin()) {
?>
        <tr>
          <td colspan="5">
       <?php echo sprintf(translate("As an admin, user %s has access to all albums."), $this_user->get("user_name")) ?>
          </td>
        </tr>
<?php
        }
        else {
?>
        <tr>
          <th><?php echo translate("name") ?></th>
          <th><?php echo translate("access level") ?></th>
          <th><?php echo translate("writable") ?></th>
        </tr>
<?php
            $albums = get_albums_select_array($this_user);
            while (list($id, $name) = each($albums)) {
                if (!$id || $id == 1) { continue; }
                $permissions = $this_user->get_album_permissions($id);
?>
        <tr>
          <td><?php echo $name ?></td>
          <td><?php echo $permissions->get("access_level") ?></td>
          <td><?php echo $permissions->get("writable") == "1" ? translate("Yes") : translate("No") ?></td>
        </tr>
<?php
            }
        }
    }
    else if ($action == "confirm") {
?>
        <tr class="titlebar">
          <th><h1><?php echo translate("delete user") ?></h1></th>
          <td class="actionlink">[
            <a href="user.php?_action=display&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $this_user->get("user_name")) ?>
          </td>
          <td class="actionlink">[
            <a href="user.php?_action=confirm&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="user.php?_action=display&amp;user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
    </table>
<?php
    }
    else {
require_once("edit_user.inc.php");
?>
  <tr>
    <td>
<form action="user.php">
      <table class="permissions">
<!--    <tr>
        <td width="25px"></td>
        <td></td>
        <td width="80px"></td>
        <td width="80px"></td>
    </tr> !-->
    <col class="col1"><col class="col2"><col class="col3"><col class="col4">
    <tr>
          <th colspan="4"><h3><?php echo translate("Albums") ?></h3></th>
        </tr>
<?php
        if ($action != "insert" && $this_user->is_admin()) {
?>
        <tr>
          <td colspan="4">
       <?php echo sprintf(translate("As an admin, user %s has access to all albums."), $this_user->get("user_name")) ?>
          </td>
        </tr>
<?php
        }
        else {
            if ($action == "insert") {
?>
        <tr>
          <td colspan="4">
       <?php echo translate("After this user is created they can be given access to albums.") ?>
          </td>
        </tr>
<?php
            }
            else {
?>
        <tr>
          <td colspan="4">
       <?php echo translate("Granting access to an album will also grant access to that album's ancestors if required.  Granting access to all albums will not overwrite previously granted permissions.") ?>
          </td>
        </tr>
        <tr>
          <th colspan="2"><?php echo translate("name") ?></th>
          <th><?php echo translate("access level") ?></th>
          <th>writable</th>
        </tr>
        <tr>
      <td>
      <input type="checkbox" name="_access_level_all_checkbox" value="1">
      </td>
          <td>
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="_action" value="update_albums">
<?php echo translate("Grant access to all existing albums:") ?>
                </td>
                <td>
<?php echo create_text_input("access_level_all", "5", 4, 2) ?>
                </td>
                <td>
<?php echo create_pulldown("writable_all", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
                </td>
        </tr>
        <tr>
      <td>
      </td>
          <td>
<input type="hidden" name="user_id_new" value="<?php echo $this_user->get("user_id") ?>">
<?php echo create_smart_pulldown("album_id_new", "", get_albums_select_array()) ?>
                </td>
                <td>
<?php echo create_text_input("access_level_new", "5", 4, 2) ?>
                </td>
                <td>
<?php echo create_pulldown("writable_new", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
                </td>
        </tr>
    <tr>
    <td colspan="4" class="permremove">
    remove
    </td>
    </tr>
<?php
            $albums = get_albums_select_array($this_user);
            while (list($id, $name) = each($albums)) {
                if (!$id || $id == 1) { continue; }
                $permissions = $this_user->get_album_permissions($id);
?>
        <tr>
      <td>
      <input type="checkbox" name="_remove_permission_album__<?php echo $id ?>" value="1">
      </td>
          <td>
<?php echo $name ?>
          </td>
          <td>
<input type="hidden" name="album_id__<?php echo $id ?>" value="<?php echo $id ?>">
<input type="hidden" name="user_id__<?php echo $id ?>" value="<?php echo $user_id ?>">
<?php echo create_text_input("access_level__$id", $permissions->get("access_level"), 4, 2) ?>
          </td>
          <td>
<?php echo create_pulldown("writable__$id", $permissions->get("writable"), array("0" => translate("No",0), "1" => translate("Yes",0))) ?>
          </td>
      </tr>
<?php
            } // while
?>
    <tr>
      <td colspan="4" class="center">
        <input type="submit" value="<? echo translate("update", 0) ?>">
      </td>
    </tr>
<?php
            } // not insert
        } // not admin
    } // edit
?>
</table>
</form>
</table>
<?php require_once("footer.inc.php"); ?>
