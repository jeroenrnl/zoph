<?php
/**
 * Point test
 * Test the working of the point class
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
 * Test the point class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class pointTest extends ZophDataBaseTestCase {

    /**
     * Create & delete point in the database
     */
    public function testCreateDeletePoint() {
        $point=new point();
        $point->set("lat",51.0);
        $point->set("lon",5.0);
        $point->set("datetime", "2015-09-01 7:55:00");
        $point->insert();
        $this->assertInstanceOf("point", $point);
        $this->assertEquals($point->getId(), 1);

        unset($point);
        $point=new point(1);
        $point->lookup();

        $this->assertInstanceOf("point", $point);
        $this->assertEquals($point->getId(), 1);
        $this->assertEquals($point->get("lat"), 51);

        $point->delete();

        unset($point);
        $point=new point(1);
        $point->lookup();

        $points=point::getRecords();
        $this->assertCount(0,$points);
    }

    /**
     * Test the getNext() and getPrev() methods
     * This is done by creating 10 point in a track with 1 minute time difference and shuffling them
     * by pulling a result from the database (at random) and then getting the next and previous entry
     * we can check it's correctness by comparing the minute value
     */

    public function testGetNextPrevious() {
        // Create a track with 10 randomized points
        $track=helpers::createTrack(10, true);

        // Take a random point from the database
        // repeat until the 'random' entry is not the first or the last.
        $points=$track->getPoints();
        $minute=0;
        while ($minute == 0 || $minute == 9) {
            shuffle($points);
            $point=$points[0];

            $minute=(int) date("i",strtotime($point->get("datetime")));
        }

        $next=$point->getNext();
        $nextmin=(int) date("i",strtotime($next->get("datetime")));

        $this->assertEquals($minute + 1, $nextmin);

        $prev=$point->getPrev();
        $prevmin=(int) date("i",strtotime($prev->get("datetime")));

        $this->assertEquals($minute - 1, $prevmin);

    }

}




