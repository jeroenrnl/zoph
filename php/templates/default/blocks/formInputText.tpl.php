<?php
/**
 * Template for HTML input field text
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

if (!ZOPH) {
    die("Illegal call");
}
?>

<?php if ($tpl_label): ?>
    <label for="<?= $tpl_name ?>">
        <?= $tpl_label ?>
    </label>
<?php endif; ?>
<input id="<?= $tpl_name ?>" type="text" name="<?= $tpl_name ?>" maxlength="<?= $tpl_maxlength ?>" size="<?= $tpl_size ?>" value="<?= $tpl_value ?>">
<?php if (!empty($tpl_hint)): ?>
    <span class="inputhint">
        <?php echo $tpl_hint ?>
    </span>
<?php endif; ?>
<br>
