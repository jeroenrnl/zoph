<?php

/*
 * A class corresponding to the color_shemes table.
 */
class color_scheme extends zoph_table {

    function color_scheme($id = 0) {
        parent::zoph_table("color_schemes", array("color_scheme_id"));
        $this->set("color_scheme_id", $id);
    }

}

?>
