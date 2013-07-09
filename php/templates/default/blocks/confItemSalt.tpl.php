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
            <input type="text" id="<?php echo $tpl_id ?>" pattern="<?php echo $tpl_regex ?>" 
                name="<?php echo $tpl_name ?>" value="<?php echo $tpl_value; ?>" 
                size="<?php echo $tpl_size ?>" <?php echo $tpl_req ?>>
            <input type="button" onclick="zConf.genSalt('<?php echo $tpl_id ?>')" 
                value="<?php echo translate("Generate",0); ?>">
        </div>
        <input class="reset" type="checkbox" name="_reset_<?php echo $tpl_name ?>">
        <span><?php echo translate("reset to default",0) ?></span>
        <?php if(!empty($tpl_hint)): ?>
            <div class="inputhint">
                <?php echo $tpl_hint ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($tpl_desc)): ?>
            <div class="desc">
                <?php echo $tpl_desc ?>
            </div>
        <?php endif; ?>
    </div>
    
