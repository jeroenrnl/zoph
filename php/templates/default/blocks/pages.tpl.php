<?php
/**
 * Template for displaying a table of pages
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
<table class="pages">
    <tr>
        <th><?= translate("title") ?></th>
        <th><?= translate("date") ?> </th>
        <th><?= translate("last modified") ?></th>
    </tr>
    <?php foreach ($tpl_pages as $page): ?>
        <tr>
            <td>
                <a href=page.php?page_id=<?= $page->getId() ?>>
                    <?= $page->get("title") ?>
                </a>
            </td>
            <td>
                <?= $page->get("date") ?>
            </td>
            <td>
                <?= $page->get("timestamp") ?>
            </td>
            <?php if (isset($tpl_pageset)): ?>
                <td>
                    <ul class="actionlink">
                        <li><a href=
"pageset.php?_action=moveup&pageset_id=<?= $tpl_pageset->getId() ?>&page_id=<?= $page->getId() ?>">
                            <?= translate("move up") ?>
                        </a></li>
                        <li><a href=
"pageset.php?_action=movedown&pageset_id=<?= $tpl_pageset->getId() ?>&page_id=<?= $page->getId() ?>">
                            <?= translate("move down") ?>
                        </a></li>
                        <li><a href=
"pageset.php?_action=remove&pageset_id=<?= $tpl_pageset->getId() ?>&page_id=<?= $page->getId() ?>">
                            <?= translate("remove") ?>
                        </a></li>
                    </ul>
                </td>
            <?php endif ?>
        </tr>
    <?php endforeach ?>
</table>
