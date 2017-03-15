<?php
/**
 * Tag test
 * Test the working of the tag class
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

use zophCode\tag;
use PHPUnit\Framework\TestCase;

/**
 * Test the tag class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class tagTest extends TestCase {
    public function testGetFromString() {
        $tag = tag::getFromString("[b]");

        $this->assertEquals("<b>", (string) $tag);
    }
}
