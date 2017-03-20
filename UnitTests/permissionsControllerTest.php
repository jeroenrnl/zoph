<?php
/**
 * Permission controller test
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
use permissions\controller;
use PHPUnit\Framework\TestCase;
use web\request;


/**
 * Test the group controller class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class permissionControllerTest extends TestCase {

    /**
     * The "new", "edit" and "delete" actions do not exist for the permissionsController
     * thus alll these are an illegal actions, this should result in
     * "display".
     * @dataProvider getActions();
     */
    public function testBasicActions($action, $expView) {
        $request=new request(array(
            "GET"   => array(
                "_action" => $action,
                "group_id" => 2,
                "album_id" => 5),
            "POST"  => array(),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals($expView, $controller->getView());
    }
    /**
     * Update all albums
     */
    public function testUpdateAllAlbumsAction() {
        // Remove current permissions first
        $albums=album::getAll();
        foreach ($albums as $album) {
            $perm=new permissions(3, $album->getId());
            $perm->delete();
        }

        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"                       => "updatealbums",
                "_access_level_all_checkbox"    => 1,
                "group_id"                      => 3,
                "access_level_all"              => 2,
                "writable_all"                  => 1),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals("group", $controller->getView());

        $albums=album::getAll();
        foreach ($albums as $album) {

            $perm=new permissions(3, $album->getId());
            $perm->lookup();

            $this->assertEquals(2, $perm->get("access_level"));
            $this->assertEquals(1, $perm->get("writable"));
            $this->assertEquals(0, $perm->get("watermark_level"));
        }
    }

    /**
     * Remove album permissions
     */
    public function testUpdateRemoveAlbumsAction() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"                       => "updatealbums",
                "_remove_permission_album__2"   => 1,
                "group_id"                      => 3),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals("group", $controller->getView());


        $perm=new permissions(3, 2);
        $perm->lookup();

        $this->assertEquals("", $perm->get("access_level"));
    }

    /**
     * Remove album permissions
     */
    public function testUpdateAddAlbumsAction() {
        $perm=new permissions(3, 1);
        $perm->delete();
        conf::set("watermark.enable", true);
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"                       => "updatealbums",
                "album_id_new"                  => 1,
                "group_id"                      => 3,
                "group_id_new"                  => 3,
                "access_level_new"              => 4,
                "watermark_level_new"           => 6,
                "writable_new"                  => 0),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals("group", $controller->getView());


        $perm=new permissions(3, 1);
        $perm->lookup();

        $this->assertEquals(4, $perm->get("access_level"));
        $this->assertEquals(0, $perm->get("writable"));
        $this->assertEquals(6, $perm->get("watermark_level"));
        conf::set("watermark.enable", false);
    }

    /**
     * Update all groups
     */
    public function testUpdateAllGroupsAction() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"                       => "updategroups",
                "_access_level_all_checkbox"    => 1,
                "album_id"                      => 4,
                "access_level_all"              => 2,
                "writable_all"                  => 1),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals("album", $controller->getView());

        $groups=group::getAll();
        foreach ($groups as $group) {

            $perm=new permissions($group->getId(), 4);
            $perm->lookup();

            $this->assertEquals(2, $perm->get("access_level"));
            $this->assertEquals(1, $perm->get("writable"));
            $this->assertEquals(0, $perm->get("watermark_level"));
        }
    }

    /**
     * Remove album permissions
     */
    public function testUpdateRemoveGroupAction() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"                       => "updategroups",
                "_remove_permission_group__2"   => 1,
                "album_id"                      => 3),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals("album", $controller->getView());


        $perm=new permissions(2, 3);
        $perm->lookup();

        $this->assertEquals("", $perm->get("access_level"));
    }

    /**
     * Add group permissions
     */
    public function testUpdateAddGroupsAction() {
        $perm=new permissions(3, 1);
        $perm->delete();
        conf::set("watermark.enable", true);
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(
                "_action"                       => "updategroups",
                "album_id_new"                  => 1,
                "album_id"                      => 1,
                "group_id_new"                  => 3,
                "access_level_new"              => 4,
                "watermark_level_new"           => 6,
                "writable_new"                  => 0),
            "SERVER" => array()
        ));

        $controller = new controller($request);
        $this->assertEquals("album", $controller->getView());


        $perm=new permissions(3, 1);
        $perm->lookup();

        $this->assertEquals(4, $perm->get("access_level"));
        $this->assertEquals(0, $perm->get("writable"));
        $this->assertEquals(6, $perm->get("watermark_level"));
        conf::set("watermark.enable", false);
    }

    public function getActions() {
        return array(
            array("new", "display"),
            array("edit", "display"),
            array("delete", "display"),
            array("nonexistant", "display")
        );
    }
}

