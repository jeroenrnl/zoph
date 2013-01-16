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
    
    $qs = preg_replace('/_crumb=\d+&?/', '', $_SERVER["QUERY_STRING"]);
    $qs = preg_replace('/_action=\w+&?/', '', $qs);
    $encoded_qs = urlencode(htmlentities($_qs));
    if (empty($encoded_qs)) {
        $encoded_qs = urlencode(htmlentities($qs));
    }
    /* if page is called via a HTTP POST, the $QUERY_STRING variable is empty
       so we need to fill $qs differently... */
    if (empty($qs)) {
        $qs=$_qs;
    }

    $prev_link="";
    $next_link="";
    $act="";
    $num_photos=0;
    if ($photo_id) { // would be passed for edit or delete
        $photo = new photo($photo_id);
    } else { // for display
        if (!$_off)  { $_off = 0; }
        $offset = $_off;

        $num_photos = get_photos($request_vars, $offset, 1, $thumbnails, $user);

        $num_thumbnails = sizeof($thumbnails);

        if  ($num_thumbnails) {
            $photo = $thumbnails[0];
            $photo_id = $photo->get("photo_id");
            if(isset($_action) && !$_action=="" ) {
                $act="_action=" . $_action . "&";
            }

            if ($offset > 0) {
                $newoffset = $offset - 1;
                $prev_link = "<a href=\"" . $_SERVER["PHP_SELF"] . "?" . $act . htmlentities(str_replace("_off=$offset", "_off=$newoffset", $qs)) . "\">" . translate("Prev") . "</a>";
            }

            if ($offset + 1 < $num_photos) {
                $newoffset = $offset + 1;
                $next_link = "<a href=\"" . $_SERVER["PHP_SELF"] . "?" . $act . htmlentities(str_replace("_off=$offset", "_off=$newoffset", $qs)) . "\">" . translate("Next") . "</a>";
            }
        }
        else {
            $photo = new photo();
        }
    }
    if(!$user->is_admin()) {
        $permissions = $user->get_permissions_for_photo($photo_id);
    }

    if (isset($offset)) {
        $ignore = array("_off", "_action");

        # To fix bug #1259152:
        # get $_off, round it down to a multiple of cols x rows.

        $_cols = (int) getvar("_cols");
        $_rows = (int) getvar("_rows");
        $_off = (int) getvar("_off");

        if (!$_cols) { $_cols = $user->prefs->get("num_rows"); }
        if (!$_rows) { $_rows = $user->prefs->get("num_cols"); }
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
        $return_qs=$qs;
    }

   if ($_action == "lightbox") {
        $photo->addTo(new album($user->get("lightbox_id")));
        $action = "display";
    } else if ($_action == "rate") {
        if (conf::get("feature.rating") && ($user->is_admin() || $user->get("allow_rating"))) {
            $rating = getvar("rating");
            $photo->rate($rating);
            $link = strip_href($user->get_last_crumb());
            if (!$link) { $link = "zoph.php"; }
            redirect(add_sid($link));
        }
        $action = "display";
    } else if ($_action == "delrate" && $user->is_admin()) {
        $rating_id=getvar("_rating_id");
        $rating=new rating( (int) $rating_id);
        $rating->delete();
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = "zoph.php"; }
        redirect(add_sid($link));
    }

    if ($user->is_admin() || 
            ($permissions instanceof photo_permissions && $permissions->get("writable"))) {
        $_deg = getvar("_deg");
        $_thumbnail = getvar("_thumbnail");
        if ($_deg && $_deg != 0) {
            if (conf::get("rotate.enable")) {
                $photo->lookup();
                try {
                    $photo->rotate($_deg);
                } catch (Exception $e) {
                    echo $e->getMessage();
                    die;
                }
            }
        } else if ($_thumbnail) {
            // thumbnails already recreated for rotations
            $photo->thumbnail();
        }
    }


    if (!$user->is_admin()) {
        if ($_action == "new" || $_action == "insert" ||
            $_action == "delete" || $_action == "confirm") {
            // only an admin can do these
            $_action = "display"; // in case redirect fails
            redirect(add_sid("zoph.php"));
        }

        if (!$permissions) {
            $photo = new photo(-1); // in case redirect fails
            redirect(add_sid("zoph.php"));
        }
        else if ($permissions->get("writable") == 0) {
            $_action = "display";
        }
    }


    if (conf::get("feature.mail")) {
        $actionlinks["email"]="mail.php?_action=compose&amp;photo_id=" . $photo->get("photo_id");
    }

    if ($user->is_admin() || $permissions->get("writable")) {
        $actionlinks["edit"]="photo.php?_action=edit&" . $qs;
    }
    if ($user->get("lightbox_id")) {
        $actionlinks["lightbox"]="photo.php?_action=lightbox&amp;" . $qs;
    }
    if (conf::get("feature.comments") && ($user->is_admin() || $user->get("leave_comments"))) {
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
   
    if ($_action == "edit") {
        $actionlinks["return"]="photo.php?_action=display&amp;" . $return_qs;
        unset($actionlinks["cancel"]);
        unset($actionlinks["edit"]);
        $action = "update";
    }
    else if ($_action == "update") {
        $actionlinks["return"]="photo.php?_action=display&amp;" . $return_qs;
        unset($actionlinks["cancel"]);
        unset($actionlinks["edit"]);

        $photo->setFields($request_vars);
        $photo->updateRelations($request_vars,"_id"); // pass again for add people, cats, etc
        $photo->update();
        $action = "update";
        if(!empty($_qs)) {
            redirect("photo.php?" . $_qs, "Update done");
        }
    }
    else if ($_action == "new") {
        unset($actionlinks);
        $actionlinks["cancel"]="photos.php?" . $_qs;
        $action = "insert";
    }
    else if ($_action == "insert") {
        $photo->setFields($request_vars);
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
        redirect(add_sid($link), "Go back");
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
        $return=getvar("_return");
        $sel_key=array_search($photo_id, $_SESSION["selected_photo"]);

        if($sel_key !== false) {
            unset($_SESSION["selected_photo"][$sel_key]);
        }
        redirect($return . "?" . html_entity_decode(urldecode($_qs)), "Redirect");
    // 2005-04-10 --JCT
    //
    // lightbox and rate actions moved
    // to prior to $user->is_admin() check
    //
    } else {
        $action = "display";
    }

    if ($action != "insert") {
        $found = $photo->lookup();
        $title = $photo->get("name");

    } else {
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
        if ($num_photos) {
            $title_bar = sprintf(translate("photo %s of %s"),  ($offset + 1) , $num_photos);
        } else {
            $title_bar = translate("photo");
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
        if (conf::get("rotate.enable") && ($user->is_admin() || $permissions->get("writable"))) {
?>
        <div id="rotate">
            <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                <p>
                    <input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
                    <select name="_deg">
                        <option>90</option>
                        <option>180</option>
                        <option>270</option>
                    </select>
                    <input type="submit" name="_button" value="<?php echo translate("rotate", 0) ?>">
                </p>
            </form>
        </div>
<?php
        }
?>
        <div class="prev"><?php echo $prev_link ? "[ $prev_link ]" : "&nbsp;" ?></div>
        <div class="photohdr">
<?php
        if (isset($up_link)) {
?>
            [ <?php echo $up_link ?> ]<br>
<?php
        }
?>
            <?php echo $photo->getFullsizeLink($photo->get("name")) ?> :
            <?php echo $photo->get("width") ?> x <?php echo $photo->get("height") ?>,
            <?php echo $photo->get("size") ?> <?php echo translate("bytes") ?>
        </div>    
        <div class="next"><?php echo $next_link ? "[ $next_link ]" : "&nbsp;" ?></div>
        <ul class="tabs">
<?php
        if(conf::get("share.enable") && ($user->is_admin() || $user->get("allow_share"))) {
            $hash=$photo->getHash();
            $full_hash=sha1(conf::get("share.salt.full") . $hash);
            $mid_hash=sha1(conf::get("share.salt.mid") . $hash);
            $full_link=getZophURL() . "image.php?hash=" . $full_hash;
            $mid_link=getZophURL() . "image.php?hash=" . $mid_hash;

            $tpl_share=new template("photo_share", array(
                "hash" => $hash,
                "full_link" => $full_link,
                "mid_link" => $mid_link
            ));
            echo $tpl_share;

        }
?>
        </ul>
            <?php echo $photo->getFullsizeLink($photo->getMidsizeImg()) ?>
<?php
        if (($user->is_admin() || $user->get("browse_people")) && $people_links = get_photo_person_links($photo)) {
?>
          <div id="personlink">
            <?php echo $people_links ?>
          </div>
<?php
        }
?>
<dl class="photo">
<?php echo create_field_html($photo->getDisplayArray()) ?>
<?php
        if ((conf::get("feature.rating")  && 
            ($user->is_admin() || $user->get("allow_rating"))) 
            || $photo->getRating()) {

            $rating = $photo->getRating();
?>
          <dt><?php echo translate("rating") ?></dt>
          <dd>
<?php
                if($rating) {
                    if($user->is_admin()) {
                        echo $photo->getRatingDetails();
                    } else {
                        echo $rating . "<br>";
                    }
                }
            if (conf::get("feature.rating") && ($user->is_admin() || $user->get("allow_rating"))) {
?>
<form id="ratingform" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
        <input type="hidden" name="_action" value="rate">
        <input type="hidden" name="photo_id" value="<?php echo $photo->get("photo_id") ?>">
        <?php echo create_rating_pulldown($photo->getRatingForUser($user)); ?>
        <input type="submit" name="_button" value="<?php echo translate("rate", 0) ?>">
</form>
        </dd>
<?php
            }
?>
<?php
        }
        if ($album_links = template::createLinkList($photo->getAlbums($user))) {
?>
<dt><?php echo translate("albums") ?></dt>
<dd><?php echo $album_links ?></dd>
<?php
        }

        if ($category_links = template::createLinkList($photo->getCategories())) {
?>
          <dt><?php echo translate("categories") ?></dt>
          <dd><?php echo $category_links ?></dd>
<?php
        }
?>
          <dt><?php echo translate("last modified") ?></dt>
          <dd><?php echo format_timestamp($photo->get("timestamp")) ?></dd>
<?php
        if ($photo->get("description")) {
        
            echo "<div class=\"photodesc\">" . $photo->get("description") . "</div><br>\n";
        }
?>
<?php
        if ($user->prefs->get("camera_info")) {
            echo create_field_html($photo->get_camera_display_array());
        }
?>
</dl><br>
<?php
        if ($user->prefs->get("allexif")) {
            $allexif=$photo->exif_to_html();

            if($allexif) {
?>
                <h2><?php echo translate("Full EXIF details",0)?></h2>
                <span class="actionlink">
                    <a href="#" onclick="document.getElementById('allexif').style.display='block'"><?php echo translate("display",0) ?></a> | 
                    <a href="#" onclick="document.getElementById('allexif').style.display='none'"><?php echo translate("hide",0) ?></a>
                </span>
<?php
                echo $allexif;
            }
        }
        $actionlinks=array();
        $related=$photo->getRelated();

        if ($related) {
            $tpl=new block("related_photos", array(
                "photo"     => $photo,
                "related"   => $related,
                "admin"     => (bool) $user->is_admin()
            ));
            echo $tpl;
        }
        if (conf::get("feature.comments")) {
            $comments=$photo->get_comments();

            if($comments) {
                echo "<h2>" . translate("comments") . "</h2>\n";
                foreach($comments as $comment) {
                    echo $comment->to_html($user) . "\n";
                }
            echo "<br>&nbsp;\n";
            }
        }
?>
        <br>
<?php
    }
    else if ($action == "confirm") {
?>
          <h1><?php echo translate("photo")?></h1>
          <div class="main">
<?php
        echo create_actionlinks($actionlinks);
        echo sprintf(translate("Confirm deletion of '%s'"), $photo->get("name"));
        echo $photo->getMidsizeImg();
    }
    else {
        require_once("edit_photo.inc.php");
    }
?>
</div>
<?php
      if(conf::get("maps.provider") && ($_action=="display" || $_action=="edit" || $_action==="")) {
        $map=new map();

        if($_action == "edit") {
            $map->setEditable();
            $map->setCenterAndZoomFromObj($photo);
            $map->addMarkers(array($photo), $user);
        } else {
            $photos=$photo->get_near(100);
            $photos[]=$photo;
            $map->addMarkers($photos, $user);
        }
        echo $map;
        
      }
?>

<?php require_once("footer.inc.php"); ?>
