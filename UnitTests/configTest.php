<?php
/**
 * Test configuration
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

/**
 * Test the conf class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class configTest extends ZophDataBaseTestCase {

    /**
     * Test loading the config from the database
     */
    public function testLoadFromDB() {
        // Default
        $title=conf::get("interface.title");
        $this->assertEquals($title, "Zoph");

        // Change config, and save to DB
        $titleItem=conf::getItemByName("interface.title");
        $titleItem->setValue("Zoph Test");
        $titleItem->update();
        $title=conf::get("interface.title");
        $this->assertEquals($title, "Zoph Test");

        // Now set it back to the default, but don't save to DB:
        conf::set("interface.title", "Zoph");
        $title=conf::get("interface.title");
        $this->assertEquals($title, "Zoph");

        // load from DB and see if title has changed
        conf::loadFromDB();
        $title=conf::get("interface.title");
        $this->assertEquals($title, "Zoph Test");
        // Need to set this to make tests run ok
        conf::set("path.images", getcwd() . "/.images");
    }

    /**
     * Test what happens if an unknown item is encountered in the DB
     */
    public function testUnkownConfItem() {
        $qry=new insert(array("co" => "conf"));
        $qry->addParams(array(
            new param(":conf_id", "test.unknown", PDO::PARAM_STR),
            new param(":value", "Unknown", PDO::PARAM_STR)
        ));
        $qry->execute();

        conf::loadFromDB();

        $qry=new select(array("co" => "conf"));
        $qry->where(new clause("conf_id=:conf_id"));
        $qry->addParam(new param(":conf_id", "test.unknown", PDO::PARAM_STR));
        $result=query($qry);
        $records=fetch_assoc($result);

        $this->assertFalse($records);

        // Need to set this to make tests run ok
        conf::set("path.images", getcwd() . "/.images");
    }

        
}
