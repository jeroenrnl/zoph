<?php
/**
 * Define or modify relations between photos
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

$photo_id_1=getvar("photo_id_1");
$photo_id_2=getvar("photo_id_2");

$desc_1=getvar("desc_1");
$desc_2=getvar("desc_2");

$photo_1=new photo($photo_id_1);
$photo_2=new photo($photo_id_2);

$photo_1->lookup();
$photo_2->lookup();

$relation=new photoRelation($photo_1, $photo_2);
$exists=$relation->lookup();

if (($_action == "insert" || $_action == "new") && $exists) {
    $_action="edit";
}

if ($_action != "insert" && $_action != "new" && $_action != "update") {
    $desc_1 = $relation->getDesc($photo_1);
    $desc_2 = $relation->getDesc($photo_2);
}

$obj = &$relation;
require_once "actions.inc.php";


if ($action=="display") {
    $title=translate("relationship");
} else {
    $title=translate($action . " relationship");
}

require_once "header.inc.php";

if ($action == "confirm") {
    ?>
    <h1><?php echo translate("delete relationship") ?></h1>
    <div class="main">
      <ul class="actionlink">
        <li><a href="relation.php?_action=confirm&amp;photo_id_1=<?php
            echo $photo_id_1 ?>&photo_id_2=<?php echo $photo_id_2 ?>">
          <?php echo translate("delete") ?>
        </a></li>
        <li><a href="relation.php?_action=edit&amp;photo_id_1=<?php
            echo $photo_id_1 ?>&photo_id_2=<?php echo $photo_id_2 ?>">
          <?php echo translate("cancel") ?>
        </a></li>
      </ul>
      <?php echo translate("Confirm deletion of this relationship") ?>
      <br>
      <div id="relation">
        <div class="thumbnail">
          <?php echo $photo_1->getImageTag(THUMB_PREFIX) ?><br>
          <?php echo $desc_1 ?>
        </div>
        <div class="thumbnail">
          <?php echo $photo_2->getImageTag(THUMB_PREFIX) ?>
          <?php echo $desc_2 ?>
        </div>
      </div>
      <br>
    </div>
  <?php
} else if ($action == "display") {
    ?>
      <h1>
        <ul class="actionlink">
          <li><a href="photo.php?photo_id=<?php echo $photo_id_1 ?>">
            <?php echo translate("return") ?>
          </a></li>
          <li><a href="relation.php?_action=edit&amp;photo_id_1=<?php
            echo $photo_id_1 ?>&amp;photo_id_2=<?php echo $photo_id_2 ?>">i
            <?php echo translate("edit") ?>
          </a></li>
          <li><a href="relation.php?_action=delete&amp;photo_id_1=<?php
            echo $photo_id_1 ?>&amp;photo_id_2=<?php echo $photo_id_2 ?>">
            <?php echo translate("delete") ?>
          </a></li>
        </ul>
        <?php echo $title; ?>
      </h1>
      <div class="main">
        <br>
        <div id="relation">
          <div class="thumbnail">
            <?php echo $photo_1->getImageTag(THUMB_PREFIX) ?><br>
            <?php echo $desc_1 ?>
          </div>
          <div class="thumbnail">
            <?php echo $photo_2->getImageTag(THUMB_PREFIX) ?><br>
            <?php echo $desc_2 ?>
          </div>
      </div>
      <br>

    </div>
    <?php
} else {
    ?>
    <h1>
      <?php echo $title ?>
    </h1>
    <div class="main">
      <br>
        <div id="relation">
          <div class="thumbnail">
            <?php echo $photo_1->getImageTag(THUMB_PREFIX) ?><br>
            <?php echo $photo_1->get("name"); ?>
          </div>
          <div class="thumbnail">
            <?php echo $photo_2->getImageTag(THUMB_PREFIX) ?>
            <?php echo $photo_2->get("name"); ?>
          </div>
        </div>
        <br>
        <form action="relation.php">
          <input type="hidden" name="_action" value="<?php echo $action ?>">
          <input type="hidden" name="photo_id_1" value="<?php echo $photo_id_1 ?>">
          <input type="hidden" name="photo_id_2" value="<?php echo $photo_id_2 ?>">
          <label for="desc_1"><?php echo translate("Description for first photo") ?></label>
          <?php echo create_text_input("desc_1", $desc_1) ?><br>
          <label for="desc_2"><?php echo translate("Description for second photo") ?></label>
          <?php echo create_text_input("desc_2", $desc_2) ?><br>
          <input type="submit" value="<?php echo translate($action, 0) ?>">
        </form>
      </div>
    <?php
}
require_once "footer.inc.php";
?>
