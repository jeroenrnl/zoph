<?php
/**
 * Saved search test
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

use PHPUnit\Framework\TestCase;

require_once "testSetup.php";

/**
 * Test the search class
 * This class takes care of saved searches
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class searchTest extends TestCase {

    /**
     * Create search in the database
     * @dataProvider getSearch();
     */
    public function testCreate($name, $owner, $public, $value) {
        $search=new search();
        $search->set("name", $name);
        $search->set("owner", $owner);
        $search->set("public", $public);
        $search->set("search", $value);
        $search->insert();
        $id=$search->getId();

        unset($search);
        $search=new search($id);
        $search->lookup();

        $this->assertEquals($name, $search->get("name"));
        $this->assertEquals($owner, $search->get("owner"));
        $this->assertEquals($public, $search->get("public"));
        $this->assertEquals($value, $search->get("search"));

        $search_time=strtotime($search->get("timestamp"));
        $current_time=strtotime("now");

        $difference=$current_time - $search_time;

        // If the the search time is maximum 60 seconds in the past,
        // we assume it's correct.
        $this->assertLessThanOrEqual(60, $difference);
    }

    /**
     * Test the lookup function by having different users lookup searches
     */
    public function testLookup() {
        $search=new search(1);
        $search->lookup();

        $this->assertEquals("Blue", $search->getName());

        // User 1 is admin and is allowed to see search 2, which is owner by user 2:
        $search=new search(2);
        $search->lookup();

        $this->assertEquals("More Blue", $search->getName());

        // User 5 logs in:
        user::setCurrent(new user(5));

        // User 5 is not allowed to see search 1 (it's owned by admin and not public)
        $search=new search(1);
        $search->lookup();

        $this->assertEquals("", $search->getName());

        // User 5 is allowed to see search 2, which he is owns:
        $search=new search(2);
        $search->lookup();

        $this->assertEquals("More Blue", $search->getName());

        // User 5 is allowed to see search 3, since it is public:
        $search=new search(3);
        $search->lookup();

        $this->assertEquals("Public Red", $search->getName());

        // User 1 logs back 1 so the rest of the tests work:
        user::setCurrent(new user(1));
    }

    /**
     * Test updating existing search
     */
    public function testUpdate() {
        $search=new search(1);
        $search->lookup();
        $this->assertEquals("Blue", $search->get("name"));

        $search->set("name", "Category Blue");
        $search->update();

        unset($search);

        $search=new search(1);
        $search->lookup();

        $this->assertEquals("Category Blue", $search->get("name"));

        $search_time=strtotime($search->get("timestamp"));
        $current_time=strtotime("now");

        $difference=$current_time - $search_time;

        // If the the search time is maximum 60 seconds in the past,
        // we assume it's correct.
        $this->assertLessThanOrEqual(60, $difference);

        // Change back so this testcase won't fail if it is executed again
        // before database is cleanred.
        $search->set("name", "Blue");
        $search->update();

    }

    /**
     * Test delete
     */
    public function testDelete() {
        $search=new search();
        $search->set("name", "To be deleted");
        $search->insert();

        $id=$search->getId();
        unset($search);

        // Verify it did end up in the db
        $search=new search($id);
        $search->lookup();

        $this->assertEquals("To be deleted", $search->get("name"));

        $search->delete();

        unset($search);

        $search=new search($id);

        // Lookup returns 0 if it can't find the record
        $this->assertEquals(0, $search->lookup());
    }

    /**
     * DataProvider for creating searches
     */
    public function getSearch() {
        return array(
            array("Blue", 1, 0, "category_id[0]=5"),
            array("More Blue", 5, 0, "category_id[0]=5&_category_id_children[0]=yes"),
            array("Public Red", 1, 1, "category_id[0]=2")
        );
    }
}
