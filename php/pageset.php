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

    $pageset_id = getvar("pageset_id");
    $pageset = new pageset($pageset_id);
    if ($pageset_id) {
        $pageset->lookup();
    }
    if(!$user->is_admin()) {
       $_action="display";
    }
    
    if ($_action == "insert") {
        $pageset->set("user", $user->get("user_id"));
    } else if ($_action == "moveup") {
        $page_id = getvar("page_id");
        $pageset->moveup($page_id);
    } else if ($_action == "movedown") {
        $page_id = getvar("page_id");
        $pageset->movedown($page_id);
    } else if ($_action == "delpage") {
        $page_id = getvar("page_id");
        $pageset->remove_page($page_id);
        $action="display";
    } else if ($_action == "addpage") {
        $page_id = getvar("page_id");
        $pageset->addpage($page_id);
        $action="display";
    }
    $obj = &$pageset;

    require_once("actions.inc.php");

    if ($_action != "new") {
        $title = $pageset->get("title");
    } else {
        $title = translate("Create new pageset");
    }
    require_once("header.inc.php");
?>
<?php
if ($action == "confirm") {
?>
          <h1><?php echo translate("delete pageset") ?></h1>
            <div class="main">
               <span class="actionlink">
                 <a href="pageset.php?_action=confirm&amp;pageset_id=<?php echo $pageset->get("pageset_id") ?>"><?php echo translate("delete") ?></a> |
                 <a href="pageset.php?_action=edit&amp;pageset_id=<?php echo $pageset->get("pageset_id") ?>"><?php echo translate("cancel") ?></a>
               </span>
               <?php echo translate("Confirm deletion of this pageset"); ?>
             </div>
<?php
    }
    else if ($action == "display") {
?>
          <h1>
            <span class="actionlink">
              <a href="pageset.php?_action=edit&amp;pageset_id=<?php echo $pageset->get("pageset_id") ?>"><?php echo translate("edit") ?></a> |
              <a href="pageset.php?_action=delete&amp;pageset_id=<?php echo $pageset->get("pageset_id") ?>"><?php echo translate("delete") ?></a>
            </span>
<?php
     echo $title;
?>
          </h1>
      <div class="main">
<br>
<dl class=pageset>
<?php 
    $pageset->lookup();
    echo create_field_html($pageset->getDisplayArray());
?>
</dl>
<br>
<h2>
    <?php echo translate("Pages in this pageset"); ?>
</h2>
<?php echo get_page_table($pageset->get_pages(), $pageset->get("pageset_id")); ?>
<form action="pageset.php" class="addpage">
    <input type="hidden" name="_action" value="addpage">
    <input type="hidden" name="pageset_id" value="<?php echo $pageset->get("pageset_id") ?>">
    <label for="page_id">
        <?php echo translate("Add a page:") ?>
    </label>
    <?php echo create_pulldown("page_id", 0, get_pages_select_array(), "onChange='form.submit()'"); ?>
    <input type="submit" name="_button" value="<?php echo translate("add",0)?>">
</form>
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
        <form action="pageset.php">
            <input type="hidden" name="_action" value="<?php echo $action ?>">
            <input type="hidden" name="pageset_id" value="<?php echo $pageset->get("pageset_id") ?>">
            <label for="title"><?php echo translate("title") ?></label>
            <?php echo create_text_input("title", $pageset->get("title")) ?><br>
            <label for="show_orig"><?php echo translate("show original page") ?></label> 
            <?php echo create_pulldown("show_orig", $pageset->get("show_orig"), $pageset->get_original_select_array()) ?><br>
            <label for="orig_pos"><?php echo translate("position of original") ?></label> 
            <?php echo create_pulldown("orig_pos", $pageset->get("orig_pos"), array("top" => translate("Top",0), "bottom" => translate("Bottom",0)) ) ?><br>
            <input type="submit" value="<?php echo translate($action, 0) ?>">
        </form>
    </div>

<?php
}
    require_once("footer.inc.php");
?>
