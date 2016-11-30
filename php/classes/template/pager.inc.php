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

namespace template;

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

    public function __construct($current, $total, $numPages, $pageSize, $maxSize, $requestVars, $var) {
        $url=$_SERVER['PHP_SELF'];
        $pageNum = floor($current / $pageSize) + 1;
        $this->current=(string) $pageNum;

        $pageGroup=0;
        $pages[$pageGroup]=array();

        if ($current > 0) {
            $newOffset = max(0, $current - $pageSize);
            $this->pages[$pageGroup][translate("Prev")]= $url . "?" . update_query_string($requestVars, $var, $newOffset);
        }

        if ($numPages > 1) {
            $midPage = floor($maxSize / 2);
            $page = $pageNum - $midPage;
            if ($page <= 0) {
                $page = 1;
            }

            $lastPage = $page + $maxSize - 1;
            if ($lastPage > $numPages) {
                $page = $page - $lastPage + $numPages;
                if ($page <= 0) {
                    $page = 1;
                }
                $lastPage = $numPages;
            }

            if ($page > 1) {
                $this->pages[$pageGroup]["1"] = $url . "?" . update_query_string($requestVars, $var, 0);
            }

            $pages[++$pageGroup]=array();

            while ($page <= $lastPage) {
                $newOffset = ($page - 1) * $pageSize;
                $this->pages[$pageGroup][(string) $page] = $url . "?" . update_query_string($requestVars, $var, $newOffset);
                $page++;
            }

            $pages[++$pageGroup]=array();

            if ($page <= $numPages) {
                $this->pages[$pageGroup][(string) $numPages] = $url . "?" . update_query_string($requestVars, $var, ($numPages-1) * $pageSize);
            }
        }
        if ($total >  $current + $pageSize) {
            $newOffset = $current + $pageSize;
            $this->pages[$pageGroup][translate("Next")]= $url . "?" . update_query_string($requestVars, $var, $newOffset);
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
