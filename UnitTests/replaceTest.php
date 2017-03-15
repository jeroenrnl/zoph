<?php
/**
 * Replace test
 * Test the working of the replace class
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

use PHPUnit\Framework\TestCase;
use zophCode\replace;

/**
 * Test the replace class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class replaceTest extends TestCase {

    public function testReplace() {
        $msg="&#40;Between brackets&#41; & some of <html>\non two (2) lines";
        $exp="(Between brackets) &amp; some of &lt;html&gt;<br>on two (2) lines";

        $replace=replace::processMessage($msg);
        $this->assertEquals($exp, $replace);
    }
}
