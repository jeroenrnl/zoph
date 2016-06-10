<?php
/**
 * Define and modify groups
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
 * @author Jeroen Roos
 */

require_once "include.inc.php";

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

$title = translate("Groups");
require_once "header.inc.php";
?>
    <h1>
      <ul class="actionlink">
        <li><a href="group.php?_action=new"><?php echo translate("new") ?></a></li>
      </ul>
      <?php echo translate("groups") ?>
    </h1>
    <div class="main">
<?php
$groups = group::getRecords("group_name");

if ($groups) {
    echo "<dl class='groups'>";
    foreach ($groups as $group) {
        ?>
        <dt><?php echo $group->getName() ?></dt>
        <dd>
        <?php
        echo $group->get("description") . "<br>";
        echo implode("&nbsp;", $group->getMemberLinks());
        ?>
        </dd>
        <ul class="actionlink">
          <li><a href="group.php?group_id=<?php echo $group->getId() ?>">
            <?php echo translate("display") ?>
          </a></li>
        </ul>
        <br>
        <?php
    }
    echo "</dl>";
}
?>
</div>
<?php
require_once "footer.inc.php";
?>
