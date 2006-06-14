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

    $photo_id = getvar("photo_id");
    $_off = getvar("_off");

    /*
    Before deciding to include the Prev and Next links, it was as
    simple as this.  But now we go through get_photos().

    $photo = new photo($photo_id);
    */

    $_qs=getvar("_qs");
    if($user->prefs->get("auto_edit") && $_qs && $_action == "update") {
        header("Location: photo.php?" . html_entity_decode(urldecode($_qs)));
    }

    $qs = preg_replace('/_crumb=\d+&?/', '', $QUERY_STRING);
    $qs = preg_replace('/_action=\w+&?/', '', $qs);
    $encoded_qs = urlencode(htmlentities($_qs));
    if (empty($encoded_qs)) {
        $encoded_qs = urlencode(htmlentities($qs));
    }

    if ($photo_id) { // would be passed for edit or delete
        $photo = new photo($photo_id);
    }
    else { // for display
        if (!$_off)  { $_off = 0; }
        $offset = $_off;

        $thumbnails;
        $num_photos = get_photos($request_vars, $offset, 1, $thumbnails, $user);

        $num_thumbnails = sizeof($thumbnails);

        if  ($num_thumbnails) {
            $photo = $thumbnails[0];
            $photo_id = $photo->get("photo_id");

            if ($offset > 0) {
                $newoffset = $offset - 1;
                $prev_link = "<a href=\"$PHP_SELF?" . htmlentities(str_replace("_off=$offset", "_off=$newoffset", $qs)) . "\">" . translate("Prev") . "</a>";
            }

            if ($offset + 1 < $num_photos) {
                $newoffset = $offset + 1;
                $next_link = "<a href=\"$PHP_SELF?" . htmlentities(str_replace("_off=$offset", "_off=$newoffset", $qs)) . "\">" . translate("Next") . "</a>";
            }
        }
        else {
            $photo = new photo();
        }
    }

    if (isset($offset)) {
        $ignore = array("_off", "_action");

        # To fix bug #1259152:
        # get $_off, round it down to a multiple of cols x rows.

        $_cols = getvar("_cols");
        $_rows = getvar("_rows");
        $_off = getvar("_off");

        if (!$_cols) { $_cols = $DEFAULT_COLS; }
        if (!$_rows) { $_rows = $DEFAULT_ROWS; }
        if (!$_off)  { $_off = 0; }

        $cells = $_cols * $_rows;

        $up_qs = update_query_string($request_vars, null, null, $ignore);
        
        if ($cells) {
            $_off = $cells * floor($_off / ($cells)); 
            $up_qs .= "&amp;_off=" . $_off;
        }
        
        $up_link = "<a href=\"photos.php?$up_qs\">" . translate("Up", 0) . "</a>";
    }
    
    $return_qs=$_qs;
    if(empty($return_qs)) { 
        if ($user->prefs->get("auto_edit")) {
            $return_qs=htmlentities(urldecode($qs));
        } else {
            $return_qs = "_action=display&amp;photo_id=" . $photo->get("photo_id");
        }
    }

    if (!$user->is_admin()) {
        if ($_action == "new" || $_action == "insert" ||
            $_action == "delete" || $_action == "confirm") {
            // only an admin can do these
            $_action = "display"; // in case redirect fails
            header("Location: " . add_sid("zoph.php"));
        }

        $permissions = $user->get_permissions_for_photo($photo_id);
        if (!$permissions) {
            $photo = new photo(-1); // in case redirect fails
            header("Location: " . add_sid("zoph.php"));
        }
        else if ($permissions->get("writable") == 0) {
            $_action = "display";
        }
    }


    if (EMAIL_PHOTOS) {
        $actionlinks["email"]="mail.php?_action=compose&amp;photo_id=" . $photo->get("photo_id");
    }

    if ($user->is_admin() || $permissions->get("writable")) {
        $actionlinks["edit"]="photo.php?_action=edit&amp;photo_id=" . $photo->get("photo_id") . "&amp;_qs=" . $encoded_qs;
    }
    if ($user->get("lightbox_id")) {
        $actionlinks["lightbox"]="photo.php?_action=lightbox&amp;" . $qs;
    }
    if ((ALLOW_COMMENTS) && ($user->is_admin() || $user->get("leave_comments"))) {
        $actionlinks["add comment"]="comment.php?_action=new&amp;photo_id=" . $photo->get("photo_id");
    }

    if (!$user->prefs->get("auto_edit") && $_action=="edit" ) {
        $actionlinks["return"]="photo.php?" .  $return_qs;
    }
    
    if ($user->is_admin() && $_action!="delete") {
        $actionlinks["select"]="photo.php?_action=select&amp;" . $qs;
        $actionlinks["delete"]="photo.php?_action=delete&amp;photo_id=" . $photo->get("photo_id") . "&amp;_qs=" . $encoded_qs;
    } else if ($_action=="delete") {    
        unset($actionlinks);
        $actionlinks["delete"]="photo.php?_action=confirm&amp;photo_id=" . $photo->get("photo_id") . "&amp;_qs=" . $encoded_qs;
        $actionlinks["cancel"]="photo.php?" . $_qs;
    }

    // jump to edit screen if auto edit pref is set
    // permission to edit checked below
    if ((!$_action || $_action == "search") && $user->prefs->get("auto_edit")) {
        $actionlinks["return"]="photo.php?_action=display&amp;" . $return_qs;
        $_action = "edit";
    }

    // 2005-04-10 --JCT
    //
    // moved from below so they are allowed
    // prior to $user->is_admin() check
    //
    if ($_action == "lightbox") {
        $photo->add_to_album($user->get("lightbox_id"));
        $action = "display";
    }
    else if ($_action == "rate") {
        if (ALLOW_RATINGS) {
            $rating = getvar("rating");
            $photo->rate($user->get("user_id"), $rating);
        }
        $action = "display";
    }

    if ($_action == "edit") {
        $actionlinks["return"]="photo.php?_action=display&amp;" . $return_qs;
        unset($actionlinks["cancel"]);
        unset($actionlinks["edit"]);
        $action = "update";
    }
    else if ($_action == "update") {
        unset($actionlinks["cancel"]);
        unset($actionlinks["edit"]);

        $photo->set_fields($request_vars);
        $photo->update($request_vars); // pass again for add people, cats, etc
        $action = "update";
    }
    else if ($_action == "new") {
        unset($actionlinks);
        $actionlinks["cancel"]="photos.php?" . $_qs;
        $action = "insert";
    }
    else if ($_action == "insert") {
        $photo->set_fields($request_vars);
        $photo->insert();
        $action = "update";
        
        unset($actionlinks["email"]);
        unset($actionlinks["lightbox"]);
        unset($actionlinks["add comment"]);
        unset($actionlinks["select"]);
        unset($actionlinks["delete"]);
    }
    else if ($_action == "delete") {
        $action = "confirm";
    }
    else if ($_action == "confirm") {
        $photo->delete();
        //if (!$user->prefs->get("auto_edit")) {
            $user->eat_crumb();
        //}
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = "zoph.php"; }
        header("Location: " . add_sid($link));
    } else if ($_action == "select") {
        $sel_key=false;
        if(is_array($_SESSION["selected_photo"])) {
            $sel_key=array_search($photo_id, $_SESSION["selected_photo"]);
        }
        if($sel_key === false) {
            $_SESSION["selected_photo"][]=$photo->get("photo_id");
        }
        $action="display";
    } else if ($_action == "deselect") {
        $sel_key=array_search($photo_id, $_SESSION["selected_photo"]);

        if($sel_key !== false) {
            unset($_SESSION["selected_photo"][$sel_key]);
        }
        header("Location: photo.php?" . html_entity_decode(urldecode($_qs)));
    // 2005-04-10 --JCT
    //
    // lightbox and rate actions moved
    // to prior to $user->is_admin() check
    //
    } else {
        $action = "display";
    }

    if ($action != "insert") {
        $found = $photo->lookup($user);
        $title = $photo->get("name");

        $_deg = getvar("_deg");
        $_thumbnail = getvar("_thumbnail");
        if ($_deg && $_deg != 0) {
            if (ALLOW_ROTATIONS) {
                $photo->rotate($_deg);
            }
        } // thumbnails already recreated for rotations
        else if ($_thumbnail) {
            $photo->thumbnail();
        }
    }
    else {
        $title = translate("New Photo");
    }

