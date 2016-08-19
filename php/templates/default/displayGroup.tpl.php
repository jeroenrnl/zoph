<?php
/**
 * Template for displaying user group information
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
<h1>
    <?= $this->getActionlinks($tpl_actionlinks) ?>
    <?= $tpl_title ?>
</h1>
<div class="main">
    <h2><?= translate("Group") ?></h2>
    <dl class="group">
        <?php foreach ($tpl_fields as $title => $value): ?>
            <dt><?= $title ?></dt>
            <dd><?= $value ?></dd>
        <?php endforeach ?>
    </dl>
    <br>
    <h2><?= translate("Albums") ?></h2>
    <table class="permissions">
        <tr>
            <th><?= translate("name") ?></th>
            <th><?= translate("access level") ?></th>
            <?php if($tpl_watermark): ?>
                <th><?= translate("watermark level") ?></th>
            <?php endif ?>
            <th><?= translate("writable") ?></th>
        </tr>
        <?php foreach($tpl_permissions as $perm): ?>
            <tr>
                <td><?= $perm->name ?></td>
                <td><?= $perm->access ?></td>
                <?php if($tpl_watermark): ?>
                    <td><?= $perm->wm ?></td>
                <?php endif ?>
                <td><?= $perm->writable ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
