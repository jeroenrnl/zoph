<?php
/*
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
 */
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $category_id = getvar("category_id");

    $category = new category($category_id);

    $obj = &$category;
    $redirect = "categories.php";
    require_once("actions.inc.php");

    if ($action == "display") {
        header("Location: " . add_sid("categories.php?parent_category_id=" . $category->get("category_id")));
    }

    if ($action != "insert") {
        $category->lookup();
        $title = $category->get("category");
    }
    else {
        $title = translate("New Category");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
<?php
    if ($action == "confirm") {
?>
        <tr>
          <th><h1><?php echo translate("delete category") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
       <?php echo sprintf(translate("Confirm deletion of '%s' and its subcategories:") , $category->get("category")) ?>
          </td>
          <td class="actionlink">[
            <a href="category.php?_action=confirm&amp;category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="category.php?_action=edit&amp;category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
<?php
    }
    else {
?>
        <tr>
          <th><h1><?php echo translate("category") ?></h1></th>
          <td class="actionlink">[
            <a href="categories.php?parent_category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("return") ?></a> |
            <a href="category.php?_action=delete&amp;category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("delete") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="category.php">
      <table class="main">
<tr><td>
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="category_id" value="<?php echo $category->get("category_id") ?>">
</td></tr>
<?php echo create_field_html($category->get_edit_array()) ?>
  <tr>
    <td colspan="2" class="center">
      <input type="submit" value="<?php echo translate($action, 0) ?>">
    </td>
  </tr>
      </table>
</form>
    </td>
  </tr>
<?php
    }
?>
</table>

<?php
    require_once("footer.inc.php");
?>
