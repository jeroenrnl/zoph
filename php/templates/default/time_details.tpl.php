<?php
/**
 * Template for detail view for time/date
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

<div class="timedetails collapsed">
    <div class="toggle" onclick="thumbview.toggle(this.parentNode)"></div>
    <?php echo $tpl_calc_time ?>
    <div class="timedetail">
        <dl class="timedetail">
            <h3><?php echo translate("database"); ?></h3>
            <dt><?php echo translate("date"); ?></dt>
            <dd><?php echo $tpl_photo_date; ?></dd>
            <dt><?php echo translate("time"); ?></dt>
            <dd><?php echo $tpl_photo_time; ?></dd>
            <dt><?php echo translate("timezone"); ?></dt>
            <?php if(isset($tpl_camera_tz)): ?>
                <dd><?php echo $tpl_camera_tz; ?></dd>
            <?php else: ?>
                <dd><i><?php echo translate("not set"); ?></i></dd>
            <?php endif; ?>
            <?php if(!empty($tpl_corr)): ?>
                <dt><?php echo translate("correction"); ?></dt>
                <dd><?php echo $corr . " " . translate("minutes"); ?></dd>
            <?php endif; ?>
            <br>
            <?php if(isset($tpl_location)): ?>
                <h3><?php echo translate("location"); ?></h3>
                <dt><?php echo translate("location"); ?></dt>
                <dd><?php echo $tpl_location; ?></dd>
                <dt><?php echo translate("timezone"); ?></dt>
                <?php if(!empty($tpl_loc_tz)): ?>
                    <dd><?php echo $tpl_loc_tz; ?></dd>
                <?php else: ?>
                    <dd><i><?php echo translate("not set"); ?></i></dd>
                <?php endif; ?>
                <br>
            <?php endif; ?>
            <h3><?php echo translate("calculated time"); ?></h3>
            <dt><?php echo translate("date"); ?></dt>
            <dd><?php echo $tpl_calc_date; ?></dd>
            <dt><?php echo translate("time"); ?></dt>
            <dd><?php echo $tpl_calc_time; ?></dd>
        </dl>
        <br>
    </div>
</div>

            
