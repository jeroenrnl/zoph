<?php
/**
 * Template for html from select tag
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
<select name="<?php echo $tpl_name ?>" id="<?php echo $tpl_id ?>" <?php echo ($tpl_autosubmit ? "onChange='form.submit()'" : "") ?> >
    <?php foreach($tpl_options as $option=>$label): ?>
        <?php if($tpl_value==$option): ?>
            <?php $selected="selected"; ?>
        <?php else: ?>
            <?php $selected=""; ?>
        <?php endif ?>    
        <option <?php echo $selected; ?> value="<?php echo $option ?>"><?php echo $label ?></option>
    <?php endforeach ?>
</select>
    
