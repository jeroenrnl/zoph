<?php
/**
 * Template for displaying the 'are you sure' (confirm) question when deleting
 * an item
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
    <?= sprintf(translate("Confirm deletion of '%s'"), $tpl_obj->getName()) ?>
    <?= $this->getActionlinks($tpl_mainActionlinks) ?>
</div>
