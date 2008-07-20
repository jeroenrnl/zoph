<?php

/*
 * A class corresponding to the color_shemes table.
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
class comment extends zoph_table {

    function comment($id = 0) {
        if($id && !is_numeric($id)) { die("comment_id must be numeric"); }
        parent::zoph_table("comments", array("comment_id"), array("subject"));
        $this->set("comment_id", $id);
    }

    function insert() {
        $this->set("comment_date", "now()");
        $this->set("ipaddr", $_SERVER['REMOTE_ADDR']);
        parent::insert();
        $this->lookup();
    }
    
    function update() {
        $this->set("timestamp","now()");
        parent::update();
        $this->lookup();
    }

    function delete() {
        if(!$this->get("comment_id")) { return; }
        parent::delete();
        
        $sql = "delete from " . DB_PREFIX . "photo_comments where comment_id=";
        $sql .= escape_string($this->get("comment_id"));
    
        mysql_query($sql) or die_with_mysql_error("Could not clean comment from photo: ", $sql);
    }
    
    
    function get_display_array($user = null) {
        $date=$this->get("comment_date");
        $changed=$this->get("timestamp");
        if($changed != $date) { $updated=$changed; }

        $zophcode = new zophcode($this->get("comment"), array("b","i", "u"));
        $comment="<div>" . $zophcode->parse() . "</div>";
    
        return array(
            translate("subject") => $this->get("subject"),
            translate("date") => $date,
            translate("user") => $this->lookup_user(),
            translate("IP address") => $user->is_admin() ? $this->get("ipaddr") : "<i>" . translate("only visible for admin users") . "</i>",
            translate("comment") => $comment,
            translate("updated") => $changed
        );
    }
    
    function lookup_user() {
        $comment_user = new user($this->get("user_id"));
        $comment_user->lookup();
        $user_name = $comment_user->get("user_name");
        $comment_user->lookup_person();
        $comment_person = $comment_user->person->get_name();
        $comment_person_id = $comment_user->person->get("person_id");
        $return = sprintf("<a href=\"user.php?user_id=%s\">%s</a> (<a href=person.php?person_id=%s>%s</a>)", $this->get("user_id"), $user_name, $comment_person_id, $comment_person);
        return $return; 
    }

    function get_photo() {
        if(!$this->get("comment_id")) { return; }
        $sql = "select photo_id from " . DB_PREFIX . "photo_comments" .
            " where comment_id=" . escape_string($this->get("comment_id")) .
            " limit 1";
        $result=get_records_from_query("photo", $sql);
        if($result[0]) { 
            $result[0]->lookup();
            return $result[0];
        } else {
            return null;
        }
    }

    function add_comment_to_photo($photo_id) {
        if (!$photo_id) { return; }
        $sql = "insert into " . DB_PREFIX . "photo_comments values" . 
            "(" . escape_string($photo_id) . ", " . escape_string($this->get("comment_id")) . ")";

      
        mysql_query($sql) or die_with_mysql_error("Failed to add comment:", $sql);
    }
 
    function is_owner($user) {
        if($user->get(user_id)==$this->get("user_id")) {
            return true;
        } else {
            return false;
        }
    }

    function to_html($user, $thumbnail=null) {
        $this->lookup();
        $photo=$this->get_photo();

        $html = "<div class=\"comment\">\n";
        $html .= "<h3>\n";
        
        if ($user->is_admin() || $this->is_owner($user)) {
            $html .= "<span class=\"actionlink\">\n";
            $html .= "<a href=\"comment.php?_action=display&amp;comment_id=" .
                $this->get("comment_id") . "\">\n";
            $html .= translate("display") . "</a> | ";

            $html .= "<a href=\"comment.php?_action=edit&amp;comment_id=" .
                $this->get("comment_id") . "\">";
            $html .= translate("edit") . "</a> | ";
            $html .= "<a href=\"comment.php?_action=delete&amp;comment_id=" .
                $this->get("comment_id") ."\">";
            $html .= translate("delete") . "</a>\n";
            $html .= "</span>\n";
        }
                           
        $html .= $this->get("subject") . "</h3>\n";
        $html .= "<div class=\"commentinfo\">\n";
        $html .= $this->get("comment_date");
        $html .= " " . translate("by",0) . " <b>" . $this->lookup_user() . "</b>";
        $html .= "</div>\n";

        if ($thumbnail) {
            $html .= "<div class=\"thumbnail\">\n";
            $html .= $photo->get_thumbnail_link();
            $html .= "</div>\n";
        }
        
        $zophcode = new zophcode($this->get("comment"), array("b","i", "u"));
        $html .= $zophcode->parse();
        $html .= "<br></div>\n";
        return $html;
    }
}

function get_all_comments() {
   return get_records_from_query("comment", "select comment_id from " . DB_PREFIX . "comments");
   }
?>
