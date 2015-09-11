<?php
/**
 * Page class
 * A page is plaintext or zophCode that can be used to personalize parts
 * of the Zoph interface
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
 * Page class
 * A page is plaintext or zophCode that can be used to personalize parts
 * of the Zoph interface
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class page extends zophTable {
    /** @var string The name of the database table */
    protected static $table_name="pages";
    /** @var array List of primary keys */
    protected static $primary_keys=array("page_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("title");
    /** @var bool keep keys with insert. In most cases the keys are set
      * by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="page.php?page_id=";

    /**
     * Insert a new page into the db
     */
    public function insert() {
        $this->set("date","now()");
        parent::insert();
        $this->lookup();
    }

    /**
     * Update an existing page in the db
     */
    public function update() {
        $this->set("timestamp","now()");
        parent::update();
        $this->lookup();
    }

    /**
     * Delete a page from the db
     */
    public function delete() {
        if (!$this->getId()) {
            return;
        }
        parent::delete(array("pages_pageset"));
    }

    /**
     * Return an array of fields to display
     * @todo Returns HTML
     * @return array array of fields
     */
    public function getDisplayArray() {
        $zophcode = new zophCode\parser($this->get("text"));
        $text="<div class='page-preview'>" . $zophcode . "</div>";

        return array(
            translate("title") => e($this->get("title")),
            translate("date") => e($this->get("date")),
            translate("updated") => e($this->get("timestamp")),
            translate("text") => $text
        );
    }

    /**
     * Parse Zophcode
     * @return string parsed code
     */
    public function display() {
        $zophcode = new zophCode\parser($this->get("text"));
        return $zophcode;
    }

    /**
     * Get the position of a page in a pageset
     * @param pageset The pageset to look in
     */
    public function getOrder(pageset $pageset) {
        $sql = "select page_order from " . DB_PREFIX . "pages_pageset" .
            " where pageset_id=" . $pageset->getId() . " and " .
            " page_id=" . $this->getId() . " limit 1";
        $result=query($sql, "Could not get current order");
        if (num_rows($result)) {
            return intval(result($result, 0));
        } else {
            return false;
        }
    }

    public function getPagesets() {
        $sql = "select pageset_id from " . DB_PREFIX . "pages_pageset" .
            " where page_id = " . $this->getId();
        return pageset::getRecordsFromQuery($sql);
    }

    /**
     * Get a table of pages
     * @param Array array of pages to show
     * @param pageset Pageset to display
     * @return block template to display
     */
    public static function getTable(array $pages = null, pageset $pageset=null) {
        if (is_null($pages)) {
            $pages=page::getAll();
        }
        $lpages=array();
        foreach ($pages as $page) {
            $page->lookup();
            $lpages[]=$page;
        }

        return new block("pages", array(
            "pages"     => $lpages,
            "pageset"   => $pageset
        ));
    }

}
