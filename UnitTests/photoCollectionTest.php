<?php
/**
 * This tests the photo collection class
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

use PHPUnit\Framework\TestCase;
use photo\collection;
use web\request;

/**
 * Test the photo collection class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class photoCollectionTest extends TestCase {
    public function testCreate() {
        $collection = new collection();

        $this->assertInstanceOf("photo\collection", $collection);
    }

    public function testCreateFromArray() {
        $photos=[ 1 => new photo(1), 2 => new photo(2), 3=> new photo(3) ];
        $collection=collection::createFromArray($photos);

        $this->assertCount(3, $collection);
        $this->assertInstanceOf("photo\collection", $collection);
    }

    /**
     * Test removeNoValidTZ() function
     * @dataProvider getCollection()
     */
    public function testRemoveNoValidTZ($collection) {
        $collection->removeNoValidTZ();

        $this->assertCount(5, $collection);
        $this->dropCollection($collection);
    }

    /**
     * Test removeWithLatLon() function
     * @dataProvider getCollection()
     */
    public function testRemoveWithLatLon($collection) {
        $collection->removeWithLatLon();

        $this->assertCount(3, $collection);
        $this->dropCollection($collection);
    }

    /**
     * Test getSubsetForGeotagging() function
     * @dataProvider getCollection()
     */
    public function testGetSubsetForGeotagging($collection) {

        $subset=$collection->getSubsetForGeoTagging(array("first"), 2);

        $this->assertCount(2, $subset);
        $this->assertArrayHasKey($collection[0]->getId(), $subset);
        $this->assertArrayHasKey($collection[1]->getId(), $subset);

        $subset=$collection->getSubsetForGeoTagging(array("last"), 2);

        $this->assertCount(2, $subset);

        $tmpColl=clone $collection;
        $last1=$tmpColl->pop()->getId();
        $last2=$tmpColl->pop()->getId();
        $this->assertArrayHasKey($last1, $subset);
        $this->assertArrayHasKey($last2, $subset);


        // check overlap
        $subset=$collection->getSubsetForGeoTagging(array("first", "last"), 6);
        $this->assertCount(8, $subset);

        $subset=$collection->getSubsetForGeoTagging(array("random"), 2);

        $this->assertCount(2, $subset);

        $this->dropCollection($collection);

    }

    public function getCollection() {
        $collection=new collection();

        $photos=array(
            array("photo1.jpg", "Europe/Amsterdam", 51.22, 4.55),
            array("photo2.jpg", "Jupiter/Europa", null, null),
            array("photo3.jpg", null, null, null),
            array("photo4.jpg", "Europe/London", 45, 0),
            array("photo5.jpg", null, 23, 58.5),
            array("photo6.jpg", "Europe/Amsterdam", 51.22, 4.55),
            array("photo7.jpg", "Europe/Amsterdam", null, null),
            array("photo8.jpg", "Europe/Amsterdam", 51.14, 5.5)
        );
        foreach ($photos as $photoData) {
            $place=new place();
            $place->set("parent_place_id", 0);
            $place->set("title", "TEST for " . $photoData[1]);
            $place->set("timezone", $photoData[1]);
            $place->insert();

            $photo=new photo();
            $photo->set("name", $photoData[0]);
            $photo->set("location_id", $place->getId());
            $photo->set("lat", $photoData[2]);
            $photo->set("lon", $photoData[3]);
            $photo->insert();
            $photo->lookup();
            $collection[]=$photo;
        }
        return array(array($collection));
    }

    public function dropCollection(collection $collection) {
        foreach ($collection as $photo) {
            $photo->location->delete();
            $photo->delete();
        }
    }

    /**
     * test createFromConstraints() function
     * @dataProvider getPhotos
     */
    public function testCreateFromConstraints($vars, $offset, $rows, $userId, $expPhotoIds) {
        user::setCurrent(new user($userId));
        $actPhotoIds=array();

        $photos=collection::createFromConstraints($vars);

        $photos=$photos->subset($offset, $rows);

        foreach ($photos as $photo) {
            $actPhotoIds[]=$photo->getId();
        }
        $this->assertEquals($expPhotoIds, $actPhotoIds);

        user::setCurrent(new user(1));
    }

    /**
     * test createFromRequest() function
     * @dataProvider getPhotos
     */
    public function testCreateFromRequest($vars, $offset, $rows, $userId, $expPhotoIds) {
        user::setCurrent(new user($userId));
        $actPhotoIds=array();

        $request = new request(array("GET" => $vars));

        $photos=collection::createFromRequest($request);

        $photos=$photos->subset($offset, $rows);

        foreach ($photos as $photo) {
            $actPhotoIds[]=$photo->getId();
        }
        $this->assertEquals($expPhotoIds, $actPhotoIds);

        user::setCurrent(new user(1));
    }

    public function testGetRandomPhoto() {
        $actPhotoIds=array();

        $vars=array(
                "_random"           => 1,
                "album_id#0"        => 4,
                "rating#0"          => 9,
                "_rating#0-conj"    => "or",
                "_rating#0-op"      => ">"
        );

        $request = new request(array("GET" => $vars));

        $photos=collection::createFromRequest($request);

        $this->assertCount(1, $photos);
        $photo=$photos->pop();
        $this->assertTrue(in_array($photo->getId(), array(2, 5, 9, 10)), true);

    }
    //================== DATA PROVIDERS =======================

    public function getPhotos() {
        // $vars, $offset, $rows, $user, $photoIds
        return array(
            array(array(), 0, 999, 1, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "_order"            => "name"
                ), 0, 999, 1, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "_order"            => "name",
                    "_dir"              => "DESC"
                ), 0, 999, 1, array(12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1)),
            array(array(
                    "PHPTEST"           => "This should be ignored"
                ), 0, 999, 1, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "album_id"          => 6
                ), 0, 999, 1, array(3, 4, 5)),
            array(array(
                    "album_id#1"        => 2,
                    "album_id#2"        => 11,
                    "_album_id#2-conj"  => "and",
                    "_album_id#2-op"    => "!="
                ), 0, 999, 1, array(1)),
            array(array(
                    "album_id#1"        => 5,
                    "category_id#1"     => 2,
                    "_cateogory_id#1-conj"  => "and",
                    "_category_id#1-op" => "!="
                ), 0, 999, 1, array(5)),
            array(array(
                    "album_id#1"        => 6,
                    "album_id#2"        => 5,
                    "_album_id#2-conj"  => "and"
                ), 0, 999, 1, array(3, 4, 5)),
            array(array(
                    "album_id#0"        => 4,
                    "rating#0"          => 9,
                    "_rating#0-conj"    => "or",
                    "_rating#0-op"      => ">"
                ), 0, 999, 1, array(2, 5, 9, 10)),
            array(array(
                    "album_id#0"        => 6,
                    "_album_id#0-children"  => "yes",
                ), 0, 999, 1, array(3, 4, 5, 6)),
            array(array(
                    "location_id#0"     => 3,
                    "_location_id#0-children"  => "yes",
                ), 0, 999, 1, array(1, 2, 3, 4, 5)),
            array(array(
                    "location_id#0"     => 4,
                    "_location_id#0-op" => "!=",
                ), 0, 999, 1, array(1, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "person_id#0"       => 2,
                ), 0, 999, 1, array(1, 2, 6, 7)),
            array(array(
                    "person_id#0"       => 7,
                    "_person_id#0-op"   => "!=",
                ), 0, 999, 1, array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "location_id#0"     => 3,
                    "_location_id#0-op" => "=",
                    "person_id#0"       => 7,
                    "_person_id#0-op"   => "!=",
                    "_person_id#0-conj"   => "or",
                ), 0, 999, 1, array(1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "location_id#0"     => 4,
                    "_location_id#0-op" => "=",
                    "person_id#0"       => 7,
                    "_person_id#0-op"   => "!=",
                    "_person_id#0-conj"   => "and",
                ), 0, 999, 1, array(3)),
            array(array(
                    "_field#0"          => "camera_make",
                    "field#0"           => "null"
                ), 0, 999, 1, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "_field#0"          => "camera_make",
                    "field#0"           => "null",
                    "_field#0-op"       => "!="
                ), 0, 999, 1, array()),
            array(array(
                    "_field#0"          => "camera_make",
                    "field#0"           => "Canon",
                    "_field#0-op"       => "!="
                ), 0, 999, 1, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)),
            array(array(
                    "_text#0"           => "album",
                    "text#0"            => "Album 2",
                ), 0, 999, 1, array(3, 4, 5, 6, 7, 8, 9)),
            array(array(
                    "_text#0"           => "album",
                    "text#0"            => "Album 21/Album 210",
                ), 0, 999, 1, array(7, 8, 9)),
            array(array(
                    "_text#0"           => "category",
                    "text#0"            => "Blue",
                ), 0, 999, 1, array(3, 4, 5, 10)),
            array(array(
                    "_text#0"           => "category",
                    "text#0"            => "white/grey25",
                ), 0, 999, 1, array(6, 7, 8, 9)),
            array(array(
                    "_text#0"           => "category",
                    "text#0"            => "white/nonexistent",
                ), 0, 999, 1, array()),
            array(array(
                    "_text#0"           => "person",
                    "text#0"            => "John",
                ), 0, 999, 1, array(1, 2, 3, 4)),
            array(array(
                    "userrating"        => 10,
                    "_userrating_user"  => 6,
                ), 0, 999, 1, array(5, 10)),
            array(array(
                    "userrating"        => "null",
                    "_userrating_user"  => 6,
                ), 0, 999, 1, array(1, 2, 3, 4, 6, 7, 9, 11, 12)),
            array(array(
                    "person_id#0"       => 2,
                    "lat"               => 52.25,
                    "lon"               => 5.75,
                    "_latlon_distance"  => 100,
                    "_latlon_photos"    => "1",
                ), 0, 999, 1, array(1, 2)),
            array(array(
                    "lat"               => 52.25,
                    "lon"               => 5.75,
                    "_latlon_distance"  => 100,
                    "_latlon_photos"    => "1",
                ), 0, 999, 1, array(1, 2, 3, 4, 5)),
            array(array(
                    "lat"               => 43,
                    "lon"               => -75,
                    "_latlon_distance"  => 500,
                    "_latlon_entity"    => "miles",
                    "_latlon_places"    => "1",
                ), 0, 999, 1, array(8, 9, 10)),
            array(array(
                    "_field#0"          => "name",
                    "field#0"           => "TEST_0001.JPG",
                ), 0, 999, 1, array(1)),
            array(array(
                    "_field#0"          => "name",
                    "field#0"           => "TEST_0001.JPG",
                ), 0, 999, 1, array(1)),
            array(array(
                    "_field#0"          => "name",
                    "field#0"           => "1.JPG",
                    "_field#0-op"       => "like",
                ), 0, 999, 1, array(1, 11)),
            array(array(
                    "lat"               => 1,
                    "lon"               => 1,
                    "_latlon_distance"  => 1,
                    "_latlon_photos"    => "1",
                ), 0, 999, 1, array()),
            array(array(
                    "rating"            => "null"
                ), 0, 999, 1, array(7, 9, 11, 12)),
            // The next is added to compare it with the same query for another user, below.
            array(array(
                    "category_id"          => 2
                ), 0, 999, 1, array(1,2,3,4)),
            // Test limits
            array(array(), 0, 5, 1, array(1, 2, 3, 4, 5)),
            array(array(), 5, 999, 1, array(6, 7, 8, 9, 10, 11, 12)),
            // from here, different user!
            array(array(
                    "userrating"        => 8,
                    "_userrating_user"  => 99,
                ), 0, 999, 2, array()),
            array(array(
                    "album_id#1"        => 2,
                    "album_id#2"        => 11,
                    "_album_id#2-conj"  => "and",
                    "_album_id#2-op"    => "!="
                ), 0, 999, 5, array(1)),
            // The next is the same query as above, but now for a non-admin user
            array(array(
                    "category_id"          => 2
                ), 0, 999, 3, array(1)),
            // Test limits
         );
    }

}
