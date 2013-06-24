<?php
/**
 * A Unit Test for the anonymousUser object.
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
require_once "php/UnitTests/testSetup.php";
/**
 * Test class for anonymousUser.
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class anonymousUserTest extends zophDatabaseTestCase {
    /** @var anonymousUser */
    protected $object;

    /**
     * Sets up the fixture
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new anonymousUser;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * Test getId() method
     */
    public function testGetId() {
        $id=$this->object->getId();
        $this->assertEquals($id,0);
    }

    /**
     * Test lookup_person() method
     */
    public function testLookup_person() {
        $this->assertFalse($this->object->lookup_person());
    }

    /**
     * Test lookup_prefs() method
     */
    public function testLookup_prefs() {
        $this->assertFalse($this->object->lookup_prefs());
    }

    /**
     * Test is_admin() method.
     */
    public function testIs_admin() {
        $this->assertFalse($this->object->is_admin());
    }

    /**
     * Test get_lastnotify() method.
     */
    public function testGet_lastnotify() {
        $ln=$this->object->get_lastnotify();
        $this->assertEquals($ln,0);
    }

    /**
     * Test getLink() method.
     */
    public function testGetLink() {
        $this->assertFalse($this->object->getLink());
    }

    /**
     * Test getURL() method.
     */
    public function testGetURL() {
        $this->assertFalse($this->object->getURL());
    }

    /**
     * Test getName() method.
     */
    public function testGetName() {
        $name=$this->object->getName();
        $this->assertEquals($name,"Anonymous User");
        
    }

    /**
     * Test get_groups() method.
     */
    public function testGet_groups() {
        $g=$this->object->get_groups();
        $this->assertEquals($g,0);
    }

    /**
     * Test get_album_permissions() method.
     * @dataProvider getAlbumIds
     */
    public function testGet_album_permissions($id, $perm) {
        $ap=$this->object->get_album_permissions($id);
        $this->assertEquals($ap,$perm);
    }

    /**
     * Test get_permissions_for_photo() method.
     * @dataProvider getPhotoIds
     */
    public function testGet_permissions_for_photo($id) {
        $pp=$this->object->get_permissions_for_photo($id);
        $this->assertInstanceOf("group_permissions",$pp);
        $this->assertEquals($pp->get("album_id"), 0);
        $this->assertEquals($pp->get("group_id"), 0);
    }

    /**
     * Test getDisplayArray() method.
     */
    public function testGetDisplayArray() {
        $da=$this->object->getDisplayArray();
        $this->assertInternalType("array", $da);
        $this->assertEmpty($da);
    }

    /**
     * Test load_language() method.
     * @dataProvider getTrueFalse
     */
    public function testLoad_language($force) {
        $lang=$this->object->load_language($force);
        $this->assertNull($lang);
    }


    /**
     * Return a list of album id's used for testing
     * @todo should actially do something
     */
    public function getAlbumIds() {
        return array(
            array(0,null),
            array(1,null),
            array(2,null),
            array(3,null),
            array(4,null),
            array(5,null),
            array(6,null),
            array(7,null),
            array(8,null),
            array(9,null),
            array(10,null),
        );
    }
    /**
     * Return a list of photo id's used for testing
     * @todo should actially do something
     */
    public function getPhotoIds() {
        return array(
            array(0),
            array(1),
            array(2),
            array(3),
            array(4),
            array(5),
            array(6),
            array(7),
            array(8),
            array(9),
            array(10),
        );
    }

    /**
     * Return true and false to test settings
     */
    public function getTrueFalse() {
        return array(
            array(True),
            array(False)
        );
    }

}
?>
