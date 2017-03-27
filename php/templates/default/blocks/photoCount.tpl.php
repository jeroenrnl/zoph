<?php
/**
 * Template to display the photocount links for organizers
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

<ul class="photolinks">
    <?php if ($tpl_tpc > 0): ?>
        <li><a href="<?= $tpl_totalUrl ?>">
            <img src="<?= template::getImage("icons/folderphoto.png") ?>">
            <span class="photocount"><?= $tpl_tpc ?> <?= translate("photo" . ($tpl_tpc == 1 ? "" :"s") ) ?></span>
        </a></li>
    <?php endif ?>
    <?php if ($tpl_pc > 0):  ?>
        <li><a href="<?= $tpl_url ?>">
            <img src="<?= template::getImage("icons/photobig.png") ?>">
            <span class="photocount"><?= $tpl_pc ?> <?= translate("photo" . ($tpl_pc == 1 ? "" : "s")) ?></span>
        </a></li>
    <?php endif ?>
</ul>
