<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $user_id = getvar("user_id");
    $album_id = getvar("album_id");

    $this_user = new user($user_id);

    if ($_action == "add_album") {
        $permissions = new album_permissions();
        $permissions->set_fields($request_vars);
        $permissions->insert();
        $action = "update";
    }
    else if ($_action == "add_all") {
        $albums = get_albums();
        if ($albums) {
            foreach ($albums as $alb) {
                $permissions = new album_permissions(
                    $user_id, $alb->get("album_id"));
                $permissions->set_fields($request_vars);
                $permissions->insert();
            }
        }
        $action = "update";
    }
    else if ($_action == "update_album") {
        $permissions = new album_permissions();
        $permissions->set_fields($request_vars);
        $permissions->update();
        $action = "update";
    }
    else if ($_action == "revoke_album") {
        $permissions = new album_permissions($user_id, $album_id);
        $permissions->delete();
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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TITLE_BG_COLOR?>">
<?php
    if ($action == "display") {
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("user") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
          [
            <a href="user.php?_action=edit&user_id=<?php echo $this_user->get("user_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("edit") ?></font></a> |
            <a href="user.php?_action=delete&user_id=<?php echo $this_user->get("user_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a> |
            <a href="user.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <th align="left" colspan="3">
            <?php echo $this_user->get("user_name") ?>
          </th>
        </tr>
<?php echo create_field_html($this_user->get_display_array(), 3) ?>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <th align="center" colspan="3"><?php echo translate("Albums") ?></th>
        </tr>
<?php
        if ($this_user->is_admin()) {
?>
        <tr>
          <td align="center" colspan="3">
       <?php echo sprintf(translate("As an admin, user %s has access to all albums."), $this_user->get("user_name")) ?>
          </td>
        </tr>
<?php
        }
        else {
?>
        <tr>
          <td align="center" width="50%"><?php echo translate("name") ?></td>
          <td align="center"><?php echo translate("access level") ?></td>
          <td align="center"><?php echo translate("writable") ?></td>
        </tr>
<?php
            $albums = get_albums_select_array($this_user);
            while (list($id, $name) = each($albums)) {
                if (!$id || $id == 1) { continue; }
                $permissions = $this_user->get_album_permissions($id);
?>
        <tr>
          <td align="left"><?php echo $name ?></td>
          <td align="center"><?php echo $permissions->get("access_level") ?></td>
          <td align="center"><?php echo $permissions->get("writable") == "1" ? translate("Yes") : translate("No") ?></td>
        </tr>
<?php
            }
        }
    }
    else if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete user") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">[
            <a href="user.php?_action=display&user_id=<?php echo $this_user->get("user_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("cancel") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $this_user->get("user_name")) ?>
          </td>
          <td align="right">[
            <a href="user.php?_action=confirm&user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="user.php?_action=display&user_id=<?php echo $this_user->get("user_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
<?php
    }
    else {
require_once("edit_user.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <th align="center" colspan="5"><?php echo translate("Albums") ?></th>
        </tr>
<?php
        if ($action != "insert" && $this_user->is_admin()) {
?>
        <tr>
          <td align="center" colspan="5">
       <?php echo sprintf(translate("As an admin, user %s has access to all albums."), $this_user->get("user_name")) ?>
          </td>
        </tr>
<?php
        }
        else {
            if ($action == "insert") {
?>
        <tr>
          <td align="center" colspan="5">
       <?php echo translate("After this user is created they can be given access to albums.") ?>
          </td>
        </tr>
<?php
            }
            else {
?>
        <tr>
          <td align="left" colspan="5">
       <?php echo translate("Granting access to an album will also grant access to that album's ancestors if required.  Granting access to all albums will not overwrite previously granted permissions.") ?>
          </td>
        </tr>
        <tr>
          <td align="center"><?php echo translate("name") ?></td>
          <td align="center"><?php echo translate("access level") ?></td>
          <td align="center">writable</td>
          <td align="center">&nbsp;</td>
          <td align="center">&nbsp;</td>
        </tr>
        <tr>
          <td align="left">
<form action="user.php">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="_action" value="add_all">
<?php echo translate("Grant access to all existing albums:") ?>
          </td>
          <td align="center">
<?php echo create_text_input("access_level", "5", 4, 2) ?>
          </td>
          <td align="center">
<?php echo create_pulldown("writable", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
          </td>
          <td align="center" colspan="2">
<input type="submit" value="<?php echo translate("add", 0); ?>">
</form>
          </td>
        </tr>
        <tr>
          <td align="left">
<form action="user.php">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="_action" value="add_album">
<?php echo create_smart_pulldown("album_id", "", get_albums_select_array()) ?>
          </td>
          <td align="center">
<?php echo create_text_input("access_level", "5", 4, 2) ?>
          </td>
          <td align="center">
<?php echo create_pulldown("writable", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
          </td>
          <td align="center" colspan="2">
<input type="submit" value="<?php echo translate("add", 0); ?>">
</form>
          </td>
        </tr>
<?php
            $albums = get_albums_select_array($this_user);
            while (list($id, $name) = each($albums)) {
                if (!$id || $id == 1) { continue; }
                $permissions = $this_user->get_album_permissions($id);
?>
        <tr>
          <td align="left">
<?php echo $name ?>
          </td>
          <td align="center">
<form action="user.php">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="_action" value="update_album">
<input type="hidden" name="album_id" value="<?php echo $id ?>">
<?php echo create_text_input("access_level", $permissions->get("access_level"), 4, 2) ?>
          </td>
          <td align="center">
<?php echo create_pulldown("writable", $permissions->get("writable"), array("0" => translate("No",0), "1" => translate("Yes",0))) ?>
          </td>
          <td align="center">
<input type="submit" value="<?php echo translate("update", 0); ?>">
</form>
          </td>
          <td align="center">
<form action="user.php">
<input type="hidden" name="user_id" value="<?php echo $this_user->get("user_id") ?>">
<input type="hidden" name="_action" value="revoke_album">
<input type="hidden" name="album_id" value="<?php echo $id ?>">
<input type="submit" value="<?php echo translate("revoke", 0); ?>">
</form>
          </td>
        </tr>
<?php
            } // while

            } // not insert
        } // not admin
    } // edit
?>
      </table>
    </td>
  </tr>
</table>

</div>

<?php require_once("footer.inc.php"); ?>
