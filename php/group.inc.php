<?php

/*
 * A class representing a usergroup of Zoph.
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

use db\select;
use db\insert;
use db\delete;
use db\param;
use db\clause;

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

    function updateMembers($vars = null) {
        parent::update();
        if ($vars["_member"]) {
            $this->add_member($vars["_member"]);
        }

        if ($vars["_remove_user"]) {
            $this->remove_members($vars["_remove_user"]);
        }

    }

    function delete() {
        parent::delete(array("groups_users", "group_permissions"));
    }

    function get_group_permissions($album_id) {
        $gp = new group_permissions($this->get("group_id"), $album_id);
        if ($gp->lookup()) {
            return $gp;
        }

        return null;
    }

    function get_albums() {
        $qry=new select(array("gp" => "group_permissions"));
        $qry->addFields(array("album_id"));
        $qry->where(new clause("group_id=:groupid"));
        $qry->addParam(new param(":groupid", (int) $this->getId(), PDO::PARAM_INT));
        return album::getRecordsFromQuery($qry);
    }

    function getDisplayArray() {
        $da = array(
            translate("group") => $this->get("group_name"),
            translate("description") => $this->get("description"),
            translate("members") => $this->get_members_links("<br>"));

        return $da;
    }

    function get_members() {
        $qry=new select(array("gu" => "groups_users"));
        $qry->addFields(array("user_id"));
        $qry->where(new clause("group_id=:groupid"));
        $qry->addParam(new param(":groupid", (int) $this->getId(), PDO::PARAM_INT));

        return user::getRecordsFromQuery($qry);
    }

    function add_member($member_id) {
        if (!is_numeric($member_id)) {
            die("member_id must be numeric");
        }
        $qry=new insert(array("gu" => "groups_users"));
        $qry->addParams(array(
            new param(":group_id", (int) $this->getId(), PDO::PARAM_INT),
            new param(":user_id", (int) $member_id, PDO::PARAM_INT)
        ));

        $qry->execute();

    }

    function remove_members($userIds) {
        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }
        $ids=new param(":userid", $userIds, PDO::PARAM_INT);

        $qry=new delete(array("gu" => "groups_users"));

        $where=new clause("group_id=:groupid");
        $where->addAnd(clause::InClause("user_id", $ids));

        $qry->addParams(array(
            new param(":groupid", (int) $this->getId(), PDO::PARAM_INT),
            $ids
        ));

        $qry->where($where);

        $qry->execute();
    }

    function get_non_members() {
        $users=user::getAll();
        $members=$this->get_members();

        foreach ($users as $u) {
            $user_ids[]=$u->get("user_id");
        }
        if ($members) {
            foreach ($members as $m) {
                $member_ids[]=$m->get("user_id");
            }
            $non_member_ids=array_diff($user_ids, $member_ids);
        } else {
            $non_member_ids=$user_ids;
        }

        $non_members=array();

        foreach ($non_member_ids as $n) {
            $non_members[]=new user($n);
        }
        return $non_members;

    }

    function get_new_member_pulldown($name) {
        $new_members=$this->get_non_members();
        $value_array[0]=null;
        foreach ($new_members as $nm) {
            $nm->lookup();
            $value_array[$nm->get("user_id")]=$nm->get("user_name");
        }
        return template::createPulldown($name, null, $value_array);
    }

    function get_members_links($separator="&nbsp;") {
        $html="";
        $members=$this->get_members();
        if ($members) {
            foreach ($members as $member) {
                $member->lookup();
                $html.=$member->getLink() . $separator;
            }
        }
        return $html;
    }
}

function getGroups($order = "group_name") {
    return group::getRecords($order);
}

?>
