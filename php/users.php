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
        redirect(add_sid("zoph.php"));
    }

    $title = translate("Users");
    require_once("header.inc.php");
?>
        <h1>
          <span class="actionlink">
            <a href="user.php?_action=new"><?php echo translate("new") ?></a>
          </span>
          <?php echo translate("users") ?>
        </h1>
        <div class="main">
          <table id="users">
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
            <?php echo $u->person->getLink() ?>
          </td>
          <td>
          <span class="actionlink">
<?php
            if ((count(get_newer_albums($u->get("user_id"), $u->get_lastnotify())) > 0)) {
?>
            <a href="notify.php?_action=notify&amp;user_id=<?php echo $u->get("user_id") ?>&amp;shownewalbums=1"><?php echo translate("Notify User", 0) ?></a> |
<?php
            }
?>
            <a href="user.php?user_id=<?php echo $u->get("user_id") ?>"><?php echo translate("display") ?></a>
            </span>
            <?php echo $u->get("lastlogin"); ?>
          </td>
        </tr>
<?php
        }
    }
?>
      </table>
    </div>
<?php
    require_once("footer.inc.php");
?>
