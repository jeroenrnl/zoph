<?php
/**
 * Test web\request class
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

use web\request;

/**
 * Test the request class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class requestTest extends PHPUnit_Framework_TestCase {

    /**
     * Test creating an object through the normal __construct()
     * @dataProvider getRequests();
     */
    public function testConstruct($reqArray, $exp, $serverExp) {
        $request=new request($reqArray);

        foreach ($exp as $key => $val) {
            $this->assertEquals($val, $request[$key]);
            $this->assertEquals($val, $request->$key);
        }

        foreach ($serverExp as $key => $val) {
            $this->assertEquals($val, $request->getServerVar($key));
        }
    }

    /**
     * Test creating an object through the create() function
     * @dataProvider getRequests();
     */
    public function testCreate($reqArray, $exp, $serverExp) {
        global $_SERVER;
        global $_GET;
        global $_POST;

        $origSERVER = $_SERVER;
        $origGET    = $_GET;
        $origPOST   = $_POST;

        $_SERVER = $reqArray["SERVER"];
        $_GET    = $reqArray["GET"];
        $_POST   = $reqArray["POST"];

        $request=request::create();

        foreach ($exp as $key => $val) {
            $this->assertEquals($val, $request[$key]);
            $this->assertEquals($val, $request->$key);
        }

        foreach ($serverExp as $key => $val) {
            $this->assertEquals($val, $request->getServerVar($key));
        }

        $_SERVER = $origSERVER;
        $_GET    = $origGET;
        $_POST   = $origPOST;
    }

    /**
     * Test requestVars
     * @dataProvider getRequestVars();
     */
    public function testRequestVars($reqArray, $exp, $expClean) {
        $request=new request($reqArray);

        $this->assertEquals($exp, $request->getRequestVars());
        $this->assertEquals($expClean, $request->getRequestVarsClean());
    }
        
        
    public function getRequests() {
        return array (
            array(
                array(
                    "SERVER" => array(
                        "REMOTE_ADDR"   => "192.168.1.1",
                        "SCRIPT_NAME"   => "test.php",
                        "REQUEST_URI"   => "/zoph/test/php"),
                    "GET" => array(
                        "photo_id"   => "3",
                        "photographer_id"   => "7"),
                    "POST" => array()),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7"),
                array(
                    "REMOTE_ADDR"   => "192.168.1.1",
                    "SCRIPT_NAME"   => "test.php",
                    "REQUEST_URI"   => "/zoph/test/php")),
            array(
                array(
                    "SERVER" => array(
                        "REMOTE_ADDR"   => "192.168.1.1",
                        "SCRIPT_NAME"   => "test.php",
                        "REQUEST_URI"   => "/zoph/test/php"),
                    "POST" => array(
                        "photo_id"   => "3",
                        "photographer_id"   => "7"),
                    "GET" => array()),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7"),
                array(
                    "REMOTE_ADDR"   => "192.168.1.1",
                    "SCRIPT_NAME"   => "test.php",
                    "REQUEST_URI"   => "/zoph/test/php")),
            array(
                array(
                    "SERVER" => array(
                        "REMOTE_ADDR"   => "192.168.1.1",
                        "SCRIPT_NAME"   => "test.php",
                        "REQUEST_URI"   => "/zoph/test/php"),
                     "GET" => array(
                        "photo_id"   => "3",
                        "photographer_id"   => "7"),
                     "POST" => array(
                        "photo_id"   => "4",
                        "album_id"   => "8")),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7",
                    "album_id"   => "8"),
                array(
                    "REMOTE_ADDR"   => "192.168.1.1",
                    "SCRIPT_NAME"   => "test.php",
                    "REQUEST_URI"   => "/zoph/test/php")));
    }

    public function getRequestVars() {
        return array (
            array(
                array(
                    "SERVER" => array(
                        "REMOTE_ADDR"   => "192.168.1.1",
                        "SCRIPT_NAME"   => "test.php",
                        "REQUEST_URI"   => "/zoph/test/php"),
                    "GET" => array(
                        "photo_id"   => "3",
                        "photographer_id"   => "7"),
                    "POST" => array()),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7"),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7")),
            array(
                array(
                    "SERVER" => array(
                        "REMOTE_ADDR"   => "192.168.1.1",
                        "SCRIPT_NAME"   => "test.php",
                        "REQUEST_URI"   => "/zoph/test/php"),
                    "POST" => array(
                        "album_id"    => array(
                            0   => "3",
                            1   => "4"),
                        "_album_id_conj"    => array(
                            0   =>  "or",
                            1   =>  "or",
                            1   =>  "or"),
                        "_category_id_conj" => array(
                            0   => "or"),
                        "photographer_id"   => "7"),
                    "GET" => array()),
                array(
                    "album_id"    => array(
                        0   => "3",
                        1   => "4"),
                    "_album_id_conj"    => array(
                        0   =>  "or",
                        1   =>  "or",
                        1   =>  "or"),
                    "_category_id_conj" => array(
                        0   => "or"),
                    "photographer_id"   => "7"),
                array(
                    "album_id#0"   => "3",
                    "album_id#1"   => "4",
                    "_album_id#0-conj"  => "or",
                    "_album_id#1-conj"  => "or",
                    "photographer_id"   => "7")),
            array(
                array(
                    "SERVER" => array(
                        "REMOTE_ADDR"   => "192.168.1.1",
                        "SCRIPT_NAME"   => "test.php",
                        "REQUEST_URI"   => "/zoph/test/php"),
                     "GET" => array(
                        "photo_id"   => "3",
                        "photographer_id"   => "7"),
                     "POST" => array(
                        "photo_id"   => "4",
                        "album_id"   => "8")),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7"),
                array(
                    "photo_id"   => "3",
                    "photographer_id"   => "7")));
    }
}
