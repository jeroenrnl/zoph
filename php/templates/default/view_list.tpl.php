<?php
/**
 * Template for album, category, people and places LIST view
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
<ul class="list">
<?php foreach ($tpl_items as $item): ?>
    <li>
        <a href="<?php echo $item->getURL() ?>"><?php echo $item->getName() ?></a>
        <span class="photocount">
            <?php
                $count=$item->getPhotoCount();
                if($item instanceof zophTreeTable): 
                    $count2=$item->getTotalPhotoCount();
                elseif ($item instanceof person):
                    $count2=$item->getPhotographer()->getPhotoCount();
                else:
                    $count2=0;
                endif;
            ?>
            <?php if($count==$count2): ?>
                (<?php echo $count; ?>)
            <?php else: ?>
                (<?php echo $count; ?>/<?php echo $count2; ?>)
            <?php endif; ?>
        </span>
        <?php if(isset($tpl_links)): ?>
            <ul class="actionlink">
                <?php foreach($tpl_links as $link => $url): ?>
                    <li>
                      <a href="<?php echo $url; ?><?php echo $item->getId(); ?>">
                        <?php echo $link; ?>
                      </a>
                   </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
     </li>
<?php endforeach ?>
</ul>
