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

use db\clause;
use db\select;
use db\insert;
use db\param;

/**
 * A class corresponding to the comments table.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class comment extends zophTable {

    /** @var string The name of the database table */
    protected static $tableName="comments";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("comment_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("subject");
    /** @var bool keep keys with insert. In most cases the keys are set by the
                  db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="comment.php?comment_id=";

    /**
     * Display comment
     */
    public function __toString() {
        return (string) $this->toHTML(true);
    }

    /**
     * Insert a new comment into the db
     */
    public function insert() {
        $this->set("comment_date", "now()");
        $this->set("ipaddr", $_SERVER['REMOTE_ADDR']);
        parent::insert();
        $this->lookup();
    }

    /**
     * Update existing comment in the db
     */
    public function update() {
        $this->set("timestamp", "now()");
        parent::update();
        $this->lookup();
    }

    /**
     * Delete a comment from the db
     */
    public function delete() {
        if (!$this->getId()) {
            return;
        }
        parent::delete(array("photo_comments"));
    }

    /**
     * Get array to display comment data
     * @return array display array
     */
    public function getDisplayArray() {
        $user=user::getCurrent();
        $date=$this->get("comment_date");
        $changed=$this->get("timestamp");

        $zophcode = new zophCode\parser($this->get("comment"), array("b", "i", "u"));
        $comment="<div>" . $zophcode . "</div>";

        return array(
            translate("subject") => $this->get("subject"),
            translate("date") => $date,
            translate("user") => $this->getUserLink(),
            translate("IP address") => $user->isAdmin() ? $this->get("ipaddr") : "<i>" .
                translate("only visible for admin users") . "</i>",
            translate("comment") => $comment,
            translate("updated") => $changed
        );
    }

    /**
     * Lookup user that created this comment and return a link
     */
    private function getUserLink() {
        $user = new user($this->get("user_id"));
        $user->lookup();
        $user->lookupPerson();

        return $user->getLink() . " (" . $user->person->getLink() . ")";
    }

    /**
     * Get the photo that this comment belongs to
     */
    public function getPhoto() {
        if (!$this->getId()) {
            return;
        }
        $qry=new select(array("pcom" => "photo_comments"));
        $qry->addFields(array("photo_id"));
        $qry->where(new clause("comment_id=:commentId"));
        $qry->addParam(new param(":commentId", (int) $this->getId(), PDO::PARAM_INT));
        $qry->addLimit(1);

        $result=photo::getRecordsFromQuery($qry);
        if ($result[0]) {
            $result[0]->lookup();
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * Add this comment to a photo
     */
    public function addToPhoto(photo $photo) {
        $qry=new insert(array("photo_comments"));
        $qry->addParams(array(
            new param(":photo_id", (int) $photo->getId(), PDO::PARAM_INT),
            new param(":comment_id", (int) $this->getId(), PDO::PARAM_INT)
        ));

        $qry->execute();
    }

    /**
     * Return whether the given user is the owner (creator) of this comment
     * @param user User to check
     * @return bool true: user is owner, false: user is not owner
     */
    public function isOwner(user $user) {
        return ($user->getId()==$this->get("user_id"));
    }

    /**
     * Display this comment
     * @param bool Display a thumbnail of the photo this comment belongs to
     * @return block Template block
     */
    public function toHTML($thumbnail=false) {
        $user=user::getCurrent();

        $this->lookup();
        $photo=$this->getPhoto();

        $tplData=array(
            "subject"       => $this->get("subject"),
            "commentdate"   => $this->get("comment_date"),
            "userlink"      => $this->getUserLink(),
            "zophcode"      => new zophCode\parser($this->get("comment"), array("b", "i", "u")),
            "actionlinks"   => null

        );

        if ($user->isAdmin() || $this->isOwner($user)) {
            $tplData["actionlinks"]=array(
                translate("display")    => "comment.php?_action=display&amp;comment_id=" .  $this->getId(),
                translate("edit")       => "comment.php?_action=edit&amp;comment_id=" .  $this->getId(),
                translate("delete")     => "comment.php?_action=delete&amp;comment_id=" .  $this->getId()
            );
        }

        if ($thumbnail) {
            $tplData["thumbnail"]=$photo->getThumbnailLink();
        }

        return new block("comment", $tplData);
    }
}

?>
