<?php

/*
 * This class corresponds to the album_permissions table which maps a user_id
 * to a ablum_id + access_level + writable flag.  If the user is not an admin,
 * access to any photo must involve a join with this table to make sure the
 * user has access to an album that the photo is in.
 */
class album_permissions extends zoph_table {

    function album_permissions($uid = -1, $aid = -1) {
        parent::zoph_table("album_permissions", array("user_id", "album_id"));
        $this->set("user_id", $uid);
        $this->set("album_id", $aid);
    }

    function insert() {
        // check if this entry already exists
        if ($this->lookup()) {
            return;
        }

        // insert records for ancestor albums if they don't exist
        $album = new album($this->get("album_id"));
        $album->lookup();

        if ($album->get("parent_album_id") > 0) {
            $ap = new album_permissions(
                $this->get("user_id"), $album->get("parent_album_id"));

            $ap->set("access_level", $this->get("access_level"));
            $ap->set("writable", $this->get("writable"));

            $ap->insert();
        }

        parent::insert(1);
    }

    function delete() {

        // delete records for descendant albums if they exist
        $album = new album($this->get("album_id"));
        $album->lookup();

        $children = $album->get_children();
        foreach ($children as $child) {
            $ap = new album_permissions(
                $this->get("user_id"), $child->get("album_id"));

            if ($ap->lookup()) {
                $ap->delete();
            }
        }

        parent::delete();
    }

}

?>
