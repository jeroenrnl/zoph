<?php
/**
 * Template for album, category, people and places TREE view
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
<?php if(isset($tpl_topnode)): ?>
    <ul class="actionlink">
        <li>
            <a href='#' onclick="thumbview.expandall('<?php echo $tpl_id ?>')">
                <?php echo translate("expand all");?>
            </a>
        </li> 
        <li>
            <a href='#' onclick='thumbview.collapseall("<?php echo $tpl_id ?>")'>
                <?php echo translate("collapse all");?>
            </a>
        </li> 
    </ul>
<?php endif ?>

<ul class="tree" id="<?php echo $tpl_id ?>">
<?php foreach ($tpl_items as $item): ?>
    <?php $children=$item->getChildren($tpl_user); ?>
    <li class="collapsed">
        <?php if($children): ?>
            <div class="toggle" onclick="thumbview.toggle(this.parentNode)"></div>
        <?php endif; ?>
        <a href="<?php echo $item->getURL() ?>">
            <?php echo $item->getName() ?></a>
        <span class="photocount">
            <?php 
                $pc=$item->getPhotoCount($tpl_user);
                $tpc=$item->getTotalPhotoCount($tpl_user);
            ?>
            <?php if($pc==$tpc): ?>
                (<?php echo $pc; ?>)
            <?php else: ?>
                (<?php echo $pc; ?>/<?php echo $tpc; ?>)
            <?php endif; ?>
        </span>
        <?php if(isset($tpl_links)): ?>
            <ul class="actionlink">
                <?php foreach($tpl_links as $link => $url): ?>
                    <li><a href="<?php echo $url; ?><?php echo $item->getId(); ?>"><?php echo $link; ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if($children): ?>
        <?php 
            $tpl=new template("view_tree", array(
                "items" => $children,
                "user" => $tpl_user,
                "id" => "sub_" . $tpl_id,
                "links" => $tpl_links
            ));
            echo $tpl;
        ?>
    <?php endif; ?>
    </li>
<?php endforeach ?>
</ul>
