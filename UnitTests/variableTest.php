<?php
/**
 * Test generic\variable class
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

use generic\variable;
use PHPUnit\Framework\TestCase;

/**
 * Test the variable class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class variableTest extends TestCase {

    /**
     * Test creating an object
     * @dataProvider getValues();
     */
    public function testCreate($value, $input, $escape) {
        $var = new variable($value);
        $this->assertEquals($value, $var->get());

        if (!is_array($value)) {
            $this->assertEquals($value, (string) $var);
        }
    }

    /**
     * Test creating an object from "dirty" input
     * @dataProvider getValues();
     */
    public function testInput($value, $input, $escape) {
        $var = new variable($value);
        $this->assertEquals($input, $var->input());
    }

    /**
     * Test escaped output
     * @dataProvider getValues();
     */
    public function testEscape($value, $input, $escape) {
        $var = new variable($value);
        $this->assertEquals($escape, $var->escape());
    }

    public function getValues() {
        return array(
            array("plain text", "plain text", "plain text"),
            array("\"quoted\"", "\"quoted\"", "&quot;quoted&quot;"),
            array("&quot;quoted&quot;", "\"quoted\"", "&amp;quot;quoted&amp;quot;"),
            array("<html>", "", "&lt;html&gt;"),
            array("<", "<", "&lt;"),
            array(5, 5, 5),
            array("<script>alert(\"boo!\");</script>", "alert(\"boo!\");", "&lt;script&gt;alert&#40;&quot;boo!&quot;&#41;;&lt;/script&gt;"),
            array(
                array("plain"       => "plain",
                      "more plain"  => "oh so plain"),
                array("plain"       => "plain",
                      "more plain"  => "oh so plain"),
                array("plain"       => "plain",
                      "more plain"  => "oh so plain")
            ),
            array(
                array("<html>" => "<b>bold</b>",
                      "&amp;lt;html&amp;gt" => "&amp;\'<"),
                array("" => "bold",
                      "&lt;html&gt" => "&\'"),
                array("&lt;html&gt;" => "&lt;b&gt;bold&lt;/b&gt;",
                      "&amp;amp;lt;html&amp;amp;gt" => "&amp;amp;\&#39;&lt;"),
            )
        );
    }
}
