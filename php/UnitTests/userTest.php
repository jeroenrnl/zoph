<?php
/*
 * A Unit Test for the user object.
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

require_once("testSetup.php");
/**
 * Test class for user.
 */
class userTest extends ZophDatabaseTestCase {
    /**
     * @var user
     */
    protected $object;

    /**
     * Sets up the fixture
     * For now, we simply setup a global var $lang, just like
     * the webinterface does during login.
     * @todo: should load a language depending on user preference
     *        like the webinterface.
     */
    protected function setUp() {
        global $lang;

        $lang=new language("en");
        $this->getDataSet();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * Test getId() method
     */
    public function testGetId() {
        $obj = new user(1);
        $id=$obj->getId();
        $this->assertEquals($id,1);
        
        $obj = new user(3);
        $id=$obj->getId();
        $this->assertEquals($id,3);
    }

    public function testCreateAndDelete() {
        $user = new user();
        $user->set("user_name", "Test User");
        $user->insert();

        $id = $user->getId();

        unset($user);
        
        $sql="SELECT count(user_id) as count FROM zoph_users WHERE user_id=" . $id;

        $result=query($sql);
        $row=mysql_fetch_row($result);
        $this->assertEquals($row[0], 1);


        $new_user=new user($id);
        $new_user->lookup();

        $this->assertInstanceOf("user", $new_user);
        $this->assertEquals($new_user->getId(), $id);
        $this->assertEquals($new_user->getName(), "Test User");

        $new_user->delete();

        $sql="SELECT count(user_id) as count FROM zoph_users WHERE user_id=" . $id;
        $result=query($sql);
        $row=mysql_fetch_row($result);
        $this->assertEquals($row[0], 0);
    }


    /**
     * Test lookup_person() method
     */
    public function testLookup_person() {
        $obj = new user(3);
        $obj->lookup();
        $obj->lookup_person();
        $this->assertInstanceOf("person", $obj->person);
        $name=$obj->person->getName();
        $this->assertEquals("Jimi Hendrix",$name);
       
        unset($obj);
        $obj = new user(10);
        $obj->lookup();
        $obj->lookup_person();
        $this->assertInstanceOf("person", $obj->person);
        $name=$obj->person->getName();
        $this->assertEquals("",$name);
    }

    /**
     * Test lookup_prefs() method
     */
    public function testLookup_prefs() {
        $obj = new user(1);
        $obj->lookup_prefs();
        $this->assertInstanceOf("prefs", $obj->prefs);
    }

    /**
     * Test is_admin() method.
     */
    public function testIs_admin() {
        $obj = new user(1);
        $obj->lookup();
        $this->assertTrue($obj->is_admin());
        $obj = new user(7);
        $obj->lookup();
        $this->assertFalse($obj->is_admin());
    }

    /**
     * Test get_lastnotify() method.
     */
    public function testGet_lastnotify() {
        $obj = new user(1);
        $ln=$obj->get_lastnotify();
        $this->assertEquals($ln,"");
    }

    /**
     * Test getLink() method.
     */
    public function testGetLink() {
        $obj = new user(1);

        $this->assertEquals($obj->getLink(),"<a href='user.php?user_id=1'></a>");
    }

    /**
     * Test getURL() method.
     */
    public function testGetURL() {
        $obj = new user(1);

        $this->assertEquals($obj->getURL(),"user.php?user_id=1");
    }

    /**
     * Test getName() method.
     */
    public function testGetName() {
        $obj = new user(1);
        $obj->lookup();
        $name=$obj->getName();
        $this->assertEquals($name,"admin");
        
    }

    /**
     * Test get_groups() method.
     */
    public function testGet_groups() {
        $obj = new user(1);
        $g=$obj->get_groups();
        $this->assertInternalType("array", $g);
        $this->assertEmpty($g);
    }

    /**
     * Test get_album_permissions() method.
     * @dataProvider getAlbumIds1
     */
    public function testGet_album_permissions1($id, $perm) {
        $obj = new user(1);
        $ap=$obj->get_album_permissions($id);
        $this->assertEquals($ap,$perm);
    }

    /**
     * Test get_album_permissions() method.
     * @dataProvider getAlbumIds3
     */
    public function testGet_album_permissions3($id, $perm) {
        $obj = new user(3);
        $obj->lookup();
        $ap=$obj->get_album_permissions($id);
        if(is_null($perm)) {
            $this->assertEquals($ap,$perm);
        } else {
            $this->assertEquals($ap->get("group_id"), $perm);
            $this->assertEquals($ap->get("album_id"), $id);
        }
    }

    /**
     * Test get_permissions_for_photo() method.
     * @dataProvider getPhotoPermissionsForUser1
     */
    public function testGet_permissions_for_photo1($id, $perm) {
        $obj = new user(1);
        $pp=$obj->get_permissions_for_photo($id);
        $this->assertEquals($pp,$perm);
    }

    /**
     * Test get_permissions_for_photo() method.
     * @dataProvider getPhotoPermissionsForUser3
     */
    public function testGet_permissions_for_photo3($id, $perm) {
        $obj = new user(3);
        $pp=$obj->get_permissions_for_photo($id);
        
        if(is_null($perm)) {
            $this->assertNull($pp);
        } else {
            $this->assertInstanceOf("group_permissions", $pp);
            $this->assertEquals($pp->get("album_id"),$perm[0]);
            $this->assertEquals($pp->get("group_id"),$perm[1]);
        }
    }

    /**
     * Test getDisplayArray() method.
     */
    public function testGetDisplayArray() {
        $obj = new user(1);
        $obj->lookup();
        $obj->load_language();
        $da=$obj->getDisplayArray();
        $this->assertInternalType("array", $da);

        // We don't want the tests to fail because someone logged in to
        // the account. So we just check if the key is there and
        // delete them prior to checking the contents.
        /** @todo This should eventually be replaced by a data fixture that
            will simply reload the data prior to testing */
        $this->assertArrayHasKey("last login", $da);
        $this->assertArrayHasKey("last ip address", $da);
        
        unset($da["last login"]);
        unset($da["last ip address"]);


        $expected=array (
          'username' => 'admin',
          'person' => '<a href="person.php?person_id=1">Unknown Person</a>',
          'class' => 'Admin',
          'can browse people' => 'Yes',
          'can browse places' => 'Yes',
          'can browse tracks' => 'Yes',
          'can view details of people' => 'Yes',
          'can view details of places' => 'Yes',
          'can import' => 'Yes',
          'can download zipfiles' => 'Yes',
          'can leave comments' => 'Yes',
          'can rate photos' => 'Yes',
          'can rate the same photo multiple times' => 'No',
          'can share photos' => 'No',
        );
        $this->assertEquals($da, $expected);
    }
    
    /**
     * Test getDisplayArray() method for a user with a lightbox Album
     */
    public function testGetDisplayArrayWithLightbox() {
        $obj = new user(7);
        $obj->lookup();
        $obj->lookup();
        $obj->load_language();
        $da=$obj->getDisplayArray();

        $this->assertArrayHasKey("lightbox album",$da);
    }

    /**
     * Test load_language() method.
     * @dataProvider getTrueFalse
     */
    public function testLoad_language($force) {
        $obj = new user(1);
        $lang=$obj->load_language($force);
        $this->assertInstanceOf("language", $lang);

        $obj->lookup_prefs();
        $obj->prefs->set("language", "nl");
        
        $lang=$obj->load_language($force);
        $this->assertInstanceOf("language", $lang);
    }

    public function testCrumbs() {
        $obj = new user(5);
        $obj->add_crumb("test", "test.html");
        $obj->add_crumb("test", "test1.html");
        $obj->add_crumb("test2", "test2.html?what=ever");
        $obj->add_crumb("test3", "test3.html");
        $obj->add_crumb("test4", "test4.html");

        $this->assertEquals($obj->get_last_crumb(),"<a href=\"test4.html?_crumb=4\">test4</a>");
        $obj->eat_crumb(2);
        $this->assertEquals($obj->get_last_crumb(),"<a href=\"test2.html?_crumb=2&what=ever\">test2</a>");
        $obj->eat_crumb(1);
        $this->assertEquals($obj->get_last_crumb(),"<a href=\"test1.html?_crumb=1\">test</a>");
        $obj->eat_crumb();
        $this->assertNull($obj->get_last_crumb());
    }
    
    /**
     * Test get_rating_graph() method.
     * @dataProvider getRatingGraphForUser
     * @todo only checks the type of the return data, not the actual data
     *       this should change once get_rating_graph return something
     *       else then a pile of HTML.
     */
    public function test_get_rating_graph($user, $graph) {
        $obj=new user($user);

        if(is_null($graph)) {
            $this->assertNull($obj->get_rating_graph());
        } else {
            $this->assertInternalType("string", $obj->get_rating_graph());
        }
    }

    /**
     * Return a list of album id's used for testing and permissions for userid 1
     * User id 1 is the admin user, who is not member of a group
     * and therefore has no permissions.
     */
    public function getAlbumIds1() {
        return array(
            array(1,null),
            array(3,null),
            array(5,null),
            array(7,null)
        );
    }
    /**
     * Return a list of album id's used for testing and permissions for userid 3
     * User id 3 is 'jimi', who is a member of the "guitarists" group.
     * and therefore has no permissions.
     */
    public function getAlbumIds3() {
        return array(
            array(1,4),
            array(2,4),
            array(3,null),
            array(4,null),
            array(5,null),
            array(6,null),
            array(7,null),
            array(8,null),
            array(9,null),
            array(10,null)
        );
    }

    /**
     * Return a list of photo id's and permissions for user 1
     * User 1 is admin, so no permissions will be returned.
     */
    public function getPhotoPermissionsForUser1() {
        return array(
            array(1,null),
            array(2,null),
            array(3,null),
            array(4,null),
            array(5,null),
            array(6,null),
            array(7,null),
            array(8,null),
            array(9,null),
            array(10,null),
        );
    }

    /**
     * Return a list of photo id's and permissions for user 3
     */
    public function getPhotoPermissionsForUser3() {
        return array(
            array(1,array(2,4)),
            array(2,null),
            array(3,null),
            array(5,null),
            array(4,null),
            array(6,null),
            array(7,array(2,4)),
            array(8,null),
            array(9,null),
            array(10,null),
        );
    }

    /**
     * Return true and false to test settings
     */
     public function getTrueFalse() {
        return array(
            array(True),
            array(False)
        );
     }

     public function getRatingGraphForUser() {
        return array(
            array(1, null),
            array(2, ""),
            array(3, ""),
            array(6, "")
        );
     }


}
?>
