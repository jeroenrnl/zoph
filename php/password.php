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

    $userid = getvar("userid");
    $password = getvar("password");
    $confirm = getvar("confirm");

    if($user->is_admin() && $userid) {
        $change=new user($userid);
        $change->lookup();
    } else {
        $change=$user;
    }
    if ($_action == "update" && conf::get("interface.user.default") != $user->get("user_id")) {

        if ($password) {
            if ($password == $confirm) {
                $change->set("password", $password);
                $change->update();
                $msg = sprintf(translate("The password for %s has been changed"), $change->get("user_name"));
            }
            else {
                $msg = translate("The passwords did not match");
            }
        }
        else {
            $msg = translate("The password may not be null");
        }
    }

    $title = translate("Change Password");

    require_once("header.inc.php");
?>
    <h1><?php echo translate("change password") ?></h1>
      <div class="main" id="passwordchange">
         <form action="password.php" method="POST">
<?php
    if (isset($msg)) {
?>
          <?php echo $msg ?>.
<?php
    }
?>
            <h2><?php echo $change->get("user_name") ?></h2>
<?php
    if (!$user->is_admin() && conf::get("interface.user.default") == $change->get("user_id")) {
?>
       <?php echo sprintf(translate("The user '%s' is currently defined as the default user and does not have permission to change its password."), $user->get("user_name")) ?>
<?php
    }
    else {
?>
          <input type="hidden" name="_action" value="update">  
          <input type="hidden" name="userid" id="userid" value="<?php echo $change->get("user_id")?>">
          <label for="password"><?php echo translate("password") ?></label>
          <input type="password" name="password" id="password" value="" size="16" maxlength="32">
          <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span><br>
          <label for="confirm"><?php echo translate("confirm password") ?></label>
          <input type="password" name="confirm" id="confirm" value="" size="16" maxlength="32"><br>
          <input type="submit" value="<?php echo translate("submit", 0); ?>">
<?php
    }
?>
</form>
</div>
<?php require_once("footer.inc.php"); ?>
