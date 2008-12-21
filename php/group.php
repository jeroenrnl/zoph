<?php
/* This file is part of Zoph.
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

    $group_id = getvar("group_id");
    $album_id_new = getvar("album_id_new");

    $group = new group($group_id);

    if ($_action == "update_albums") {
        // Check if the "Grant access to all albums" checkbox is ticked
        $_access_level_all_checkbox = getvar("_access_level_all_checkbox");

        if($_access_level_all_checkbox) {
            $albums = get_albums();
            if ($albums) {
                foreach ($albums as $alb) {
                    $permissions = new group_permissions(
                        $group_id, $alb->get("album_id"));
                    $permissions->set_fields($request_vars,"","_all");
                    if(!WATERMARKING) {
                        $permissions->set("watermark_level", 0);
                    }
                    $permissions->insert();
                }
            }
        }

        $albums = $group->get_albums();
        foreach ($albums as $album) {
            $id=$album->get("album_id");
            $remove_permission_album = $request_vars["_remove_permission_album__$id"];
            // first check if album needs to be revoked
            if ($remove_permission_album) {
                $permissions = new group_permissions($group_id, $id);
                $permissions->delete();
            }
        }
        // Check if new album should be added
        if($album_id_new) {
            $permissions = new group_permissions();
            $permissions->set_fields($request_vars,"","_new");
            if(!WATERMARKING) {
                $permissions->set("watermark_level", 0);
            }
            $permissions->insert();
        }
        // update ablums
        $albums = $group->get_albums();

        foreach ($albums as $album) {
            $album->lookup();
            $name=$album->get("album");
            $id=$album->get("album_id");
            $permissions = new group_permissions();
            $permissions->set_fields($request_vars,"","__$id");
            $permissions->update();
        }

        $action = "update";
    } else if ($_action=="update") {
        $group->set_fields($request_vars);
        $group->update($request_vars);
        $action = "update";
    } else {
        $obj = &$group;
        $redirect = "groups.php";
        require_once("actions.inc.php");
    }

    // edit after insert to add album permissions
    if ($_action == "insert") {
        $action = "update";
    }

    if ($action != "insert") {
        $group->lookup();
        $title = $group->get("group_name");
    } else {
        $title = translate("New Group");
    }

    require_once("header.inc.php");
?>
<?php
    if ($action == "display") {
?>
		<h1>
			<span class="actionlink">
				<a href="group.php?_action=edit&amp;group_id=<?php echo $group->get("group_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="group.php?_action=delete&amp;group_id=<?php echo $group->get("group_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="group.php?_action=new"><?php echo translate("new") ?></a>
         </span>
          <?php echo translate("group") ?>
		</h1>
      <div class="main">
      	<h2><?php echo $group->get("group_name") ?></h2>
         	<dl>
            	<?php echo create_field_html($group->get_display_array()) ?>
            </dl>
            <br>
      	<h3><?php echo translate("Albums") ?></h3>
      	<table class="permissions">
        	<tr>
         	<th><?php echo translate("name") ?></th>
          	<th><?php echo translate("access level") ?></th>
<?php 
   	if (WATERMARKING) { 
?>
          	<th><?php echo translate("watermark level") ?></th>
<?php 
   	} 
?>
          	<th><?php echo translate("writable") ?></th>
			</tr>
<?php
			$albums = get_albums_select_array();
         foreach ($albums as $id=>$name) {
         	if (!$id || $id == 1) { continue; }
            	$permissions = $group->get_group_permissions($id);
               if($permissions) {
?>
        	<tr>
         	<td><?php echo $name ?></td>
          	<td><?php echo $permissions->get("access_level") ?></td>
<?php 
   	if (WATERMARKING) { 
?>
          	<td><?php echo $permissions->get("watermark_level") ?></td>
<?php 
   	} 
?>
          	<td><?php echo $permissions->get("writable") == "1" ? translate("Yes") : translate("No") ?></td>
			</tr>
<?php
	}
}
?>
		</table>
<?php
    } else if ($action == "confirm") {
?>
		<h1>
      	<span class="actionlink">
         	<a href="group.php?_action=display&amp;group_id=<?php echo $group->get("group_id") ?>"><?php echo translate("cancel") ?></a>
         </span>
         <?php echo translate("delete group") ?>
      </h1>
      <div class="main">
      	<span class="actionlink">
         	<a href="group.php?_action=confirm&amp;group_id=<?php echo $group->get("group_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="group.php?_action=display&amp;group_id=<?php echo $group->get("group_id") ?>"><?php echo translate("cancel") ?></a>
         </span>
         <?php echo sprintf(translate("Confirm deletion of '%s'"), $group->get("group_name")) ?>
<?php
   } else {
require_once("edit_group.inc.php");
?>
		<form action="group.php" method="post">
			<table class="permissions">
    			<col class="col1"><col class="col2"><col class="col3"><col class="col4">
    				<tr>
          			<th colspan="4"><h3><?php echo translate("Albums") ?></h3></th>
        			</tr>
<?php
		if ($action == "insert") {
?>
      			<tr>
          			<td colspan="4">
       					<?php echo translate("After this group is created it can be given access to albums.") ?>
          			</td>
        			</tr>
      	</table>
		</form>
<?php
      } else {
?>
        				<tr>
          				<td colspan="4">
<?php
			echo translate("Granting access to an album will also grant access to that album's ancestors if required. Granting access to all albums will not overwrite previously granted permissions.");
      	if (WATERMARKING) { 
     			echo "<br>\n" . translate("A photo will be watermarked if the photo level is higher than the watermark level.");
       	}
?>
							</td>
        				</tr>
        				<tr>
          				<th colspan="2"><?php echo translate("name") ?></th>
          				<th><?php echo translate("access level") ?></th>
<?php
   		if (WATERMARKING) { 
?>
          				<th><?php echo translate("watermark level") ?></th>
<?php 
   		}
?>
          				<th>writable</th>
        				</tr>
        				<tr>
      					<td>
      						<input type="checkbox" name="_access_level_all_checkbox" value="1">
      					</td>
          			<td>
							<input type="hidden" name="group_id" value="<?php echo $group->get("group_id") ?>">
							<input type="hidden" name="_action" value="update_albums">
							<?php echo translate("Grant access to all existing albums:") ?>
                	</td>
                	<td>
							<?php echo create_text_input("access_level_all", "5", 4, 2) ?>
                	</td>
<?php 
   		if (WATERMARKING) { 
?>
                	<td>
							<?php echo create_text_input("watermark_level_all", "5", 4, 2) ?>
                	</td>
<?php
    		}
?>
                	<td>
							<?php echo create_pulldown("writable_all", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
                	</td>
        			</tr>
        			<tr>
      				<td>
      				</td>
          			<td>
							<input type="hidden" name="group_id_new" value="<?php echo $group->get("group_id") ?>">
							<?php echo create_pulldown("album_id_new", "", get_albums_select_array()) ?>
                	</td>
                	<td>
							<?php echo create_text_input("access_level_new", "5", 4, 2) ?>
                	</td>
<?php 
    		if (WATERMARKING) { 
?>
         			<td>
							<?php echo create_text_input("watermark_level_new", "5", 4, 2) ?>
                	</td>
<?php 
    		} 
?>
						<td>
							<?php echo create_pulldown("writable_new", "0", array("0" => translate("No"), "1" => translate("Yes"))) ?>
                	</td>
        			</tr>
    				<tr>
    					<td colspan="4" class="permremove">
							<?php echo translate("remove") ?>
    					</td>
    				</tr>
<?php
			$albums = get_albums_select_array();
         foreach ($albums as $id=>$name) {
         	if (!$id || $id == 1) { continue; }
         		$permissions = $group->get_group_permissions($id);

               if($permissions) {
?>
        			<tr>
      				<td>
      					<input type="checkbox" name="_remove_permission_album__<?php echo $id ?>" value="1">
      				</td>
          			<td>
							<?php echo $name ?>
          			</td>
          			<td>
							<input type="hidden" name="album_id__<?php echo $id ?>" value="<?php echo $id ?>">
							<input type="hidden" name="group_id__<?php echo $id ?>" value="<?php echo $group_id ?>">
							<?php echo create_text_input("access_level__$id", $permissions->get("access_level"), 4, 2) ?>
          			</td>
<?php 
			      	if (WATERMARKING) { 
?>
						<td>
							<?php echo create_text_input("watermark_level__$id", $permissions->get("watermark_level"), 4, 2) ?>
          			</td>
<?php
        				}
?>
         			<td>
							<?php echo create_pulldown("writable__$id", $permissions->get("writable"), array("0" => translate("No",0), "1" => translate("Yes",0))) ?>
          			</td>
      			</tr>
<?php
					}
				} // while
?>
  				</table>
  				<input type="submit" value="<?php echo translate("update", 0) ?>">
			</form>
<?php
			} // not insert
		} // edit
?>
		</div>
<?php require_once("footer.inc.php"); ?>
