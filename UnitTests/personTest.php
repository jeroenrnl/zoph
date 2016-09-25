<?php
/**
 * Person test
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
 * Test class for person class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class personTest extends ZophDataBaseTestCase {
    /**
     * Create People in the db
     * @dataProvider getPeople();
     */
    public function testCreatePerson($id, $first, $last, $user_id) {
        $person=new person();
        $person->set("first_name", $first);
        $person->set("last_name", $last);
        $person->insert();
        $this->assertInstanceOf("person", $person);
        $this->assertEquals($person->getId(), $id);
        if (!is_null($user_id)) {
            $user=new user($user_id);
            $user->lookup();
            $user->set("person_id", $id);
            $user->update();
        }
    }

   /**
    * Test adding a person to a photo
    * @dataProvider getPersonAndPhotos();
    */
    public function testAddRemovePhoto($id, $photo_id) {
        $photo=new photo($photo_id);
        $photo->lookup();

        $people=$photo->getPeople();
        $people_ids=array();
        foreach ($people as $p) {
            $people_ids[]=$p->getId();
        }

        $this->assertNotContains($id, $people_ids);

        $person=new person($id);

        unset($photo);
        $photo=new photo($photo_id);
        $photo->lookup();
        $person->addPhoto($photo);

        $people=$photo->getPeople();
        $people_ids=array();
        foreach ($people as $p) {
            $people_ids[]=$p->getId();
        }

        $this->assertContains($id, $people_ids);

        unset($person);
        unset($people);
        unset($people_ids);
        unset($photo);
        $photo=new photo($photo_id);

        $person=new person($id);

        $person->removePhoto($photo);

        $people=$photo->getPeople();
        $people_ids=array();
        foreach ($people as $p) {
            $people_ids[]=$p->getId();
        }

        $this->assertNotContains($id, $people_ids);
    }

   /**
    * Test adding a person to a photo
    * @dataProvider getPersonAndPlaces();
    */
    public function testLookupPlaces($id, $home_id, $work_id) {
        $person=new person($id);

        $person->set("home_id", $home_id);
        $person->set("work_id", $work_id);

        $person->update();

        unset($person);
        $person=new person($id);
        $person->lookup();

        $home=new place($home_id);
        $home->lookup();

        $work=new place($work_id);
        $work->lookup();

        $this->assertEquals($home, $person->home);
        $this->assertEquals($work, $person->work);

    }

    public function testGetPhotoGrapher() {
        $person=new person(3);

        $photographer=$person->getPhotographer();

        $this->assertInstanceOf("photographer", $photographer);
        $this->assertEquals(3, $photographer->getId());
    }

    public function testDelete() {
        $person=new person();
        $person->insert();
        $id=$person->getId();

        $photo=new photo();
        $photo->insert();
        $photo->setPhotographer($person->getPhotographer());
        $photo->addTo($person);
        $photo->update();

        $son1=new person();
        $son1->set("father_id", $person->getId());
        $son1->insert();

        $son2=new person();
        $son2->set("mother_id", $person->getId());
        $son2->insert();

        $spouse=new person();
        $spouse->set("spouse_id", $person->getId());
        $spouse->insert();

        $person->delete();

        $son1->lookup();
        $son2->lookup();
        $spouse->lookup();
        $this->assertEmpty($son1->get("father_id"));
        $this->assertEmpty($son2->get("mother_id"));
        $this->assertEmpty($spouse->get("spouse_id"));
        $this->assertEquals(array(), $photo->getPeople());

        $person=new person($id);
        $person->lookup();
        $this->assertCount(1,$person->fields);
    }

    public function testGetDisplayArray() {
        $person=new person();
        $person->setName("Test Person");
        $person->set("called", "Tester");
        $person->set("dob", "1970-01-01");
        $person->set("gender", 1);

        $father=new person();
        $father->setName("Father of Test");
        $father->insert();
        $f_id=$father->getId();

        $mother=new person();
        $mother->setName("Mother of Test");
        $mother->insert();
        $m_id=$mother->getId();

        $spouse=new person();
        $spouse->setName("Spouse of Test");
        $spouse->insert();
        $s_id=$spouse->getId();

        $person->set("father_id", $f_id);
        $person->set("mother_id", $m_id);
        $person->set("spouse_id", $s_id);
        $person->insert();

        $exp=array(
            "called" => "Tester",
            "date of birth" => "<a href=\"calendar.php?date=1970-01-01&amp;search_field=date\">01-01-1970</a>",
            "date of death" => null,
            "gender" => "male",
            "mother" => "<a href=\"person.php?person_id=" . $m_id . "\">Mother Test</a>",
            "father" => "<a href=\"person.php?person_id=" . $f_id . "\">Father Test</a>",
            "spouse" => "<a href=\"person.php?person_id=" . $s_id . "\">Spouse Test</a>"
        );

        $this->assertEquals($exp, $person->getDisplayArray());
    }

    /**
     * Test getAutoCover function for a manual cover
     */
    public function testGetAutoCoverManual() {
        $person=new person(2);
        $person->set("coverphoto", 1);
        $person->update();

        $cover=$person->getAutoCover();
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals(1, $cover->getId());
    }


    /**
     * Test getAutoCover function
     / @dataProvider getCovers();
     */
    public function testGetAutoCover($user,$type,$alb_id,$photo) {
        user::setCurrent(new user($user));
        $person=new person($alb_id);
        $person->lookup();

        $cover=$person->getAutoCover($type);
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
        $person=new person($alb_id);
        $person->lookup();

        $cover=$person->getAutoCover($type, true);
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals($photo, $cover->getId());
        user::setCurrent(new user(1));
    }

    /**
     * Test getDetails()
     / @dataProvider getDetails();
     */
    public function testGetDetails($user,$person_id, array $exp_details) {
        user::setCurrent(new user($user));
        $person=new person($person_id);
        $person->lookup();

        $details=$person->getDetails();
        $this->assertEquals($exp_details, $details);

        user::setCurrent(new user(1));
    }



    /**
     * Test getDetailsXML()
     / @dataProvider getDetails();
     */
    public function testGetDetailsXML($user,$person_id, array $exp_details) {
        user::setCurrent(new user($user));
        $person=new person($person_id);
        $person->lookup();
        $details=$person->getDetailsXML();

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
                          <class>person</class>
                          <id>%s</id>
                        </request>
                        <response>
                          <detail>
                            <subject>title</subject>
                            <data>Photos taken by this person:</data>
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
                        </response>
                      </details>",
                       $person_id, $exp_details["count"],$disp_oldest, $disp_newest, $disp_first, $disp_last,  $exp_details["lowest"], $exp_details["highest"], $exp_details["average"]);

        $this->assertXmlStringEqualsXmlString($expectedXML, $details);

        user::setCurrent(new user(1));
    }

    /**
     * Test getTopN() function
     * @dataProvider getTopNData();
     */
    public function testGetTopN($user, $expected) {
        user::setCurrent(new user($user));
        $personids=array();
        $topN=person::getTopN();

        foreach ($topN as $person) {
            $personids[]=$person["id"];
        }
        $this->assertEquals($expected, $personids);
        user::setCurrent(new user(1));
    }

    /**
     * Test getAllPeopleAndPhotographers() function
     * @dataProvider getAll();
     */
    public function testGetAllPeopleAndPhotographers($user, $expected, $search=null) {
        user::setCurrent(new user($user));
        $personids=array();
        $all=person::getAllPeopleAndPhotographers($search);

        foreach ($all as $person) {
            $personids[]=$person->getId();
        }
        $this->assertEquals($expected, $personids);
        user::setCurrent(new user(1));
    }




    /*****************************************************************************
     * DataProviders
     *****************************************************************************/

    public function getPeople() {
        return array(
            array(11, "First", "Last", null),
            array(11, "First2", "Last2", 4)
        );
    }

    public function getPersonAndPhotos() {
        return array(
            array(1,3),
            array(5,4),
            array(8,2)
        );
    }

    public function getPersonAndPlaces() {
        return array(
            array(1,2,3),
            array(5,1,1),
            array(8,3,6)
        );
    }
    /**
     * dataProvider function
     * @return array user,type of autocover, person_id, cover photo
     */
    public function getCovers() {
        return array(
            array(1,"oldest", 2, 1),
            array(1,"newest", 2, 7),
            array(1,"first", 2, 1),
            array(1,"last", 4, 4),
            array(1,"newest", 5, 7),
            array(5,"oldest", 2, 1),
            array(5,"newest", 2, 7),
            array(3,"first", 2, 1),
            array(4,"last", 2, 7),
        );
    }

    /**
     * dataProvider function
     * @return array user,type of autocover, person_id, cover photo
     */
    public function getCoversChildren() {
        return array(
            array(1,"oldest", 2, 1),
            array(1,"newest", 2, 7),
            array(1,"first", 3, 5),
            array(1,"last", 4, 4),
            array(1,"newest", 4, 4),
            array(5,"oldest", 2, 1),
            array(5,"newest", 2, 7),
            array(5,"first", 2, 1),
            array(4,"last", 2, 7),
        );
    }

    /**
     * dataProvider function
     * @return user, person, array(count, oldest, newest, first, last, highest, average)
     */
    public function getDetails() {
        return array(
            array(5,2, array(
                "count"     => "1",
                "oldest"    => "2014-01-01 00:01:00",
                "newest"    => "2014-01-01 00:01:00",
                "first"     => "2013-12-31 23:01:00",
                "last"      => "2013-12-31 23:01:00",
                "lowest"    => "7.5",
                "highest"   => "7.5",
                "average"   => "7.50"
            )),
            array(4,4, array(
                "count"     => "1",
                "oldest"    => "2014-01-08 00:01:00",
                "newest"    => "2014-01-08 00:01:00",
                "first"     => "2014-01-09 23:04:00",
                "last"      => "2014-01-09 23:04:00",
                "lowest"    => "6.0",
                "highest"   => "6.0",
                "average"   => "6.00",
            )),
            array(1,6,array(
                "count"     => "1",
                "oldest"    => "2014-01-10 00:01:00",
                "newest"    => "2014-01-10 00:01:00",
                "first"     => "2014-01-09 23:02:00",
                "last"      => "2014-01-09 23:02:00",
                "lowest"    => "5.5",
                "highest"   => "5.5",
                "average"   => "5.50",
            )),
        );
    }

    /**
     * dataProvider function
     * @return array userid, topN
     */
    public function getTopNData() {
        return array(
            array(1,array(2,3,5,9,8)),
            array(5,array(2,5,9,3,7)),
            array(4,array(2,5,9,7,3))
        );
    }

    public function getAll() {
        return array(
            array(1,array(6,9,3,8,2,4,5,1,10,7)),
            array(5,array(9,3,2,5,7)),
            array(4,array(9,3,2,4,5,7)),
            array(1,array(2,4,5), "M"),
            array(5,array(9), "D"),
            array(4,array(7), "T")
        );
    }
}
