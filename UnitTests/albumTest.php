<?php
/**
 * Test the album class
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

/**
 * Test class that tests the album class
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class albumTest extends ZophDataBaseTestCase {
    
    /**
     * Create Albums in the database
     * @dataProvider getAlbums();
     */
    public function testCreateAlbum($id, $name, $parent) {
        $album=new album();
        $album->set("album",$name);
        $album->set("parent_album_id", $parent);
        $album->insert();
        $this->assertInstanceOf("album", $album);
        $this->assertEquals($album->getId(), $id);
    }
    
    public function testSAcache() {
        album::setSAcache();
    }

    public function getAlbums() {
        return array(
            array(15, "TestAlbum1", 2),
            array(15, "TestAlbum2", 3)
        );
    }

}
