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
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("users") ?></h1></th>
          <td class="actionlink">[
            <a href="user.php?_action=new"><?php echo translate("new") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
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
            <?php echo $u->get("lastlogin"); ?>
          </td>
          <td class="actionlink"> [
<?php
            if ((count(get_newer_albums($u->get("user_id"), $u->get_lastnotify())) > 0)) {
?>
            <a href="notify.php?_action=notify&amp;user_id=<?php echo $u->get("user_id") ?>&amp;shownewalbums=1"><?php echo translate("Notify User", 0) ?></a> |
<?php
            }
?>
            <a href="user.php?user_id=<?php echo $u->get("user_id") ?>"><?php echo translate("view") ?></a> ]
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

<?php
    require_once("footer.inc.php");
?>
