<?php
/**
 * Template for 'share this photo' tab
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
<li class="share">
    <div class="tab"><img src="images/icons/default/rating.png"></div>
    <div class="contents">
        <h1><?echo translate("share this photo", 0) ?></h1>
        <ul class="share">
            <?php if(!empty($tpl_hash)): ?>
                <li class="direct_link"><input type="text" value="<?php echo $tpl_full_link; ?>"></li>
                <li class="html"><textarea><img src="<?php echo $tpl_mid_link ?>"></textarea></li>
            <?php else: ?>
                <?php echo translate("This feature is not available because the photo was not found.",0); ?>
            <?php endif; ?>
         </ul>
    </div>
</li>
