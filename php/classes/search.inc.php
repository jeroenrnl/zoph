<?php
/**
 * Object for storing & retrieving searches
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
 * @author Jeroen Roos
 */

use db\select;
use db\param;
use db\clause;

use template\block;
use template\template;

/**
 * Store and retrieve searches
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class search extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="saved_search";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("search_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("name");
    /** @var bool keep keys with insert. In most cases the keys are set
                  by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="search.php?search_id=";

    /**
     * Update an existing search in the db
     */
    public function update() {
        $this->set("timestamp", "now()");
        parent::update();
    }

    /**
     * Lookup an existing search in the db
     */
    public function lookup() {
        $user=user::getCurrent();
        $qry=new select(array("ss" => "saved_search"));
        $where=new clause("search_id=:searchid");

        $qry->addParam(new param(":searchid", $this->getId(), PDO::PARAM_INT));

        if (!$user->isAdmin()) {
            $clause=new clause("owner=:owner");
            $qry->addParam(new param(":owner", $user->getId(), PDO::PARAM_INT));
            $clause->addOr(new clause("public=TRUE"));
            $where->addAnd($clause);
        }

        $qry->where($where);
        return $this->lookupFromSQL($qry);
    }

    /**
     * Get the name of this search
     */
    public function getName() {
        return $this->get("name");
    }

    /**
     * Dummy function that acts as a placeholder for functionality that should be created
     * someday
     * @todo This should be created some time, but might slow down too much
     */
    public function getPhotoCount() {

    }

    /**
     * Display the search
     */
    public function getLink() {
        $user=user::getCurrent();
        $tplData=array(
            "href"      => $this->getSearchURL() . "&_action=" . translate("search"),
            "link"      => $this->getName(),
            "target"    => ""
        );

        if ($this->get("owner") != $user->get("user_id")) {
            $owner=new user($this->get("owner"));
            $owner->lookup();
            $tplData["owner"]=$owner;
        }
        return new block("savedSearch", $tplData);
    }

    /**
     * Get a link to use this search
     * This is different from getURL(), the URL returned by this function will take you to the
     * photo page, with the saved search applied.
     */
    public function getSearchURL() {
        return "search.php?" . $this->get("search");
    }

    /**
     * Get a link to this search
     */
    public function getURL() {
        return "search.php?search_id=" . $this->getId();
    }

    /**
     * Get a list of saved searches
     */
    public static function getList() {
        $user=user::getCurrent();
        $searches=static::getRecords("name", array(
            "owner"     => $user->getId(),
            "public"    => "true"
        ), "OR");

        if ($searches) {
            return new block("savedSearches", array(
                "searches"  => $searches,
                "user"      => $user
            ));
        }
        return;
    }
}
?>
