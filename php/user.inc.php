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


    private static $current;

    public $person;
    public $prefs;
    public $crumbs;
    public $lang; // holds translations

    function insert() {
        parent::insert();
        $this->prefs = new prefs($this->get("user_id"));
        $this->prefs->insert();
    }

    function delete() {
        parent::delete(array("prefs"));
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
        $this->lookup();
        return $this->get("user_class") == 0;
    }

    function get_lastnotify() {
        return $this->get("lastnotify");
    }

    /**
     * Get a link to this object
     * @todo should be phased out in favour of @see getURL, since this contains HTML
     * @return string link
     */
    function getLink() {
        return "<a href='" . $this->getURL() . "'>" . $this->getName() . "</a>";
    }

    /**
     * Get URL to this object
     * @return string URL
     */
    function getURL() {
        return "user.php?user_id=" . $this->getId();
    }

    function getName() {
        return $this->get("user_name");
    }

    function get_groups() {
        $qry = new select(array("gu" => "groups_users"));
        $qry->addFields(array("group_id"));
        $qry->where(new clause("user_id=:userid"));
        $qry->addParam(new param(":userid", (int) $this->getId(), PDO::PARAM_INT));

        return group::getRecordsFromQuery($qry);
    }



    function get_album_permissions($album_id) {
        if (!is_numeric($album_id)) { die("album_id must be numeric"); }
        if (!$album_id) { return; }

        $groups=$this->get_groups();

        $groupIds=array();
        foreach ($groups as $group) {
            $groupIds[]=(int) $group->getId();
        }

        if (is_array($groupIds) && sizeof($groupIds) > 0) {
            $qry=new select(array("gp" => "group_permissions"));
            $where = new clause("album_id=:albumid");
            $groups=new param(":groupid", $groupIds, PDO::PARAM_INT);
            $qry->addParams(array(
                new param(":albumid", (int) $album_id, PDO::PARAM_INT),
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


    function get_permissions_for_photo($photo_id) {
        $qry=new select(array("p" => "photos"));
        $qry->addFields(array("photo_id"));

        $where=new clause("p.photo_id = :photoid");
        $qry->addParam(new param(":photoid", (int) $photo_id, PDO::PARAM_INT));

        list($qry, $where) = selectHelper::expandQueryForUser($qry, $where, $this);

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

    function getDisplayArray() {
        $this->lookup_person();
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

    function load_language($force = 0) {
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

    /**
     * Create a graph of the ratings this user has made
     */
    public function getRatingGraph() {
        return rating::getGraphArrayForUser($this);
    }

    public function getComments() {
        return comment::getRecords("comment_date", array("user_id" => (int) $this->getId()));
    }

    /**
     * Get user object by searching for username
     * @param string name
     * @return user user object
     */
    public static function getByName($name) {
        $users=self::getRecords(null, array("user_name" => $name));
        return $users[0];
    }

    /**
     * Get all users
     * @param string sort order
     * @return array Array of all users
     */
    public static function getAll($order = "user_name") {
        return self::getRecords($order);
    }

    /**
     * Set currently logged in user
     * @param user user object
     */
    public static function setCurrent(user $user) {
        $user->lookup();
        $user->lookup_prefs();
        $user->lookup_person();
        self::$current=$user;
    }

    /**
     * Delete currently logged in user
     */
    public static function unsetCurrent() {
        self::$current=null;
    }

    /**
     * Get currently logged in user
     */
    public static function getCurrent() {
        return self::$current;
    }

}
?>
