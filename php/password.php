<?php
    require_once("include.inc.php");

    $password = getvar("password");
    $confirm = getvar("confirm");

    if ($_action == "update" && DEFAULT_USER != $user->get("user_id")) {

        if ($password) {
            if ($password == $confirm) {
                $user->set("password", $password);
                $user->update();
                $msg = translate("Your password has been changed");
            }
            else {
                $msg = translate("Your passwords did not match");
            }
        }
        else {
            $msg = translate("Your password may not be null");
        }
    }

    $title = translate("Change Password");

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("change password") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<?php
    if ($msg) {
?>
        <tr>
          <td align="center" colspan="3"><?= $msg ?>.</td>
        </tr>
<?php
    }
?>
        <tr>
          <th align="left" colspan="3">
            <?= $user->get("user_name") ?>
          </th>
        </tr>
<?php
    if (DEFAULT_USER == $user->get("user_id")) {
?>
        <tr>
          <td colspan="3">
       <?php echo sprintf(translate("The user '%s' is currently defined as the default user and does not have permission to change its password."), $user->get("user_name")) ?>
          </td>
        </tr>
<?php
    }
    else {
?>
        <tr>
          <td><?php echo translate("password") ?></td>
          <td>
<form action="password.php" method="POST">
<input type="hidden" name="_action" value="update">
<input type="password" name="password" value="" size="16" maxlength="32">
          </td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("confirm password") ?></td>
          <td><input type="password" name="confirm" value="" size="16" maxlength="32"></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="center" colspan="3">
<input type="submit" value="<?php echo translate("submit", 0); ?>">
</form>
          </td>
        </tr>
<?php
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>

<?php require_once("footer.inc.php"); ?>
