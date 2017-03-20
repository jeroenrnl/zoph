<?php
/**
 * Group controller test
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

use group\controller;
use PHPUnit\Framework\TestCase;
use web\request;

/**
 * Test the group controller class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class groupControllerTest extends TestCase {

    /**
     * Test the "new", "edit" and "delete" actions
     * also tests handing an illegal action, this should result in
     * "display".
     * @dataProvider getActions
     */
    public function testBasicActions($action, $expView) {
        $request=new request(array(
            "GET"   => array("_action" => $action),
            "POST"  => array(),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals($expView, $controller->getView());
    }

    /**
     * Create group in the db
     */
    public function testInsertAction() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "insert",
                "group_name"    => "The Animals",
                "description"   => "60s rock band"),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $group=$controller->getObject();

        $this->assertEquals("update", $controller->getView());
        $this->assertEquals("The Animals", $group->getName());

        return $group;
    }

    /**
     * Update group in the db
     * @depends testInsertAction
     */
    public function testUpdateAction(group $group) {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "update",
                "group_id"      => $group->getId(),
                "group_name"    => "Eric Burtons Animals",
                "_member"    =>  2
            ),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $group=$controller->getObject();

        $this->assertEquals("update", $controller->getView());
        $this->assertEquals("Eric Burtons Animals", $group->getName());
        $this->assertEquals(2, $group->getMembers()[0]->getId());

        return $group;
    }

    /**
     * Update group, remove member
     * @depends testUpdateAction
     */
    public function testUpdateRemoveMemberAction(group $group) {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "update",
                "group_id"      => $group->getId(),
                "_removeMember" =>  array(2)
            ),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $group=$controller->getObject();

        $this->assertEquals("update", $controller->getView());
        $this->assertEquals("Eric Burtons Animals", $group->getName());
        $this->assertEquals(0, sizeof($group->getMembers()));

        return $group;
    }

    /**
     * Test confirm (delete) acrion
     * @depends testUpdateRemoveMemberAction
     */
    public function testConfirmAction(group $group) {
        $id=$group->getId();
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "confirm",
                "group_id"      => $id,
            ),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $groups=group::getAll();
        $ids=array();
        foreach ($groups as $group) {
            $ids[]=$group->getId();
        }
        $this->assertNotContains($id, $ids);
    }

    public function getActions() {
        return array(
            array("new", "insert"),
            array("edit", "update"),
            array("delete", "confirm"),
            array("nonexistant", "display")
        );
    }
}
