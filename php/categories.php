<?php
    require_once("include.inc.php");

    $parent_category_id = getvar("parent_category_id");

    if (!$parent_category_id) {
        $category = get_root_category();
    }
    else {
        $category = new category($parent_category_id);
    }
    $category->lookup();
    $ancestors = $category->get_ancestors();
    $children = $category->get_children();

    $photo_count = $category->get_total_photo_count($user);

    $title = $category->get("parent_category_id") ? $category->get("category") : translate("Categories");

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("categories") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
<?php
    if ($user->is_admin()) {
?>
            [
            <a href="category.php?_action=new&parent_category_id=<?php echo $category->get("category_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
            ]
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <th align="left">
<?php
    if ($ancestors) {
        while ($parent = array_pop($ancestors)) {
?>
            <?php echo $parent->get_link() ?> &gt;
<?php
        }
    }
?>
            <?php echo $title ?>
          </th>
          <td align="right">
<?php
    if ($user->is_admin()) {
?>
          [
            <a href="category.php?_action=edit&category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("edit") ?></a>
          ]
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
        </tr>
<?php
    if ($category->get("category_description")) {
?>
        <tr>
          <td colspan="2">
            <?php echo $category->get("category_description") ?>
          </td>
        </tr>
<?php
    }
?>
        <tr>
          <td>
<?php
    $fragment = translate("in this category");
    if ($photo_count > 0) {
        if (!$category->get("parent_category_id")) {
            $fragment = translate("that have been categorized");
        }
        else {
            if ($children) {
                $fragment .= " " . translate("or its children");
            }
        }

        if ($photo_count > 1) {
            echo sprintf(translate("There are %s photos"), $photo_count);
            echo " $fragment.";
        }
        else {
            echo sprintf(translate("There is %s photo"), $photo_count);
            echo " $fragment.";
        }
?>
          </td>
          <td align="right">
            [ <a href="photos.php?category_id=<?php echo $category->get_branch_ids($user) ?>"><?php echo translate("view photos") ?></a> ]
          </td>
<?php
    }
    else {
?>
          <?php echo translate("There are no photos") ?> <?php echo $fragment ?>.
          </td>
          <td align="right">&nbsp;</td>
<?php
    }
?>
        </tr>
<?php
    if ($children) {
?>
        <tr>
          <td colspan="2">
<?php
        foreach($children as $c) {
?>
            <li>
            <a href="categories.php?parent_category_id=<?php echo $c->get("category_id") ?>"><?php echo $c->get("category") ?></a>
            </li>
<?php
        }
?>
          </td>
        </tr>
<?php
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>
<?php
    require_once("footer.inc.php");
?>
