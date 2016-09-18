<?php
/**
 * Template for editing group permissions
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
<h3><?= translate("Albums") ?></h3>
<?= translate("Granting access to an album will also grant access to that album's " .
    "ancestors if required. Granting access to all albums will not overwrite " .
    "previously granted permissions."); ?>
<?php if ($tpl_watermark): ?>
    <br>
    <?= translate("A photo will be watermarked if the photo level is " .
        "higher than the watermark level.") ?>
<?php endif ?>
<form action="group.php" method="post" class="grouppermissions">
    <table class="permissions">
        <col class="col1"><col class="col2"><col class="col3"><col class="col4">
        <tr>
            <th colspan="2"><?= translate("name") ?></th>
            <th><?= translate("access level") ?></th>
            <?php if ($tpl_watermark): ?>
                <th><?= translate("watermark level") ?></th>
            <?php endif ?>
            <th><?php echo translate("writable"); ?></th>
            <th><?php echo translate("grant to subalbums"); ?></th>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="_access_level_all_checkbox" value="1">
            </td>
            <td>
                <input type="hidden" name="group_id" value="<?= $tpl_group_id ?>">
                <input type="hidden" name="_action" value="update_albums">
                <?= translate("Grant access to all existing albums:") ?>
            </td>
            <td>
                <?= $tpl_accessLevelAll ?>
            </td>
            <?php if ($tpl_watermark): ?>
                <td>
                    <?= $tpl_wmLevelAll ?>
                </td>
            <?php endif ?>
            <td>
                <?php echo template::createYesNoPulldown("writable_all", "0") ?>
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td>
                <input type="hidden" name="group_id_new" value="<?= $tpl_group_id ?>">
                <?= template::createPulldown("album_id_new", "", album::getTreeSelectArray()) ?>
            </td>
            <td>
                <?= $tpl_accessLevelNew?>
            </td>
            <?php if ($tpl_watermark): ?>
                <td>
                    <?= $tpl_wmLevelNew ?>
                </td>
            <?php endif ?>
            <td>
                <?php echo template::createYesNoPulldown("writable_new", "0") ?>
            </td>
            <td>
                <?php echo template::createYesNoPulldown("subalbums_new", "0") ?>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="permremove">
                <?php echo translate("remove") ?>
            </td>
        </tr>
        <?php foreach ($tpl_permissions as $perm): ?>
            <tr>
                <td>
                    <input type="checkbox" name="_remove_permission_album__<?= $perm->id ?>" value="1">
                </td>
                <td>
                    <?= $perm->name ?>
                </td>
                <td>
                    <input type="hidden" name="album_id__<?= $perm->id ?>" value="<?= $perm->id ?>">
                    <input type="hidden" name="group_id__<?= $perm->id ?>" value="<?= $tpl_group_id ?>">
                    <?= template::createInput("access_level__" . $perm->id, $perm->access, 4) ?>
                </td>
                <?php if ($tpl_watermark): ?>
                    <td>
                        <?= template::createInput("watermark_level__" . $perm->id, $perm->wm, 4) ?>
                     </td>
                <?php endif ?>
                <td>
                    <?= template::createYesNoPulldown("writable__" . $perm->id, $perm->writable) ?>
                </td>
                <td>
                    <?= template::createYesNoPulldown("subalbums__" . $perm->id, $perm->subalbums) ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
    <input type="submit" value="<?= translate("update", 0) ?>">
</form>
