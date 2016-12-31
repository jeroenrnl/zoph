<?php
/**
 * A Unit Test for the validator object.
 * The validator object checks user passwords
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

use conf\conf;

/**
 * Test class for validator.
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class validatorTest extends ZophDatabaseTestCase {
    protected function setUp() {
        global $lang;

        $lang=new language("en");
        $this->getDataSet();

        // Make sure any old test users are removed
        $this->removeTestUsers();
        conf::set("interface.user.default", 0);
    }

    /**
     * Remove test users after test
     */
    protected function tearDown() {
        $this->removeTestUsers();
        conf::set("interface.user.default", 0);
    }

    private function removeTestUsers() {
        $users=array("Test User", "Old User", "InvalidPassword");
        foreach ($users as $username) {
            try {
                $user=user::getByName($username);
                $user->delete();
            } catch (userException $e) {
                // User wasn't there, nothing to do
            }
        }
    }

    public function testValidate() {
        $user = new user();
        $user->set("user_name", "Test User");
        $user->set("password", validator::hashPassword("secret"));
        $user->insert();

        unset($user);

        $validator=new validator("Test User", "secret");
        $user=$validator->validate();
        $user->lookup();

        $this->assertEquals($user->getName(), "Test User");
    }

    public function testDefaultUser() {
        conf::set("interface.user.default", 3);
        $validator=new validator("", "");
        $user=$validator->validate();
        $this->assertEquals(3, $user->getId());
    }

    /**
     * Try to set the default user to an admin user
     * @expectedException ConfigurationException
     */
    public function testDefaultUserAdmin() {
        conf::set("interface.user.default", 1);
    }

    /**
     * The validateOld function is the fallback algorithm that
     * uses the old mysql password() function, and is used for
     * users that have not yet been updated to the new hash
     * if a user succesfully logs in, the password is updated
     * to the new hash
     */
    public function testValidateOld() {
        $user=new user();
        $user->set("user_name", "Old User");
        // This is the hash for "secret"
        $user->set("password", "*14E65567ABDB5135D0CFD9A70B3032C179A49EE7");
        $user->insert();

        unset($user);

        $validator=new validator("Old User", "secret");
        $user=$validator->validate();
        $user->lookup();

        $this->assertEquals($user->getName(), "Old User");

        // We cannot check the hash as it is created with a random salt and will
        // generate a different hash every time, so we'll only check if the
        // type of hash has changed/
        $this->assertEquals('$2y$10$', substr($user->get("password"), 0, 7));

        // Now check if validation still works with new algorithm:
        unset($user);

        $validator=new validator("Old User", "secret");
        $user=$validator->validate();
        $user->lookup();

        $this->assertEquals($user->getName(), "Old User");
    }


    /**
     * Test with unknown user
     */
    public function testValidateInvalidUser() {
        $validator=new validator("DoesNotExist", "secret");
        $user=$validator->validate();
        $this->assertNotInstanceOf("user", $user);
    }

    /**
     * Test with wrong password
     */
    public function testValidateInvalidPassword() {
        $user=new user();
        $user->set("user_name", "InvalidPassword");
        // This the hash for "secret"
        $user->set("password", "*14E65567ABDB5135D0CFD9A70B3032C179A49EE7");
        $user->insert();

        unset($user);

        $validator=new validator("InvalidPassword", "wrong");
        $user=$validator->validate();
        $this->assertNotInstanceOf("user", $user);

        $user=user::getByName("InvalidPassword");
        $user->lookup();

        // Let's make sure the hash was NOT updated in this case
        $this->assertEquals("*14E65567ABDB5135D0CFD9A70B3032C179A49EE7", $user->get("password"));

    }
}
