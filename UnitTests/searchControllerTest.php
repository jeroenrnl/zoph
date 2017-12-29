<?php
/**
 * Search controller test
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

use search\controller;
use PHPUnit\Framework\TestCase;
use web\request;

/**
 * Test the search controller class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class searchControllerTest extends TestCase {

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
     * Create search in the db
     */
    public function testNewAction() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "new",
                "album_id#0"    => "5"
            ),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $search=$controller->getObject();

        $this->assertEquals("insert", $controller->getView());
        $this->assertEquals("album_id&#91;0&#93;=5", $search->get("search"));

        return $search;
    }

    /**
     * Create search in the db
     * @depends testNewAction
     */
    public function testInsertAction() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "insert",
                "search"        => "albun_id#0=5",
                "name"          => "Search for Album 2"),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $search=$controller->getObject();

        $this->assertEquals("display", $controller->getView());
        $this->assertEquals("Search for Album 2", $search->getName());

        return $search;
    }

    /**
     * Update search in the db
     * @depends testInsertAction
     */
    public function testUpdateAction(search $search) {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "update",
                "search_id"     => $search->getId(),
                "name"          => "Search for the second album",
            ),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $search=$controller->getObject();

        $this->assertEquals("display", $controller->getView());
        $this->assertEquals("Search for the second album", $search->getName());

        return $search;
    }

    /**
     * Test confirm (delete) acrion
     * @depends testUpdateAction
     */
    public function testConfirmAction(search $search) {
        $id=$search->getId();
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"       => "confirm",
                "search_id"      => $id,
            ),
            "SERVER" => array()
        ));

        $controller = new controller($request);

        $searches=search::getAll();
        $ids=array();
        foreach ($searches as $search) {
            $ids[]=$search->getId();
        }
        $this->assertNotContains($id, $ids);
    }

    public function getActions() {
        return array(
            array("new", "insert"),
            array("edit", "update"),
            array("delete", "confirm"),
            array("search", "photos"),
            array("nonexistant", "display")
        );
    }
}
