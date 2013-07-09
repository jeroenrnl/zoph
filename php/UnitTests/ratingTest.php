<?php
/**
 * Unittests for rating class
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
 * Test rating class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class ratingTest extends ZophDataBaseTestCase {
    
    /**
     * Test retrieving ratings from database
     * @dataProvider getRatings
     */
    public function testGetRating($photo_id, $user_id, $rating, $avg) {
        global $user;
        $user = new user($user_id);
        $user->lookup();

        $photo=new photo($photo_id);
        $photo->lookup();

        $objs = rating::getRatings($photo, $user);
        $this->assertCount(1, $objs);

        $obj=array_pop($objs);

        $this->assertInstanceOf("rating", $obj);

        $rate=$obj->get("rating");

        $this->assertEquals($rate, $rating);

    }
    
    /**
     * Test retrieving average ratings from database
     * @dataProvider getAverage
     */
    public function testGetAverage($photo_id, $avg) {
        $photo=new photo($photo_id);
        $photo->lookup();

        $rate=rating::getAverage($photo);

        $this->assertEquals($rate, $avg);
    }

    /**
     * Test rating photos
     * @dataProvider getNewRatings
     */
    public function testSetRating($photo_id, $rating, $user_id, $avg) {
        global $_SERVER;
        global $user;
        $user = new user($user_id);
        $user->lookup();

        user::setCurrent($user);

        $photo=new photo($photo_id);
        $photo->lookup();

        $_SERVER["REMOTE_ADDR"]=$user->getName() . ".zoph.org";

        $photo->rate($rating);

        $this->assertEquals(rating::getAverage($photo), $avg);
    }

    /**
     * Test multirating
     * @dataProvider getMultiRatings
     */
    public function testMultiRating($photo_id, $ratings, $user_id, $avg) {
        global $_SERVER;
        global $user;
        $user = new user($user_id);
        $user->lookup();
        
        $user->set("allow_multirating", true);
        $user->update();

        user::setCurrent($user);
        
        $photo=new photo($photo_id);
        $photo->lookup();

        foreach($ratings as $nr=>$rating) {

            $_SERVER["REMOTE_ADDR"]=$user->getName() . $nr . ".zoph.org";

            $photo->rate($rating);

            $this->assertEquals(rating::getAverage($photo), $avg[$nr]);
        }


    }

    /**
     * Test rating when user is not allowed to rate
     */
    public function testDenyRating() {
        $user=new user(5);

        $user->set("allow_rating", false);
        $user->update();

        user::setCurrent($user);
        
        // This photo currently has an average of 5
        // this should stay this way!
        $photo=new photo(3);
        $photo->lookup();

        $this->assertEquals($photo->getRating(), 5);

        $photo->rate(8);

        $this->assertEquals($photo->getRating(), 5);

    }

    /**
     * Test whether the functions still work when there are no ratings in the db
     */
    public function testNoRatings() {
        $user=new user(1);
        user::setCurrent($user);
        for($r=1; $r<=19; $r++) {
            $rating=new rating($r);
            $rating->delete();
        }

        $ratings=rating::getGraphArray();
        $this->assertInternalType("array", $ratings);

        $this->assertEquals($ratings[0]["count"], 10);
        
        for($c=1; $c<=10; $c++) {
            $this->assertEquals($ratings[$c]["count"], 0);
        }

    }

    /**
     * Test whether the functions still work when there are no photos in the db
     */
    public function testNoPhotos() {
        $user=new user(1);
        user::setCurrent($user);
        
        for($p=1; $p<=10; $p++) {
            $photo=new photo($p);
            $photo->delete();
        }
        $ratings=rating::getGraphArray();
        $this->assertInternalType("array", $ratings);

        for($c=0; $c<=10; $c++) {
            $this->assertEquals($ratings[$c]["count"], 0);
        }

    }

    /**
     * Test getting the array to build the graph on the
     * reports page.
     * @dataProvider getRatingArray
     * @todo At this moment only tests count, which is probably most important.
     */
    public function testGetGraphArray($user_id, $rating_array) {
        $user=new user($user_id);
        user::setCurrent($user);
        $_SERVER["REMOTE_ADDR"]=$user->getName() . ".zoph.org";
        $ratings=rating::getGraphArray();

        $this->assertInternalType("array", $ratings);
        foreach ($rating_array as $rating=>$count) {
            $this->assertEquals($count, $ratings[$rating]["count"]);
        }
    }

    /**
     * Test getting the array to build the graph on the
     * user page.
     * @dataProvider getRatingArrayForUser
     * @todo At this moment only tests count, which is probably most important.
     */
    public function testGetGraphArrayForUser($user_id, $rating_array) {
        $user=new user($user_id);
        $ratings=rating::getGraphArrayForUser($user);
        $this->assertInternalType("array", $ratings);
        foreach ($rating_array as $rating=>$count) {
            $this->assertEquals($count, $ratings[$rating]["count"]);

            
        }
    }

    /**
     * Test the getDetails function
     * @dataProvider getDetailsArray
     */
    public function testGetDetails($photo_id, $det_array) {
        $photo=new photo($photo_id);
        $details=$photo->getRatingDetails();

        $this->assertInstanceOf("block", $details);

        $this->assertEquals($details->template, "templates/default/blocks/rating_details.tpl.php");
        $vars=$details->vars;

        $this->assertEquals($det_array[0], $vars["rating"]);
        $this->assertInternalType("array", $vars["ratings"]);
        foreach ($vars["ratings"] as $rating) {
            $this->assertInstanceOf("rating", $rating);
            
            $expected=array_shift($det_array[1]);

            $username=$rating->getUser()->getName();
            $this->assertEquals($expected[0], $rating->getId());
            $this->assertEquals($expected[1], $username);
            $this->assertEquals($expected[2], $rating->get("rating"));
            $this->assertEquals($expected[3], $rating->get("ipaddress"));

        }
        


    }
    
    /**
     * Test deleting a rating
     * @dataProvider getRatingsToBeDeleted
     */
    public function testDeleteRating($rating_id, $avg) {
        $rating=new rating($rating_id);
        $rating->lookup();
        $photo=new photo($rating->get("photo_id"));

        /*$tmp_avg=rating::getAverage($photo);
        $tmp_rating=$rating->get("rating");*/
        $photo->lookup();

        $rating->delete();

        $new_avg=rating::getAverage($photo);
        $this->assertEquals($avg, $new_avg);
        
    }   
    public function getAverage() {
        return array(
            array(1, 7.5),
            array(2, 4.25),
            array(3, 5),
            array(4, 6.5),
            array(5, 9.5),
            array(6, 6),
            array(7, null),
            array(8, 6),
            array(9, null),
            array(10, 5.5)
        );
    }

    public function getNewRatings() {
        // photo_id, rating, user_id, new avg
        return array(
            array(1, 10, 3, 8),
            array(1, 8, 6, 7.6),
            array(1, 8, 4, 7.6),
            array(2, 3, 4, 4),
            array(2, 7, 5, 5.25)
         );
    }

    public function getMultiRatings() {
        // photo_id, rating, user_id, new avg
        return array(
            array(1, array(5, 1, 6, 8), 3, array(7, 6, 6, 6.25)),
            array(1, array(7, 8, 8, 7), 6, array(7.4, 7.5, 7.57, 7.5))
         );
    }

    public function getRatings() {
        // photo, user, rating, ip
        return array(
            array(1, 2, 8, "brian.zoph.org"),
            array(1, 5, 7, "freddie.zoph.org"),
            array(1, 7, 8, "roger.zoph.org"),
            array(1, 9, 7, "johnd.zoph.org"),
            array(2, 2, 5, "brian.zoph.org"),
            array(2, 5, 3, "freddie.zoph.org"),
            array(2, 7, 6, "roger.zoph.org"),
            array(2, 9, 3, "johnd.zoph.org"),
            array(3, 3, 5, "jimi.zoph.org"),
            array(4, 4, 7, "paul.zoph.org"),
            array(4, 8, 6, "johnl.zoph.org"),
            array(5, 6, 10, "phil.zoph.org"),
            array(5, 3, 9, "jimi.zoph.org"),
            array(6, 3, 7, "jimi.zoph.org"),
            array(6, 2, 5, "brian.zoph.org"),
            array(8, 4, 9, "paul.zoph.org"),
            array(8, 6, 3, "phil.zoph.org"),
            array(10, 6, 10, "phil.zoph.org"),
            array(10, 4, 1, "paul.zoph.org")
        );
    }

    public function getRatingArray() {
        // user, rating => count
        return array(
            array(1, array(
                0 => 2,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 1,
                5 => 1,
                6 => 3,
                7 => 1,
                8 => 1,
                9 => 0,
                10 => 1)
            ), array(3, array(
                0 => 1,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 1,
                9 => 0,
                10 => 0)
            ),
            array(4, array(
                0 => 1,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 1,
                5 => 0,
                6 => 1,
                7 => 0,
                8 => 1,
                9 => 0,
                10 => 0)
            ));
    }

    public function getRatingArrayForUser() {
        // user, rating => count
        return array(
            array(2, array(
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 2,
                6 => 0,
                7 => 0,
                8 => 1,
                9 => 0,
                10 => 0)
            ), array(3, array(
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 1,
                6 => 0,
                7 => 1,
                8 => 0,
                9 => 1,
                10 => 0)
            ),
            array(1, array(
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 0,
                9 => 0,
                10 => 0)
            ));
    }

    public function getDetailsArray() {
        // photo_id, array details
        return array(
            array(1, array(7.5,array(
                array(1, "brian", 8, "brian.zoph.org"),
                array(2, "freddie", 7, "freddie.zoph.org"),
                array(3, "roger", 8, "roger.zoph.org"),
                array(4, "johnd", 7, "johnd.zoph.org")))),
            array(3, array(5, array(
                array(9, "jimi", 5, "jimi.zoph.org")))),
            array(7, array(0, array()))
        );
    }

    public function getRatingsToBeDeleted() {
        // keep in mind that every time this test is run, the
        // db is restored to it's old state, so immediately
        // after the test, the deleted record is back!
        // rating_id, new_avg
        return array(
            array(1, 7.33),
            array(2, 7.67),
            array(9, null),
            array(19, 10)
        );
    }

}
