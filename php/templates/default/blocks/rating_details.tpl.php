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

<div class="ratingdetails collapsed">
    <div class="toggle" onclick="thumbview.toggle(this.parentNode)"></div>
    <?php echo $tpl_rating ?>
    <div class="ratingdetail">
        <table class="ratingdetail">
            <tr>
                <th><?php echo translate("user"); ?></th>
                <th><?php echo translate("rating"); ?> </th>
                <th><?php echo translate("IP address"); ?></th>
                <th><?php echo translate("date"); ?></th></tr>
            </tr>
            <?php foreach($tpl_ratings as $rating): ?>
                <tr>
                    <td>
                        <a href="<?php echo $rating->getUser()->getURL() ?>">
                            <?php echo $rating->getUser()->getName() ?>
                        </a>
                    </td>
                    <td><?php echo $rating->get("rating") ?></td>
                    <td><?php echo $rating->get("ipaddress") ?></td>
                    <td><?php echo $rating->get("timestamp") ?></td>
                    <td>
                      <ul class="actionlink">
                        <li>
                          <a href="photo.php?_action=delrate&photo_id=<?php
                            echo $tpl_photo_id; ?>&_rating_id=<?php
                            echo $rating->get("rating_id"); ?>">
                            <?php echo translate("delete") ?>
                          </a>
                        </li>
                      </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
    </div>
</div>


