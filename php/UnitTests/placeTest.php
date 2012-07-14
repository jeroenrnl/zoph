<?php
/*
 * Place test
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
 * Test the place class
 */
class placeTest extends ZophDataBaseTestCase {

    /**
     * Create place in the database
     * @dataProvider getPlaces();
     */
    public function testCreatePlace($id, $name, $parent) {
        $place=new place();
        $place->set("title",$name);
        $place->set("parent_place_id", $parent);
        $place->insert();
        $this->assertInstanceOf("place", $place);
        $this->assertEquals($place->getId(), $id);
    }

    public function getPlaces() {
        return array(
            array(18, "City", 1),
            array(18, "Town", 4)
        );
    }
}    
