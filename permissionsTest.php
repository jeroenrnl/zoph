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

    public function getPermissions() {
        return array(
            array(5, array(1,2,3), 4,0,false),
            array(5, array(4,5,6), 2,2,false),
            array(5, array(2,4,6), 4,1,true),
            array(5, array(1,3,5), 4,5,true),
        );
    }
}
