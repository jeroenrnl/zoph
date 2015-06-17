<?php
/**
 * Unittests for XML API
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
 * Test XML API
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class XMLdataTest extends PHPUnit_Framework_TestCase {

    /**
     * Test getting XML data
     * @dataProvider getXMLtestdata
     */
    public function testGetXML($object, $search, $xml) {
        user::setCurrent(new user(1));
        $actual = new DOMDocument;
        $actual->loadXML($object::getXML($search)->saveXML());
        $expected=new DOMDocument();
        $expected->loadXML($xml);
        $this->assertEqualXMLStructure($expected->firstChild, $actual->firstChild, false);

    }

    /**
     * Test getting XML data as non-admin user
     * @dataProvider getXMLtestdataForUser
     */
    public function testGetXMLForUser($object, $search, $xml) {
        user::setCurrent(new user(2));

        $actual = new DOMDocument;
        $actual->loadXML($object::getXML($search)->saveXML());
        $expected=new DOMDocument();
        $expected->loadXML($xml);

        $this->assertEqualXMLStructure($expected->firstChild, $actual->firstChild, false);
        user::setCurrent(new user(1));

    }

    
    public function getXMLtestdata() {
        return array(
            array("album", "", $this->getAllAlbumsXML()),
            array("category", "", $this->getAllCategoriesXML()),
            array("place", "", $this->getAllPlacesXML()),
            array("person", "", $this->getAllPeopleXML()),
            array("photographer", "", $this->getAllPeopleXML()),
            array("album", "Album", $this->getAllAlbumsXML()),
            array("album", "Album 2", $this->getAllAlbumsXMLSearch2())
        );
    }
    
    public function getXMLtestdataForUser() {
        return array(
            array("album", "", $this->getAllAlbumsXMLForUser()),
            array("category", "", $this->getAllCategoriesXMLForUser()),
            array("place", "", $this->getAllPlacesXMLForUser()),
            array("person", "", $this->getAllPeopleXMLForUser()),
            array("photographer", "", $this->getAllPhotographersXMLForUser())
        );
    }
    
    private function getAllAlbumsXML() {
        $xml= "<albums>\n";
        $xml.="  <album><key/><title/>\n"; // empty 
        $xml.="  </album>\n";
        $xml.="  <album><key/><title/>\n"; // root = 1
        $xml.="    <albums>\n";
        $xml.="      <album><key/><title/>\n"; // 2 
        $xml.="        <albums>\n";
        $xml.="          <album><key/><title/>\n"; // 3
        $xml.="            <albums>\n";
        $xml.="              <album><key/><title/>\n"; // 4
        $xml.="              </album>\n";
        $xml.="            </albums>\n";
        $xml.="          </album>\n";
        $xml.="        </albums>\n";
        $xml.="      </album>\n"; // 2
        $xml.="      <album><key/><title/>\n"; // 5
        $xml.="        <albums>\n";
        $xml.="          <album><key/><title/>\n"; // 6
        $xml.="            <albums>\n";
        $xml.="              <album><key/><title/>\n"; // 7
        $xml.="                <albums>\n";
        $xml.="                  <album><key/><title/>\n"; // 9
        $xml.="                  </album>\n";
        $xml.="                  <album><key/><title/>\n"; // 10
        $xml.="                  </album>\n";
        $xml.="                </albums>\n";
        $xml.="              </album>\n";
        $xml.="            </albums>\n";
        $xml.="          </album>\n";
        $xml.="          <album><key/><title/>\n"; // 8
        $xml.="            <albums>\n";
        $xml.="              <album><key/><title/>\n"; // 11
        $xml.="              </album>\n";
        $xml.="            </albums>\n";
        $xml.="          </album>\n";
        $xml.="        </albums>\n";
        $xml.="      </album>\n";
        $xml.="      <album><key/><title/>\n"; // 3 
        $xml.="      </album>\n";
        $xml.="      <album><key/><title/>\n"; // 4 
        $xml.="      </album>\n";
        $xml.="      <album><key/><title/>\n"; // 5 
        $xml.="      </album>\n";
        $xml.="    </albums>\n";
        $xml.="  </album>\n";
        $xml.="</albums>\n";

        return $xml;
    }

    private function getAllCategoriesXML() {
        $xml= "<categories>\n";
        $xml.="  <category><key/><title/>\n"; // empty 
        $xml.="  </category>\n";
        $xml.="  <category><key/><title/>\n"; // root = 1
        $xml.="    <categories>\n";
        $xml.="      <category><key/><title/>\n"; // 5
        $xml.="        <categories>\n";
        $xml.="          <category><key/><title/>\n"; // 6
        $xml.="            <categories>\n";
        $xml.="              <category><key/><title/>\n"; // 7
        $xml.="              </category>\n";
        $xml.="            </categories>\n";
        $xml.="          </category>\n";
        $xml.="        </categories>\n";
        $xml.="      </category>\n";
        $xml.="      <category><key/><title/>\n"; // 2 
        $xml.="        <categories>\n";
        $xml.="          <category><key/><title/>\n"; // 4
        $xml.="          </category>\n";
        $xml.="          <category><key/><title/>\n"; // 3
        $xml.="          </category>\n";
        $xml.="        </categories>\n";
        $xml.="      </category>\n"; // 2
        $xml.="      <category><key/><title/>\n"; // 9
        $xml.="        <categories>\n";
        $xml.="          <category><key/><title/>\n"; // 10
        $xml.="          </category>\n";
        $xml.="          <category><key/><title/>\n"; // 11
        $xml.="          </category>\n";
        $xml.="          <category><key/><title/>\n"; // 12 
        $xml.="          </category>\n";
        $xml.="          <category><key/><title/>\n"; // 13 
        $xml.="          </category>\n";
        $xml.="        </categories>\n";
        $xml.="      </category>\n";
        $xml.="      <category><key/><title/>\n"; // 8
        $xml.="      </category>\n";
        $xml.="    </categories>\n";
        $xml.="  </category>\n";
        $xml.="</categories>\n";

        return $xml;
    }

    private function getAllPlacesXML() {
        $xml= "<places>\n";
        $xml.="  <place><key/><title/>\n"; // empty 
        $xml.="  </place>\n";
        $xml.="  <place><key/><title/>\n"; // root = 1
        $xml.="    <places>\n";
        $xml.="      <place><key/><title/>\n"; // 18 
        $xml.="      </place>\n";
        $xml.="      <place><key/><title/>\n"; // 2
        $xml.="        <places>\n";
        $xml.="          <place><key/><title/>\n"; // 6
        $xml.="            <places>\n";
        $xml.="              <place><key/><title/>\n"; // 7
        $xml.="              </place>\n";
        $xml.="            </places>\n";
        $xml.="          </place>\n"; 
        $xml.="          <place><key/><title/>\n"; // 3 
        $xml.="            <places>\n";
        $xml.="              <place><key/><title/>\n"; // 5
        $xml.="              </place>\n";
        $xml.="              <place><key/><title/>\n"; // 4
        $xml.="              </place>\n";
        $xml.="            </places>\n";
        $xml.="          </place>\n";
        $xml.="        </places>\n";
        $xml.="      </place>\n";
        $xml.="      <place><key/><title/>\n"; // 8
        $xml.="        <places>\n";
        $xml.="          <place><key/><title/>\n"; // 9
        $xml.="            <places>\n";
        $xml.="              <place><key/><title/>\n"; // 10
        $xml.="              </place>\n";
        $xml.="            </places>\n";
        $xml.="          </place>\n";
        $xml.="          <place><key/><title/>\n"; // 11
        $xml.="            <places>\n";
        $xml.="              <place><key/><title/>\n"; // 14
        $xml.="                <places>\n";
        $xml.="                  <place><key/><title/>\n"; // 15
        $xml.="                  </place>\n";
        $xml.="                </places>\n";
        $xml.="              </place>\n";
        $xml.="              <place><key/><title/>\n"; // 12
        $xml.="                <places>\n";
        $xml.="                  <place><key/><title/>\n"; // 13
        $xml.="                  </place>\n";
        $xml.="                </places>\n";
        $xml.="              </place>\n";
        $xml.="              <place><key/><title/>\n"; // 16
        $xml.="                <places>\n";
        $xml.="                  <place><key/><title/>\n"; // 17
        $xml.="                  </place>\n";
        $xml.="                </places>\n";
        $xml.="              </place>\n";
        $xml.="            </places>\n";
        $xml.="          </place>\n";
        $xml.="        </places>\n";
        $xml.="      </place>\n";
        $xml.="    </places>\n";
        $xml.="  </place>\n";
        $xml.="</places>\n";

        return $xml;
    }

    private function getAllPeopleXML() {
        $xml ="<people>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="</people>\n";
        return $xml;
    }

    private function getAllAlbumsXMLSearch2() {
        $xml= "<albums>\n";
        $xml.="  <album><key/><title/>\n"; // empty 
        $xml.="  </album>\n";
        $xml.="  <album>\n"; // root = 1
        $xml.="    <albums>\n";
        $xml.="      <album>\n"; // 2 
        $xml.="        <albums>\n";
        $xml.="          <album>\n"; // 3
        $xml.="            <albums>\n";
        $xml.="              <album>\n"; // 4
        $xml.="              </album>\n";
        $xml.="            </albums>\n";
        $xml.="          </album>\n";
        $xml.="        </albums>\n";
        $xml.="      </album>\n"; // 2
        $xml.="      <album><key/><title/>\n"; // 5
        $xml.="        <albums>\n";
        $xml.="          <album><key/><title/>\n"; // 6
        $xml.="            <albums>\n";
        $xml.="              <album><key/><title/>\n"; // 7
        $xml.="                <albums>\n";
        $xml.="                  <album><key/><title/>\n"; // 9
        $xml.="                  </album>\n";
        $xml.="                  <album><key/><title/>\n"; // 10
        $xml.="                  </album>\n";
        $xml.="                </albums>\n";
        $xml.="              </album>\n";
        $xml.="            </albums>\n";
        $xml.="          </album>\n";
        $xml.="          <album><key/><title/>\n"; // 8
        $xml.="            <albums>\n";
        $xml.="              <album><key/><title/>\n"; // 11
        $xml.="              </album>\n";
        $xml.="            </albums>\n";
        $xml.="          </album>\n";
        $xml.="        </albums>\n";
        $xml.="      </album>\n";
        $xml.="      <album>"; // 3 
        $xml.="      </album>\n";
        $xml.="      <album>\n"; // 4 
        $xml.="      </album>\n";
        $xml.="      <album>\n"; // 5 
        $xml.="      </album>\n";
        $xml.="    </albums>\n";
        $xml.="  </album>\n";
        $xml.="</albums>\n";

        return $xml;
    }
    
    private function getAllAlbumsXMLForUser() {
        $xml= "<albums>\n";
        $xml.="  <album><key/><title/>\n"; // empty 
        $xml.="  </album>\n";
        $xml.="  <album><key/><title/>\n"; // root = 1
        $xml.="    <albums>\n";
        $xml.="      <album><key/><title/>\n"; // 2 
        $xml.="      </album>\n"; // 2
        $xml.="    </albums>\n";
        $xml.="  </album>\n";
        $xml.="</albums>\n";

        return $xml;
    }
    
    private function getAllCategoriesXMLForUser() {
        $xml= "<categories>\n";
        $xml.="  <category><key/><title/>\n"; // empty 
        $xml.="  </category>\n";
        $xml.="  <category><key/><title/>\n"; // root = 1
        $xml.="    <categories>\n";
        $xml.="      <category><key/><title/>\n"; // 2 
        $xml.="        <categories>\n";
        $xml.="          <category><key/><title/>\n"; // 3
        $xml.="          </category>\n";
        $xml.="        </categories>\n";
        $xml.="      </category>\n"; // 2
        $xml.="      <category><key/><title/>\n"; // 9
        $xml.="        <categories>\n";
        $xml.="          <category><key/><title/>\n"; // 10
        $xml.="          </category>\n";
        $xml.="          <category><key/><title/>\n"; // 11
        $xml.="          </category>\n";
        $xml.="        </categories>\n";
        $xml.="      </category>\n";
        $xml.="    </categories>\n";
        $xml.="  </category>\n";
        $xml.="</categories>\n";

        return $xml;
    }

    private function getAllPlacesXMLForUser() {
        $xml= "<places>\n";
        $xml.="  <place><key/><title/>\n"; // empty 
        $xml.="  </place>\n";
        $xml.="  <place><key/><title/>\n"; // root = 1
        $xml.="    <places>\n";
        $xml.="      <place><key/><title/>\n"; // 2
        $xml.="        <places>\n";
        $xml.="          <place><key/><title/>\n"; // 6
        $xml.="            <places>\n";
        $xml.="              <place><key/><title/>\n"; // 7
        $xml.="              </place>\n";
        $xml.="            </places>\n";
        $xml.="          </place>\n"; 
        $xml.="          <place><key/><title/>\n"; // 3 
        $xml.="          </place>\n";
        $xml.="        </places>\n";
        $xml.="      </place>\n";
        $xml.="    </places>\n";
        $xml.="  </place>\n";
        $xml.="</places>\n";

        return $xml;
    }

    private function getAllPeopleXMLForUser() {
        $xml ="<people>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="</people>\n";
        return $xml;
    }
    private function getAllPhotographersXMLForUser() {
        $xml ="<people>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="  <person><key/><title/></person>\n";
        $xml.="</people>\n";
        return $xml;
    }

}
