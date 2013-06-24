<?php
/**
 * A class corresponding to the comments table.
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
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */

/**
 * A class corresponding to the comments table.
 * 
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class comment extends zophTable {

    /** @var string The name of the database table */
    protected static $table_name="comments";
    /** @var array List of primary keys */
    protected static $primary_keys=array("comment_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("subject");
    /** @var bool keep keys with insert. In most cases the keys are set by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="comment.php?comment_id=";

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
    
        query($sql, "Could not clean comment from photo");
    }
    
    
    function getDisplayArray($user = null) {
        $date=$this->get("comment_date");
        $changed=$this->get("timestamp");
        if($changed != $date) { $updated=$changed; }

        $zophcode = new zophcode($this->get("comment"), array("b","i", "u"));
        $comment="<div>" . $zophcode . "</div>";
    
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
        $comment_person = $comment_user->person->getName();
        $comment_person_id = (int) $comment_user->person->getId();
        $return = sprintf("<a href=\"user.php?user_id=%s\">%s</a> (<a href=person.php?person_id=%s>%s</a>)", $this->get("user_id"), $user_name, $comment_person_id, $comment_person);
        return $return; 
    }

    function get_photo() {
        if(!$this->get("comment_id")) { return; }
        $sql = "select photo_id from " . DB_PREFIX . "photo_comments" .
            " where comment_id=" . (int) $this->getId() .
            " limit 1";
        $result=photo::getRecordsFromQuery($sql);
        if($result[0]) { 
            $result[0]->lookup();
            return $result[0];
        } else {
            return null;
        }
    }

    function addToPhoto(photo $photo) {
        $sql = "insert into " . DB_PREFIX . "photo_comments values" . 
            "(" . (int) $photo->getId() . ", " . (int) $this->getId() . ")";

      
        query($sql, "Failed to add comment:");
    }
 
    function is_owner($user) {
        if($user->getId()==$this->get("user_id")) {
            return true;
        } else {
            return false;
        }
    }

    function toHTML($user, $thumbnail=null) {
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
            $html .= $photo->getThumbnailLink();
            $html .= "</div>\n";
        }
        
        $zophcode = new zophcode($this->get("comment"), array("b","i", "u"));
        $html .= $zophcode;
        $html .= "<br></div>\n";
        return $html;
    }
}

function get_all_comments() {
   return comment::getRecordsFromQuery("SELECT comment_id FROM " . DB_PREFIX . "comments");
   }

function format_comments($user, $comments) {
    $html=null;
    foreach ($comments as $comment) {
        $comment->lookup();
        $html.=$comment->toHTML($user, true);
    }
    return $html;
}
?>
