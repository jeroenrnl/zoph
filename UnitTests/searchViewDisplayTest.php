<?php
/**
 * Search View test
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

use search\view\display;
use PHPUnit\Framework\TestCase;
use web\request;
use conf\conf;

/**
 * Test the search view display class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class searchViewDisplayTest extends TestCase {

    /**
     * Test creating a View
     */
    public function testCreate() {
        $request=new request(array(
            "GET"   => array(),
            "POST"  => array(),
            "SERVER" => array()
        ));

        $display = new display($request);
        $this->assertInstanceOf('\search\view\display', $display);
        return $display;
    }

    /**
     * Create view
     * 
     * @depends testCreate
     */
    public function testView(display $display) {
        $tpl=$display->view();
        $this->assertInstanceOf('\template\template', $tpl);
    }

    /**
     * Create view with map
     * 
     * @depends testCreate
     */
    public function testViewWithMap(display $display) {
        conf::set("maps.provider", "osm");
        $tpl=$display->view();
        $this->assertInstanceOf('\template\template', $tpl);
        conf::set("maps.provider", "");
    }
}
