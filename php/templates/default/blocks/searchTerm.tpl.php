<?php
/**
 * Template for a search term
 * This is one line on the search screen
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
 * @package ZophTemplates
 * @author Jeroen Roos
 */

if (!ZOPH) { die("Illegal call"); }
?>
<div class="searchTerm">
    <div class="searchIncrement">
        <?php if ($tpl_inc): ?>
            <input type="submit" class="increment" name="<?= $tpl_inc ?>" value="+">
        <?php endif ?>
    </div>
    <div class="searchConj">
        <?= $tpl_conj ?>
    </div>
    <div class="searchLabel">
        <?= $tpl_label; ?>
    </div>
    <div class="searchOp">
        <?= $tpl_op ?>
    </div>
    <div class="searchValue">
        <?= $tpl_value ?>
        <?php if (isset($tpl_value_text)): ?>
            <span class="searchValueText">
                <?= $tpl_value_text ?>
            </span>
        <?php endif ?>
        <?php if (isset($tpl_child)): ?>
            <br>
            <input type="checkbox" name="<?= $tpl_child ?>" value="yes" <?= $tpl_child_checked ?>>
            <label for="<?= $tpl_child ?>">
                <?= $tpl_child_label ?>
            </label>
        <?php endif ?>
    </div>
</div>
