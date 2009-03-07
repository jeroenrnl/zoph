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

class search extends zoph_table {

    function search($id = 0) {
        if($id && !is_numeric($id)) { die("search_id must be numeric"); }
        parent::zoph_table("saved_search", array("search_id"), array("name"));
        $this->set("search_id", $id);

    }

    function lookup($user) {
        
        $sql="SELECT * FROM " . DB_PREFIX . "saved_search WHERE" .
            "(owner=" . escape_string($user->get("user_id")) . " OR " .
            "public=TRUE) " .
            "AND search_id=" . escape_string($this->get("search_id"));
            
        return parent::lookup($sql);
    }

    function get_name() {
        return $this->get("name");
    }

    function get_photo_count($user = null) {
        // This should be created some time, but might slow down too much
    }

    function get_edit_array($user) {
        $edit_array=array();


        $edit_array[]=array(
            translate("Name"),  
            create_text_input("name", $this->get("name"),40,64));

        if($user->is_admin()) {
            $edit_array[]=array (
                translate("Owner"),
                create_pulldown("owner", $this->get("owner"), create_select_array(get_records("user", "user_name"), array("user_name"))));
            $edit_array[]=array(
                translate("Public"),
                create_pulldown("public", $this->get("public"), array("0" => translate("No",0), "1" => translate("Yes",0))) );

        }
        return $edit_array;
    }

    function display($user = null) {
        if($user && ($this->get("owner") != $user->get("user_id"))) {
            $owner=new user($this->get("owner"));
            $owner->lookup();
            $ownertext="<span class='searchinfo'>(" . translate("by") . " " .
                $owner->get_link() . ")</span>";
        }
        return "<a href='" . $this->get_link() . "&_action=" . 
            translate("search") . "'>" . $this->get_name() .
            "</a> " . $ownertext;
    }

    function get_link() {
        return "search.php?" . $this->get("search");
    }
}

function get_saved_searches($user) {

    $sql="SELECT * FROM " . DB_PREFIX . "saved_search";
    if (!$user->is_admin()) {
        $sql.=" WHERE (owner=" . escape_string($user->get("user_id")) . 
            " OR " .  "public=TRUE)"; 
    }
    return get_records_from_query("search", $sql);
}

function get_list_of_saved_searches($user) {
    $searches=get_saved_searches($user);
    if($searches) {
        $html="<h2>" . translate("Saved searches") . "</h2>";
        $html.="<ul class='saved_search'>";

        foreach ($searches as $search) {
            $html.="<span class='actionlink'>";
            $html.="<a href='" . $search->get_link() . "'>" .
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
