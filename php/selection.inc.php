<?php
if($_SESSION["selected_photo"]) {
?>
<div id="selection">
<?php printf(translate("%s photo(s) selected"), count($_SESSION["selected_photo"]))?><br>

<?php
foreach ($_SESSION["selected_photo"] as $selected_photo_id) {
    $selected_photo=new photo($selected_photo_id);

    $selected_photo->lookup();
    unset($selection_actionlinks);
    $return="_return=photo.php&amp;_qs=" . $encoded_qs;
    if ($photo && $selected_photo->get("photo_id")!=$photo->get("photo_id")) {
        $selection_actionlinks["relate"]="relation.php?_action=new&amp;" .
                   "photo_id_1=" . $selected_photo->get("photo_id") . "&amp;" .
                   "photo_id_2=" . $photo->get("photo_id") . "&amp;" .
                   $return;
    }
    if ($album) {
        $return="_return=albums.php&amp;_qs=parent_album_id=" .  $parent_album_id;
        $selection_actionlinks["coverphoto"]="album.php?_action=update&amp;" .
                    "album_id=" . $album->get("album_id") . "&amp;" .
                    "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                    $return;
    } else if ($category) {
        $return="_return=categories.php&amp;_qs=parent_category_id=" . 
            $parent_category_id;
        $selection_actionlinks["coverphoto"]="category.php?_action=update&amp;" .
                    "category_id=" . $category->get("category_id") . "&amp;" .
                    "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                    $return;
    } else if ($place) {
        $return="_return=places.php&amp;_qs=parent_place_id=" . 
            $parent_place_id;
        $selection_actionlinks["coverphoto"]="place.php?_action=update&amp;" .
                    "place_id=" . $place->get("place_id") . "&amp;" .
                    "coverphoto=" . $selected_photo->get("photo_id") . "&amp;" .
                    $return;
    } else if ($person) {
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
        echo $selected_photo->get_image_tag("thumb");
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
