<?php

/*
 * This class corresponds to the album_permissions table which maps a user_id
 * to a ablum_id + access_level + writable flag.  If the user is not an admin,
 * access to any photo must involve a join with this table to make sure the
 * user has access to an album that the photo is in.
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

class album_permissions extends zoph_table {

    function album_permissions($uid = -1, $aid = -1) {
        parent::zoph_table("album_permissions", array("user_id", "album_id"), array(""));
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
            $ap->set("watermark_level", $this->get("watermark_level"));
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
