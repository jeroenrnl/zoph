<?php
/**
 * Permissions Test
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

/**
 * Test Permissions class.
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class permissionsTest extends ZophDataBaseTestCase {

    /**
     * Create permissions in the db
     * @dataProvider getPermissions();
     */
    public function testCreatePermissions($group, $albums, $al, $wml, $wr) {
        $gr=new group($group);
        $gr->lookup();
        foreach ($albums as $alb) {
            $prm=new permissions($group, $alb);
            $prm->set("access_level", $al);
            $prm->set("watermark_level", $wml);
            $prm->set("writable", $wr);
            $prm->insert();

            $perm=$gr->getGroupPermissions(new album($alb));
            $this->assertEquals($al, $perm->get("access_level"));
            $this->assertEquals($wml, $perm->get("watermark_level"));
            $this->assertEquals($wr, $perm->get("writable"));
        }
    }

    public function testCreatePermissionsWithSubalbums() {
        $perm = new permissions(2,7);
        $perm->set("access_level", 5);
        $perm->set("watermark_level", 5);
        $perm->set("writable", "0");
        $perm->set("subalbums", "1");

        $perm->insert();
        // Check if the new permissions have been inserted
        $perm = new permissions(2,7);
        $this->assertTrue($perm->lookup());

        // ...and check if the subalbums have automatically received the new permissions
        $perm = new permissions(2,9);
        $this->assertTrue($perm->lookup());

        $perm = new permissions(2,10);
        $this->assertTrue($perm->lookup());

    }

    /**
     * Test update permissions in the db
     * this also tests the delete() function
     */
    public function testUpdatePermissionsWithSubalbums() {
        $perm = new permissions(2,12);
        $perm->set("access_level", 5);
        $perm->set("watermark_level", 5);
        $perm->set("writable", "0");
        $perm->set("subalbums", "1");
        $perm->insert();

        $testAlbum=new album();
        $testAlbum->set("album", "Album 31");
        $testAlbum->set("parent_album_id", 12);
        $testAlbum->insert();
        $id=$testAlbum->getId();

        // Because of the automatic subalbum permissions, a
        // permission entry for this album should have been
        // automatically created. Let's see...
        $testPerm = new permissions(2, $id);
        $this->assertTrue($testPerm->lookup());

        // Now we delete this permission.
        $testPerm->delete();

        // And update the original permissions.
        $perm->update();

        // Because we did not change the subalbum permission
        // on the original, it should NOT recreate the permission
        $testPerm = new permissions(2, $id);
        $this->assertFalse($testPerm->lookup());

        // But, if we flip the setting, it should be recreated:
        $perm->set("subalbums", "0");
        $perm->update();
        $perm->set("subalbums", "1");
        $perm->update();
        $testPerm = new permissions(2, $id);
        $this->assertTrue($testPerm->lookup());

        // Finally, we delete the original permission

        $perm->delete();

        // This should also delete the new one, let's check
        $this->assertFalse($testPerm->lookup());

    }

    public function testGetNameAndId() {
        $perm = new permissions(3, 2);
        $perm->lookup();

        $exp = array(
            "album_id"  => 2,
            "group_id"  => 3);

        $this->assertEquals($exp, $perm->getId());

        $this->assertEquals("Album 1", $perm->getAlbumName());
        $this->assertEquals("Genesis", $perm->getGroupName());
    }

    public function getPermissions() {
        return array(
            array(5, array(1,2,3), 4,0,false),
            array(5, array(4,5,6), 2,2,false),
            array(5, array(2,4,6), 4,1,true),
            array(5, array(1,3,5), 4,5,true),
        );
    }
}
