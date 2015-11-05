<?php
/**
 * Comment test
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
 * Test the comment class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class commentTest extends ZophDataBaseTestCase {

    /**
     * Create comment in the database
     * @dataProvider getComments();
     */
    public function testCreate($subject, $user, $ip, $text) {
        global $_SERVER;
        $comment=new comment();
        $comment->set("subject", $subject);
        $comment->set("user_id", $user);
        $comment->set("comment", $text);
        $_SERVER["REMOTE_ADDR"]=$ip;
        $comment->insert();
        $id=$comment->getId();

        unset($comment);
        $comment=new comment($id);
        $comment->lookup();

        $this->assertEquals($subject, $comment->get("subject"));
        $this->assertEquals($user, $comment->get("user_id"));
        $this->assertEquals($ip, $comment->get("ipaddr"));
        $this->assertEquals($text, $comment->get("comment"));

        $comment_time=strtotime($comment->get("timestamp"));
        $current_time=strtotime("now");

        $difference=$current_time - $comment_time;

        // If the the comment time is maximum 60 seconds in the past,
        // we assume it's correct.
        $this->assertLessThanOrEqual(60, $difference);
    }

    /**
     * Test updating existing comment
     */
    public function testUpdate() {
        $comment=new comment(3);
        $comment->lookup();
        $this->assertEquals("Nice", $comment->get("comment"));

        $comment->set("comment", "Very Nice!");
        $comment->update();

        unset($comment);

        $comment=new comment(3);
        $comment->lookup();

        $this->assertEquals("Very Nice!", $comment->get("comment"));

        $comment_time=strtotime($comment->get("timestamp"));
        $current_time=strtotime("now");

        $difference=$current_time - $comment_time;

        // If the the comment time is maximum 60 seconds in the past,
        // we assume it's correct.
        $this->assertLessThanOrEqual(60, $difference);

    }

    /**
     * Test delete
     */
    public function testDelete() {
        global $_SERVER;
        $_SERVER["REMOTE_ADDR"]="127.0.0.1";

        $comment=new comment();
        $comment->set("comment", "comment");
        $comment->insert();
        
        $id=$comment->getId();
        unset($comment);

        // Verify it did end up in the db
        $comment=new comment($id);
        $comment->lookup();

        $this->assertEquals("comment", $comment->get("comment"));

        $photo=new photo(1);
        $count=sizeOf($photo->getComments());
       
        $comment->addToPhoto($photo);

        $newcount=sizeOf($photo->getComments());

        $this->assertEquals($count + 1, $newcount);

        $comment->delete();

        unset($comment);

        $comment=new comment($id);

        // Lookup returns 0 if it can't find the record
        $this->assertEquals(0, $comment->lookup());

        $newcount=sizeOf($photo->getComments());

        $this->assertEquals($count, $newcount);
    }

    /**
     * Test getPhoto() function
     * @dataProvider getIds();
     */
    public function testGetPhoto($commentId, $photoId) {
        $comment=new comment($commentId);
        $photo=$comment->getPhoto();

        $this->assertEquals($photoId, $photo->getId());
    }
        

    /**
     * Test isOwner() function
     * @dataProvider getUsers();
     */
    public function testIsOwner($commentId, $userId) {
        $comment=new comment($commentId);
        $comment->lookup();
        $user=new user($userId);
        $wronguser=new user($userId + 1);

        $this->assertTrue($comment->isOwner($user));
        $this->assertFalse($comment->isOwner($wronguser));
    }
        

    /**
     * DataProvider for creating comments
     */
    public function getComments() {
        return array(
            array("Test comment", 3, "127.0.0.1", "This is just a [b]test[/b]"),
            array("Another comment", 5, "8.8.8.8", "Only a [i]test[/i]")
        );
    }

    /**
     * DataProvider for getPhoto test
     */
    public function getIds() {
        return array(
            array(1,1),
            array(4,2),
            array(6,5)
        );
    }

    /**
     * DataProvider for getOwner test
     */
    public function getUsers() {
        return array(
            array(1,2),
            array(4,2),
            array(6,3)
        );
    }

}
