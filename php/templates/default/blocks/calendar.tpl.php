<?php
/**
 * Template to display a map.
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
<?php $d=0; ?>
<table class="calendar">
  <tr>
    <td class="prev">
      <a href="<?php echo $tpl_prev ?>">&lt;&lt;</a>
    </td>
    <td colspan=5>
      <h2><?php echo $tpl_header ?></h2>
    </td>
    <td class="next">
      <a href="<?php echo $tpl_next ?>">&gt;&gt;</a>
    </td>
  </tr>
  <tr>
    <?php foreach ($tpl_titles as $title): ?>
      <th><?php echo $title ?></th>
    <?php endforeach; ?>
  </tr>
  <tr>
    <?php foreach ($tpl_days as $day): ?>
      <td class="<?php echo $day["class"] ?>">
        <?php if ($day["link"]): ?>
          <a href="<?php echo ($day["link"]) ?>">
        <?php endif; ?>
        <?php echo $day["date"] ?>
        <?php if ($day["link"]): ?>
          </a>
        <?php endif; ?>
      </td>
      <?php $d++ ?>
      <?php if ($d % 7 == 0): ?>
        </tr>
        <tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </tr>
</table>
