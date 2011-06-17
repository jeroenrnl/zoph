<?php

/*
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

class pageset extends zophTable {
    function pageset($id = 0) {
        if($id && !is_numeric($id)) { die("pageset_id must be numeric"); }
        parent::__construct("pageset", array("pageset_id"), array("title"));
        $this->set("pageset_id", $id);
        $this->set("date","now()");
    }

    function update() {
        $this->set("timestamp","now()");
        parent::update();
        $this->lookup();
    }

    function delete() {
        if(!$this->get("pageset_id")) { return; }
        parent::delete();
        
        $sql = "delete from " . DB_PREFIX . "pages_pageset where pageset_id=";
        $sql .= $this->get("pageiset_id");
    
        query($sql, "Could not remove page from pageset: ");
    }
    
    
    function getDisplayArray() {
        return array(
            translate("title") => $this->get("title"),
            translate("date") => $this->get("date"),
            translate("updated") => $this->get("timestamp"),
            translate("created by") => $this->lookup_user(),
            translate("show original page") => translate($this->get("show_orig"),0),
            translate("position of original") => translate($this->get("orig_pos"),0)
        );
    }
    function get_original_select_array() {
        return array(
            "never" => translate("Never",0), 
            "first" => translate("On first page",0), 
            "last" => translate("On last page",0),
            "all" => translate("On all pages",0));
    }

    function get_pages($pagenum=null) {
        $sql = "select page_id from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id = " . $this->get("pageset_id") .
            " order by page_order";
        if($pagenum) {
            $sql.=" limit " . escape_string($pagenum) . ",1";
        }
        $pages=page::getRecordsFromQuery("page", $sql);
        return $pages;
    }

    function get_pagecount() {
        $sql = "select count(page_id) from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id = " . $this->get("pageset_id");
        return pageset::getCountFromQuery($sql);
    }

    function addpage($page_id) {
        $page=new page($page_id);
        if (!$page->get_order($this->get("pageset_id"))) {
            $sql = "insert into " . DB_PREFIX . "pages_pageset " . 
                "values(" . $this ->get("pageset_id") . ", " .
                escape_string($page_id) . ", " .
                ($this->get_maxorder() + 1) . ")";
            query($sql, "Could not add page to pageset");
        } else {
            // The page already exists in this pageset.
            // at this moment a page cannot be more than once in a pagest
            // Someday, this should either give a nice error or this
            // limitation should be removed.
        }
    }
    
    function remove_page($page_id) {
        $sql = "delete from " . DB_PREFIX . "pages_pageset " . 
            "where pageset_id=" . $this ->get("pageset_id") . " and " .
            "page_id=" . escape_string($page_id);
        query($sql, "Could not remove page from pageset");
    }

    function moveup($page_id) {
        $page=new page($page_id);
        $order=$page->get_order($this->get("pageset_id"));
        if($order>=2) {
            $prevorder=$this->get_prevorder($order);
            $sql="update zoph_pages_pageset set page_order=" . $order .
                " where page_order=" . $prevorder;
            query($sql, "Could not change order");
            $sql="update zoph_pages_pageset set page_order=" . $prevorder .
                " where page_id=" . $page_id;
            query($sql, "Could not change order");
        }
    }
    function movedown($page_id) {
        $page=new page($page_id);
        $order=$page->get_order($this->get("pageset_id"));
        $max=$this->get_maxorder();
        if($order!=0 and $order<$max) {
            $nextorder=$this->get_nextorder($order);
            $sql="update zoph_pages_pageset set page_order=" . $order .
                " where page_order=" . $nextorder;
            query($sql, "Could not change order");
            $sql="update zoph_pages_pageset set page_order=" . $nextorder .
                " where page_id=" . $page_id;
            query($sql, "Could not change order");

        }
    }

    function get_maxorder() {
        $sql = "select max(page_order) from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id=" . $this->get("pageset_id");
        $result=query($sql, "Could not get max order");
        return intval(result($result, 0, 0));
    }
    
    function get_nextorder($order) {
        // If pages have been deleted, the page_order field may no longer
        // be nicely numbered 1,2,3, etc. but there may be holes in the list
        // so this function and get_prevorder() determine the next or previous
        // value of page_order.
        $sql = "select min(page_order) from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id=" . $this->get("pageset_id") .
            " and page_order>" . $order;
        $result=query($sql, "Could not get max order");
        return intval(result($result, 0, 0));
    }

    function get_prevorder($order) {
        $sql = "select max(page_order) from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id=" . $this->get("pageset_id") .
            " and page_order<" . $order;
        $result=query($sql, "Could not get max order");
        return intval(result($result, 0, 0));
    }


    function lookup_user() {
        $pagesetuser = new user($this->get("user"));
        $pagesetuser->lookup();
        $user_name = $pagesetuser->get("user_name");
        $pagesetuser->lookup_person();
        $pagesetperson = $pagesetuser->person->get_name();
        $pagesetperson_id = $pagesetuser->person->get("person_id");
        $return = sprintf("<a href=\"user.php?user_id=%s\">%s</a> (<a href=person.php?person_id=%s>%s</a>)", $this->get("user"), $user_name, $pagesetperson_id, $pagesetperson);
        return $return;
    }

    function get_list_line() {
        $html="<tr>";
        $html.="<td><a href=pageset.php?pageset_id=" . $this->get("pageset_id") . ">";
        $html.=$this->get("title");
        $html.="</a></td>";
        $html.="<td>" . $this->get("date") . "</td>";
        $html.="<td>" . $this->get("timestamp") . "</td>";
        $html.="<td>" . $this->lookup_user() . "</td>";
        $html.="</tr>";
        return $html;
    }
}    
function get_all_pagesets() {
    $sql = "select pageset_id,title,date,timestamp,user from " . DB_PREFIX . "pageset";

    $pagesets=pageset::getRecordsFromQuery("pageset", $sql);
    $html=get_pagesets_table_header();

    foreach ($pagesets as $pageset) {
        $html.=$pageset->get_list_line();
    }
    $html.="</table><br>";
    return $html;
}

function get_pagesets_table_header() {
    $html="<table class='pagesets'>";
    $html.="<tr><th>" . translate("title") . "</th>";
    $html.="<th>" . translate("date") . "</th>";
    $html.="<th>" . translate("last modified") . "</th>";
    $html.="<th>" . translate("user") . "</th>";
    $html.="</tr>";
    return $html;
}
function get_pagesets($constraints = null, $conj = "and", $ops = null,
    $order = "title") {

    return pageset::getRecords("pageset", $order, $constraints, $conj, $ops);
}

function get_pageset_select_array($pageset_array = null) {

    $psa[""] = "";

    if (!$pageset_array) {
        $pageset_array = get_pagesets();
    }

    if ($pageset_array) {
        foreach ($pageset_array as $pageset) {
            $psa[$pageset->get("pageset_id")] = $pageset->get("title");
        }
    }

    return $psa;
}

