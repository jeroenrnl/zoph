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

$page=array_reverse(explode("/", $_SERVER["PHP_SELF"]));
if($page[0]=="show_page.inc.php") {
   redirect("zoph.php");
}

// If no page is set, we always show original.
$page_html="";
$show_orig=true;
if($obj->get("pageset")) {
    $pageset=new pageset($obj->get("pageset"));
    $pageset->lookup();
    $pagecount=$pageset->get_pagecount();
    if($pagecount==0) {
        $show_orig=true;
        return;
    }
    $pageset_page=getvar("_pageset_page");
    $orig=$pageset->get("show_orig");
    if(!$pageset_page || $pageset_page==0) {
        $pageset_page=0;
        $first=true;
    } else if ($pageset_page==$pagecount - 1) {
        $last=true;
    }

    if (($orig=="last" && !$last) ||
        ($orig=="first" && !$first) ||
        ($orig=="never")) {
            $show_orig=false;
    }
    $pages=$pageset->get_pages($pageset_page);
    $page=$pages[0];
    $page->lookup();
    $page_html="<div class='page'>" .
        $page->display() .
        pager($pageset_page, $pagecount, $pagecount, 1, 5, $request_vars, "_pageset_page") .
        "<br>\n</div>\n";

    if($pageset->get("orig_pos") == "bottom") {
        echo $page_html;
        $page_html="";
    }
}

