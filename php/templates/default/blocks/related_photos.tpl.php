<?php
/**
 * Template to show related photos
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

<div class="related">
    <h2><?php echo translate("related photos") ?></h2>
    <?php foreach($tpl_related as $related): ?>
        <div class="thumbnail">
            <?php if($tpl_admin): ?>
                <ul class="actionlink">
                    <li>
                      <a href="relation.php?photo_id_1=<?php 
                        echo (int) $tpl_photo->getId() ?>&photo_id_2=<?php 
                        echo (int) $related->getId() ?>">
                        <?php echo translate("edit") ?>
                      </a>
                    </li>
                 </ul>
            <?php endif; ?>
            <?php echo $related->getThumbnailLink() ?>
            <?php echo $tpl_photo->getRelationDesc($related) ?>
        </div>
    <?php endforeach; ?>
</div>
