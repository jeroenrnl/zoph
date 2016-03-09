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
     * Test delete() function
     */
    public function testDelete() {
        $photo_id=5;
        $photo=new photo($photo_id);
        $photo->lookup();


        $place=new place();
        $place->set("title", "TESTplace");
        $place->set("parent_place_id", 1);
        $place->insert();

        $place_id=$place->getId();

        $place->addPhoto($photo);

        $loc=$photo->getLocation();
        $this->assertEquals($place_id, $loc->getId());

        $person=new person(5);
        $person->lookup();
        $person->set("home_id", $place_id);
        $person->update();


        $place->delete();
        $this->assertEmpty(place::getByName("TESTplace"));

        $loc=$photo->getLocation();
        $this->assertNull($loc);

        $person->lookup();

        $this->assertEmpty($person->get("home"));

    }

    /**
     * Test getChildren function
     * @dataProvider getChildrenData
     */
    public function testGetChildren($id, $order, $expected) {
        $place=new place($id);
        $place->lookup();

        $children=$place->getChildren($order);

        $ids=array();
        foreach ($children as $child) {
            $ids[]=$child->getId();
        }

        // We can't test order for random, so sort them first
        if ($order=="random") {
            sort($ids);
        }

        $this->assertEquals($expected, $ids);
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
     * Test getPhotos()
     * @dataProvider getPhotosForPlace
     */
    public function testGetPhotos($user, $loc_id, $expected) {
        user::setCurrent(new user($user));

        $loc=new place($loc_id);
        $photos=$loc->getPhotos();

        $ids=array();
        foreach ($photos as $photo) {
            $ids[]=$photo->getId();
        }

        $this->assertEquals($expected, $ids);
    }


    /**
     * Test getDetails()
     / @dataProvider getDetails();
     */
    public function testGetDetails($user,$place_id, $subplace, array $exp_details) {
        user::setCurrent(new user($user));
        $place=new place($place_id);
        $place->lookup();

        $details=$place->getDetails();
        $this->assertEquals($exp_details, $details);

        user::setCurrent(new user(1));
    }

    /**
     * Test getDetailsXML()
     / @dataProvider getDetails();
     */
    public function testGetDetailsXML($user,$place_id, $subplace, array $exp_details) {
        user::setCurrent(new user($user));
        $place=new place($place_id);
        $place->lookup();
        $details=$place->getDetailsXML();

        $timezone=array("e", "I", "O", "P", "T", "Z");
        $timeformat=str_replace($timezone, "", conf::get("date.timeformat"));
        $timeformat=trim(preg_replace("/\s\s+/", "", $timeformat));
        $format=conf::get("date.format") . " " . $timeformat;

        $oldest=new Time($exp_details["oldest"]);
        $disp_oldest=$oldest->format($format);

        $newest=new Time($exp_details["newest"]);
        $disp_newest=$newest->format($format);

        $first=new Time($exp_details["first"]);
        $disp_first=$first->format($format);

        $last=new Time($exp_details["last"]);
        $disp_last=$last->format($format);


        $expectedXML=sprintf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                      <details>
                        <request>
                          <class>place</class>
                          <id>%s</id>
                        </request>
                        <response>
                          <detail>
                            <subject>title</subject>
                            <data>In this place:</data>
                          </detail>
                          <detail>
                            <subject>count</subject>
                            <data>%s photos</data>
                          </detail>
                          <detail>
                            <subject>taken</subject>
                            <data>taken between %s and %s</data>
                          </detail>
                          <detail>
                            <subject>modified</subject>
                            <data>last changed from %s to %s</data>
                          </detail>
                          <detail>
                            <subject>rated</subject>
                            <data>rated between %s and %s and an average of %s</data>
                          </detail>
                          <detail>
                          <subject>children</subject>
                            <data>%s sub-places</data>
                          </detail>
                        </response>
                      </details>",
                       $place_id, $exp_details["count"],$disp_oldest, $disp_newest, $disp_first, $disp_last,  $exp_details["lowest"], $exp_details["highest"], $exp_details["average"],$subplace);

        $this->assertXmlStringEqualsXmlString($expectedXML, $details);

        user::setCurrent(new user(1));
    }

    /**
     * Test getTopN() function
     * @dataProvider getTopNData();
     */
    public function testGetTopN($user, $expected) {
        user::setCurrent(new user($user));
        $pl_ids=array();
        $topN=place::getTopN();

        foreach ($topN as $place) {
            $pl_ids[]=$place["id"];
        }
        $this->assertEquals($expected, $pl_ids);
        user::setCurrent(new user(1));
    }


    /**
     * Test getPhotoCount() & getTotalPhotoCount() functions
     * @dataProvider getPhotoCount()
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
        user::setCurrent(new user(1));
    }

    /**
     * Test getCount() static function
     * @dataProvider getPlaceCount
     */
    public function testGetCount($user_id, $expected) {
        user::setCurrent(new user($user_id));

        $count=place::getCount();

        $this->assertEquals($expected, $count);


        user::setCurrent(new user(1));
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
    public function getPhotoCount() {
        // user_id, place_id, count, totalcount
        return array(
            array(1, 1, 0, 12),
            array(1, 6, 1, 2),
            array(1, 7, 1, 1),
            array(1, 3, 1, 5),
            array(5, 3, 1, 1),
            array(5, 2, 0, 2)
        );
    }

    /**
     * Data provider for testGetCount
     */
    public function getPlaceCount() {
        // user_id, count
        return array(
            array(1, 18),
            array(5, 2),
            array(3, 2),
            array(4, 4)
        );
    }

    public function getChildrenData() {
        return array(
            array(3, null, array(5,4)),
            array(3, "oldest", array(4,5)),
            array(3, "newest", array(4,5)),
            array(11, "first", array(12,16,14)),
            array(11, "lowest", array(14)),
            array(11, "random", array(12,14,16))
        );
    }

    /**
     * dataProvider function
     * @return user, place, array(count, oldest, newest, first, last, highest, average)
     */
    public function getDetails() {
        return array(
            array(1,4,"no",array(
                "count" 	=> "2",
                "oldest" 	=> "2014-01-02 00:01:00",
                "newest" 	=> "2014-01-03 00:01:00",
                "first" 	=> "2014-01-01 23:01:00",
                "last" 	    => "2014-01-02 23:01:00",
                "lowest" 	=> "4.3",
                "highest" 	=> "5.0",
                "average" 	=> "4.63"
            )),
            array(1,3,2, array(
                "count" 	=> "1",
                "oldest" 	=> "2014-01-01 00:01:00",
                "newest" 	=> "2014-01-01 00:01:00",
                "first" 	=> "2013-12-31 23:01:00",
                "last" 	    => "2013-12-31 23:01:00",
                "lowest" 	=> "7.5",
                "highest" 	=> "7.5",
                "average" 	=> "7.50",
            )),
            array(5,3,"no",array(
                "count" 	=> "1",
                "oldest" 	=> "2014-01-01 00:01:00",
                "newest" 	=> "2014-01-01 00:01:00",
                "first" 	=> "2013-12-31 23:01:00",
                "last" 	    => "2013-12-31 23:01:00",
                "lowest" 	=> "7.5",
                "highest" 	=> "7.5",
                "average" 	=> "7.50",
            )),
        );
    }

    /**
     * dataProvider function
     * @return array userid, topN
     */
    public function getTopNData() {
        return array(
            array(1,array(5,18,4,7,14)),
            array(5,array(7,3))
        );
    }

    public function getPhotosForPlace() {
        return array(
            array(1, 4, array(2,3)),
            array(1, 5, array(4,5)),
            array(8, 4, array(2)),
            array(3, 6, array()),
            array(4, 4, array(2))
        );
    }


}
