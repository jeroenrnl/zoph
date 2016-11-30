<?php
/**
 * Show categories
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
 *
 */
use template\block;
use template\template;

require_once "include.inc.php";

$_view=getvar("_view");
if (empty($_view)) {
    $_view=$user->prefs->get("view");
}
$_autothumb=getvar("_autothumb");
if (empty($_autothumb)) {
    $_autothumb=$user->prefs->get("autothumb");
}

$parent_category_id = getvar("parent_category_id");
if (!$parent_category_id) {
    $category = category::getRoot();
} else {
    $category = new category($parent_category_id);
}
$category->lookup();
$obj=&$category;
$ancestors = $category->getAncestors();
$order = $user->prefs->get("child_sortorder");
$children = $category->getChildren($order);

$photoCount = $category->getPhotoCount();
$totalPhotoCount = $category->getTotalPhotoCount();

$title = $category->get("parent_category_id") ?
    $category->get("category") : translate("Categories");

$pagenum = getvar("_pageset_page");

require_once "header.inc.php";

try {
    $pageset=$category->getPageset();
    $page=$category->getPage($request_vars, $pagenum);
    $showOrig=$category->showOrig($pagenum);
} catch (pageException $e) {
    $showOrig=true;
    $page=null;
}

?>
<h1>
    <?php if ($user->canEditOrganizers()):  ?>
        <ul class="actionlink">
            <li><a href="category.php?_action=new&amp;parent_category_id=<?php
            echo $category->getId() ?>"><?php echo translate("new") ?>
            </a></li>
            <li><a href="category.php?_action=edit&amp;category_id=<?php
            echo $category->getId() ?>"><?php echo translate("edit") ?>
            </a></li>
            <?php
            if ($category->get("coverphoto")) {
                ?>
                <li><a href="category.php?_action=update&amp;category_id=<?php
                    echo $category->getId() ?>&amp;coverphoto=NULL"><?php
                    echo translate("unset coverphoto") ?>
                </a></li>
                <?php
            }
            ?>
        </ul>
    <?php endif ?>
    <?= $title ?>
</h1>
<?php
if ($user->isAdmin()) {
    include "selection.inc.php";
}
if ($category->showPageOnTop()) {
    echo $page;
}

if ($showOrig) {
    ?>
    <div class="main">
      <form class="viewsettings" method="get" action="categories.php">
        <?php echo create_form($request_vars, array ("_view", "_autothumb", "_button")) ?>
        <?php echo translate("Category view", 0) . "\n" ?>
        <?php echo template::createViewPulldown("_view", $_view, true) ?>
        <?php echo translate("Automatic thumbnail", 0) . "\n" ?>
        <?php echo template::createAutothumbPulldown("_autothumb", $_autothumb, true) ?>
    </form>
    <br>
    <h2>
    <?php
    if ($ancestors) {
        while ($parent = array_pop($ancestors)) {
            ?>
            <?php echo $parent->getLink() ?> &gt;
            <?php
        }
    }
    ?>
        <?php echo $title . "\n" ?>
    </h2>
    <p>
    <?= $category->displayCoverphoto(); ?>
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
    } else {
        $sort = "";
    }

    if ($totalPhotoCount > 0) {
        if ($totalPhotoCount > $photoCount && $children) {
            ?>
            <ul class="actionlink">
                <li><a href="photos.php?category_id=<?php echo $category->getBranchIds() .
                    $sort ?>"><?php echo translate("view photos") ?>
                </a></li>
            </ul>
            <?php
            if (!$category->get("parent_category_id")) {
                $fragment = translate("that have been categorized");
            } else {
                if ($children) {
                    $fragment .= " " . translate("or its children");
                }
            }

            if ($totalPhotoCount > 1) {
                echo sprintf(translate("There are %s photos"), $totalPhotoCount);
                echo " $fragment.<br>\n";
            } else {
                echo sprintf(translate("There is %s photo"), $totalPhotoCount);
                echo " $fragment.<br>\n";
            }
        }
    }
    $fragment = translate("in this category");
    if ($photoCount > 0) {
        ?>
        <ul class="actionlink">
            <li><a href="photos.php?category_id=<?php echo $category->getId() .
                $sort ?>"><?php echo translate("view photos")?>
            </a></li>
        </ul>
        <?php
        if ($photoCount > 1) {
            echo sprintf(translate("There are %s photos"), $photoCount);
            echo " $fragment.<br>\n";
        } else {
            echo sprintf(translate("There is %s photo"), $photoCount);
            echo " $fragment.<br>\n";
        }
    }

    if ($children) {
        $tpl=new block("view_" . $_view, array(
            "id" => $_view . "view",
            "items" => $children,
            "autothumb" => $_autothumb,
            "topnode" => true,
            "links" => array(
                translate("view photos") => "photos.php?category_id="
            )
        ));
        echo $tpl;
    }
    ?>
    </div>
    <?php
} // if show_orig
if ($category->showPageOnBottom()) {
    echo $page;
}
require_once "footer.inc.php";
?>
