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
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("change password") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="password.php" method="POST">
      <table class="main">
<?php
    if ($msg) {
?>
        <tr>
          <td class="center" colspan="3"><?php echo $msg ?>.</td>
        </tr>
<?php
    }
?>
        <tr>
          <th colspan="3">
            <h2><?php echo $user->get("user_name") ?></h2>
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
          <td class="fieldtitle"><?php echo translate("password") ?></td>
          <td class="field">
<input type="hidden" name="_action" value="update">
<input type="password" name="password" value="" size="16" maxlength="32">
          </td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("confirm password") ?></td>
          <td class="field"><input type="password" name="confirm" value="" size="16" maxlength="32"></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="center" colspan="3">
<input type="submit" value="<?php echo translate("submit", 0); ?>">
          </td>
        </tr>
<?php
    }
?>
      </table>
</form>
    </td>
  </tr>
</table>

<?php require_once("footer.inc.php"); ?>
