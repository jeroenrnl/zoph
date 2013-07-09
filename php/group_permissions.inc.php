<?php

/*
 * This class corresponds to the group_permissions table which maps a group_id
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

class group_permissions extends zophTable {
    /** @var string The name of the database table */
    protected static $table_name="group_permissions";
    /** @var array List of primary keys */
    protected static $primary_keys=array("group_id", "album_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array();
    /** @var bool keep keys with insert. In most cases the keys are set by 
                  the db with auto_increment */
    protected static $keepKeys = true;
    /** @var string URL for this class */
    protected static $url="group.php?group_id=";


    function __construct($gid = -1, $aid = -1) {
        if($gid && !is_numeric($gid)) { die("group_id must be numeric"); }
        if($aid && !is_numeric($aid)) { die("album_id must be numeric"); }
        $this->set("group_id", $gid);
        $this->set("album_id", $aid);
    }

    public function getId() {
        return array(
            "group_id" => (int) $this->get("group_id"),
            "album_id" => (int) $this->get("album_id")
            );
    }

    function insert() {
        // check if this entry already exists
        if ($this->lookup()) {
            return;
        }

        // insert records for anc/uiestor albums if they don't exist
        $album = new album($this->get("album_id"));
        $album->lookup();

        if ($album->get("parent_album_id") > 0) {
            $gp = new group_permissions(
                $this->get("group_id"), $album->get("parent_album_id"));

            $gp->set("access_level", $this->get("access_level"));
            $gp->set("watermark_level", $this->get("watermark_level"));
            $gp->set("writable", $this->get("writable"));

            $gp->insert();
        }

        parent::insert();
    }

    function delete() {

        // delete records for descendant albums if they exist
        $album = new album($this->get("album_id"));
        $album->lookup();

        $children = $album->getChildren();
        foreach ($children as $child) {
            $gp = new group_permissions(
                $this->get("group_id"), $child->get("album_id"));

            if ($gp->lookup()) {
                $gp->delete();
            }
        }

        parent::delete();
    }

}

?>
