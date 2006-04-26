<?php
if($_SESSION["selected_photo"]) {
?>
<div id="selection">
<?php echo count($_SESSION["selected_photo"])?> image(s) selected<br>

<?php

foreach ($_SESSION["selected_photo"] as $selected_photo_id) {
    $selected_photo=new photo($selected_photo_id);

    $selected_photo->lookup();
    
    unset($selection_actionlinks);
    if ($selected_photo->get("photo_id")!=$photo->get("photo_id")) {
    $selection_actionlinks["relate"]="relation.php?_action=new&amp;" .
                   "photo_id_1=" . $selected_photo->get("photo_id") . "&amp;" .
                   "photo_id_2=" . $photo->get("photo_id") . "&amp;" .
                   "qs=" . $encoded_qs;
    }

    $selection_actionlinks["x"]="photo.php?_action=deselect&amp;photo_id=" . 
                    $selected_photo->get("photo_id") . 
                    "&amp;_qs=" . $encoded_qs;
                                                 

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
