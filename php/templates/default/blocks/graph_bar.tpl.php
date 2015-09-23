<?php
/**
 * Template for a bar graph
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

if(!ZOPH) { die("Illegal call"); }
?>

<div class="graph bar <?php echo $tpl_class ?>">
    <h3><?php echo $tpl_title ?></h3>
    <table>
        <tr>
            <th><?php echo $tpl_value_label ?></th>
            <th><?php echo $tpl_count_label ?></th>
        </tr>

        <?php foreach($tpl_rows as $row): ?>
        <tr>
            <td>
                <?php if(isset($row["link"])): ?>
                    <a href="<?php echo $row["link"] ?>">
                <?php endif ?>
                <?php echo $row["value"] ?>
                <?php if(isset($row["link"])): ?>
                    </a>
                <?php endif ?>
            </td>
            <td>
                <div class="bar">
                    <div class="fill" style="width: <?php echo $row["width"] ?>%">&nbsp;</div>
                    <div class="count"><?php echo $row["count"] ?></div>
                </div>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
</div>


