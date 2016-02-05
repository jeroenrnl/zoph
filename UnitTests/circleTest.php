<?php
/**
 * Test the circle class
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
 * Run tests on the circle class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class circleTest extends ZophDataBaseTestCase {

    /**
     * Create circles in the db
     * @dataProvider getCircles();
     */
    public function testCreateCircles($id, $name, array $members) {
        $circle=new circle();
        $circle->set("circle_name", $name);
        $circle->insert();
        $this->assertEquals($circle->getId(), $id);
        foreach ($members as $member) {
            $person=person::getByName($member);
            $circle->addMember($person[0]);
        }
        $circle->update();
    }

    /**
     * Test getName() function
     */
    public function testGetName() {
        $circle=new circle(1);
        $circle->lookup();
        $this->assertEquals("Queen", $circle->getName());
    }

    /**
     * Test getDisplayArray function
     */
    public function testGetDisplayArray() {
        $circle=new circle(1);
        $circle->lookup();
        $memberLinks=$circle->getMemberLinks();
        $exp=array(
            "circle"        => "Queen",
            "description"   => "",
            "members"       => implode("<br>", $memberLinks)
        );
        $this->assertEquals($exp, $circle->getDisplayArray());
    }

    /**
     * Test getMemberLinks() function
     */
    public function testGetMemberLinks() {
        $circle=new circle(3);
        $exp=array(
            "<a href=\"person.php?person_id=4\">Paul McCartney</a>",
            "<a href=\"person.php?person_id=9\">John Deacon</a>"
        );
        $this->assertEquals($exp, $circle->getMemberLinks());
    }

    /**
     * Test getMembers() function
     * @dataProvider getCircleMembers();
     */
    public function testGetMembers($circleId, array $expPersonIds) {
        $circle=new circle($circleId);
        $members=$circle->getMembers();

        $actPersonIds=array();
        foreach ($members as $member) {
            $actPersonIds[]=$member->getId();
        }

        $this->assertEquals($expPersonIds, $actPersonIds);
    }

    /**
     * Test getNonMembers() function
     */
    public function testGetNonMembers() {
        $circle=new circle(1);
        $nonMembers=$circle->getNonMembers();

        $actPersonIds=array();
        foreach ($nonMembers as $nonMember) {
            $actPersonIds[]=$nonMember->getId();
        }
        sort($actPersonIds);
        $this->assertEquals(array(1,3,4,6,8,10), $actPersonIds);
    }

    /**
     * Test removeMembers() function
     */
    public function testRemoveMembers() {
        $actPersonIds=array();

        $circle=new circle(1);
        $circle->removeMember(new person(2));
        $circle->removeMember(new person(5));
        $circle->removeMember(new person(7));

        $members=$circle->getMembers();
        foreach ($members as $member) {
            $actPersonIds[]=$member->getId();
        }

        $this->assertEquals(array(9), $actPersonIds);
    }

    public function getCircles() {
        return array(
            array(4, "TestCircle", array("Freddie Mercury", "John Deacon","Brian May","Roger Taylor")),
            array(4, "AnotherTestCircle", array("Paul McCartney","Jimi Hendrix"))
        );
    }

    public function getCircleMembers() {
        return array(
            array(1, array(2,5,7,9)),
            array(2, array(2,4,5,6,8)),
            array(3, array(4,9))
        );
    }

}
