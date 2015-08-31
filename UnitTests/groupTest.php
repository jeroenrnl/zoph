<?php
/**
 * Group test
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
 * Test the group class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class groupTest extends ZophDataBaseTestCase {

    /**
     * Create groups in the db
     * @dataProvider getGroups();
     */
    public function testCreateGroups($id, $name, array $members) {
        $group=new group();
        $group->set("group_name", $name);
        $group->insert();
        $this->assertEquals($group->get("group_id"), $id);
        foreach ($members as $member) {
            $user=user::getByName($member);
            $group->add_member($user->getId());
        }
        $group->update();
    }

    /**
     * Test get_albums() function
     * @dataProvider getGroupAlbums();
     */
    public function testGetAlbums($groupId, array $expAlbumids) {
        $group=new group($groupId);
        $albums=$group->get_albums();

        $actAlbumids=array();
        foreach ($albums as $album) {
            $actAlbumids[]=$album->getId();
        }

        $this->assertEquals($expAlbumids, $actAlbumids);
    }

    /**
     * Test get_members() function
     * @dataProvider getGroupMembers();
     */
    public function testGetMembers($groupId, array $expUserIds) {
        $group=new group($groupId);
        $users=$group->get_members();

        $actUserIds=array();
        foreach ($users as $user) {
            $actUserIds[]=$user->getId();
        }

        $this->assertEquals($expUserIds, $actUserIds);
    }

    /**
     * Test remove_members() function
     */
    public function testRemoveMembers() {
        $group=new group(1);
        $group->remove_members(2);
        $group->remove_members(array(5,7));

        $users=$group->get_members();
        foreach ($users as $user) {
            $actUserIds[]=$user->getId();
        }

        $this->assertEquals(array(9), $actUserIds);
    }

    public function getGroups() {
        return array(
            array(5, "TestGroup", array("freddie", "johnd","brian","roger")),
            array(5, "AnotherTestGroup", array("paul","jimi"))
        );
    }

    public function getGroupAlbums() {
        return array(
            array(1, array(1,2)),
            array(2, array(1,2,3)),
            array(3, array())
        );
    }

    public function getGroupMembers() {
        return array(
            array(1, array(2,5,7,9)),
            array(2, array(4,8)),
            array(4, array(2,3))
        );
    }

}
