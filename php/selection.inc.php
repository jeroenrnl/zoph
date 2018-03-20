<?php
/**
 * Display selection header on top of pages that can use a selected
 * photo, for example to set as cover photo
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
 */
$url=array_reverse(explode("/", $_SERVER["PHP_SELF"]));
if ($url[0]=="selection.inc.php") {
    redirect("zoph.php");
}
if (!empty($_SESSION["selected_photo"])) {
    ?>
    <div id="selection">
    <?php printf(translate("%s photo(s) selected"), count($_SESSION["selected_photo"]))?><br>

    <?php
    foreach ($_SESSION["selected_photo"] as $selected_photo_id) {
        $selected_photo=new photo($selected_photo_id);

        $selected_photo->lookup();
        unset($selection_actionlinks);
        $return="_return=photo.php";
        if (isset($encoded_qs)) {
            $return.="&amp;_qs=" . $encoded_qs;
        }
        if (isset($photo) && $selected_photo->get("photo_id")!=$photo->get("photo_id")) {
            $selection_actionlinks["relate"]="relation.php?_action=new&amp;" .
                "photo_id_1=" . $selected_photo->get("photo_id") . "&amp;" .
                "photo_id_2=" . $photo->get("photo_id") . "&amp;" .
                $return;
        }
        if (isset($album)) {
            $return="_return=albums.php&amp;_qs=parent_album_id=" .  $parent_album_id;
            $selection_actionlinks["coverphoto"]="album.php?_action=update&amp;" .
                "album_id=" . $album->get("album_id") . "&amp;" .
                "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                $return;
        } else if (isset($category)) {
            $return="_return=categories.php&amp;_qs=parent_category_id=" .
                $parent_category_id;
            $selection_actionlinks["coverphoto"]="category.php?_action=update&amp;" .
                "category_id=" . $category->get("category_id") . "&amp;" .
                "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                $return;
        } else if (isset($place)) {
            $return="_return=places.php&amp;_qs=parent_place_id=" .
                $parent_place_id;
            $selection_actionlinks["coverphoto"]="place.php?_action=update&amp;" .
                "place_id=" . $place->get("place_id") . "&amp;" .
                "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                $return;
        } else if (isset($person)) {
            $return="_return=person.php&amp;_qs=person_id=" .
                $person_id;
            $selection_actionlinks["coverphoto"]="person.php?_action=update&amp;" .
                "person_id=" . $person->get("person_id") . "&amp;" .
                "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                $return;
        }
        $selection_actionlinks["x"]="photo.php?_action=deselect&amp;photo_id=" .
                $selected_photo->get("photo_id") .
                "&amp;" . $return;


        ?>
        <div class="thumbnail">
        <?php
        echo create_actionlinks($selection_actionlinks);
        echo $selected_photo->getImageTag(THUMB_PREFIX);
        ?>
        </div>
        <?php
    }
    ?>
      <br>

    </div>
    <?php
}
?>
