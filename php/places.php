<?php
/**
 * Display places
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
 */

require_once "include.inc.php";

$_view=getvar("_view");
if(empty($_view)) {
    $_view=$user->prefs->get("view");
}
$_autothumb=getvar("_autothumb");
if(empty($_autothumb)) {
    $_autothumb=$user->prefs->get("autothumb");
}

if (!$user->isAdmin() && !$user->get("browse_places")) {
    redirect("zoph.php");
}

$parent_place_id = getvar("parent_place_id");
if (!$parent_place_id) {
    $place = place::getRoot();
} else {
    $place = new place($parent_place_id);
}
$place->lookup();
$obj=&$place;
$ancestors = $place->get_ancestors();
$order = $user->prefs->get("child_sortorder");
$children = $place->getChildren($order);
$totalPhotoCount = $place->getTotalPhotoCount();
$photoCount = $place->getPhotoCount();

$title = $place->get("parent_place_id") ? $place->get("title") : translate("Places");

require_once "header.inc.php";
?>
<h1>

<?php
if ($user->isAdmin()) {
    $new="<a href=\"place.php?_action=new&amp;parent_place_id=" . $place->get("place_id") . "\">" .
      translate("new") . "</a> |";
}
if ($user->isAdmin() || $user->get("browse_tracks")) {
    ?>
    <span class="actionlink">
        <?php echo $new; ?>
        <a href="tracks.php"><?php echo translate("tracks") ?></a>
    </span>
    <?php
}
?>
<?php echo translate("places") . "\n" ?>
</h1>
<?php
if ($user->isAdmin()) {
    include "selection.inc.php";
}
$page_html="";
include "show_page.inc.php";
if ($show_orig) {
    ?>
    <div class="main">
      <form class="viewsettings" method="get" action="places.php">
    <?php
    echo template::createPulldown("parent_place_id", 0, place::getSelectArray($user), true);
    echo create_form($request_vars, array ("_view", "_autothumb", "_button"));
    echo translate("Category view", 0) . "\n";
    echo template::createViewPulldown("_view", $_view, true);
    echo translate("Automatic thumbnail", 0) . "\n";
    echo template::createAutothumbPulldown("_autothumb", $_autothumb, true);
    ?>
      </form>
      <br>
    <?php
    if ($user->isAdmin()) {
        ?>
        <span class="actionlink">
            <a href="place.php?_action=edit&amp;place_id=<?php echo $place->get("place_id") ?>">
                <?php echo translate("edit") ?>
            </a> |
            <a href="place.php?_action=delete&amp;place_id=<?php echo $place->get("place_id") ?>">
                <?php echo translate("delete") ?>
            </a>
        <?php
        if($place->get("coverphoto")) {
            ?>
                |
                <a href="place.php?_action=update&amp;place_id=<?php echo $place->get("place_id") ?>&amp;coverphoto=NULL">
                  <?php echo translate("unset coverphoto") ?>
                </a>
            <?php
        }
        ?>
        </span>

        <h2>
        <?php
        if ($ancestors) {
            while ($parent = array_pop($ancestors)) {
                ?>
                <a href="<?php echo $parent->getURL() ?>"><?php echo $parent->getName() ?></a> &gt;
                <?php
            }
        }
        ?>
        </h2>
        <p>
        <?php
    }
    echo $place->displayCoverphoto();
    ?>
        </p>
    <?php
    if ($user->get("detailed_places") || $user->isAdmin()) {
        echo $place->toHTML();
        if ($place->get("notes")) {
            echo "<p>";
            echo e($place->get("notes"));
            echo "</p>";
        }
    } else {
        echo "<h2>" . $title . "</h2>\n";
    }
    if ($place->get("place_description")) {
        echo $place->get("place_description");
    }
    if ($place->get("timezone")) {
        printf("<p><b>%s:</b> %s</p>", translate("timezone"), $place->get("timezone"));
    }
    ?>
    <br><br>
    <?php
    $fragment = translate("in this place");
    if($totalPhotoCount > 0) {
        if ($totalPhotoCount > $photoCount && $children) {
            ?>
            <span class="actionlink">
              <a href="photos.php?location_id=<?php echo $place->getBranchIds() ?>">
                <?php echo translate("view photos") ?>
              </a>
            </span>
            <?php
            $fragment .= " " . translate("or its children");
            if ($totalPhotoCount > 1) {
                echo sprintf(translate("There are %s photos"), $totalPhotoCount);
                echo " $fragment.<br>\n";
            } else {
                echo sprintf(translate("There is %s photo"), $totalPhotoCount);
                echo " $fragment.<br>\n";
            }
        }
        $fragment = translate("in this place");
        if (!$place->get("parent_place_id")) { // root place
            $fragment = translate("available");
        }
        if ($photoCount > 0) {
            ?>
            <span class="actionlink">
              <a href="photos.php?location_id=<?php echo $place->get("place_id") ?>">
                <?php echo translate("view photos")?>
              </a>
            </span>
            <?php
            if ($photoCount > 1) {
                echo sprintf(translate("There are %s photos"), $photoCount);
                echo " $fragment.<br>\n";
            } else {
                echo sprintf(translate("There is %s photo"), $photoCount);
                echo " $fragment.<br>\n";
            }
        }
    } else {
        echo translate("There are no photos");
        echo $fragment . ".<br>\n";
    }
    if ($children) {
        $tpl=new template("view_" . $_view, array(
            "id"        => $_view . "view",
            "items"     => $children,
            "autothumb" => $_autothumb,
        "topnode"   => true,
        "links"     => array(
            translate("view photos") => "photos.php?location_id="
        )));
        echo $tpl;
    }
    ?>
    </div>
    <?php
    if(conf::get("maps.provider")) {
        $map=new map();
        $map->setCenterAndZoomFromObj($place);
        $marker=$place->getMarker();
        if($marker instanceof marker) {
            $map->addMarker($marker);
        }
        $map->addMarkers($children);
        echo $map;
    }
} // if show_orig
echo $page_html;
require_once "footer.inc.php";
?>
