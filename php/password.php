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
    <h1><?php echo translate("change password") ?></h1>
      <div class="main" id="passwordchange">
         <form action="password.php" method="POST">
<?php
    if ($msg) {
?>
          <?php echo $msg ?>.
<?php
    }
?>
            <h2><?php echo $user->get("user_name") ?></h2>
<?php
    if (DEFAULT_USER == $user->get("user_id")) {
?>
       <?php echo sprintf(translate("The user '%s' is currently defined as the default user and does not have permission to change its password."), $user->get("user_name")) ?>
<?php
    }
    else {
?>
<input type="hidden" name="_action" value="update">
          <label for="password"><?php echo translate("password") ?></label>
          <input type="password" name="password" id="password" value="" size="16" maxlength="32">
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
          <label for="confirm"><?php echo translate("confirm password") ?></label>
          <input type="password" name="confirm" id="confirm" value="" size="16" maxlength="32"><br>
          <div class="center">
<input type="submit" value="<?php echo translate("submit", 0); ?>">
          </div>
<?php
    }
?>
</form>
</div>
<?php require_once("footer.inc.php"); ?>
