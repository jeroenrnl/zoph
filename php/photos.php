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

    
    $_cols = (int) getvar("_cols");
    $_rows = (int) getvar("_rows");
    $_off = (int) getvar("_off"); 
    
    $_order = getvar("_order");
    $_dir = getvar("_dir");
    $_show = getvar("_show");
    $vars=clean_request_vars($request_vars);
    if(!preg_match("/^[a-zA-Z_]*$/", $_order)) {
        die("Illegal characters in _order");
    }

    if (!$_cols) { $_cols = $user->prefs->get("num_rows"); }
    if (!$_rows) { $_rows = $user->prefs->get("num_cols"); }
    if (!$_off)  { $_off = 0; }

    if (!$_order) { $_order = conf::get("interface.sort.order"); }
    if (!$_dir)   { $_dir = conf::get("interface.sort.dir"); }
   
    $cells = $_cols * $_rows;
    $offset = $_off;

    // remove photo from lightbox
    $photo_id = getvar("_photo_id");
    if ($user->get("lightbox_id") && $photo_id) {
        $photo = new photo($photo_id);
        $photo->removeFrom(new album($user->get("lightbox_id")));
    }

    $album_id = getvar("album_id");
    if ($album_id && $user->get("lightbox_id") &&
        $album_id == $user->get("lightbox_id")) {

        $lightbox = true;
    }

    $thumbnails;
    $num_photos =
        get_photos($vars, $offset, $cells, $thumbnails, $user);

    $num_thumbnails = sizeof($thumbnails);

    if  ($num_thumbnails) {
        $num_pages = ceil($num_photos / $cells);
        $page_num = floor($offset / $cells) + 1;

        $num = min($cells, $num_thumbnails);

        $name = isset($lightbox) ? "Lightbox" : "Photos";

        $title = sprintf(translate("$name (Page %s/%s)", 0), $page_num, $num_pages);
        $title_bar = sprintf(translate("photos %s to %s of %s"), ($offset + 1), ($offset + $num), $num_photos);
    }
    else {
        $title = translate("No Photos Found");
        $title_bar = translate("photos");
    }

    if (!($num_thumbnails == 0 || $_cols <= 4)) {
        $width = ((THUMB_SIZE + 14) * $_cols) + 25;
        $default_width= conf::get("interface.width");
        if ($width > $default_width || strpos($default_width, "%")) {
            $extrastyle = "body	{ width: " . $width . "px; }\n"; 
        }
    }
    require_once("header.inc.php");
?>
    <h1>
        <span class="actionlink">
<?php
    $qs = preg_replace('/_crumb=\d+&?/', '', $_SERVER["QUERY_STRING"]);
    $qs_no_action=preg_replace('/_action=\w+&?/', '', $qs);

    $qs=htmlentities($qs);
    $qs_no_action=htmlentities($qs_no_action);

    if($qs_no_action) {
        $qs_no_action .= "&amp;";
    }

    if($_action=translate("search")) {
?>

        <a href="search.php?<?php echo $qs_no_action ?>_action=new"><?php echo translate("save search") ?></a> |
<?php
    }
    if ($user->is_admin()) {
?>
            <a href="edit_photos.php?<?php echo $qs ?>"><?php echo translate("edit") ?></a> |
            <a href="tracks.php?_action=geotag&<?php echo $qs_no_action ?>"><?php echo translate("geotag") ?></a> |
<?php
    }
?>
            <a href="slideshow.php?<?php echo $qs ?>"><?php echo translate("slideshow") ?></a> 
<?php
        if(conf::get("feature.download") && ($user->get("download") || $user->is_admin())) {
?>
            |
            <a href="download.php?<?php echo $qs ?>"><?php echo translate("download") ?></a>
<?php
        }
?>
        </span>
        <?php echo $title_bar . "\n" ?>
    </h1>
    <div class="main">
        <form class="viewsettings" action="photos.php" method="GET">
<?php
    if ($num_thumbnails <= 0) {
?>
            <?php echo translate("No photos were found matching your search criteria.") . "\n" ?>
        </form>
<?php
    } else {
        switch($_dir) {
        case "asc":
            $up = template::getImage("up1.gif");
            $down = template::getImage("down2.gif");
            break;
        case "desc":
            $up = template::getImage("up2.gif");
            $down = template::getImage("down1.gif");
            break;
        }
        
?>
            <div id="sortorder">
<?php echo create_form($vars, array ("_rows", "_cols", "_order", "_button")) ?>
                <?php echo translate("order by", 0) . "\n" ?>
                <?php echo create_photo_field_pulldown("_order", $_order) ?>
            </div>
            <div id="updown">
                <a href="photos.php?<?php echo update_query_string($vars, "_dir", "asc") ?>"><img class="up" alt="sort ascending" src="<?php echo $up ?>"></a>
                <a href="photos.php?<?php echo update_query_string($vars, "_dir", "desc") ?>"><img class="down" alt="sort descending" src="<?php echo $down ?>"></a>
            </div>
            <div id="rowscols">
<?php
                echo create_integer_pulldown("_rows", $_rows, 1, 10);
                echo translate("rows") . "\n";
                echo create_integer_pulldown("_cols", $_cols, 1, 10);
                echo translate("cols") . "\n";
?>
                <input type="submit" name="_button" value="<?php echo translate("go", 0) ?>">
	    </div>
        </form>
        <br>
<?php
        for ($i = 0; $i < $num; $i++) {

            if ($i > 0 && $i % $_cols == 0) {
                echo "<br>";
            }

            $ignore = array("_action", "_photo_id");
?>
                <div class="thumbnail">

<?php
            if (getvar("_random")) {
                echo $thumbnails[$i]->get_thumbnail_link("photo.php?photo_id=" . $thumbnails[$i]->get("photo_id")) . "\n";
            } else {
                echo $thumbnails[$i]->get_thumbnail_link("photo.php?" . update_query_string($vars, "_off", $offset + $i, $ignore)) . "\n"; 
            }

            if (!empty($lightbox)) {
?>
                <div class="actionlink"><a href="photos.php?<?php echo update_query_string($vars, "_photo_id", $thumbnails[$i]->get("photo_id"), $ignore) ?>">x</a></div>
<?php
            }

?>
            </div>
<?php
        }

//        $diff = $cells - $num_thumbnails;
//        if ($diff > 0) {
//            for ($i = $diff % $_cols; $i > 0; $i--) {
//                echo "                <td>&nbsp;</td>\n";
//            }
//        }
?>
       <br>
<?php
        echo pager($offset, $num_photos, $num_pages, $cells, $user->prefs->get("max_pager_size"), $vars, "_off");
    } // if photos
?>
       <br>

      </div>
<?php
      if(conf::get("maps.provider")) {
        $map=new map();
        foreach($thumbnails as $thumbnail) {
            $thumbnail->lookup();
            $marker=$thumbnail->getMarker();
            if($marker instanceof marker) {
                $map->addMarker($marker);
            }
        }
        echo $map;
    }
?>
<?php require_once("footer.inc.php"); ?>
