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
     * Lookup an existing search in the db
     */
    public function lookup() {
        $user=user::getCurrent();
        if (!$user->is_admin()) {
            $where= "(owner=" . escape_string($user->get("user_id")) .
            " OR " . "public=TRUE) AND ";
        }

        $sql="SELECT * FROM " . DB_PREFIX . "saved_search WHERE " .
            $where .
            "search_id=" . escape_string($this->get("search_id"));
        /** @todo Once this function has been changed to new db code,
                  the fetch_assoc in zophTable::lookupFromSQL can
                  be removed
         */
        return $this->lookupFromSQL($sql);
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
     * Get array that can be used to build an edit form
     * @return array edit array
     */
    public function getEditArray() {
        $user=user::getCurrent();
        $edit_array=array();


        $edit_array[]=array(
            translate("Name"),
            create_text_input("name", $this->get("name"),40,64));

        if ($user->is_admin()) {
            $edit_array[]=array (
                translate("Owner"),
                template::createPulldown("owner", $this->get("owner"),
                    template::createSelectArray(user::getRecords("user_name"),
                    array("user_name"))));
            $edit_array[]=array(
                translate("Public"),
                template::createYesNoPulldown("public", $this->get("public")));

        }
        return $edit_array;
    }

    /**
     * Display the search
     * @todo Contains HTML
     * @todo The user param should probably be removed
     * @todo To be in line with the rest of Zoph, this should be called getLink()
     * @param user
     */
    public function display($user = null) {
        if ($user && ($this->get("owner") != $user->get("user_id"))) {
            $owner=new user($this->get("owner"));
            $owner->lookup();
            $ownertext="<span class='searchinfo'>(" . translate("by") . " " .
                $owner->getLink() . ")</span>";
        }
        return "<a href='" . $this->getLink() . "&_action=" .
            translate("search") . "'>" . $this->getName() .
            "</a> " . $ownertext;
    }

    /**
     * Get a link to this search
     * @todo to be in line with the rest of Zoph, this should be called getURL()
     */
    public function getLink() {
        return "search.php?" . $this->get("search");
    }

    /**
     * Get list of saved searches
     * @todo can possibly be rewritten as a call to getRecords()
     */
    public static function getAll() {
        $user=user::getCurrent();
        $sql="SELECT * FROM " . DB_PREFIX . "saved_search";
        if (!$user->is_admin()) {
            $sql.=" WHERE (owner=" . escape_string($user->get("user_id")) .
                " OR " .  "public=TRUE)";
        }
        return search::getRecordsFromQuery($sql);
    }

    /**
     * Get a list of saved searches
     * @todo should be renamed to comply to naming convention
     * @todo Contains HTML
     */ 
    public static function getList() {
        $user=user::getCurrent();
        $searches=static::getAll();
        if ($searches) {
            $html="<h2>" . translate("Saved searches") . "</h2>";
            $html.="<ul class='saved_search'>";

            foreach ($searches as $search) {
                $html.="<span class='actionlink'>";
                $html.="<a href='" . $search->getLink() . "'>" .
                    translate("load") . "</a>";
                if (($search->get("owner") == $user->get("user_id")) ||
                    $user->is_admin()) {
                    $html.=" | <a href='search.php?search_id=" .
                            $search->get("search_id") .
                            "&_action=edit'>" . translate("edit") . "</a>";
                    $html.=" | <a href='search.php?search_id=" .
                            $search->get("search_id") .
                            "&_action=delete'>" . translate("delete") . "</a>";
                }
                $html.="</span>";
                $html.="<li>" . $search->display($user) . "</li>\n";

            }
            $html.="</ul>\n\n";
        }
        return $html;
    }
}
?>
