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
    }

    /**
     * Test removeWithLatLon() function
     * @dataProvider getCollection()
     */
    public function testRemoveWithLatLon($collection) {
        $collection->removeWithLatLon();

        $this->assertCount(3, $collection);
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
}