require_once("header.inc.php");
    // no photo was found and this isn't a new record
    if ($action != "insert" && !$found) {
?>
          <h1>
          
          <?php echo translate("photo") ?>
          </h1>
          <div class="main">
           <?php echo translate("No photo was found.") ?>
          </div>
<?php
    }
    else if ($action == "display") {
        unset($actionlinks["cancel"]);
        unset($actionlinks["return"]);
        $title_bar = translate("photo");
        if ($num_photos) {
            $title_bar .= " " . ($offset + 1) . " of $num_photos";
        }
?>
          <h1>
<?php
    echo create_actionlinks($actionlinks);
?>
          <?php echo $title_bar ?>
          </h1>
<?php
    require_once("selection.inc.php");
?>
    <div class="main">

<?php
        if (ALLOW_ROTATIONS && ($user->is_admin() || $permissions->get("writable"))) {
?>
          <div id="rotate">
        <form action="<?php echo $PHP_SELF ?>" method="POST">
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">

<select name="_deg">
<option>90</option>
<option>180</option>
<option>270</option>
</select>
<input type="submit" name="_button" value="<?php echo translate("rotate", 0) ?>">
</form>
      </div>
<?php
        }
?>
                <div id="prev"><?php echo $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></div>
                <div id="photohdr">
<?php
        if ($up_link) {
?>
            [ <?php echo $up_link ?> ]<br>
<?php
        }
?>
          <?php echo $photo->get_fullsize_link($photo->get("name"),$FULLSIZE_NEW_WIN) ?> :
                  <?php echo $photo->get("width") ?> x <?php echo $photo->get("height") ?>,
            <?php echo $photo->get("size") ?> <?php echo translate("bytes") ?>
            </div>    
            <div id="next"><?php echo $next_link ? "[ $next_link ]" : "&nbsp;" ?></div>
            <?php echo $photo->get_fullsize_link($photo->get_midsize_img(),$FULLSIZE_NEW_WIN) ?>
<?php
        if (($user->is_admin() || $user->get("browse_people")) && $people_links = get_photo_person_links($photo)) {
?>
          <div id="personlink">
            <?php echo $people_links ?>
          </div>
<?php
        }
?>
<table id="photo">
<?php echo create_field_html($photo->get_display_array(), 2) ?>
<?php
        if (ALLOW_RATINGS || $photo->get("rating")) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("rating") ?></td>
          <td class="field">
<form action="<?php echo $PHP_SELF ?>" method="POST">
                  <?php echo $photo->get("rating") != 0 ? $photo->get("rating") . " / 10" : ""; ?>
<?php
            if (ALLOW_RATINGS) {
?>
<input type="hidden" name="_action" value="rate">
<input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
<input type="submit" name="_button" value="<?php echo translate("rate", 0) ?>">
<?php echo create_rating_pulldown($photo->get_rating($user->get("user_id"))); ?>
<?php
            }
?>
           </form>
          </td>
        </tr>
<?php
        }
        if ($album_links = create_link_list($photo->lookup_albums($user))) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("albums") ?></td>
          <td class="field"><?php echo $album_links ?></td>
        </tr>
