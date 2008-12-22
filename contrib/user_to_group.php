<?php
/*
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

/* This script can be used to migrate from per-user authentication (Zoph
 * 0.7.3 and before) to per-group authentication (Zoph 0.7.4 and later).
 * This script creates a group for each user and migrates the user rights
 * from the user to the group.
 *
 * Make a backup of your database before using this script!
 * Copy the script to Zoph's directory on your webserver and call it from
 * your webbrowser (you must be logged on as admin user before, and did
 * I mention making a backup?) After the script has run, you can remove the
 * zoph_album_permissions from your database, as it is no longer used.
 */
    require_once("include.inc.php");


    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    echo "<h1>Migrating user permissions to group permissions</h1>\n";

    echo "<h2>Getting list of users...</h2>\n";
    $users=get_users();
    echo "<h2>Creating a group for each user...</h2>\n";
    echo "<ul>\n";
    foreach($users as $u) {
        $user_name=$u->get("user_name");
        echo "<li>" . $user_name . "</li>\n";
        $user_id=$u->get("user_id");
        $group=new group();
        $group->set("group_name", $user_name);
        $group->insert();
        $group_id=$group->get("group_id");
        $user_group[$user_id]=$group_id;
        $group->add_member($user_id);
    }
    echo "</ul>\n";
    echo "<h2>Migrating user permissions to group permissions...</h2>\n";
    echo "<ul>\n";
    foreach($users as $u) {
        $user_name=$u->get("user_name");
        echo "<li>" . $user_name . "</li>\n";
        $user_id=$u->get("user_id");
        $sql="SELECT * from " . DB_PREFIX . "album_permissions " .
            "WHERE user_id=" . escape_string($user_id);

        $result=mysql_query($sql);
        while($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
            unset($row["user_id"]);
            $row["group_id"]=$user_group[$user_id];
            $gp=new group_permissions();
            $gp->set_fields($row);
            $gp->insert();
        }
    }
?>
