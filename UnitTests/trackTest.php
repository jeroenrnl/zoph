<?php
/**
 * Track test
 * Test the working of the track class
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

use geo\track;
use geo\point;

/**
 * Test the track class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class trackTest extends ZophDataBaseTestCase {

    public function testDelete() {
        // Create a track
        $track=helpers::createTrack(5, false);

        $trackId=$track->getId();

        $track=new track($trackId);
        $track->lookup();
        $points=point::getRecords("point_id", array("track_id" => (int) $trackId));

        // verify insertion
        $this->assertInstanceOf("geo\\track", $track);
        $this->assertEquals("Test Track", $track->get("name"));
        $this->assertCount(5, $points);


        $track->delete();

        $track=new track($trackId);
        $track->lookup();
        $this->assertEquals("", $track->get("name"));

        // Check if there are no orphan points left in the db
        $points=point::getRecords("point_id", array("track_id" => (int) $trackId));
        $this->assertCount(0, $points);
    }

    public function testGetFirstLast() {
        // Create a track with 10 randomized points
        $track=helpers::createTrack(10, true);

        $first=$track->getFirstPoint();
        $minute=(int) date("i",strtotime($first->get("datetime")));
        // We know the first entry has the time set to xx:00:00
        $this->assertEquals(0, $minute);

        $last=$track->getLastPoint();
        $minute=(int) date("i",strtotime($last->get("datetime")));
        // We know the first entry has the time set to xx:09:00
        $this->assertEquals(9, $minute);
    }

    public function testGetPointCount() {
        $track=helpers::createTrack(50);
        $this->assertEquals(50, $track->getPointCount());
    }
}
