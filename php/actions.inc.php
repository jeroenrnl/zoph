<?php
    /*
     * Since the same sort of actions (inserting/updating/deleting) are done
     * on many pages I have extracted some of the common code here.  The $obj
     * variable just needs to be set to the object to act on before this file
     * is included.
     */
    if ($_action == "edit") {
        $action = "update";
    }
    else if ($_action == "update") {
        $obj->set_fields($request_vars);
        $obj->update();
        $action = "display";
    }
    else if ($_action == "new") {
        $obj->set_fields($request_vars);
        $action = "insert";
    }
    else if ($_action == "insert") {
        $obj->set_fields($request_vars);
        $obj->insert();
        $action = "display";
    }
    else if ($_action == "delete") {
        $action = "confirm";
    }
    else if ($_action == "confirm") {
        $obj->delete();
        $_action = "new";
        $action = "insert"; // in case redirect doesn't work

        $user->eat_crumb();
        $link = strip_href($user->get_last_crumb());
        if (!$link) { $link = $redirect; }
        header("Location: " . add_sid($link));
    }
    else {
        $action = "display";
    }
?>
