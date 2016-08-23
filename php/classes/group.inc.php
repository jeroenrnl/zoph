<?php
/**
 * A class representing a group of users.
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
 *
 * @author Jeroen Roos
 * @package Zoph
 */

use db\select;
use db\insert;
use db\delete;
use db\param;
use db\clause;

/**
 * A class representing a group of users
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class group extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="groups";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("group_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("group_name");
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="group.php?group_id=";

    /**
     * Delete a group
     * Also remove all users from this group and remove any permissions set for this group
     */
    public function delete() {
        parent::delete(array("groups_users", "group_permissions"));
    }

    public function getName() {
        return $this->get("group_name");
    }

    /**
     * Get permissions for a group
     * @param album Album to lookup permissions for
     * @return group_permissions Permissions object
     */
    public function getGroupPermissions(album $album) {
        $gp = new group_permissions($this->getId(), $album->getId());
        if ($gp->lookup()) {
            return $gp;
        }

        return null;
    }

    /**
     * Get albums associated with this permissions object
     * @return array of albums
     */
    public function getAlbums() {
        $qry=new select(array("gp" => "group_permissions"));
        $qry->addFields(array("album_id"));
        $qry->where(new clause("group_id=:groupid"));
        $qry->addParam(new param(":groupid", (int) $this->getId(), PDO::PARAM_INT));
        return album::getRecordsFromQuery($qry);
    }

    /**
     * Get display array
     * Get an array of properties to display
     * @return array properties
     */
    public function getDisplayArray() {
        return array(
            translate("group") => $this->get("group_name"),
            translate("description") => $this->get("description"),
            translate("members") => implode("<br>", $this->getMemberLinks())
        );
    }

    /**
     * Create an array describing permissions for all albums
     * for display or edit
     */
    public function getPermissionArray() {
        $albums = album::getSelectArray();
        $perms=array();
        foreach ($albums as $id => $name) {
            if (!$id || $id == 1) {
                continue;
            }
            $permissions = $this->getGroupPermissions(new album((int) $id));
            if ($permissions) {
                $albumPermissions=new stdClass();
                $albumPermissions->id=$id;
                $albumPermissions->name=$name;
                $albumPermissions->access=$permissions->get("access_level");
                if (conf::get("watermark.enable")) {
                    $albumPermissions->wm=$permissions->get("watermark_level");
                }
                $albumPermissions->writable=$permissions->get("writable");
                $perms[]=$albumPermissions;
            }
        }
        return $perms;
    }
    /**
     * Get members of this group
     * @return array of users
     */
    public function getMembers() {
        $qry=new select(array("gu" => "groups_users"));
        $qry->addFields(array("user_id"));
        $qry->where(new clause("group_id=:groupid"));
        $qry->addParam(new param(":groupid", (int) $this->getId(), PDO::PARAM_INT));

        $members=user::getRecordsFromQuery($qry);
        $return=array();
        foreach ($members as $member) {
            $member->lookup();
            $return[]=$member;
        }
        return $return;
    }

    /**
     * Add a member to a group
     * @param user User to add
     */
    public function addMember(user $user) {
        $qry=new insert(array("gu" => "groups_users"));
        $qry->addParams(array(
            new param(":group_id", (int) $this->getId(), PDO::PARAM_INT),
            new param(":user_id", (int) $user->getId(), PDO::PARAM_INT)
        ));

        $qry->execute();

    }

    /**
     * Remove a member from a group
     * @param user User to remove
     */
    public function removeMember(user $user) {

        $qry=new delete(array("gu" => "groups_users"));

        $where=new clause("group_id=:groupid");
        $where->addAnd(new clause("user_id=:userid"));

        $qry->addParams(array(
            new param(":groupid", (int) $this->getId(), PDO::PARAM_INT),
            new param(":userid", $user->getId(), PDO::PARAM_INT)
        ));

        $qry->where($where);

        $qry->execute();
    }

    /**
     * Get an array of users that are NOT a member of this group
     * @return array of users
     */
    private function getNonMembers() {
        $userIds=array();
        $memberIds=array();

        $users=user::getAll();
        $members=$this->getMembers();

        foreach ($users as $user) {
            $userIds[]=$user->getId();
        }
        if ($members) {
            foreach ($members as $member) {
                $memberIds[]=$member->getId();
            }
            $nonMemberIds=array_diff($userIds, $memberIds);
        } else {
            $nonMemberIds=$userIds;
        }

        $nonMembers=array();

        foreach ($nonMemberIds as $id) {
            $nonMembers[]=new user($id);
        }
        return $nonMembers;

    }

    /**
     * Create a pulldown to add new members to this group
     * @param string name for the pulldown field
     * @return template Pulldown
     */
    public function getNewMemberPulldown($name) {
        $valueArray=array();

        $newMembers=$this->getNonMembers();
        $valueArray[0]=null;
        foreach ($newMembers as $nm) {
            $nm->lookup();
            $valueArray[$nm->getId()]=$nm->getName();
        }
        return template::createPulldown($name, null, $valueArray);
    }

    /**
     * Get links to all members of this group
     * @return array array of links
     */
    public function getMemberLinks() {
        $links=array();
        $members=$this->getMembers();
        if ($members) {
            foreach ($members as $member) {
                $member->lookup();
                $links[]=$member->getLink();
            }
        }
        return $links;
    }
}

?>
