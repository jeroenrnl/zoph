<?php
/**
 * Edit groups
 *
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
 *
 * @package Zoph
 * @author Jeroen Roos
 */
?>

<!-- begin edit_group.inc !-->
  <h1>
    <span class="actionlink">
      <a href="group.php?group_id=<?php echo $group->get("group_id")?>">
        <?php echo translate("return") ?>
      </a> |
      <a href="group.php?_action=new"><?php echo translate("new") ?></a>
    </span>
    <?php echo translate("add/edit group") ?>
  </h1>
  <div class="main">
    <form action="group.php" method="POST" class="editgroup">
      <p>
      <input type="hidden" name="_action" value="<?php echo $action ?>">
      <input type="hidden" name="group_id" value="<?php echo $group->get("group_id") ?>">
       <label for="groupname"><?php echo translate("group name") ?></label>
       <?php echo create_text_input("group_name", $group->get("group_name"), 32, 32) ?>
       <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "32") ?></span>
       <br>
       <label for="description"><?php echo translate("description") ?></label>
       <?php echo create_text_input("description", $group->get("description"), 32, 128) ?>
       <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "128") ?></span>
       <br>
<?php
if ($action!="insert") {
    ?>
    <fieldset class="addusers">
      <legend><?php echo translate("members") ?></legend>

    <?php
    $members=$group->getMembers();
    foreach ($members as $member) {
        $member->lookup();
        ?>
        <input class="remove" type="checkbox" name="_remove_user[]"
            value="<?php echo $member->getId()?>">
        <?php echo $member->getLink() ?>
        <br>
        <?php
    }
    echo $group->getNewMemberPulldown("_member");
    ?>
    </fieldset>
    <?php
}
?>
    <input type="submit" value="<?php echo translate($action, 0) ?>">
  </p>
</form>
<!-- end edit_group.inc !-->
