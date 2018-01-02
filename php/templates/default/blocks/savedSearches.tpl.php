<?php
/**
 * Template for displaying a table of saved searches
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
<br>
<h2><?= translate("Saved searches") ?></h2>
<ul class="saved_search">
    <?php foreach ($tpl_searches as $search): ?>
        <li>
            <ul class="actionlink">
                <li><a href="<?= $search->getSearchURL() ?>"><?= translate("load") ?></a></li>
                <?php if (($search->get("owner") == $tpl_user->getId()) || $tpl_user->isAdmin()): ?>
                    <li><a href="<?= $search->getURL() ?>&_action=edit"><?= translate("edit") ?></a></li>
                    <li><a href="<?= $search->getURL() ?>&_action=delete"><?= translate("delete") ?></a></li>
                <?php endif ?>
           </ul>
           <?= $search->getLink(); ?>
       </li>
    <?php endforeach ?>
</ul>

