<?php
/**
 * Define and modify categories
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
require_once "include.inc.php";

if (!$user->canEditOrganizers()) {
    redirect("zoph.php");
}

$category_id = getvar("category_id");

$category = new category($category_id);

$obj = &$category;
$redirect = "categories.php";

if ($_action=="update" && getvar("sortorder")=="") {
    // overiding the default action, to be able to clear the sortorder
    $obj->setFields($request_vars);
    $obj->set("sortorder", "");
    $obj->update();
    $action = "display";
} else {
    require_once "actions.inc.php";
}

if ($action == "display") {
    redirect("categories.php?parent_category_id=" . $category->get("category_id"), "Redirect");
}

if ($action != "insert") {
    $category->lookup();
    $title = $category->get("category");
} else {
    $title = translate("New Category");
}

require_once "header.inc.php";
?>
<h1>
<?php
if ($action == "confirm") {
    ?>
      <ul class="actionlink">
        <li><a href="category.php?_action=confirm&amp;category_id=<?php
            echo $category->getId() ?>"><?php echo translate("delete") ?></a></li>
        <li><a href="category.php?_action=edit&amp;category_id=<?php
            echo $category->get("category_id") ?>"><?php echo translate("cancel") ?></a></li>
      </ul>
      <?php echo translate("delete category") ?>
    </h1>
    <div class="main">
      <?php echo sprintf(translate("Confirm deletion of '%s' and its subcategories:") ,
        $category->get("category")) ?>
    <?php
} else {
    ?>
    <ul class="actionlink">
      <li><a href="categories.php?parent_category_id=<?php echo $category->getId() ?>">
        <?php echo translate("return") ?>
      </a></li>
      <li><a href="category.php?_action=delete&amp;category_id=<?php
        echo $category->get("category_id") ?>">
        <?php echo translate("delete") ?>
      </a></li>
    </ul>
    <?php echo translate("category") ?>
    </h1>
    <div class="main">
      <form action="category.php">
        <input type="hidden" name="_action" value="<?php echo $action ?>">
        <input type="hidden" name="category_id" value="<?php echo $category->getId() ?>">
        <?php echo create_edit_fields($category->getEditArray()) ?>
        <input type="submit" value="<?php echo translate($action, 0) ?>">
      </form>
    <?php
}
?>
</div>

<?php
require_once "footer.inc.php";
?>
