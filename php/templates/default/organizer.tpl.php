<?php
/**
 * Template for displaying albums, categories, people (circles) and places
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

use template\template;

if (!ZOPH) {
    die("Illegal call");
}
?>
<h1>
    <?= $this->getActionlinks() ?>
    <?= $tpl_title ?>
</h1>
<?php if ($tpl_selection): ?>
    <?= $tpl_selection ?>
<?php endif ?>
<div class="main">
    <form class="viewsettings" method="get">
        <?php foreach ($tpl_view_hidden as $field => $value): ?>
            <input type="hidden" name="<?= $field ?>" value="<?= $value ?>">
        <?php endforeach ?>
        <?= translate($tpl_view_name) ?>
        <?= template::createViewPulldown("_view", $tpl_view, true) ?>
        <?= translate("Automatic thumbnail") ?>
        <?php echo template::createAutothumbPulldown("_autothumb", $tpl_autothumb, true) ?>
    </form><br>
    <?= $this->displayBlocks(); ?>
</div>
