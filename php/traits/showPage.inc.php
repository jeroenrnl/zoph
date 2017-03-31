<?php
/**
 * This is a trait to add pages capability to organizer classes
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
 * @author Jeroen Roos
 * @package Zoph
 */

use template\block;
use template\pager;
/**
 * Show page trait
 * A page is plaintext or zophCode that can be used to personalize parts
 * of the Zoph interface
 *
 * @author Jeroen Roos
 * @package Zoph
 */
trait showPage {

    public function getPageset() {
        if (!$this->get("pageset")) {
            throw new PageNoPagesetForObjectException();
        }
        $pageset=new pageset($this->get("pageset"));
        $pageset->lookup();

        return $pageset;
    }



    public function showOrig($num) {
        $pageset=$this->getPageset();
        $pagecount=$pageset->getPageCount();

        $first=($num==0);
        $last=($num==$pagecount - 1);
        $orig=$pageset->get("show_orig");
        return ($orig=="all" || ((($orig=="last" && $last) || ($orig=="first" && $first)) && ($orig!="never")));
    }

    public function getPage($request_vars, $num=1) {
        $pageset=$this->getPageset();

        $pagecount=$pageset->getPageCount();
        if ($pagecount==0) {
            throw new PagePagesetHasNoPagesException();
        }

        $pages=$pageset->getPages($num);
        $page=$pages[0];
        $page->lookup();

        $tpl=new block("page");
        $tpl->addPage($page);
        $pager=new pager($num, $pagecount, $pagecount, 1, 5, $request_vars, "_pageset_page");
        $tpl->addBlock($pager->getBlock());
        return $tpl;
    }

    /**
     * Is the page supposed to be on top?
     */
    public function showPageOnTop() {
        try {
            $pageset=$this->getPageset();
            return ($pageset->get("orig_pos") == "bottom");
        } catch (pageException $e) {
            return false;
        }
    }

    /**
     * Is the page supposed to be on the bottom?
     */
    public function showPageOnBottom() {
        try {
            $pageset=$this->getPageset();
            return ($pageset->get("orig_pos") == "top");
        } catch (pageException $e) {
            return false;
        }

    }
}
