<?php
/**
 * Pageset test
 * Test the working of the pageset class
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
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
require_once "testSetup.php";
/**
 * Test the pageset class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class pagesetTest extends PHPUnit_Framework_TestCase {
    static $psIds=array();
    static $pIds=array();

    public static function setUpBeforeClass() {
        list($psIds, $pIds) = helpers::createPagesPagesets(10);
        self::$psIds=$psIds;
        self::$pIds=$pIds;
    }

    public static function tearDownAfterClass() {
        $pages=page::getRecords();
        foreach ($pages as $page) {
            $page->delete();
        }

        $pagesets=pageset::getRecords();
        foreach ($pagesets as $pageset) {
            $pageset->delete();
        }

    }


    public function testCreateDelete() {
        $pageset = new pageset();

        $pageset->set("title", "Test Pageset");

        $pageset->insert();
        $id=$pageset->getId();

        unset($pageset);
        $pagesets = pageset::getRecords();
        $this->assertCount(3, $pagesets);

        $pageset=new pageset($id);
        $pageset->lookup();

        $this->assertInstanceOf("pageset", $pageset);
        $this->assertEquals("Test Pageset", $pageset->get("title"));

        $pageset->delete();

        $pagesets = pageset::getRecords();
        $this->assertCount(2, $pagesets);
    }

    public function testUpdate() {
        $pageset = new pageset();

        $pageset->set("title", "Test Pageset");

        $pageset->insert();
        $id=$pageset->getId();

        unset($pageset);
        $pageset=new pageset($id);
        $pageset->lookup();
        $this->assertEquals("Test Pageset", $pageset->get("title"));
        $pageset->set("title", "Updated");
        $pageset->update();

        unset($pageset);
        $pageset=new pageset($id);
        $pageset->lookup();
        $this->assertEquals("Updated", $pageset->get("title"));
    }

    /**
     * Test the getOrder() function
     * also tests getPageCount() function
     * @dataProvider getPages
     */
    public function testGetPages($pagesetId, array $pageIds) {
        $pageset=new pageset(self::$psIds[$pagesetId]);
        $pages=$pageset->getPages();

        $actPageIds=array();
        foreach ($pages as $page) {
            $actPageIds[]=$page->getId();
        }

        $expPageIds=array();
        foreach ($pageIds as $pageId) {
            $expPageIds[]=self::$pIds[$pageId];
        }

        $this->assertEquals($expPageIds, $actPageIds);
        $this->assertEquals($pageset->getPageCount(), count($pageIds));

    }

    /**
     * Test the addPage() and removePage() function
     */
    public function testAddRemove() {
        $pageset = new pageset(self::$psIds[1]);
        $pageset->lookup();
        $page=new page(self::$pIds[5]);

        $pageset->addPage($page);

        $pages=$pageset->getPages();

        $actPageIds=array();
        foreach ($page as $paget) {
            $actPageIds[$page->getId()]=$page->getId();
        }

        $this->assertArrayHasKey(self::$pIds[5], $actPageIds);

        $pageset->removePage($page);

        $pages=$pageset->getPages();

        $actPageIds=array();
        foreach ($pages as $page) {
            $actPageIds[$page->getId()]=$page->getId();
        }

        $this->assertArrayNotHasKey(self::$pIds[5], $actPageIds);
    }

    /**
     * Move pages up and down in the order list for a pageset
     * tests the moveUp() and moveDown() functions as well as the
     * private functions getNextOrder(), getPrevOrder() and getMaxOrder()
     * Also tests the "get a specific page" function from getPages()
     */
    public function testMoveUpDown() {
        $pageset=new pageset(self::$psIds[0]);
        $pageset->lookup();

        // We take the first page, this page is the first page in both
        // pageset 1 and 2
        $page=new page(self::$pIds[0]);
        $page->lookup();

        // Check if it really the first
        $this->assertEquals(1, $page->getOrder($pageset));

        // And move down
        $pageset->moveDown($page);

        // Check if it's number 2 now
        $this->assertEquals(2, $page->getOrder($pageset));

        // The other way around, if we request the second page, do we get
        // this page back?
        $this->assertEquals(self::$pIds[0], $pageset->getPages(1)[0]->getId());

        // Check if page two has moved up
        $this->assertEquals(self::$pIds[1], $pageset->getPages(0)[0]->getId());

        $pageset2=new pageset(self::$psIds[1]);
        $pageset2->lookup();

        // Check if page 1 is still at the top in the other pageset
        $this->assertEquals(self::$pIds[0], $pageset2->getPages(0)[0]->getId());

        // Move the page back up
        $pageset->moveUp($page);
        // And check if both pages have returned to their original position
        $this->assertEquals(self::$pIds[0], $pageset->getPages(0)[0]->getId());
        $this->assertEquals(self::$pIds[1], $pageset->getPages(1)[0]->getId());

        // Check if page 1 is still at the top in the other pageset
        $this->assertEquals(self::$pIds[0], $pageset2->getPages(0)[0]->getId());
    }

    public function getPages() {
        return array(
            array(0, array(0,1,3,5,7,9)),
            array(1, array(0,1,2,4,6,8))
        );
    }
}
