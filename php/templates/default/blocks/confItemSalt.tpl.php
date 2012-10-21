<?php
/**
 * Template for the confItemSalt object
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
    <div>
        <label for="<?php echo $tpl_name; ?>"><?php echo $tpl_label; ?></label>
        <div class="generate">
            <input type="text" id="<?php echo $tpl_id ?>" pattern="<?php echo $tpl_regex ?>" title="<?php echo $tpl_title ?>" name="<?php echo $tpl_name ?>" value="<?php echo $tpl_value; ?>" size="<?php echo $tpl_size ?>">
            <input type="button" onclick="zConf.genSalt('<?php echo $tpl_id ?>')" value="<?php echo translate("Generate"); ?>">
        </div>
        <div class="desc">
            <?php echo $tpl_desc ?>
        </div>
        <div class="inputhint">
            <?php echo $tpl_hint ?>
        </div>
    </div>
    
