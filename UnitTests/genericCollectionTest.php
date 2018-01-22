<?php
/**
 * This tests the generic collection object
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
require_once "testCollection.php";

use PHPUnit\Framework\TestCase;

/**
 * Test the generic collection object
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class collectionTest extends TestCase {
    public function testCreate() {
        $collection = new testCollection();

        $this->assertInstanceOf("testCollection", $collection);
    }

    public function testCreateFromArray() {
        $collection=testCollection::createFromArray( [ "do", "re", "mi", "fa", "so", "la", "ti" ]);

        $this->assertCount(7, $collection);
        $this->assertInstanceOf("testCollection", $collection);
    }

    public function testOffsetGetUnSetExist() {
        $collection=testCollection::createFromArray( [
            "c" => "do",
            "d" => "re",
            "e" => "mi",
            "f" => "fa",
            "g" => "so",
            "a" => "la",
            "b" => "ti" ]);

        $this->assertEquals("do", $collection["c"]);
        $this->assertEquals("re", $collection->offsetGet("d"));

        $this->assertEquals("so", $collection->offsetGet("g"));
        $collection["g"] = "sol";
        $this->assertEquals("sol", $collection->offsetGet("g"));

        $collection["h"] = "ti";
        $this->assertTrue(isset($collection["h"]));
        unset($collection["h"]);
        $this->assertFalse(isset($collection["h"]));

        $newCollection=new testCollection();
        $newCollection[] = "a";
        $newCollection[] = "b";
        $newCollection[] = "c";

        $this->assertCount(3, $newCollection);
    }

    public function testSubset() {
        $collection = testCollection::createFromArray(["a", "b", "c", "d", "e", "f"]);
        $subset=$collection->subset(0,3);
        $this->assertEquals([ "a", "b", "c" ], $subset->getArray());
    }

    public function testPopShift() {
        $collection = testCollection::createFromArray(["a", "b", "c", "d", "e", "f"]);
        $this->assertEquals("f", $collection->pop());
        $this->assertEquals("e", $collection->pop());

        $this->assertEquals("a", $collection->shift());
        $this->assertEquals("b", $collection->shift());
    }

    public function testForEach() {
        $collection = testCollection::createFromArray(["a", "b", "c", "d", "e", "f"]);

        $array=array();
        foreach ($collection as $letter) {
            $array[]=$letter;
        }

        $this->assertEquals($collection->getArray(), $array);
    }

    public function testRandom() {
        $array=[ "a", "b", "c", "d", "e", "f" ];

        $collection = testCollection::createFromArray($array);

        $random = $collection->random(2);

        foreach ($random as $rnd) {
            $this->assertContains($rnd, $array);
        }
    }

    public function testMerge() {
        $collection1 = testCollection::createFromArray(["a", "b", "c"]);
        $collection2 = testCollection::createFromArray(["d", "e", "f"]);

        $merged = $collection1->merge($collection2);

        $this->assertEquals(["a", "b", "c", "d", "e", "f"], $merged->getArray());

        $collection1 = testCollection::createFromArray(["a", "b", "c"]);
        $collection2 = testCollection::createFromArray(["d", "e", "f"]);
        $collection3 = testCollection::createFromArray(["g", "h", "i"]);

        $merged = $collection1->merge($collection2, $collection3);

        $this->assertEquals(["a", "b", "c", "d", "e", "f", "g", "h", "i"], $merged->getArray());
    }


}
