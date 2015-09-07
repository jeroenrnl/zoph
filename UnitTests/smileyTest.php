<?php
/**
 * Smiley test
 * Test the working of the smiley class
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
use zophCode\smiley as smiley;

/**
 * Test the smiley class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class smileyTest extends PHPUnit_Framework_TestCase {

    /**
     * Test creating a smiley
     * @dataProvider getSmileys();
     */
    public function testCreate($smiley, $file, $desc) {
        $smileys = smiley::getArray();
        $counter = 0;
        foreach($smileys as $smile) {
            if($smile->smiley == $smiley) {
                $html = "<img src=\"templates/default/images/smileys/" . $file . "\" class=\"smiley\"\n" .
                        "     alt=\"" . $desc . "\">";
                $this->assertEquals($html, trim((string) $smile));
                $counter++;
            }
        }
        // Test to make sure 1 and only 1 smiley has been found
        $this->assertEquals($counter, 1);
    }

    public function getSmileys() {
        return array(
            array(":-)", "icon_smile.gif", "Smile"),
            array("8-)", "icon_cool.gif", "Cool"),
            array(":-P", "icon_razz.gif", "Razz")
        );
    }
}
