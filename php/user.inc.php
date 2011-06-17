<?php

/*
 * A class representing a user of Zoph.
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
class user extends zophTable {

    var $person;
    var $prefs;
    var $crumbs;
    var $lang; // holds translations

    public function __construct($id = 0) {
        if($id && !is_numeric($id)) { die("user_id must be numeric"); }
        parent::__construct("users", array("user_id"), array("user_name"));
        $this->set("user_id", $id);
    }

    function insert() {
        parent::insert();
        $this->prefs = new prefs($this->get("user_id"));
        $this->prefs->insert();
    }

    function delete() {
        parent::delete(null, array("prefs"));
    }

    function lookup_person() {
        $this->person = new person($this->get("person_id"));
        $this->person->lookup();
    }

    function lookup_prefs() {
        $this->prefs = new prefs($this->get("user_id"));
        $this->prefs->lookup();
    }

    function is_admin() {
        return $this->get("user_class") == 0;
    }

    function get_lastnotify() {
        return $this->get("lastnotify");
    }

    function getLink() {
        return "<a href='user.php?user_id=" . $this->get("user_id") . "'>" .
            $this->get("user_name") . "</a>";
    }

    function get_groups() {
        $sql="SELECT group_id FROM " .
            DB_PREFIX . "groups_users " .
            "WHERE user_id=" . escape_string($this->get("user_id"));

        return group::getRecordsFromQuery("group", $sql);
    }



    function get_album_permissions($album_id) {
        if(!is_numeric($album_id)) { die("album_id must be numeric"); }
        if(!$album_id) { return; }

        $groups=$this->get_groups();
        foreach($groups as $group) {
            $group_id_array[]=$group->get("group_id");
        }
        if($group_id_array) {
            $group_ids=implode(",", $group_id_array);
            $sql = "SELECT * FROM " .
                DB_PREFIX . "group_permissions WHERE " .
                "album_id=".escape_string($album_id) . " AND " .
                "group_id IN (" . escape_string($group_ids) . ") " .
                "ORDER BY access_level DESC, writable DESC, " . 
                "watermark_level DESC " .
                "LIMIT 0, 1";
            $aps=group_permissions::getRecordsFromQuery("group_permissions", $sql);
            if ($aps && sizeof($aps) >= 1) {
                return $aps[0];
            }
        }

        return null;
    }


    function get_permissions_for_photo($photo_id) {

        // do ordering to grab entry with most permissions
        $sql =
            "select gp.* from " .
            DB_PREFIX . "photos AS ph JOIN " .
            DB_PREFIX . "photo_albums AS pa ON " .
            "ph.photo_id = pa.photo_id JOIN " .
            DB_PREFIX . "group_permissions as gp ON " .
            "pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users as gu ON " .
            "gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . escape_string($this->get("user_id")) . "'".
            " AND ph.photo_id = '" . escape_string($photo_id) . "'" .
            " AND gp.access_level >= ph.level " .
            "ORDER BY gp.access_level DESC, writable DESC, " .
            "watermark_level DESC " .
            "LIMIT 0, 1";

        $gps = group_permissions::getRecordsFromQuery("group_permissions", $sql);
        if ($gps && sizeof($gps) >= 1) {
            return $gps[0];
        }

        return null;
    }

    function getDisplayArray() {
        $da = array(
            translate("username") => $this->get("user_name"),
            translate("person") => $this->getLink(),
            translate("class") =>
                $this->get("user_class") == 0 ? "Admin" : "User",
            translate("can browse people") => $this->get("browse_people") == 1
                ? translate("Yes") : translate("No"),
            translate("can browse places") => $this->get("browse_places") == 1
                ? translate("Yes") : translate("No"),
            translate("can browse tracks") => $this->get("browse_tracks") == 1
                ? translate("Yes") : translate("No"),
            translate("can view details of people") =>
                $this->get("detailed_people") == 1
                ? translate("Yes") : translate("No"),
            translate("can view details of places") =>
                $this->get("detailed_places") == 1
                ? translate("Yes") : translate("No"),
            translate("can import") =>
                $this->get("import") == 1
                ? translate("Yes") : translate("No"),
            translate("can download zipfiles") =>
                $this->get("download") == 1
                ? translate("Yes") : translate("No"),
            translate("can leave comments") =>
                $this->get("leave_comments") == 1
                ? translate("Yes") : translate("No"),
            translate("can rate photos") =>
                $this->get("allow_rating") == 1
                ? translate("Yes") : translate("No"),
            translate("can rate the same photo multiple times") =>
                $this->get("allow_multirating") == 1
                ? translate("Yes") : translate("No"),
            translate("last login") =>
                $this->get("lastlogin"),
            translate("last ip address") =>
                $this->get("lastip"));

        if ($this->get("lightbox_id")) {
            $lightbox = new album($this->get("lightbox_id"));
            $lightbox->lookup();

            if ($lightbox->get("album")) {
                $da[translate("lightbox album")] = $lightbox->get("album");
            }
        }

        return $da;
    }

    function load_language($force = 0) {
        $langs=array();

        if (!$force && $this->lang != null) {
            return $this->lang;
        }

        if ($this->prefs != null && $this->prefs->get("language") != null) {
            $langs[] = $this->prefs->get("language");
        }

        $langs=array_merge($langs, language::http_accept());

        $this->lang=language::load($langs);
        return $this->lang;
    }

    function add_crumb($title, $link) {
        $numCrumbs = count($this->crumbs);
        if ($numCrumbs == 0 || (!strpos($link, "_crumb="))) {

            // if title is the same remove last and add new
            if ($numCrumbs > 0 &&
                strpos($this->crumbs[$numCrumbs - 1], ">$title<")) {

                $this->eat_crumb();
            }
            else {
                $numCrumbs++;
            }

            $question = strpos($link, "?");
            if ($question > 0) {
                $link =
                    substr($link, 0, $question) ."?_crumb=$numCrumbs&amp;" .
                    substr($link, $question + 1);
            }
            else {
                $link .= "?_crumb=$numCrumbs";
            }

            $this->crumbs[] = "<a href=\"$link\">$title</a>";
        }
    }

    function eat_crumb($num = -1) {
        if ($this->crumbs && count($this->crumbs) > 0) {
            if ($num < 0) { $num = count($this->crumbs) - 1; }
            $this->crumbs = array_slice($this->crumbs, 0, $num);
        }
    }

    function get_last_crumb() {
        if ($this->crumbs && count($this->crumbs) > 0) {
            return html_entity_decode($this->crumbs[count($this->crumbs) - 1]);
        }
    }

    function get_rating_graph() {
        $sql = "SELECT ROUND(rating), count(*) FROM " . 
            DB_PREFIX . "photo_ratings " .
            "WHERE user_id=" . escape_string($this->get("user_id")) .
            " GROUP BY ROUND(rating) ORDER BY ROUND(rating) ";

        $result = query($sql, "Rating grouping failed");

        $legend=array(translate("rating"), translate("count"));


        while($row = fetch_row($result)) {
            $link="search.php?_action=" . translate("search") . 
                "&userrating=$row[0]" .
                "&_userrating_user=" . escape_string($this->get("user_id"));
            $value=$row[0];
            $count=$row[1];

            $value_array[]=array($value, $link, $count);
        }
        if($value_array) {
            return "<h3>" . translate("photo ratings") . "</h3>" .
                create_bar_graph($legend, $value_array, 150);
        }
    }
    function get_comments() {
        $sql = "select comment_id from " . DB_PREFIX . "comments where" .
            " user_id = " .  $this->get("user_id") . " order by comment_date";
        $comments=comment::getRecordsFromQuery("comment", $sql);
        return $comments;
    }
    
    public static function getByName($name) {
        $sql = "select user_id from " . DB_PREFIX . "users where" .
            " user_name = '" .  escape_string($name) ."'";
        $users=user::getRecordsFromQuery("user", $sql);
        return $users[0];
    }        
}

function get_users($order = "user_name") {
    return user::getRecords("user", $order);
}

?>
