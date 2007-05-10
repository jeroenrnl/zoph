<?php
if($_SESSION["selected_photo"]) {
?>
<div id="selection">
<?php printf(translate("%s photo(s) selected"), count($_SESSION["selected_photo"]))?><br>

<?php
switch (array_pop(explode("/", $PHP_SELF))) {
    case "photo.php":
        $return="_return=photo.php&amp;_qs=" . $encoded_qs;
        break;
    case "albums.php":
        $return="_return=albums.php&amp;_qs=parent_album_id=" . $parent_album_id;
        break;
    default:
        // This should never happen, but just in case...
        $return="_return=zoph.php&amp;_qs=";
        break;
    }

foreach ($_SESSION["selected_photo"] as $selected_photo_id) {
    $selected_photo=new photo($selected_photo_id);

    $selected_photo->lookup();
    unset($selection_actionlinks);
    if ($photo && $selected_photo->get("photo_id")!=$photo->get("photo_id")) {
        $selection_actionlinks["relate"]="relation.php?_action=new&amp;" .
                   "photo_id_1=" . $selected_photo->get("photo_id") . "&amp;" .
                   "photo_id_2=" . $photo->get("photo_id") . "&amp;" .
                   $return;
    }
    if ($album) {
        $selection_actionlinks["coverphoto"]="album.php?_action=update&amp;" .
                    "album_id=" . $album->get("album_id") . "&amp;" .
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
