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

    $_view=getvar("_view");
    if(empty($_view)) {
        $_view=$user->prefs->get("view");
    }
    $_autothumb=getvar("_autothumb");
    if(empty($_autothumb)) {
        $_autothumb=$user->prefs->get("autothumb");
    }

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

    $photo_count = $category->get_photo_count($user);
    $total_photo_count = $category->get_total_photo_count($user);

    $title = $category->get("parent_category_id") ? $category->get("category") : translate("Categories");

    require_once("header.inc.php");
?>
    <h1>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink"><a href="category.php?_action=new&amp;parent_category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("new") ?></a></span>
          <?php 
	  }
     echo "\n" . translate("categories") . "\n" ?>
    </h1>
<?php
    if($user->is_admin()) {
        include("selection.inc.php");
    }
?>
    <div class="main">
<?php
    if (JAVASCRIPT) {
?>
        <form class="viewsettings" method="get" action="categories.php">
            <?php echo create_form($request_vars, array ("_view", "_autothumb",
"_button")) ?>
            <?php echo translate("Category view", 0) . "\n" ?>
            <?php echo create_view_pulldown("_view", $_view,"onChange='form.submit()'") ?>
            <?php echo translate("Automatic thumbnail", 0) . "\n" ?>
            <?php echo create_autothumb_pulldown("_autothumb", $_autothumb,"onChange='form.submit()'") ?>
        </form>
        <br>
<?php
    }
?>
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
            <?php echo $title . "\n" ?>
        </h2>
<?php
    if ($user->is_admin()) {
?>
        <span class="actionlink">
            <a href="category.php?_action=edit&amp;category_id=<?php echo $category->get("category_id") ?>"><?php echo translate("edit") ?></a>
        </span>
        <br>
        <p>
<?php
    }
    echo $category->get_coverphoto($user);
?>
        </p>
<?php
if ($category->get("category_description")) {
?>
        <div class="description">
            <?php echo $category->get("category_description") ?>
        </div>
<?php
    }
?>
<br>
<?php
    $fragment = translate("in this category");
    $sortorder = $category->get("sortorder");
    if ($sortorder) {
        $sort = "&amp;_order=" . $sortorder;
    }
    if ($total_photo_count > 0) {
        if ($total_photo_count > $photo_count && $children) {
?>
            <span class="actionlink">
                <a href="photos.php?category_id=<?php echo $category->get_branch_ids($user) . $sort ?>"><?php echo translate("view photos") ?></a>
            </span>
<?php
            if (!$category->get("parent_category_id")) {
                $fragment = translate("that have been categorized");
            } else {
                if ($children) {
                    $fragment .= " " . translate("or its children");
                }
            }

            if ($total_photo_count > 1) {
                echo sprintf(translate("There are %s photos"), $total_photo_count);
                echo " $fragment.<br>\n";
            } else {
                echo sprintf(translate("There is %s photo"), $total_photo_count);
                echo " $fragment.<br>\n";
            }
?>
<?php
        }
    }
    $fragment = translate("in this category");
    if ($photo_count > 0) {
?>
        <span class="actionlink">
            <a href="photos.php?category_id=<?php echo $category->get("category_id") . $sort ?>"><?php echo translate("view photos")?></a>
        </span>
<?php
        if ($photo_count > 1) {
            echo sprintf(translate("There are %s photos"), $photo_count);
            echo " $fragment.<br>\n";
        } else {
            echo sprintf(translate("There is %s photo"), $photo_count);
            echo " $fragment.<br>\n";
        }
    }

?>
<?php
    if ($children) {
?>
        <ul class="<?php echo $_view ?>">
<?php
            if($_view!="tree") {
                foreach($children as $c) {
                    $photo_count=$c->get_photo_count($user);
                    $total_photo_count=$c->get_total_photo_count($user);
                    if($photo_count==$total_photo_count) {
                        $count=" <span class=\"photocount\">(" . 
                            $photo_count . ")</span>";
                    } else {
                        $count=" <span class=\"photocount\">(" . 
                            $photo_count ."/" . $total_photo_count . ")</span>";
                    }   
?>
                    <li>
                        <a href="categories.php?parent_category_id=<?php echo $c->get("category_id") ?>">
<?php
                            if ($_view=="thumbs") {
?>
                                <p>
                                    <?php echo $c->get_coverphoto($user,$_autothumb); ?>
                                    &nbsp;
                                </p>
                                <div>
<?php
                            }
                            echo $c->get("category");
                            echo $count;
                            if ($_view=="thumbs") {
?>
                                </div>
<?php
                            }
?>
                        </a>
                    </li>
<?php
                }
            } else {
                echo $category->get_html_tree();
            }
?>
        </ul>
        <br>
<?php
    }
?>
    </div>
<?php
    require_once("footer.inc.php");
?>
