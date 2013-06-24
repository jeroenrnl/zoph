<?php
/**
 * Template for the admin page.
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
require_once "header.inc.php";
?>
    <h1>
        <?php echo $tpl_title; ?>
    </h1>
    <div class="main">
        <ul class="admin">
            <?php foreach($tpl_adminpages as $adminpage): ?>
            <li>
                <a href="<?php echo $adminpage->url ?>">
                    <img src="<?php echo $adminpage->icon; ?>">
                    <br>
                    <?php echo translate($adminpage->name); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php require_once "footer.inc.php"; ?>
