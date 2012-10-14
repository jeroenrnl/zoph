<?php
/**
 * Template for a boolean configuration item
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
    <label for="<?php echo $tpl_name; ?>"><?php echo $tpl_label; ?></label>
    <input type="hidden" name="<?php echo $tpl_name ?>" value="0">
    <input type="checkbox" name="<?php echo $tpl_name ?>" value="1" <?php echo $tpl_checked ?>>
    <div class="desc">
        <?php echo $tpl_desc ?>
    </div>
    <div class="inputhint">
        <?php echo $tpl_hint ?>
    </div>
    
