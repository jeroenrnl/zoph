<?php
/**
 * Map test
 * Test the working of the map class
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

use geo\point;
use geo\map;
use geo\marker;

/**
 * Test the  geo\point class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class mapTest extends PHPUnit_Framework_TestCase {

    public function testCreate() {
        $map=new map();

        $this->assertInstanceOf("geo\map", $map);

        $this->assertFalse($map->hasMarkers());
        $this->assertFalse($map->hasTracks());

        return $map;
    }

    /**
     * @depends testCreate
     */
    public function testAddMarker(map $map) {
        $marker=new marker(51.0, 5.0, "photo.png", "photo", "<h1>marker</h1>");
        $map->addMarker($marker);

        $markers=$map->getMarkers();
        $this->assertCount(1, $markers);
        $this->assertInstanceOf("geo\marker", $markers[0]);
        $this->assertTrue($map->hasMarkers());
    }

    /**
     * @depends testCreate
     */
    public function testAddMarkers(map $map) {
        $markers=array();
        foreach ([ 1, 2, 3 ] as $placeId) {
            $place=new place($placeId);
            $place->lookup();
            $markers[]=$place;
        }
        $map->addMarkers($markers);
        unset($markers);
        $markers=$map->getMarkers();
        $this->assertCount(3, $markers);
        $this->assertInstanceOf("geo\marker", $markers[2]);
    }

    /**
     * @depends testCreate
     */
    public function testAddTrack(map $map) {
        // Create a track
        $track=helpers::createTrack(5, false);

        $map->addTrack($track);

        $this->assertTrue($map->hasTracks());

        $html=(string) $map;
        $this->assertContains("points.push([ 51.98, 5 ] );", $html);
        return $map;
    }

    /**
     * @depends testAddTrack
     */
    public function testGetTracks(map $map) {
        $this->assertTrue($map->hasTracks());

        $tracks=$map->getTracks();
        foreach ($tracks as $track) {
            $this->assertInstanceOf("geo\\track", $track);
        }
    }

    /**
     * @depends testCreate
     */
    public function testSetCenterAndZoom(map $map) {
        $map->setCenterAndZoom(51,5,15);
        $html=(string) $map;
        $this->assertContains("zMaps.setCenterAndZoom([ 51, 5 ], 15", $html);
    }

    /**
     * @depends testCreate
     */
    public function testSetCenterAndZoomFromObj(map $map) {
        $photo=new photo();
        $photo->set("lat", "52");
        $photo->set("lon", "6");
        $photo->set("mapzoom", "7");

        $map->setCenterAndZoomFromObj($photo);
        $html=(string) $map;
        $this->assertContains("zMaps.setCenterAndZoom([ 52, 6 ], 7", $html);

        $place=new place();
        $place->set("lat", "50");
        $place->set("lon", "3");
        $place->set("mapzoom", "8");

        $map->setCenterAndZoomFromObj($place);
        $html=(string) $map;
        $this->assertContains("zMaps.setCenterAndZoom([ 50, 3 ], 8", $html);

        $place=new place();
        $place->set("lat", "55");
        $place->set("lon", "4");
        $place->set("mapzoom", "9");

        $photo=new photo();
        $photo->location=$place;

        $map->setCenterAndZoomFromObj($photo);
        $html=(string) $map;
        $this->assertContains("zMaps.setCenterAndZoom([ 55, 4 ], 9", $html);

        $perth=new place();
        $perth->set("parent_place_id", 18);
        $perth->insert();

        $photo=new photo();
        $photo->location=$perth;
        $photo->update();

        $map->setCenterAndZoomFromObj($photo);
        $html=(string) $map;
        $this->assertContains("zMaps.setCenterAndZoom([ -25, 135 ], 2", $html);

    }

    /**
     * @depends testCreate
     */
    public function testSetEditable(map $map) {
        $html=(string) $map;
        $this->assertNotContains("zMaps.setUpdateHandlers", $html);

        $map->setEditable();
        $html=(string) $map;
        $this->assertContains("zMaps.setUpdateHandlers", $html);

        $map->setEditable(false);
        $html=(string) $map;
        $this->assertNotContains("zMaps.setUpdateHandlers", $html);
    }
}
