<?php
/**
 * Place test
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
 * Test the place class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class placeTest extends ZophDataBaseTestCase {

    /**
     * Create place in the database
     * @dataProvider getPlaces();
     */
    public function testCreatePlace($id, $name, $parent) {
        $place=new place();
        $place->set("title",$name);
        $place->set("parent_place_id", $parent);
        $place->insert();
        $this->assertInstanceOf("place", $place);
        $this->assertEquals($place->getId(), $id);
    }
    /**
     * Test adding a photo to a place
     */
    public function testAddPhoto() {
        $photo=new photo(2);
        $photo->lookup();
        $place=new place(5);
        $place->lookup();

        $place->addPhoto($photo);

        $loc=$photo->getLocation();
        $this->assertInstanceOf("place", $loc);
        $this->assertEquals(5, $loc->getId());
    }

    /**
     * Test removing a photo from a place
     */
    public function testRemovePhoto() {
        $photo=new photo(2);
        $photo->lookup();
        $place=new place(4);
        $place->lookup();

        $place->removePhoto($photo);

        $loc=$photo->getLocation();
        $this->assertNull($loc);
    }

    /**
     * Test getting the address for a place
     */
    public function testGetAddress() {
        $place=new place(3);
        $place->set("address", "Addressline 1");
        $place->set("address2", "Addressline 2");
        $place->set("state", "NL");
        $place->set("zip", "1234");
        $place->set("country", "Netherlands");

        $place->update();
        $address=$place->getAddress();

        $this->assertInstanceOf("block", $address);
        $this->assertEquals("Addressline 1", $address->vars["lines"][0]);
        $this->assertEquals("Addressline 2", $address->vars["lines"][1]);
        $this->assertEquals("NL 1234", $address->vars["lines"][2]);
        $this->assertEquals("Netherlands", $address->vars["lines"][3]);
        
        
        $place->set("city", "City");
        $place->update();
        $address=$place->getAddress();
        $this->assertEquals("City, NL 1234", $address->vars["lines"][2]);
    }

    /**
     * Test getting the name for a place
     */
    public function testGetName() {
        $place=new place(3);
        $place->lookup();

        $title=$place->getName();

        $this->assertEquals("Netherlands", $title);
    }

    /**
     * Test getPhotoCount() & getTotalPhotoCount() functions
     * @dataProvider getPlaceCount()
     * @param int user id
     * @param int place id
     * @param int expected count
     * @param int expexcted total count (including sub-places)
     */
    public function testGetPhotoCount($user_id, $place_id, $exp_count, $exp_totalcount) {
        user::setCurrent(new user($user_id));
        $place=new place($place_id);
        $place->lookup();

        $count=$place->getPhotoCount();
        $this->assertEquals($exp_count, $count);

        $totalcount=$place->getTotalPhotoCount();
        $this->assertEquals($exp_totalcount, $totalcount);
    }

    /**
     * Test getting cover photo for place
     */
    public function testCoverPhoto() {
        user::setCurrent(new user(1));
        $photo=new photo(2);
        $photo->lookup();
        $place=new place(4);
        $place->lookup();
        
        $place->set("coverphoto", 2);

        $cover=$place->getCoverphoto();

        $this->assertEquals($photo, $cover);

        $cover=$place->getAutoCover("highest");

        $this->assertEquals($photo, $cover);
    }

    /**
     * Test getting autocover for a place
     */
    public function testAutoCover() {
        $photo=new photo(5);
        $photo->lookup();

        $place=new place(5);
        $place->lookup();

        $cover=$place->getAutoCover("highest");

        $this->assertEquals($photo, $cover);
    }

    /**
     * Test getting autocover for a user that cannot see all photos
     */
    public function testAutoCoverForUser() {
        user::setCurrent(new user(3));
        $photo=new photo(1);
        $photo->lookup();

        $place=new place(3);
        $place->lookup();

        $cover=$place->getAutoCover("highest");

        $this->assertEquals($photo, $cover);
        user::setCurrent(new user(1));
   }

    /**
     * Test getting autocover when current place does not have photos
     * and therefore recursively searches sub-places
     */
    public function testAutoCoverWithChildren() {
        $photo=new photo(5);
        $photo->lookup();

        $place=new place(2);
        $place->lookup();

        $cover=$place->getAutoCover("highest");

        $this->assertEquals($photo, $cover);
    }

    /**
     * Test timzone functions
     */
    public function testTZfunctions() {
        $place=new place(3);
        $place->lookup();
        $place->set("lat", 52);
        $place->set("lon", 5);

        $place->update();

        $tz=$place->guessTZ();

        $this->assertEquals("Europe/Amsterdam", $tz);

        $place->set("timezone", $tz);

        $place->setTzForChildren();

        $ams=new place(4);
        $ams->lookup();
        $rot=new place(5);
        $rot->lookup();

        $this->assertEquals("Europe/Amsterdam", $rot->get("timezone"));
        $this->assertEquals("Europe/Amsterdam", $ams->get("timezone"));
    }

    /**
     * Test getNear() function
     */
    public function testGetNear() {
        $ams=new place(5);
        $ams->lookup();

        $rot=new place(4);
        $rot->lookup();
        
        $bln=new place(7);
        $bln->lookup();
        
        $places=$ams->getNear(100);
        $place=$places[1];
        $place->lookup();
        $this->assertEquals(3, count($places));
        $this->assertEquals($rot->getId(), $place->getId());
        
        
        $places=$rot->getNear(1000);
        $place=$places[5];
        $place->lookup();
        $this->assertEquals(6, count($places));
        $this->assertEquals($bln->getId(), $place->getId());
    }

    /**
     * DataProvider for creating places
     */
    public function getPlaces() {
        return array(
            array(19, "City", 1),
            array(19, "Town", 4)
        );
    }

    /**
     * Data provider for testGetPhotoCount
     */
    public function getPlaceCount() {
        // user_id, place_id, count, totalcount
        return array(
            array(1, 1, 0, 12),
            array(1, 6, 1, 2),
            array(1, 7, 1, 1),
            array(1, 3, 1, 5),
            array(2, 3, 1, 1),
            array(2, 2, 0, 2)
        );
    }
}    
