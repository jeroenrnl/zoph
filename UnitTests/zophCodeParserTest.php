<?php
/**
 * zophCode Parser test
 * Test the working of the zophCode\parser class
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

use zophCode\parser as parser;

/**
 * Test the zophCode parser class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class zophCodeParserTest extends PHPUnit_Framework_TestCase {
    /**
     * Test constructor class
     * @dataProvider getMsgs();
     */
    public function testCreate($msg, $allowed, $exp) {
        $code = new parser($msg, $allowed);
        $this->assertEquals($exp, (string) $code);
    }

    public function getMsgs() {
        return array(
            array("Let's make things [b]bold[/b]", null, "Let's make things <b>bold</b>"),
            array("Let's make things [b]bold[/b]", array("b"), "Let's make things <b>bold</b>"),
            array("Let's make [i]things[/i] [b]bold[/b]", null, "Let's make <i>things</i> <b>bold</b>"),
            array("Let's make [i]things[/i] [b]bold[/b]", array("b"), "Let's make things <b>bold</b>")
        );
    }
}
