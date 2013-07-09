<?php
/**
 * Template for overview table for tracks
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
<?php if($tpl_test==true): ?>
    <?php echo $this->getActionlinks($tpl_actionlinks); ?>
    <?php if($tpl_tagged_count==0): ?>
        <?php printf(translate("The location of none of the photos in the test set " .
            "could be determined. This does not necessarily mean that none of the " .
            "photos can be geotagged, since only a subset was tried. You can try to " .
            "geotag %s photos by clicking 'geotag'."), $tpl_total_count); ?>
    <?php else: ?>
        <?php printf(translate("The location of %s photos has been determined. You can " .
            "check the results below. Click 'geotag' to geotag all %s photos."), 
            $tpl_tagged_count, $tpl_total_count); ?>
    <?php endif; ?>
<?php else: ?>
    <?php if($tpl_tagged_count==0): ?>
        <?php echo translate("No photos were found matching your search criteria."); ?>
    <?php else: ?>
        <?php printf(translate("%s photos were geotagged"), $tpl_tagged_count); ?>
    <?php endif; ?>
<?php endif; ?>
