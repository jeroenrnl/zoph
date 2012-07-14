<?php
/*
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

    public function getPeople() {
        return array(
            array(11, "First", "Last", null),
            array(11, "First2", "Last2", 4)
        );
    }
}    
