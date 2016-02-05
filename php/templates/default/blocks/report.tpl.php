<?php
/**
 * Template to display a TopN list on the reports page.
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
<table class="reports">
    <tr>
        <th colspan="3"><h3><?php echo $tpl_title ?></h3></th>
    </tr>
    <?php foreach ($tpl_lines as $line): ?>
    <tr>
        <td>
            <a href="<?php echo $line["url"] ?>">
                <?php echo $line["title"] ?>
            </a>
        </td>
        <td>
            <?php echo $line["count"] ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
