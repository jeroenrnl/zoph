<?php
/**
 * Unittests for photo class
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

require_once("testSetup.php");

/**
 * Test photo class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class photoTest extends ZophDataBaseTestCase {
    
    /**
     * Test setting of location
     * @dataProvider getLocation
     */
    public function testSetLocation($photo, $loc) {
        $photo=new photo($photo);
        $photo->set("location_id",$loc);
        $photo->update();
        $photo->lookup();
        $this->assertInstanceOf("place", $photo->location);
        $this->assertEquals($photo->location->getId(), $loc);
    }

    /**
     * Test setting of photographer
     * @dataProvider getPhotographer
     */
    public function testSetPhotographer($photo, $phg) {
        $photo=new photo($photo);
        $photo->set("photographer_id",$phg);
        $photo->update();
        $photo->lookup();
        $this->assertInstanceOf("person", $photo->photographer);
        $this->assertEquals($photo->photographer->getId(), $phg);
    }

    /**
     * Test adding to albums
     * @dataProvider getAlbums
     */
    public function testAddToAlbum($photo, array $newalbums) {
        $ids=array();
        $photo=new photo($photo);
        foreach($newalbums as $alb) {
            $photo->addTo(new album($alb));
        }
        $albums=$photo->getAlbums();
        foreach($albums as $album) {
            $ids[]=$album->getId();
            $this->assertInstanceOf("album", $album);
        }
        foreach($newalbums as $album_id) {
            $this->assertContains($album_id, $ids);
        }
    }

    /**
     * Test adding to categories
     * @dataProvider getCategories
     */
    public function testAddToCategories($photo, array $newcats) {
        $ids=array();
        $photo=new photo($photo);
        foreach($newcats as $cat) {
            $photo->addTo(new category($cat));
        }
        $cats=$photo->getCategories();
        foreach($cats as $cat) {
            $ids[]=$cat->getId();
            $this->assertInstanceOf("category", $cat);
        }
        foreach($newcats as $cat_id) {
            $this->assertContains($cat_id, $ids);
        }
    }

    /**
     * Test adding people
     * @dataProvider getPeople
     */
    public function testAddPerson($photo, array $newpers) {
        $ids=array();
        $photo=new photo($photo);
        foreach($newpers as $pers) {
            $photo->addTo(new person($pers));
        }
        $peo=$photo->getPeople();
        foreach($peo as $per) {
            $ids[]=$per->getId();
            $this->assertInstanceOf("person", $per);
        }
        foreach($newpers as $per_id) {
            $this->assertContains($per_id, $ids);
        }
    }


    /**
     * Test adding comments
     * @dataProvider getComments
     */
    public function testAddComment($photo_id, $comment, $user_id) {
        $obj = new comment();
        $user = new user($user_id);
        $user->lookup();

        $photo=new photo($photo_id);
        $photo->lookup();

        $subj="Comment by " . $user->getName();

        $obj->set("comment", $comment);
        $obj->set("subject", $subj);
        $obj->set("user_id", $user_id);
        $_SERVER["REMOTE_ADDR"]=$user->getName() . ".zoph.org";
        $obj->insert();
        $obj->add_comment_to_photo($photo->get("photo_id"));
        
        $this->assertInstanceOf("comment", $obj);
        $this->assertEquals($obj->get_photo()->get("photo_id"), $photo->get("photo_id"));
    }

    /**
     * Test getTime function
     */
    private function setupTime() {
        ini_set("date.timezone", "Europe/Amsterdam");
        $photo=new photo(9);
        $photo->lookup();

        $photo->set("date", "2013-01-01");
        $photo->set("time", "4:00:00");
        $photo->update();

        // Camera's timezone is UTC
        $conf=conf::set("date.tz", "UTC");
        $conf->update();
        
        $conf=conf::set("date.format", "d-m-Y");
        $conf->update();

        $conf=conf::set("date.timeformat", "H:i:s T");
        $conf->update();
        
        $location=$photo->location;
        
        // Timezone for New York, where photo was taken.
        $location->set("timezone", "America/New_York");
        $location->update();

    }


    /**
     * Test getTime function
     */
    public function testGetTime() {
        $this->setupTime();
        
        $photo=new photo(9);
        $photo->lookup();
        
        $datetime=$photo->getTime();

        $expected=new Time("31-12-2012 23:00:00 America/New_York");

        $this->assertEquals($expected, $datetime);
    }

    /**
     * test getFormattedDateTime
     */
    public function testGetFormattedDateTime() {
        $this->setupTime();
        
        $photo=new photo(9);
        $photo->lookup();
        // First test:
        // camera timezone is UTC
        // place timezone is America/New York
        // The time should be -5 hrs from 1/1/13 4:00, New York timezone (EST)
        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("31-12-2012", $datetime[0]);
        $this->assertEquals("23:00:00 EST", $datetime[1]);

        // Second test:
        // camera timezone is Moscow
        // place timezone is Invalid
        // The time should be 1/1/13 4:00, Moscow timezone (MSK)
        $conf=conf::set("date.tz", "Europe/Moscow");
        $conf->update();
        $location=$photo->location;
        $location->set("timezone", "Nonsense/Timezone");
        $location->update();

        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("04:00:00 MSK", $datetime[1]);

        // Third test:
        // camera timezone is empty (local time)
        // place timezone is Invalid
        // The time should be 1/1/13 4:00, Default timezone (php.ini) (CET)
        $conf=conf::set("date.tz", "");
        $conf->update();
        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("04:00:00 CET", $datetime[1]);

        // Fourth test:
        // camera timezone is empty (local time)
        // place timezone is New York
        // The time should be 1/1/13 4:00, EST
        $location->set("timezone", "America/New_York");
        $location->update();
        
        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("04:00:00 EST", $datetime[1]);
        
        // Fifth Test
        // camera timezone is Australia (+8)
        // place timezone is Los Angeles (-8)
        // this makes the time 31/12/12 12:00
        // however, with an extra correction of -1 minute, it becomes 11:59
        $conf=conf::set("date.tz", "Australia/Perth");
        $conf->update();
        $location->set("timezone", "America/Los_Angeles");
        $location->update();

        $photo->set("time_corr", "-1");
        $photo->update();

        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("31-12-2012", $datetime[0]);
        $this->assertEquals("11:59:00 PST", $datetime[1]);

    }

    /**
     * test getReverseDate
     */
    public function testGetReverseDate() {
        $this->setupTime();
        
        $photo=new photo(9);
        $photo->lookup();
        
        $date=$photo->getReverseDate();
        $this->assertEquals("2012-12-31", $date);
    }
        
        

    /**
     * Test getUTCtime function
     */
    public function testGetUTCtime() {
        $this->setupTime();
        
        conf::set("date.tz", "Europe/Amsterdam");
        
        $photo=new photo(9);
        $photo->lookup();
        
        $datetime=$photo->getUTCtime();

        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("03:00:00 UTC", $datetime[1]);
    }

    /**
     * Test getSubset function
     */
     public function testGetSubset() {
        $photos=array();
        $first=array();
        $last=array();

        for($i=1; $i<=50; $i++) {
            if($i<=5) {
                $first[$i]=new photo($i);
            }
            $photos[]=new photo($i);
            if($i>45) {
                $last[$i]=new photo($i);
            }

        }
        $firstlast=$first + $last;

        $subset=photo::getSubset($photos, array("first", "last"), 5);
        $this->assertEquals($firstlast, $subset);


        $subset=photo::getSubset($photos, array("random"), 5);

        $this->assertCount(5, $subset);

        $subset=photo::getSubset($photos, array("first", "random", "last"), 5);
        $this->assertCount(15, $subset);

        $photos=array();
        
        for($i=1; $i<=4; $i++) {
            $photos[]=new photo($i);
        }
        $subset=photo::getSubset($photos, array("first", "random", "last"), 5);
        
        $photos=array();
        for($i=1; $i<=8; $i++) {
            $photos[]=new photo($i);
        }
        $subset=photo::getSubset($photos, array("first", "random"), 5);


    }

    //================== DATA PROVIDERS =======================

    public function getLocation() {
        return array(
            array(1, 5),
            array(2, 6),
            array(3, 7),
            array(4, 8)
         );
    }

    public function getPhotographer() {
        return array(
            array(1, 5),
            array(2, 6),
            array(3, 7),
            array(4, 8)
         );
    }

    public function getAlbums() {
        return array(
            array(1, array(2,3,4)),
            array(2, array(1,5,6)),
            array(3, array(7)),
            array(4, array(8,9))
         );
    }

    public function getCategories() {
        return array(
            array(1, array(2,3,4)),
            array(2, array(1,5,6)),
            array(3, array(7)),
            array(4, array(8,9))
         );
    }

    public function getPeople() {
        return array(
            array(1, array(3,4,6)),
            array(2, array(1,6,8)),
            array(3, array(7)),
            array(4, array(6,9))
         );
    }

    public function getComments() {
        return array(
            array(1, "Test Comment", 3),
            array(2, "Test comment [b]with bold[/b]", 4),
            array(3, "Test comment with [i]unclosed tag",5),
            array(4, "Test comment with <b>html</b>", 6)
         );
    }

    public function getRatings() {
        return array(
            array(1, 10, 3, 8),
            array(1, 8, 6, 7.6),
            array(1, 8, 4, 7.6),
            array(2, 3, 4, 4),
            array(2, 7, 5, 5.25)
         );
    }
}
