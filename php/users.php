<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $title = translate("Users");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("users") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">[
            <a href="user.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<?php
    $users = get_users();

    if ($users) {
        foreach($users as $u) {
            $u->lookup_person();
?>
        <tr>
          <td>
            <a href="user.php?user_id=<?php echo $u->get("user_id") ?>"><?php echo $u->get("user_name") ?></a>
          </td>
          <td>
            <?php echo $u->person->get_link() ?>
          </td>
          <td>
<?php
            if ((count(get_newer_albums($u->get("user_id"), $u->get_lastnotify())) > 0)) {
?>
            <a href="notify.php?_action=notify&user_id=<?php echo $u->get("user_id") ?>&shownewalbums=1"><?php echo translate("Notify User", 0) ?></a>
<?php
            }
            else {
                echo "&nbsp;";
            }
?>
          </td>
          <td>
            <?php echo $u->get("lastlogin"); ?>
          </td>
          <td align="right">
            [ <a href="user.php?user_id=<?php echo $u->get("user_id") ?>"><?php echo translate("view") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>
<?php
    require_once("footer.inc.php");
?>
