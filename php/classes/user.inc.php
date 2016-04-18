<?php
/**
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
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */

use db\select;
use db\clause;
use db\param;
use db\selectHelper;

/**
 * A class representing a user of Zoph
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class user extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="users";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("user_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("user_name");
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="user.php?user_id=";

    /** @var user currently logged on user */
    private static $current;

    /** @var person Person linked to this user */
    public $person;
    /** @var user_prefs Preferences of this user */
    public $prefs;
    /** @var lang Holds translations */
    public $lang;

    /**
     * Insert a new user into the db
     */
    public function insert() {
        parent::insert();
        $this->prefs = new prefs($this->getId());
        $this->prefs->insert();
    }

    /**
     * Delete a user from the db
     * also delete the preferences for this user
     */
    public function delete() {
        parent::delete(array("prefs", "groups_users"));
    }

    /**
     * Lookup the person linked to this user
     */
    public function lookupPerson() {
        $this->person = new person($this->get("person_id"));
        $this->person->lookup();
    }

    /**
     * Lookup the preferences of this user
     */
    public function lookupPrefs() {
        $this->prefs = new prefs($this->getId());
        $this->prefs->lookup();
    }

    /**
     * Is this user an admin?
     * @return bool
     */
    public function isAdmin() {
        $this->lookup();
        return $this->get("user_class") == 0;
    }

    /**
     * When was this user last notified of new albums?
     * @return string timestamp
     */
    public function getLastNotify() {
        return $this->get("lastnotify");
    }

    /**
     * Get a link to this object
     * @todo should be phased out in favour of @see getURL, since this contains HTML
     * @return string link
     */
    public function getLink() {
        return "<a href='" . $this->getURL() . "'>" . $this->getName() . "</a>";
    }

    /**
     * Get the username
     * @return string name
     */
    public function getName() {
        return $this->get("user_name");
    }

    /**
     * Get groups for this user
     * @return array Groups
     */
    public function getGroups() {
        $qry = new select(array("gu" => "groups_users"));
        $qry->addFields(array("group_id"));
        $qry->where(new clause("user_id=:userid"));
        $qry->addParam(new param(":userid", (int) $this->getId(), PDO::PARAM_INT));

        return group::getRecordsFromQuery($qry);
    }

    /**
     * Get album permissions for this user
     * @param album album to get permissions for
     * @return group_permissions Permissions object
     */
    public function getAlbumPermissions(album $album) {
        $groups=$this->getGroups();

        $groupIds=array();
        foreach ($groups as $group) {
            $groupIds[]=(int) $group->getId();
        }

        if (is_array($groupIds) && sizeof($groupIds) > 0) {
            $qry=new select(array("gp" => "group_permissions"));
            $where = new clause("album_id=:albumid");
            $groups=new param(":groupid", $groupIds, PDO::PARAM_INT);
            $qry->addParams(array(
                new param(":albumid", (int) $album->getId(), PDO::PARAM_INT),
                $groups
            ));
            $where->addAnd(clause::InClause("gp.group_id", $groups));
            $qry->where($where);
            $qry->addOrder("access_level DESC")
                ->addOrder("writable DESC")
                ->addOrder("watermark_level DESC");
            $qry->addLimit(1);

            $aps=group_permissions::getRecordsFromQuery($qry);
            if (is_array($aps) && sizeof($aps) >= 1) {
                return $aps[0];
            }
        }

        return null;
    }

    /**
     * Get permissions for a specific photo, for this user
     * @param photo Photo to get permissions for
     * @return group_permissions Permissions object
     */
    public function getPhotoPermissions(photo $photo) {
        $qry=new select(array("p" => "photos"));
        $qry->addFields(array("photo_id"));

        $where=new clause("p.photo_id = :photoid");
        $qry->addParam(new param(":photoid", (int) $photo->getId(), PDO::PARAM_INT));
        $qry->addParam(new param(":userid", (int) $this->getId(), PDO::PARAM_INT));

        $qry->join(array("pa" => "photo_albums"), "pa.photo_id=p.photo_id");
        $qry->join(array("gp" => "group_permissions"), "gp.album_id=pa.album_id");
        $qry->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");

        $where->addAnd(new clause("gp.access_level>=p.level"));
        $where->addAnd(new clause("gu.user_id=:userid"));
        $qry->addFields(array("gp.*"));
        $qry->addLimit(1);
        // do ordering to grab entry with most permissions
        $qry->addOrder("gp.access_level DESC")->addOrder("writable DESC")->addOrder("watermark_level DESC");
        $qry->where($where);

        $gps = group_permissions::getRecordsFromQuery($qry);
        if ($gps && sizeof($gps) >= 1) {
            return $gps[0];
        }

        return null;
    }

    /**
     * Check wheter this user can see hidden circles
     * @return bool user can see hidden circles
     */
    public function canSeeHiddenCircles() {
        return ($this->isAdmin() || $this->get("see_hidden_circles"));
    }

    /**
     * Get array to display information about this user
     * @return array of properties to display
     */
    public function getDisplayArray() {
        $this->lookupPerson();
        $da = array(
            translate("username") => $this->get("user_name"),
            translate("person") => $this->person->getLink(),
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
            translate("can view hidden circles") =>
                $this->get("see_hidden_circles") == 1
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
            translate("can share photos", 0) =>
                $this->get("allow_share") == 1
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
    /**
     * Load language
     * This loads the translations of Zoph's web gui
     * @param bool Even load when already loaded
     */
    public function loadLanguage($force = 0) {
        $langs=array();

        if (!$force && $this->lang != null) {
            return $this->lang;
        }

        if ($this->prefs != null && $this->prefs->get("language") != null) {
            $langs[] = $this->prefs->get("language");
        }

        $langs=array_merge($langs, language::httpAccept());

        $this->lang=language::load($langs);
        return $this->lang;
    }

    /**
     * Create a graph of the ratings this user has made
     */
    public function getRatingGraph() {
        return rating::getGraphArrayForUser($this);
    }

    /**
     * Get the comments this user has placed
     * @return array comments
     */
    public function getComments() {
        return comment::getRecords("comment_date", array("user_id" => (int) $this->getId()));
    }

    /**
     * Get user object by searching for username
     * @param string name
     * @return user user object
     */
    public static function getByName($name) {
        $users=static::getRecords(null, array("user_name" => $name));
        if (sizeof($users)==1) {
            return $users[0];
        } else if (sizeof($users)==0) {
            throw new userNotFoundException("User not found");
        } else {
            throw new userMultipleFoundException("Multiple users with the same name found");
        }
    }

    /**
     * Get all users
     * @param string sort order
     * @return array Array of all users
     */
    public static function getAll($order = "user_name") {
        return static::getRecords($order);
    }

    /**
     * Set currently logged in user
     * (log in)
     * @param user user object
     * @todo a proper framework needs to be made to invalidate caches
     */
    public static function setCurrent(user $user) {
        category::$categoryCache=null;
        $user->lookup();
        $user->lookupPrefs();
        $user->lookupPerson();
        static::$current=$user;
    }

    /**
     * Delete currently logged in user
     * (Log out)
     */
    public static function unsetCurrent() {
        static::$current=null;
    }

    /**
     * Get currently logged in user
     */
    public static function getCurrent() {
        return static::$current;
    }

}
?>
