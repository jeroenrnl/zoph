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

require_once "testSetup.php";

use conf\conf;

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

    /**
     * Test adding a photo to an album
     * @dataProvider getAddPhoto();
     */
    public function testAddPhoto($photo_id, $album_id) {
        $photo=new photo($photo_id);
        $album=new album($album_id);

        $album->addPhoto($photo);

        $albums=$photo->getAlbums();

        foreach ($albums as $alb) {
            $ids[]=$alb->getId();
        }

        $this->assertContains($album_id, $ids);

    }

    /**
     * Test delete() function
     */
    public function testDelete() {
        $photo_id=5;
        $photo=new photo($photo_id);
        $photo->lookup();


        $album=new album();
        $album->set("album", "TESTalbum");
        $album->set("parent_album_id", 1);
        $album->insert();

        $album_id=$album->getId();

        $user=new user(5);
        $user->lookup();
        $user->set("lightbox_id", $album_id);
        $user->update();

        $album->addPhoto($photo);

        $album->delete();
        $this->assertEmpty(album::getByName("TESTalbum"));

        $albums=$photo->getAlbums();

        foreach ($albums as $alb) {
            $ids[]=$alb->getId();
        }

        $this->assertNotContains($album_id, $ids);

        $user->lookup();

        $this->assertEmpty($user->get("lightbox_id"));

    }

    /**
     * Test getChildren() function
     * @dataProvider getChildren()
     */
    public function testGetChildren($id, array $exp_children, $order=null) {
        user::setCurrent(new user(1));
        $album=new album($id);
        $alb_children=$album->getChildren($order);
        $children=array();
        foreach ($alb_children as $child) {
            $children[]=$child->getId();
        }

        if ($order=="random") {
            // Of course, we cannot check the order for random, therefore we sort them.
            // Thus we only check if all the expected categories are present, not the order
            sort($children);
        }
        $this->assertEquals($exp_children, $children);
    }

    /**
     * Test getDetails()
     / @dataProvider getDetails();
     */
    public function testGetDetails($user,$alb_id, $subalb, array $exp_details) {
        user::setCurrent(new user($user));
        $album=new album($alb_id);
        $album->lookup();

        $details=$album->getDetails();
        $this->assertEquals($exp_details, $details);

        user::setCurrent(new user(1));
    }



    /**
     * Test getDetailsXML()
     / @dataProvider getDetails();
     */
    public function testGetDetailsXML($user,$alb_id, $subalb, array $exp_details) {
        user::setCurrent(new user($user));
        $album=new album($alb_id);
        $album->lookup();
        $details=$album->getDetailsXML();

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
                          <class>album</class>
                          <id>%s</id>
                        </request>
                        <response>
                          <detail>
                            <subject>title</subject>
                            <data>In this album:</data>
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
                            <data>%s sub-albums</data>
                          </detail>
                        </response>
                      </details>",
                       $alb_id, $exp_details["count"],$disp_oldest, $disp_newest, $disp_first, $disp_last,  $exp_details["lowest"], $exp_details["highest"], $exp_details["average"],$subalb);

        $this->assertXmlStringEqualsXmlString($expectedXML, $details);

        user::setCurrent(new user(1));
    }

    /**
     * Test getPhotoCount() function
     * @dataProvider getPhotoCount();
     */
    public function testGetPhotoCount($user, $album, $pc) {
        user::setCurrent(new user($user));

        $album=new album($album);
        $album->lookup();
        $count=$album->getPhotoCount();
        $this->assertEquals($pc, $count);
        user::setCurrent(new user(1));
    }

    /**
     * Test getTotalPhotoCount() function
     * @dataProvider getTotalPhotoCount();
     */
    public function testGetTotalPhotoCount($user, $album_id, $pc) {
        user::setCurrent(new user($user));
        $album=new album($album_id);
        $album->lookup();
        $count=$album->getTotalPhotoCount();
        $this->assertEquals($pc, $count);
        user::setCurrent(new user(1));
    }

    /**
     * Test getAutoCover function for a manual cover
     */
    public function testGetAutoCoverManual() {
        $album=new album(2);
        $album->set("coverphoto", 1);
        $album->update();

        $cover=$album->getAutoCover();
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals(1, $cover->getId());
    }


    /**
     * Test getAutoCover function
     / @dataProvider getCovers();
     */
    public function testGetAutoCover($user,$type,$alb_id,$photo) {
        user::setCurrent(new user($user));
        $album=new album($alb_id);
        $album->lookup();

        $cover=$album->getAutoCover($type);
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals($photo, $cover->getId());
        user::setCurrent(new user(1));
    }


    /**
     * Test getAutoCover function with children
     / @dataProvider getCoversChildren();
     */
    public function testGetAutoCoverChildren($user,$type,$alb_id,$photo) {
        user::setCurrent(new user($user));
        $album=new album($alb_id);
        $album->lookup();

        $cover=$album->getAutoCover($type, true);
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals($photo, $cover->getId());
        user::setCurrent(new user(1));
    }

    /**
     * Test getTopN() function
     * @dataProvider getTopNData();
     */
    public function testGetTopN($user, $expected) {
        user::setCurrent(new user($user));
        $albids=array();
        $topN=album::getTopN();

        foreach ($topN as $album) {
            $albids[]=$album["id"];
        }
        $this->assertEquals($expected, $albids);
        user::setCurrent(new user(1));
    }

    /**
     * Test getAll() function
     * @dataProvider getAllData();
     */
    public function testGetAll($user, $expected) {
        user::setCurrent(new user($user));
        $albids=array();
        $all=album::getAll();

        foreach ($all as $album) {
            $albids[]=$album->getId();
        }
        $this->assertEquals($expected, $albids);
        user::setCurrent(new user(1));
    }

    /**
     * Test getNewer() function
     */
     public function testGetNewer() {
        $user=new user(2);
        $newer=album::getNewer($user, "1970-01-01");
        $albids=array();
        foreach ($newer as $album) {
            $albids[]=$album->getId();
        }
        $this->assertEquals([1,2], $albids);
    }

    /**
     * Test getCount() function
     * @dataProvider getAlbumCount()
     */
    public function testGetCount($user, $exp_count) {
        user::setCurrent(new user($user));

        $count=album::getCount();

        $this->assertEquals($exp_count, $count);

        user::setCurrent(new user(1));
    }

    public function testSAcache() {
        album::setSAcache();
    }

    /* *************************************************************
     * Dataprovider functions
     * *************************************************************/

    public function getAlbums() {
        return array(
            array(15, "TestAlbum1", 2),
            array(15, "TestAlbum2", 3)
        );
    }

    /**
     * Dataprovider for testAddPhoto();
     */
    public function getAddPhoto() {
        return array(
            array(1,5),
            array(2,6)
        );
    }

    /**
     * Dataprovider for testGetChildren function
     * Rating related are commented because they are known to be broken
     */
    public function getChildren() {
        return array(
            array(1, array(13,14,2,5,12), "oldest"),
            array(1, array(13,14,5,2,12), "newest"),
            array(1, array(13,14,2,5,12), "first"),
            array(1, array(13,14,5,12,2), "last"),
//            array(1, array(5,12,2,13,14), "lowest"),
//            array(1, array(12,2,5,13,14), "highest"),
//            array(1, array(12,5,2,13,14), "average"),
            array(1, array(2,5,12,13,14), "sortname"),
            array(1, array(2,5,12,13,14), "name")
        );
    }

    /**
     * dataProvider function
     * @return user, album, number of subalbums, array(count, oldest, newest, first, last, highest, average)
     */
    public function getDetails() {
        return array(
            array(2, 2, 1, array(
                "count"     => "2",
                "oldest"    => "2014-01-01 00:01:00",
                "newest"    => "2014-01-07 00:01:00",
                "first"     => "2013-12-31 23:01:00",
                "last"      => "2014-01-09 23:05:00",
                "lowest"    => "7.5",
                "highest"   => "7.5",
                "average"   => "7.50"
            )),
            array(4, 3, "no", array(
                "count"     => "3",
                "oldest"    => "2014-01-01 00:01:00",
                "newest"    => "2014-01-08 00:01:00",
                "first"     => "2013-12-31 23:01:00",
                "last"      => "2014-01-09 23:04:00",
                "lowest"    => "4.3",
                "highest"   => "7.5",
                "average"   => "5.92",
            )),
            array(1, 6, 1,array(
                "count"     => "3",
                "oldest"    => "2014-01-03 00:01:00",
                "newest"    => "2014-01-05 00:01:00",
                "first"     => "2014-01-02 23:01:00",
                "last"      => "2014-01-04 23:01:00",
                "lowest"    => "5.0",
                "highest"   => "9.5",
                "average"   => "7.00",
            )),
        );
    }

    public function getPhotoCount() {
        return array(
            array(1,2,2),
            array(1,6,3),
            array(2,2,2),
            array(4,3,3)
        );
    }

    public function getTotalPhotoCount() {
        return array(
            array(1,1,10),
            array(1,6,4),
            array(1,11,3),
            array(5,1,2),
            array(5,8,0),
            array(4,1,4),
            array(4,2,4),
        );
    }
    /**
     * dataProvider function
     * @return array user,type of autocover, category_id, cover photo
     */
    public function getCovers() {
        return array(
            array(1,"oldest", 2, 1),
            array(1,"newest", 2, 7),
            array(1,"first", 2, 1),
            array(1,"last", 4, 9),
            array(1,"newest", 5, 5),
            array(2,"oldest", 2, 1),
            array(2,"newest", 2, 7),
            array(3,"first", 2, 1),
            array(4,"last", 2, 7),
        );
    }

    /**
     * dataProvider function
     * @return array user,type of autocover, category_id, cover photo
     */
    public function getCoversChildren() {
        return array(
            array(1,"oldest", 1, 1),
            array(1,"newest", 2, 10),
            array(1,"first", 3, 1),
            array(1,"last", 4, 9),
            array(1,"newest", 4, 10),
            array(5,"oldest", 1, 1),
            array(5,"newest", 2, 7),
            array(5,"first", 2, 1),
            array(4,"last", 2, 7),
        );
    }

    /**
     * dataProvider function
     * @return array userid, topN
     */
    public function getTopNData() {
        return array(
            array(1,array(3,4,5,6,11)),
            array(5,array(2)),
            array(4,array(3,2))
        );
    }

    /**
     * dataProvider function
     * @return array userid, array all albums
     */
    public function getAllData() {
        return array(
            array(1,array(2,3,4,5,6,7,9,10,8,11,12,13,14,1)),
            array(5,array(2,1)),
            array(4,array(2,3,1))
        );
    }

    /**
     * dataProvider function
     * @return array userid, exp_count
     */
    public function getAlbumCount() {
        return array (
            array(1, 14),
            array(5, 2),
            array(4, 3)
         );
     }



}
