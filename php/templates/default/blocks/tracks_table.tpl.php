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
        <br>
        <?php if(is_array($tpl_tracks)): ?>
        <table class="tracks">
            <tr>
                <th><?php echo translate("name") ?></th>
                <th><?php echo translate("time of first point") ?></th>
                <th><?php echo translate("time of last point") ?></th>
                <th><?php echo translate("number of points") ?></th>

            </tr>
            <?php foreach($tpl_tracks as $track): ?>
            <tr>
                <td>
                     <a href="track.php?track_id=<?php echo $track->getId(); ?>">
                        <?php echo e($track->get("name")); ?>
                    </a>
                </td>
                <td>
                    <?php echo e($track->getFirstPoint()->get("datetime")); ?>
                </td>
                <td>
                    <?php echo e($track->getLastPoint()->get("datetime")); ?>
                </td>
                <td>
                    <?php echo e($track->getPointCount()); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <?php echo translate("No tracks found, you should import a GPX file.") ?>
        <?php endif;?>
