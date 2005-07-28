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
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("categories") ?></h1></th>
          <td class="actionlink">
<?php
    if ($user->is_admin()) {
?>
            [
            <a href="category.php?_action=new&parent_category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("new") ?></a>
            ]
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <th>
            <h2>
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
            </h2>
          </th>
          <td class="actionlink">
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
          <td class="description" colspan="2">
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
          <td class="actionlink">
            [ <a href="photos.php?category_id=<?php echo $category->get_branch_ids($user) ?>"><?php echo translate("view photos") ?></a> ]
          </td>
<?php
    }
    else {
?>
          <?php echo translate("There are no photos") ?> <?php echo $fragment ?>.
          </td>
          <td>&nbsp;</td>
<?php
    }
?>
        </tr>
<?php
    if ($children) {
?>
        <tr>
          <td colspan="2">
            <ul>
<?php
        foreach($children as $c) {
?>
            <li>
            <a href="categories.php?parent_category_id=<?php echo $c->get("category_id") ?>"><?php echo $c->get("category") ?></a>
            </li>
<?php
        }
?>
            </ul>
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
