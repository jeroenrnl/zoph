<?php
/**
 * Pager class
 * Displays a list of pages, usually at the bottom of a page, to navigate to different pages
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

/**
 * Pager class
 * Displays a list of pages, usually at the bottom of a page, to navigate to different pages
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class pager {
    private $current=0;
    private $pages=array();

    public function __construct($current, $total, $num_pages, $page_size, $max_size, $request_vars, $var) {
        $url=$_SERVER['PHP_SELF'];
        $page_num = floor($current / $page_size) + 1;
        $this->current=(string) $page_num;

        $pageGroup=0;
        $pages[$pageGroup]=array();

        if ($current > 0) {
            $new_offset = max(0, $current - $page_size);
            $this->pages[$pageGroup][translate("Prev")]= $url . "?" . update_query_string($request_vars, $var, $new_offset);
        }

        if ($num_pages > 1) {
            $mid_page = floor($max_size / 2);
            $page = $page_num - $mid_page;
            if ($page <= 0) {
                $page = 1;
            }

            $last_page = $page + $max_size - 1;
            if ($last_page > $num_pages) {
                $page = $page - $last_page + $num_pages;
                if ($page <= 0) {
                    $page = 1;
                }
                $last_page = $num_pages;
            }

            if ($page > 1) {
                $this->pages[$pageGroup]["1"] = $url . "?" . update_query_string($request_vars, $var, 0);
            }

            $pages[++$pageGroup]=array();

            while ($page <= $last_page) {
                $new_offset = ($page - 1) * $page_size;
                $this->pages[$pageGroup][(string) $page] = $url . "?" . update_query_string($request_vars, $var, $new_offset);
                $page++;
            }

            $pages[++$pageGroup]=array();

            if ($page <= $num_pages) {
                $this->pages[$pageGroup][(string) $num_pages] = $url . "?" . update_query_string($request_vars, $var, ($num_pages-1) * $page_size);
            }
        }
        if ($total >  $current + $page_size) {
            $new_offset = $current + $page_size;
            $this->pages[$pageGroup][translate("Next")]= $url . "?" . update_query_string($request_vars, $var, $new_offset);
        }
    }

    public function __toString() {
        return (string) $this->getBlock();
    }

    public function getBlock() {
        return new block("pager", array(
            "pages"     => $this->pages,
            "current"   => $this->current
        ));
    }
}
