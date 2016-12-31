<?php
/**
 * Display color schemes
 *
 * This file is part of Zoph.
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
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */

use template\colorScheme;

require_once "include.inc.php";

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

$title = translate("Color Schemes");
require_once "header.inc.php";
?>
      <h1>
      <ul class="actionlink">
        <li><a href="color_scheme.php?_action=new"><?php echo translate("new") ?></a></li>
      </ul>
      <?php echo translate("color schemes") ?>
      </h1>
  <div class="main">
<?php
$colorSchemes = colorScheme::getRecords("name");

if ($colorSchemes) {
    foreach ($colorSchemes as $cs) {
        ?>
        <ul class="actionlink">
          <li><a href="color_scheme.php?_action=delete&amp;color_scheme_id=<?php
              echo $cs->getId() ?>">
            <?php echo translate("delete") ?>
          </a></li>
          <li><a href="color_scheme.php?_action=edit&amp;color_scheme_id=<?php
              echo $cs->getId() ?>">
             <?php echo translate("edit") ?>
          </a></li>
          <li><a href="color_scheme.php?_action=copy&amp;color_scheme_id=<?php
              echo $cs->getId() ?>">
            <?php echo translate("copy") ?>
          </a></li>
        </ul>
        <?php echo $cs->get("name") ?>
        <br>
        <?php
    }
}
?>
</div>
<?php
require_once "footer.inc.php";
?>
