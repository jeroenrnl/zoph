<?php
/**
 * A Unit Test for the breadcrumb object.
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

use db\select;
use db\clause;
use db\param;

/**
 * Test class for breadcrumbs.
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class breadcrumbTest extends ZophDatabaseTestCase {

    public function testCrumbs() {
        $obj = new user(5);
        $obj->addCrumb("test", "test.html");
        $obj->addCrumb("test", "test1.html");
        $obj->addCrumb("test2", "test2.html?what=ever");
        $obj->addCrumb("test3", "test3.html");
        $obj->addCrumb("test4", "test4.html");

        $this->assertEquals($obj->getLastCrumb(),"<a href=\"test4.html?_crumb=4\">test4</a>");
        $obj->eatCrumb(2);
        $this->assertEquals($obj->getLastCrumb(),
            "<a href=\"test2.html?_crumb=2&what=ever\">test2</a>");
        $obj->eatCrumb(1);
        $this->assertEquals($obj->getLastCrumb(),"<a href=\"test1.html?_crumb=1\">test</a>");
        $obj->eatCrumb();
        $this->assertNull($obj->getLastCrumb());
    }

}
?>