<?php
        }

        if ($category_links = create_link_list($photo->lookup_categories())) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("categories") ?></td>
          <td class="field"><?php echo $category_links ?></td>
        </tr>
<?php
        }
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("last modified") ?></td>
          <td class="field"><?php echo format_timestamp($photo->get("timestamp")) ?></td>
        </tr>
        <tr>
          <td colspan="2" class="photodesc">
<?php
        if ($photo->get("description")) {
            echo $photo->get("description");
        }
?>
          </td>
        </tr>
<?php
        if ($user->prefs->get("camera_info")) {
            echo create_field_html($photo->get_camera_display_array(), 2);
            echo "</table>\n";
        }

        $related=$photo->get_related();

        if ($related) {
            $header="<h2>" . translate("related photos") . "</h2>";
            $i=0;
            foreach($related as $rel_photo) {
                $rel_photo->lookup();
                if ($user->get_permissions_for_photo($rel_photo->get("photo_id")) || $user->is_admin()) {
                    echo $header;   // Makes sure that header is only
                    unset($header); // displayed when there are photos
                    echo "<div class=\"thumbnail\">";
                    if($user->is_admin()) {
                        echo "<span class=\"actionlink\">";
                        echo "<a href=\"relation.php?photo_id_1=" .
                            $photo->get("photo_id") . "&amp;" . "photo_id_2=" .
                            $rel_photo->get("photo_id") . "\">edit</a>";
                        echo "</span>";
                    }
                    echo $rel_photo->get_thumbnail_link() . "<br>";
                    echo $photo->get_relation_desc($rel_photo->get("photo_id"));
                    echo "</div>";
                    $i++;
                    if($i>=5) { echo "<br>"; $i=0; }
               }
          }
          echo "<br>";
        }
        if (ALLOW_COMMENTS) {
            $comments=$photo->get_comments();

            if($comments) {
                echo "<h2>" . translate("comments") . "</h2>\n";
                foreach($comments as $comment) {
                    echo $comment->to_html($user) . "\n";
                }
            echo "<br>&nbsp;\n";
            }
        }
    }
    else if ($action == "confirm") {
?>
          <h1><?php echo translate("photo") ?></h1>
          <div class="main">
<?php
    echo create_actionlinks($actionlinks);
?>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $photo->get("name")) ?>

<?php
    }
    else {
require_once("edit_photo.inc.php");
    }
?>
</div>
<?php require_once("footer.inc.php"); ?>
