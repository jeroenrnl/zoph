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
        if(!is_null($user_id)) {
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
        foreach($people as $p) {
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
        foreach($people as $p) {
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
        foreach($people as $p) {
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


    public function getPeople() {
        return array(
            array(11, "First", "Last", null),
            array(11, "First2", "Last2", 4)
        );
    }
    
    public function getPersonAndPhotos() {
        return array(
            array(1,3),
            array(2,4),
            array(8,2)
        );
    }

    public function getPersonAndPlaces() {
        return array(
            array(1,2,3),
            array(2,1,1),
            array(8,3,6)
        );
    }
}    
