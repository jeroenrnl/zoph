<?php
/**
 * Page test
 * Test the working of the page class
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
 * Test the page class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class pageTest extends PHPUnit_Framework_TestCase {
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
        $page = new page();

        $page->set("title", "Test Page");
        $page->set("text", "[b]bold[/b], [i]italic[/i]");

        $page->insert();
        $id=$page->getId();

        unset($page);
        $pages = page::getRecords();
        $this->assertCount(11, $pages);

        $page=new page($id);
        $page->lookup();

        $this->assertInstanceOf("page", $page);
        $this->assertEquals("Test Page", $page->get("title"));

        $page->delete();

        $pages = page::getRecords();
        $this->assertCount(10, $pages);
    }

    public function testUpdate() {
        $page = new page();

        $page->set("title", "Test Page");
        $page->set("text", "[b]bold[/b], [i]italic[/i]");

        $page->insert();
        $id=$page->getId();

        unset($page);
        $page=new page($id);
        $page->lookup();
        $this->assertEquals("Test Page", $page->get("title"));
        $page->set("title", "Updated");
        $page->update();

        unset($page);
        $page=new page($id);
        $page->lookup();
        $this->assertEquals("Updated", $page->get("title"));
    }

    /**
     * Test the getOrder() function
     * @dataProvider getPages
     */
    public function testGetOrder($pageId, $pagesetId, $expOrder) {

        $page=new page(self::$pIds[$pageId]);
        $pageset=new pageset(self::$psIds[$pagesetId]);

        $actOrder=$page->getOrder($pageset);

        $this->assertEquals($expOrder, $actOrder);
    }

    /**
     * Test the getPagesets() function
     * @dataProvider getPagesetsForPages
     */
    public function testGetPagesets($pageId, array $pagesetIds) {
        $page=new page(self::$pIds[$pageId]);
        $pagesets=$page->getPagesets();

        $actPagesetIds=array();
        foreach ($pagesets as $pageset) {
            $actPagesetIds[]=$pageset->getId();
        }

        $expPagesetIds=array();
        foreach ($pagesetIds as $pagesetId) {
            $expPagesetIds[]=self::$psIds[$pagesetId];
        }

        $this->assertEquals(sort($expPagesetIds), sort($actPagesetIds));
    }

    public function getPagesetsForPages() {
        return array(
            array(0,array(0,1)),
            array(1,array(1,0)),
            array(3,array(0)),
            array(8,array(1)),
        );
    }

    public function getPages() {
        return array(
            array(0,0,1),
            array(0,1,1),
            array(1,0,2),
            array(2,0,false),
            array(2,1,3),
            array(8,0,false)
        );
    }
}
