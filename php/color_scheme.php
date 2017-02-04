<?php
/**
 * Display and define color schemes
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
    $_action = "display";
}

$color_scheme_id = getvar("color_scheme_id");

$colorScheme = new colorScheme($color_scheme_id);

if ($_action == "copy") {
    $title = translate("Copy Color Scheme");
    $colorScheme->lookup();
    $name = "copy of " . $colorScheme->get("name");
    $color_scheme_id = 0;
    $_action = "new";
    $copy=1;
}
$obj = &$colorScheme;
$redirect = "color_schemes.php";
require_once "actions.inc.php";

if ($_action == "update") {
    $user->prefs->load();
}

if ($action != "insert") {
    $colorScheme->lookup();
    $title = $colorScheme->get("name");
} else {
    $title = translate("New Color Scheme");
}

require_once "header.inc.php";
?>
<?php
if ($action == "display") {
    ?>
    <h1>
    <?php
    if ($user->isAdmin()) {
        ?>
        <ul class="actionlink">
          <li><a href="color_scheme.php?_action=edit&amp;color_scheme_id=<?php
              echo $colorScheme->getId() ?>"><?php echo translate("edit") ?>
          </a></li>
          <li><a href="color_scheme.php?_action=delete&amp;color_scheme_id=<?php
              echo $colorScheme->getId() ?>"><?php echo translate("delete") ?>
          </a></li>
          <li><a href="color_scheme.php?_action=new"><?php echo translate("new") ?></a></li>
        </ul>
        <?php
    }
    ?>
    <?php echo translate("color scheme") ?>
    </h1>
    <div class="main">
      <h2><?php echo $colorScheme->get("name") ?></h2>
        <dl class="display colorScheme">
    <?php
    $colors = $colorScheme->getDisplayArray();

    while (list($name, $value) = each($colors)) {
        if ($name == "Name") { continue; }
        ?>
        <dt><?php echo $name ?></dt>
        <dd>
          <div class="colordef"><?php echo $value ?></div>
          <div class="color" style="background: #<?php echo $value ?>;">&nbsp;</div>
        </dd>
        <?php
    }
    ?>
      </dl>
    <?php
} else if ($action == "confirm") {
    ?>
      <h1><?php echo translate("delete color scheme") ?></h1>
      <div class="main">
        <ul class="actionlink">
          <li><a href="color_scheme.php?_action=confirm&amp;color_scheme_id=<?php
            echo $colorScheme->getId() ?>">
            <?php echo translate("delete") ?>
          </a></li>
          <li><a href="color_schemes.php"><?php echo translate("cancel") ?></a></li>
        </ul>
        <?php echo sprintf(translate("Confirm deletion of '%s'"), $colorScheme->get("name")) ?>:
        <br>
    <?php
} else {
    $colors = $colorScheme->getColors();
    ?>
    <h1>
      <ul class="actionlink">
        <li><a href="color_schemes.php"><?php echo translate("return") ?></a></li>
      </ul>
      <?php echo translate("color scheme") ?>
    </h1>
    <div class="main">
      <form action="color_scheme.php">
       <input type="hidden" name="_action" value="<?php echo $action ?>">
       <input type="hidden" name="color_scheme_id" value="<?php
         echo $colorScheme->get("color_scheme_id") ?>">
       <label for="name">Name</label>
       <div class="colordef">
    <?php
    if (isset($copy)) {
        echo create_text_input("name", "copy of " . $colorScheme->get("name"), 16, 64);
    } else {
        echo create_text_input("name", $colorScheme->get("name"), 16, 64);
    }
    ?>
        </div>
        <br>
    <?php
    foreach ($colors as $id => $value) {
        $name=ucfirst(str_replace("_", " ", $id));
        ?>
        <label for="<?php echo $id ?>"><?php echo $name ?></label>
        <div class="colordef"><input type="color" name="<?= $id ?>" value="#<?= $value?>"></div>
        <div class="color" style="background: #<?php echo $value ?>">&nbsp;
        </div><br>
        <?php
    }
    ?>
    <input type="submit" value="<?php echo translate($action, 0) ?>">
    <?php
}
?>
<?php echo ( $action == "" ||
    $action == "display" ||
    $action == "delete" ||
    $action == "confirm" ) ? "" : "</form>"; ?>
  <br>
</div>

<?php require_once "footer.inc.php"; ?>
