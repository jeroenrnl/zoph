<?php
/**
 * Template for displaying the pager list
 * Displays a list of pages, usually at the bottom of a page, to navigate to different pages
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
<ul class="pager">
    <?php foreach ($tpl_pages as $pageGroups): ?>
        <li><ul class="pagegroup">
        <?php foreach ($pageGroups as $title => $link): ?>
            <li <?= ($title == $tpl_current) ? "class='current'" : "" ?>><a href="<?= $link ?>"><?= $title ?></a>
        <?php endforeach ?>
        </li></ul>
    <?php endforeach ?>
</ul>
