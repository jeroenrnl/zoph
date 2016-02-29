<?php
/**
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

require_once "testSetup.php";

use db\select;
use db\clause;
use db\param;

/**
 * Test class for user.
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
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
        $user->set("password", "secret");
        $user->insert();

        $id = $user->getId();

        unset($user);

        $qry=new select(array("users"));
        $qry->addFunction(array("count" => "COUNT(user_id)"));
        $qry->where(new clause("user_id=:userid"));
        $qry->addParam(new param(":userid", $id, PDO::PARAM_INT));

        $this->assertEquals($qry->getCount(), 1);

        // Test Password

        $validator=new validator("Test User", "secret");
        $user=$validator->validate();
        $user->lookup();

        $this->assertEquals($user->getId(), $id);


        // Delete user

        $new_user=new user($id);
        $new_user->lookup();

        $this->assertInstanceOf("user", $new_user);
        $this->assertEquals($new_user->getId(), $id);
        $this->assertEquals($new_user->getName(), "Test User");

        $new_user->delete();

        $qry=new select(array("users"));
        $qry->addFunction(array("count" => "COUNT(user_id)"));
        $qry->where(new clause("user_id=:userid"));
        $qry->addParam(new param(":userid", $id, PDO::PARAM_INT));

        $this->assertEquals($qry->getCount(), 0);
    }

    public function testSetPasswordOnUpdate() {
        $user=new user(3);
        $user->lookup();
        $user->set("password", "secret");
        $user->update();

        unset($user);

        $validator=new validator("jimi", "secret");
        $user=$validator->validate();
        $user->lookup();

        $this->assertEquals($user->getId(), 3);
    }


    /**
     * Test lookupPerson() method
     */
    public function testLookup_person() {
        $obj = new user(3);
        $obj->lookup();
        $obj->lookupPerson();
        $this->assertInstanceOf("person", $obj->person);
        $name=$obj->person->getName();
        $this->assertEquals("Jimi Hendrix",$name);

        unset($obj);
        $obj = new user(10);
        $obj->lookup();
        $obj->lookupPerson();
        $this->assertInstanceOf("person", $obj->person);
        $name=$obj->person->getName();
        $this->assertEquals("",$name);
    }

    /**
     * Test lookupPrefs() method
     */
    public function testLookup_prefs() {
        $obj = new user(1);
        $obj->lookupPrefs();
        $this->assertInstanceOf("prefs", $obj->prefs);
    }

    /**
     * Test isAdmin() method.
     */
    public function testIs_admin() {
        $obj = new user(1);
        $obj->lookup();
        $this->assertTrue($obj->isAdmin());
        $obj = new user(7);
        $obj->lookup();
        $this->assertFalse($obj->isAdmin());
    }

    /**
     * Test getLastNotify() method.
     */
    public function testGet_lastnotify() {
        $obj = new user(1);
        $ln=$obj->getLastNotify();
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
     * Test getGroups() method.
     */
    public function testGet_groups() {
        $obj = new user(1);
        $g=$obj->getGroups();
        $this->assertInternalType("array", $g);
        $this->assertEmpty($g);
    }

    /**
     * Test getAlbumPermissions() method.
     * @dataProvider getAlbumIds1
     */
    public function testGet_album_permissions1($id, $perm) {
        $obj = new user(1);
        $ap=$obj->getAlbumPermissions(new album($id));
        $this->assertEquals($ap,$perm);
    }

    /**
     * Test getAlbumPermissions() method.
     * @dataProvider getAlbumIds3
     */
    public function testGet_album_permissions3($id, $perm) {
        $obj = new user(3);
        $obj->lookup();
        $ap=$obj->getAlbumPermissions(new album($id));
        if (is_null($perm)) {
            $this->assertEquals($ap,$perm);
        } else {
            $this->assertEquals($perm, $ap->get("group_id"));
            $this->assertEquals($id, $ap->get("album_id"));
        }
    }

    /**
     * Test getPhotoPermissions() method.
     * @dataProvider getPhotoPermissionsForUser1
     */
    public function testGet_permissions_for_photo1($id, $perm) {
        $obj = new user(1);
        $pp=$obj->getPhotoPermissions(new photo($id));
        $this->assertEquals($pp,$perm);
    }

    /**
     * Test getPhotoPermissions() method.
     * @dataProvider getPhotoPermissionsForUser3
     */
    public function testGet_permissions_for_photo3($id, $perm) {
        $obj = new user(3);
        $pp=$obj->getPhotoPermissions(new photo($id));

        if (is_null($perm)) {
            $this->assertNull($pp);
        } else {
            $this->assertInstanceOf("group_permissions", $pp);
            $this->assertEquals($perm[0],$pp->get("album_id"));
            $this->assertEquals($perm[1],$pp->get("group_id"));
        }
    }

    /**
     * Test getDisplayArray() method.
     */
    public function testGetDisplayArray() {
        $obj = new user(1);
        $obj->lookup();
        $obj->loadLanguage();
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
          'can view hidden circles' => 'Yes',
          'can share photos' => 'No',
        );
        $this->assertEquals($expected, $da);
    }

    /**
     * Test getDisplayArray() method for a user with a lightbox Album
     */
    public function testGetDisplayArrayWithLightbox() {
        $obj = new user(7);
        $obj->lookup();
        $obj->lookup();
        $obj->loadLanguage();
        $da=$obj->getDisplayArray();

        $this->assertArrayHasKey("lightbox album",$da);
    }

    /**
     * Test loadLanguage() method.
     * @dataProvider getTrueFalse
     */
    public function testLoad_language($force) {
        $obj = new user(1);
        $lang=$obj->loadLanguage($force);
        $this->assertInstanceOf("language", $lang);

        $obj->lookupPrefs();
        $obj->prefs->set("language", "nl");

        $lang=$obj->loadLanguage($force);
        $this->assertInstanceOf("language", $lang);
    }

    /**
     * Test getRatingGraph() method.
     * Tests only 1 user with no ratings
     * full testing is done in the rating object
     */
    public function testGetRatingGraph() {
        $user=new user(1);
        $graph=$user->getRatingGraph();
        $this->assertInternalType("array", $graph);

        // Check if all keys are present
        for ($i=1; $i<=10; $i++) {
            $this->assertArrayHasKey($i, $graph);
        }

        foreach ($graph as $rating=>$array) {
            $this->assertEquals($array, array("count" => 0, "width" => 0.0, "value" => $rating));
        }
    }

    /**
     * Test retrieving comments for a user
     * @dataProvider getComments
     */
    public function testGetComments($user_id, array $exp) {
        $user=new user($user_id);

        $comments=$user->getComments();

        $ids=array();
        foreach ($comments as $comment) {
            $ids[]=$comment->getId();
        }

        $this->assertEquals($exp, $ids);

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

     /**
      * Get comment ids for various users
      */
     public function getComments() {
        return array(
            array(1, array(11,14)),
            array(2, array(1,4,8)),
            array(3, array(6,7))
        );
     }

}
?>
