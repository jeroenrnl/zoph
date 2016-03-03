<?php
/**
 * Template to show members of a group
 * used for usergroups and circles
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
 * @author Jeroen Roos
 * @package ZophTemplates
 */

if (!ZOPH) {
    die("Illegal call");
}
?>
<fieldset class="members">
    <legend><?= translate("members") ?></legend>
    <?php foreach ($tpl_members as $member): ?>
        <input class="remove" type="checkbox" name="_removeMember[]"
            value="<?= $member->getId() ?>">
        <a href="<?= $member->getURL() ?>">
            <?= $member->getName() ?>
        </a><br>
    <?php endforeach ?>
    <?= $tpl_group->getNewMemberPulldown("_member") ?>
</fieldset>
<br>
