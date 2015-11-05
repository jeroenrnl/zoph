<?php
/**
 * Template for progressbar
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

if(!ZOPH) {
    die("Illegal call");
}
?>
<div class="uploadprogress" id="prog_<?php echo $tpl_upload_num ?>">
    <span id="fn_<?php echo $tpl_upload_num ?>" class="fn_upload">
        <?php echo $tpl_name; ?>
    </span>
    <span id="sz_<?php echo $tpl_upload_num ?>" class="sz_upload">
        <?php echo $tpl_size; ?>
    </span>
    <div id="pb_<?php echo $tpl_upload_num; ?>_outer" class="progressbar"
        style="width: <?php echo $tpl_width; ?>px">
        <div id="pb_<?php echo $tpl_upload_num; ?>_inner" class="progressfill"
            style="width: <?php echo $tpl_complete; ?>%">
                <?php echo $tpl_complete; ?>%
        </div>
    </div>
</div>

