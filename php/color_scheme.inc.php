<?php

/*
 * A class corresponding to the color_shemes table.
 */
class color_scheme extends zoph_table {

    function color_scheme($id = 0) {
        $this->table_name = "color_schemes";
        $this->primary_keys = array("color_scheme_id");

        $this->fields["color_scheme_id"] = $id;
    }

}

?>
