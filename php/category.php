<?php
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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TITLE_BG_COLOR?>">
<?php
    if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete category") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
        <tr>
          <td>
       <?php echo sprintf(translate("Confirm deletion of '%s' and its subcategories:") , $category->get("category")) ?>
          </td>
          <td align="right">[
            <a href="category.php?_action=confirm&category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="category.php?_action=edit&category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("cancel") ?></a>
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
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("category") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">[
            <a href="categories.php?parent_category_id=<?php echo $category->get("category_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a> |
            <a href="category.php?_action=delete&category_id=<?php echo $category->get("category_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
<form action="category.php">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="category_id" value="<?php echo $category->get("category_id") ?>">
<?php echo create_field_html($category->get_edit_array()) ?>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="<?php echo translate($action, 0) ?>">
    </td>
  </tr>
</form>
      </table>
    </td>
  </tr>
<?php
    }
?>
</table>

</div>
<?php
    require_once("footer.inc.php");
?>
