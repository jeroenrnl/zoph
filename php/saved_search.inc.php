<?php

/*
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
 */

class search extends zophTable {
    /** @var string The name of the database table */
    protected static $table_name="saved_search";
    /** @var array List of primary keys */
    protected static $primary_keys=array("search_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("name");
    /** @var bool keep keys with insert. In most cases the keys are set by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="search.php?search_id=";

    public function lookup() {
        $user=user::getCurrent();
        if($user->is_admin()) {
            $where= "(owner=" . escape_string($user->get("user_id")) . 
            " OR " . "public=TRUE) AND "; 
        }
        
        $sql="SELECT * FROM " . DB_PREFIX . "saved_search WHERE " .
            $where .
            "search_id=" . escape_string($this->get("search_id"));
            
        return $this->lookupFromSQL($sql);
    }

    function getName() {
        return $this->get("name");
    }

    function getPhotoCount($user = null) {
        // This should be created some time, but might slow down too much
    }

    public function getEditArray() {
        $user=user::getCurrent();
        $edit_array=array();


        $edit_array[]=array(
            translate("Name"),  
            create_text_input("name", $this->get("name"),40,64));

        if($user->is_admin()) {
            $edit_array[]=array (
                translate("Owner"),
                template::createPulldown("owner", $this->get("owner"), template::createSelectArray(user::getRecords("user_name"), array("user_name"))));
            $edit_array[]=array(
                translate("Public"),
                template::createYesNoPulldown("public", $this->get("public")));

        }
        return $edit_array;
    }

    function display($user = null) {
        if($user && ($this->get("owner") != $user->get("user_id"))) {
            $owner=new user($this->get("owner"));
            $owner->lookup();
            $ownertext="<span class='searchinfo'>(" . translate("by") . " " .
                $owner->getLink() . ")</span>";
        }
        return "<a href='" . $this->getLink() . "&_action=" . 
            translate("search") . "'>" . $this->getName() .
            "</a> " . $ownertext;
    }

    function getLink() {
        return "search.php?" . $this->get("search");
    }
}

function get_saved_searches($user) {

    $sql="SELECT * FROM " . DB_PREFIX . "saved_search";
    if (!$user->is_admin()) {
        $sql.=" WHERE (owner=" . escape_string($user->get("user_id")) . 
            " OR " .  "public=TRUE)"; 
    }
    return search::getRecordsFromQuery($sql);
}

function get_list_of_saved_searches($user) {
    $searches=get_saved_searches($user);
    if($searches) {
        $html="<h2>" . translate("Saved searches") . "</h2>";
        $html.="<ul class='saved_search'>";

        foreach ($searches as $search) {
            $html.="<span class='actionlink'>";
            $html.="<a href='" . $search->getLink() . "'>" .
                translate("load") . "</a>";
            if(($search->get("owner") == $user->get("user_id")) || 
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

?>
