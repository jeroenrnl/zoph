<?php
/**
 * Template for 'breadcrumbs'
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

if(!ZOPH) { die("Illegal call"); }
?>
<div class="breadcrumbs">
    <ul class="actionlink">
        <li>
            <a href="<?= $tpl_clearURL ?>">x</a>
        </li>
    </ul>
    <ul class="breadcrumbs <?= $tpl_class ?>">
        <?php foreach($tpl_crumbs as $crumb): ?>
            <li>
                <a href="<?= $crumb->getLink() ?>"><?= $crumb->getTitle() ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
