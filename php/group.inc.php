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
class group extends zophTable {

    function group($id = 0) {
        if($id && !is_numeric($id)) { die("user_id must be numeric"); }
        parent::__construct("groups", array("group_id"), array("group_name"));
        $this->set("group_id", $id);
    }

    function update($vars = null) {
        parent::update();
        if($vars["_member"]) {
            $this->add_member($vars["_member"]);
        }

        if($vars["_remove_user"]) {
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
        $sql="SELECT album_id FROM " .
            DB_PREFIX . "group_permissions " .
            "WHERE group_id=" . escape_string($this->get("group_id"));
        return album::getRecordsFromQuery("album", $sql);
    }

    function getDisplayArray() {
        $members=$this->get_members();

        $da = array(
            translate("group") => $this->get("group_name"),
            translate("description") => $this->get("description"),
            translate("members") => $this->get_members_links("<br>"));

        return $da;
    }

    function get_members() {
        $sql="SELECT user_id FROM " .
            DB_PREFIX . "groups_users " .
            "WHERE group_id=" . escape_string($this->get("group_id"));

        return user::getRecordsFromQuery("user", $sql);
    }

    function add_member($member_id) {
        if(!is_numeric($member_id)) { die("member_id must be numeric"); }

        $sql="INSERT INTO " . DB_PREFIX . "groups_users " .
            "VALUES (" . escape_string($this->get("group_id")) . "," .
            escape_string($member_id) . ", null)";
 
        query($sql, "Failed to add member:");
    }

    function remove_members($user_ids) {
        if(!is_array($user_ids)) {
            $user_ids = array($user_ids);
        }

        foreach($user_ids as $user_id) {
            $sql =
                "DELETE FROM " . DB_PREFIX . "groups_users " .
                "WHERE group_id = '" . escape_string($this->get("group_id")) . "'" .
                " and user_id = '" . escape_string($user_id) . "'";
            query($sql);
        }
    }

    function get_non_members() {
        $users=get_users();
        $members=$this->get_members();
        
        foreach($users as $u) {
            $user_ids[]=$u->get("user_id");
        }
        if($members) {
            foreach($members as $m) {
                $member_ids[]=$m->get("user_id");
            }
            $non_member_ids=array_diff($user_ids, $member_ids);
        } else {
            $non_member_ids=$user_ids;
        }

        $non_members=array();

        foreach($non_member_ids as $n) {
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
        return create_pulldown($name, null, $value_array);
    }

    function get_members_links($separator="&nbsp;") {
        $members=$this->get_members();
        if($members) {
            foreach ($members as $member) {
                $member->lookup();
                $html.=$member->getLink() . $separator;
            }
        }
        return $html;
    }
}

function get_groups($order = "group_name") {
    return group::getRecords("group", $order);
}

?>
