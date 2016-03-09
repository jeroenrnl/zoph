<?php
/**
 * Test photographer class
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
 * Test class for photographer class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class photographerTest extends ZophDataBaseTestCase {

    public function testAddPhoto() {
        $pg=new photographer(3);
        $photo=new photo(2);

        $pg->lookup();
        $photo->lookup();

        $pg->addPhoto($photo);

        $photo->update();

        $actual=$photo->getPhotographer();

        $this->assertEquals($pg->getId(), $actual->getId());
    }

    public function testRemovePhoto() {
        $pg=new photographer(2);
        $photo=new photo(2);

        $pg->lookup();
        $photo->lookup();

        $pg->removePhoto($photo);
        $photo->update();

        $actual=$photo->getPhotographer();
        $this->assertEquals(null, $actual);
    }

    /**
     * Test getPhotoCount() function
     * @param int user id to log on with
     * @param int photographer id to test
     * @param int expected count
     * @dataProvider getPhotoCount()
     */
    public function testPhotoCount($user_id, $pg_id, $count) {
        user::setCurrent(new user($user_id));

        $pg=new photographer($pg_id);

        $this->assertEquals($count, $pg->getPhotoCount());

        user::setCurrent(new user(1));
    }

    /**
     * Test getPhotoCount() function
     * @param int user id to log on with
     * @param int photographer id to test
     * @param int expected count
     * @dataProvider getAllPhotographers()
     */
    public function testGetAll($user_id, array $pg_ids) {
        user::setCurrent(new user($user_id));

        $pgs=photographer::getAll();

        foreach ($pgs as $pg) {
            $this->assertContains($pg->getId(), $pg_ids);
        }

        user::setCurrent(new user(1));
    }


    public function getPhotoCount() {
        // user_id, photographer_id, count
        return array(
            array(1, 2, 3),
            array(1, 3, 4),
            array(1, 4, 1),
            array(5, 2, 1),
            array(5, 3, 1),
            array(5, 5, 0),
            array(4, 2, 2)
         );
    }

    public function getAllPhotographers() {
        return array(
            array(1, array(1,2,3,4,5,6,7,8,9,10,11)),
            array(5, array(2,3)),
            array(4, array(2,3,4))
        );
     }


}
