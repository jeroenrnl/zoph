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

$zophpage_id = getvar("page_id");
$zophpage = new page($zophpage_id);
if ($zophpage_id) {
    $zophpage->lookup();
    $title = $zophpage->get("title");
    if(empty($title)) {
        $title=translate("Page");
    }
} else if ($_action == "new") {
    $title = translate("Create new page");
} else if ($_action != "insert") {
    // no id given and action is not new or insert
    redirect(add_sid("zoph.php"), "No page id given!");
}
    
if(!$user->is_admin()) {
   $_action="display";
}

$obj = &$zophpage;

require_once("actions.inc.php");
require_once("header.inc.php");

if ($action == "confirm") {
?>
    <h1><?php echo translate("delete page") ?></h1>
        <div class="main">
            <span class="actionlink">
                <a href="page.php?_action=confirm&amp;page_id=<?php echo $zophpage->get("page_id") ?>"><?php echo translate("delete") ?></a> |
                 <a href="page.php?_action=edit&amp;page_id=<?php echo $zophpage->get("page_id") ?>"><?php echo translate("cancel") ?></a>
            </span>
            <?php echo translate("Confirm deletion of this page"); ?>
        </div>
<?php
} else if ($action == "display") {
?>
    <h1>
        <span class="actionlink">
            <a href="pages.php"><?php echo translate("return") ?></a> |
            <a href="page.php?_action=edit&amp;page_id=<?php echo $zophpage->get("page_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="page.php?_action=delete&amp;page_id=<?php echo $zophpage->get("page_id") ?>"><?php echo translate("delete") ?></a>
        </span>
<?php
        echo $title;
?>
    </h1>
        <div class="main">
            <br>
            <dl>
                <?php echo create_field_html($zophpage->get_display_array()) ?>
            </dl>
            <br>
<?php
    $zophpagesets=$zophpage->get_pagesets();

    if(!empty($zophpagesets)) {
?>
        <h2><?php echo translate("Pagesets")?></h2>
        <?php echo translate("This page is used in the following pagesets:") ?>
        <?php echo $zophpage->get_pagesets(); ?>
<?php
    }
?>
    </div>
<?php
    } else {
?>
    <h1>
        <?php echo $title ?>
    </h1>
    <div class="main">
        <br>
        <form method="post" action="page.php">
            <input type="hidden" name="_action" value="<?php echo $action ?>">
            <input type="hidden" name="page_id" value="<?php echo $zophpage->get("page_id") ?>">
            <label for="title"><?php echo translate("title") ?></label>
            <?php echo create_text_input("title", $zophpage->get("title")) ?><br>
            <label for="text"><?php echo translate("text") ?></label> 
            <textarea name="text" rows="20" cols="80"><?php echo $zophpage->get("text") ?></textarea><br>
            <input type="submit" value="<?php echo translate($action, 0) ?>">
            <h2><?php echo translate("smileys you can use"); ?></h2>
            <?php echo smiley::getOverview(); ?>
        </form>
    </div>

<?php
}
    require_once("footer.inc.php");
?>
