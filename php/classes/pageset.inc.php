<?php
/**
 * Pagesets are a set of pages.
 * You can associate an album, category, person or place with a pageset.
 * This means that the first page in the set is shown when calling this album, etc.
 * Through a pagination link, one can go to the other pages.
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
 * The pageset class groups a set of pages in a certain order
 * @author Jeroen Roos
 * @package Zoph
 */
class pageset extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="pageset";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("pageset_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("title");
    /** @var bool keep keys with insert. In most cases the keys are set
     *  by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="pageset.php?pageset_id=";

    /**
     * Create pageset
     * @param int If existing pageset is to be pulled from the db, the id has to be given
     * @return pageset
     */
    public function __construct($id = 0) {
        parent::__construct($id);
        $this->set("date", "now()");
    }

    /**
     * Update existing pageset in db
     */
    public function update() {
        $this->set("timestamp", "now()");
        parent::update();
        $this->lookup();
    }

    /**
     * Delete pageset from db
     * Also delete page-pageset relations
     */
    public function delete() {
        if (!$this->get("pageset_id")) {
            return;
        }
        parent::delete(array("pages_pageset"));
    }

    /**
     * Get an array of information to be displayed for this pageset
     */
    public function getDisplayArray() {
        return array(
            translate("title") => $this->get("title"),
            translate("date") => $this->get("date"),
            translate("updated") => $this->get("timestamp"),
            translate("created by", false) => $this->getUser()->getLink(),
            translate("show original page") => translate($this->get("show_orig"), 0),
            translate("position of original") => translate($this->get("orig_pos"), 0)
        );
    }

    /**
     * Get a dropdown to select what to do with the original (zoph default) page to
     * be displayed
     */
    public function getOriginalDropdown() {
        return template::createPulldown("show_orig", $this->get("show_orig"),
            array(
                "never" => translate("Never", 0),
                "first" => translate("On first page", 0),
                "last" => translate("On last page", 0),
                "all" => translate("On all pages", 0)
            )
        );
    }

    /**
     * Get the pages in this pageset
     * @param int Specific page to get instead of all
     */
    public function getPages($pagenum=null) {
        $qry=new select(array("pps" => "pages_pageset"));
        $qry->addFields(array("page_id"));
        $qry->where(new clause("pageset_id=:pagesetid"));
        $qry->addParam(new param(":pagesetid", $this->getId(), PDO::PARAM_INT));
        $qry->addOrder("page_order");
        if ($pagenum) {
            $qry->addLimit(1, (int) $pagenum);
        }
        return page::getRecordsFromQuery($qry);
    }

    /**
     * Get the number of pages in this pageset
     */
    public function getPageCount() {
        $qry=new select(array("pps" => "pages_pageset"));
        $qry->addFunction(array("count" => "COUNT(page_id)"));
        $qry->where(new clause("pageset_id=:pagesetid"));
        $qry->addParam(new param(":pagesetid", $this->getId(), PDO::PARAM_INT));
        return $qry->getCount();
    }

    /**
     * Add a page to this set
     * @param page Page to add
     * @todo If the page already exists in this pageset, it fails silently
     *       because, at this moment a page cannot be more than once in a pageset
     *       Someday, this should either give a nice error or this limitation
     *       should be removed.
     */
    public function addPage(page $page) {
        if (!$page->getOrder($this)) {
            $qry=new insert(array("pages_pageset"));
            $qry->addParam(new param(":pageset_id", $this->getId(), PDO::PARAM_INT));
            $qry->addParam(new param(":page_id", $page->getId(), PDO::PARAM_INT));
            $qry->addParam(new param(":page_order", $this->getMaxOrder() + 1, PDO::PARAM_INT));
            $qry->execute();
        }
    }

    /**
     * Remove a page from this pageset
     * @param page Page to remove
     */
    public function removePage(page $page) {
        $qry=new delete(array("pages_pageset"));
        $where=new clause("pageset_id=:pagesetid");
        $where->addAnd(new clause("page_id=:pageid"));
        $qry->addParam(new param(":pagesetid", $this->getId(), PDO::PARAM_INT));
        $qry->addParam(new param(":pageid", $page->getId(), PDO::PARAM_INT));
        $qry->where($where);

        $qry->execute();
    }

    /**
     * Move a page up in the order list
     * @param page Page to move up
     */
    public function moveUp(page $page) {
        $order=$page->getOrder($this);
        if ($order>=2) {
            $currentOrder=new param(":curorder", $order, PDO::PARAM_INT);
            $newOrder=new param(":neworder", $this->getPrevOrder($order), PDO::PARAM_INT);
            $pageId=new param(":pageid", $page->getId(), PDO::PARAM_INT);

            $this->move($currentOrder, $newOrder, $pageId);
        }
    }

    /**
     * Move a page down in the order list
     * @param page Page to move down
     */
    public function moveDown(page $page) {
        $order=$page->getOrder($this);
        $max=$this->getMaxOrder();
        if ($order!=0 && $order<$max) {
            $currentOrder=new param(":curorder", $order, PDO::PARAM_INT);
            $newOrder=new param(":neworder", $this->getNextOrder($order), PDO::PARAM_INT);
            $pageId=new param(":pageid", $page->getId(), PDO::PARAM_INT);

            $this->move($currentOrder, $newOrder, $pageId);
        }
    }

    /**
     * Move a page up or down in a pageset
     * First, it changes the page that has the new order for the page we want to move
     * to the old order for that page.
     * For example, if we have a pageset with 2 pages, page 1 and 2, in that order:
     * pageId = 1, order = 1
     * pageId = 2, order = 2
     * [step 1]
     * We are going to move page 2 up, then after the first action, it will look like this:
     * pageId = 1, order = 1
     * pageId = 2, order = 1
     * [step 2]
     * Then finally, we update the order for the page we are actually moving:
     * pageId = 1, order = 2
     * pageId = 2, order = 1
     * @param param currentOrder: a database parameter for the current order
     * @param param newOder: a database parameter for the new order
     * @param param pageId: a database parameter for the pageId.
     */
    private function move(param $currentOrder, param $newOrder, param $pageId) {
        $pagesetId=new param(":pagesetid", $this->getId(), PDO::PARAM_INT);

        // [step 1]
        $qry=new update(array("pages_pageset"));
        $qry->addSet("page_order", "curorder");
        $where=new clause("page_order=:neworder");
        $where->addAnd(new clause("pageset_id=:pagesetid"));
        $qry->where($where);
        $qry->addParams(array($currentOrder, $newOrder, $pagesetId));

        $qry->execute();

        // [step 2]
        $qry=new update(array("pages_pageset"));
        $qry->addSet("page_order", "neworder");
        $where=new clause("page_id=:pageid");
        $where->addAnd(new clause("pageset_id=:pagesetid"));
        $qry->where($where);
        $qry->addParams(array($newOrder, $pageId, $pagesetId));

        $qry->execute();
    }

    /**
     * Get the highest used page_order value for this pageset
     * @return int maximum page_order
     */
    private function getMaxOrder() {
        $qry=new select(array("pps" => "pages_pageset"));
        $qry->addFunction(array("max_order" => "MAX(page_order)"));
        $qry->where(new clause("pageset_id=:pagesetid"));
        $qry->addParam(new param(":pagesetid", $this->getId(), PDO::PARAM_INT));

        $stmt=$qry->execute();
        return intval($stmt->fetchColumn());
    }

    /**
     * Get Next order
     * If pages have been deleted, the page_order field may no longer
     * be nicely numbered 1, 2, 3, etc. but there may be holes in the list
     * so this function and getPrevOrder() determine the next or previous
     * value of page_order.
     * @param int Get the next order after...
     */
    private function getNextOrder($order) {
        $qry=new select(array("pps" => "pages_pageset"));
        $qry->addFunction(array("next_order" => "MIN(page_order)"));
        $where=new clause("pageset_id=:pagesetid");
        $where->addAnd(new clause("page_order>:order"));
        $qry->where($where);
        $qry->addParam(new param(":pagesetid", $this->getId(), PDO::PARAM_INT));
        $qry->addParam(new param(":order", $order, PDO::PARAM_INT));

        $stmt=$qry->execute();
        return intval($stmt->fetchColumn());
    }

    /**
     * Get previous order
     * If pages have been deleted, the page_order field may no longer
     * be nicely numbered 1, 2, 3, etc. but there may be holes in the list
     * so this function and getiNextOrder() determine the next or previous
     * value of page_order.
     * @param int Get the previous order before...
     */
    private function getPrevOrder($order) {
        $qry=new select(array("pps" => "pages_pageset"));
        $qry->addFunction(array("prev_order" => "MAX(page_order)"));
        $where=new clause("pageset_id=:pagesetid");
        $where->addAnd(new clause("page_order<:order"));
        $qry->where($where);
        $qry->addParam(new param(":pagesetid", $this->getId(), PDO::PARAM_INT));
        $qry->addParam(new param(":order", $order, PDO::PARAM_INT));

        $stmt=$qry->execute();
        return intval($stmt->fetchColumn());
    }

    /**
     * Get the user who created this pageset
     * @return user the user
     */
    public function getUser() {
        $user = new user($this->get("user"));
        $user->lookup();
        return $user;
    }

    /**
     * Get table of pagesets
     * @param array pagesets to put in the table (default: all)
     * @return block template block with all pagesets
     */
    public static function getTable(array $pagesets=null) {
        if (!$pagesets) {
           $pagesets=pageset::getAll();
        }
        $lpagesets=array();
        foreach ($pagesets as $pageset) {
            $pageset->lookup();
            $lpagesets[]=$pageset;
        }
        return new block("pagesets", array(
            "pagesets" => $lpagesets
        ));
    }
}
