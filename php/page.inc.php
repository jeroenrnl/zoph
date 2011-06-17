<?php

/*
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

class page extends zophTable {
    function __construct($id = 0) {
         if($id && !is_numeric($id)) { die("page_id must be numeric"); }
        parent::__construct("pages", array("page_id"), array("title"));
        $this->set("page_id", $id);
    }
    
    function insert() {
        $this->set("date","now()");
        parent::insert();
        $this->lookup();
    }

    function update() {
        $this->set("timestamp","now()");
        parent::update();
        $this->lookup();
    }

    function delete() {
        if(!$this->get("page_id")) { return; }
        parent::delete();
        
        $sql = "delete from " . DB_PREFIX . "pages_pageset where page_id=";
        $sql .= $this->get("page_id");
    
        query($sql, "Could not remove page from pageset: ");
    }
    
    
    function getDisplayArray() {
        $zophcode = new zophcode($this->get("text"));
        $text="<div class='page-preview'>" . $zophcode . "</div>";

        return array(
            translate("title") => e($this->get("title")),
            translate("date") => e($this->get("date")),
            translate("updated") => e($this->get("timestamp")),
            translate("text") => $text
        );
    }

    function display() {
        $zophcode = new zophcode($this->get("text"));
        return $zophcode;
    }

    function get_list_line($pageset_id=null) {
        $html="<tr>";
        $html.="<td><a href=page.php?page_id=" . $this->get("page_id") . ">";
        $html.=$this->get("title");
        $html.="</a></td>";
        $html.="<td>" . $this->get("date") . "</td>";
        $html.="<td>" . $this->get("timestamp") . "</td>";
        if(isset($pageset_id)) {
            $html.="<td><span class='actionlink'><a href='pageset.php?_action=moveup&pageset_id=" . $pageset_id . "&page_id=". $this->get("page_id") . "'>";
            $html.=translate("move up") . "</a> | ";
            $html.="<a href='pageset.php?_action=movedown&pageset_id=" . $pageset_id . "&page_id=". $this->get("page_id") . "'>";
            $html.=translate("move down") . "</a> | ";
            $html.="<a href='pageset.php?_action=delpage&pageset_id=" . $pageset_id . "&page_id=". $this->get("page_id") . "'>";
            $html.=translate("remove") . "</a></span></td>";
        } else {
        }    
        $html.="</tr>";
        return $html;
    }

    function get_order($pageset_id) {
        $sql = "select page_order from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id=" . $pageset_id . " and " .
            " page_id=" . $this->get("page_id") . " limit 1";
        $result=query($sql, "Could not get current order");
        if(num_rows($result)) {
            return intval(result($result, 0, 0));
        } else {
            return false;
        }
    }

    function get_pagesets() {
        $html="";
        $sql = "select pageset_id from " . DB_PREFIX . "pages_pageset" .
            " where page_id = " . $this->get("page_id");
        $pagesets=pageset::getRecordsFromQuery("pageset", $sql);
        if(!empty($pagesets)) {
            $html=get_pagesets_table_header();
            foreach ($pagesets as $pageset) {
                $pageset->lookup();
                $html.=$pageset->get_list_line();
            }
            $html.="</table><br>";
        }
        return $html;
    }

}
function get_all_pages() {
    $pages=get_pages();
    $html=get_pages_table_header();
    foreach ($pages as $page) {
        $page->lookup();
        $html.=$page->get_list_line();
    }
    $html.="</table><br>";
    return $html;
}

function get_pages_table_header() {
    $html="<table class='pages'>";
    $html.="<tr><th>" . translate("title") . "</th>";
    $html.="<th>" . translate("date") . "</th>";
    $html.="<th>" . translate("last modified") . "</th>";
    $html.="</tr>";
    return $html;
}

function get_page_table($pages_array, $pageset_id) {
    $html=get_pages_table_header();

    foreach ($pages_array as $page) {
        $page->lookup();
        $html.=$page->get_list_line($pageset_id);
    }
    $html.="</table><br>";
    return $html;
}

function get_pages($constraints = null, $conj = "and", $ops = null,
    $order = "title") {

    return pageset::getRecords("page", $order, $constraints, $conj, $ops);
}

function get_pages_select_array($pages_array = null) {

    $pa[""] = "";

    if (!$pages_array) {
        $pages_array = get_pages();
    }

    if ($pages_array) {
        foreach ($pages_array as $page) {
            $pa[$page->get("page_id")] = $page->get("title");
        }
    }

    return $pa;
}
