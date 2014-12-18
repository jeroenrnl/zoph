<?php
/**
 * Category test
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
 * Test the category class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class categoryTest extends ZophDataBaseTestCase {
    /**
     * Create categories in the database
     * @dataProvider getCategories();
     */
    public function testCreateCategories($id, $name, $parent) {
        $category=new category();
        $category->set("category",$name);
        $category->set("parent_category_id", $parent);
        $category->insert();
        $this->assertInstanceOf("category", $category);
        $this->assertEquals($category->getId(), $id);
    }

    /**
     * Test deleting categories
     * @depends testCreateCategories
     * @dataProvider getCategories();
     */
    public function testDeleteCategories($id, $name, $parent) {
        $category=new category($id);
        $category->lookup();
        $category->delete();

        $retry=new category($id);
        $this->assertEquals(0, $retry->lookup());
    }

    /**
     * test getChildren() function including sortorder
     * @dataProvider getChildren();
     */
     public function testGetChildren($id, array $exp_children, $order=null) {
        $category=new category($id);
        $cat_children=$category->getChildren($order);
        $children=array();
        foreach($cat_children as $child) {
            $children[]=$child->getId();
        }

        if($order=="random") {
            // Of course, we cannot check the order for random, therefore we sort them.
            // Thus we only check if all the expected categories are present, not the order
            sort($children);
        }
        $this->assertEquals($exp_children, $children);
     }

        
    /**
     * Test getPhotoCount() function
     * @dataProvider getCategoryPhotoCount();
     */
    public function testGetPhotoCount($user, $category, $pc) {
        user::setCurrent(new user($user));

        $category=new category($category);
        $category->lookup();
        $count=$category->getPhotocount();
        $this->assertEquals($pc, $count);
        user::setCurrent(new user(1));
    }

    /**
     * Test getAutoCover function for a manual cover
     */
    public function testGetAutoCoverManual() {
        $category=new category(2);
        $category->set("coverphoto", 1);
        $category->update();

        $cover=$category->getAutoCover();
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals(1, $cover->getId());
    }


    /**
     * Test getAutoCover function
     / @dataProvider getCovers();
     */
    public function testGetAutoCover($user,$type,$cat_id,$photo) {
        user::setCurrent(new user($user));
        $category=new category($cat_id);
        $category->lookup();

        $cover=$category->getAutoCover($type);
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals($photo, $cover->getId());
        user::setCurrent(new user(1));
    }


    /**
     * Test getAutoCover function with children
     / @dataProvider getCoversChildren();
     */
    public function testGetAutoCoverChildren($user,$type,$cat_id,$photo) {
        user::setCurrent(new user($user));
        $category=new category($cat_id);
        $category->lookup();

        $cover=$category->getAutoCover($type, true);
        $this->assertInstanceOf("photo", $cover);

        $this->assertEquals($photo, $cover->getId());
        user::setCurrent(new user(1));
    }

    /**
     * Test getDetails()
     / @dataProvider getDetails();
     */
    public function testGetDetails($user,$cat_id, $subcat, array $exp_details) {
        user::setCurrent(new user($user));
        $category=new category($cat_id);
        $category->lookup();

        $details=$category->getDetails();
        $this->assertEquals($exp_details, $details);

        user::setCurrent(new user(1));
    }

    /**
     * Test getDetailsXML()
     / @dataProvider getDetails();
     */
    public function testGetDetailsXML($user,$cat_id, $subcat, array $exp_details) {
        user::setCurrent(new user($user));
        $category=new category($cat_id);
        $category->lookup();
        $details=$category->getDetailsXML();

        $timezone=array("e", "I", "O", "P", "T", "Z");
        $timeformat=str_replace($timezone, "", conf::get("date.timeformat"));
        $timeformat=trim(preg_replace("/\s\s+/", "", $timeformat));
        $format=conf::get("date.format") . " " . $timeformat;

        $oldest=new Time($exp_details["oldest"]);
        $disp_oldest=$oldest->format($format);

        $newest=new Time($exp_details["newest"]);
        $disp_newest=$newest->format($format);

        $first=new Time($exp_details["first"]);
        $disp_first=$first->format($format);

        $last=new Time($exp_details["last"]);
        $disp_last=$last->format($format);


        $expectedXML=sprintf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                      <details>
                        <request>
                          <class>category</class>
                          <id>%s</id>
                        </request>
                        <response>
                          <detail>
                            <subject>title</subject>
                            <data>In this category:</data>
                          </detail>
                          <detail>
                            <subject>count</subject>
                            <data>%s photos</data>
                          </detail>
                          <detail>
                            <subject>taken</subject>
                            <data>taken between %s and %s</data>
                          </detail>
                          <detail>
                            <subject>modified</subject>
                            <data>last changed from %s to %s</data>
                          </detail>
                          <detail>
                            <subject>rated</subject>
                            <data>rated between %s and %s and an average of %s</data>
                          </detail>
                          <detail>
                          <subject>children</subject>
                            <data>%s sub-categories</data>
                          </detail>
                        </response>
                      </details>", 
                       $cat_id, $exp_details["count"],$disp_oldest, $disp_newest, $disp_first, $disp_last,  $exp_details["lowest"], $exp_details["highest"], $exp_details["average"],$subcat);

        $this->assertXmlStringEqualsXmlString($expectedXML, $details);

        user::setCurrent(new user(1));
    }

    /**
     * Test getTopN() function
     * @dataProvider getTopNData();
     */
    public function testGetTopN($user, $expected) {
        user::setCurrent(new user($user));
        $catids=array();
        $topN=category::getTopN();

        foreach($topN as $category) {
            $catids[]=$category["id"];
        }
        $this->assertEquals($expected, $catids);
        user::setCurrent(new user(1));
    }

    /**
     * Test getCountForUser() function
     * @dataProvider getCategoryCount()
     */
     public function testGetCountForUser($user, $exp_count) {
        user::setCurrent(new user($user));

        $count=category::getCountForUser();

        $this->assertEquals($exp_count, $count);
        
        user::setCurrent(new user(1));
     }

    /**
     * Test getTotalPhotoCount() function
     * @dataProvider getTotalPhotoCount()
     */
     public function testTotalPhotoCount($user, $cat, $exp_count) {
        user::setCurrent(new user($user));

        $category=new category($cat);
        $category->lookup();

        $count=$category->getTotalPhotoCount();

        $this->assertEquals($exp_count, $count);
        
        user::setCurrent(new user(1));
     }


    /**
     * dataProvider function
     * @return array category_id, name, parent_id
     */
    public function getCategories() {

        return array(
            array(14, "Testcat1", 2),
            array(14, "Testcat2", 3)
        );
    }

    /**
     * dataProvider function
     * @return array category_id, array(children), order
     */
    public function getChildren() {
        return array(
            array(1, array(2,5,8,9), "oldest"),
            array(1, array(2,8,9,5), "newest"),
            array(1, array(2,5,8,9), "first"),
            array(1, array(2,8,9,5), "last"),
            array(1, array(2,5,9,8), "lowest"),
            array(1, array(5,9,2,8), "highest"),
            array(1, array(5,2,9,8), "average"),
            array(1, array(5,2,9,8), "name"),
            array(1, array(5,2,9,8), "sortname"),
            array(1, array(2,5,8,9), "random")
        );
    }

    /**
     * dataProvider function
     * @return array user_id, category_id, photocount
     */
    public function getCategoryPhotoCount() {
        return array(
            array(1, 2, 4),
            array(1, 3, 2),
            array(3, 2, 1),
            array(3, 3, 1),
            array(3, 4, 0)
        );
    }

    /**
     * dataProvider function
     * @return array user,type of autocover, category_id, cover photo
     */
    public function getCovers() {
        return array(
            array(1,"oldest", 2, 1),
            array(1,"newest", 2, 4),
            array(1,"first", 3, 1),
            array(1,"last", 4, 10),
            array(1,"newest", 5, 10),
            array(2,"oldest", 2, 1),
            array(2,"newest", 2, 1),
            array(3,"first", 3, 1),
            array(4,"last", 4, 2),
        );
    }

    /**
     * dataProvider function
     * @return array user,type of autocover, category_id, cover photo
     */
    public function getCoversChildren() {
        return array(
            array(1,"oldest", 1, 1),
            array(1,"newest", 2, 10),
            array(1,"first", 3, 1),
            array(1,"last", 4, 10),
            array(1,"newest", 4, 10),
            array(2,"oldest", 1, 1),
            array(2,"newest", 2, 1),
            array(2,"first", 3, 1),
            array(4,"last", 4, 2),
        );
    }
    /**
     * dataProvider function
     * @return user, category, array(count, oldest, newest, first, last, highest, average)
     */
    public function getDetails() {
        return array(
            array(1,2,2,array(
                "count" 	=> "4",
                "oldest" 	=> "2014-01-01 00:01:00",
                "newest" 	=> "2014-01-04 00:01:00",
                "first" 	=> "2013-12-31 23:01:00",
                "last" 	    => "2014-01-03 23:01:00",
                "lowest" 	=> "4.3",
                "highest" 	=> "7.5",
                "average" 	=> "5.81"
            )),
            array(1,3,"no", array(
                "count" 	=> "2",
                "oldest" 	=> "2014-01-01 00:01:00",
                "newest" 	=> "2014-01-10 00:01:00",
                "first" 	=> "2013-12-31 23:01:00",
                "last" 	    => "2014-01-09 23:02:00",
                "lowest" 	=> "5.5",
                "highest" 	=> "7.5",
                "average" 	=> "6.50",
            )),
            array(2,2,1,array(
                "count" 	=> "1",
                "oldest" 	=> "2014-01-01 00:01:00",
                "newest" 	=> "2014-01-01 00:01:00",
                "first" 	=> "2013-12-31 23:01:00",
                "last" 	    => "2013-12-31 23:01:00",
                "lowest" 	=> "7.5",
                "highest" 	=> "7.5",
                "average" 	=> "7.50",
            )),
        );
    }

    /**
     * dataProvider function
     * @return array userid, topN
     */
    public function getTopNData() {
        return array(
            array(1,array(10,2,11,5,7)),
            array(2,array(10,11,3,2))
        );
    }

    /**
     * dataProvider function
     * @return array userid, count
     */
    public function getCategoryCount() {
        return array(
            array(1,13),
            array(2,6),
            array(3,6),
            array(4,7),
            array(6,0)
        );
    }
    /**
     * dataProvider function
     * @return array userid, category, count
     */
    public function getTotalPhotoCount() {
        return array(
            array(1,1,10),
            array(1,2,5),
            array(2,1,2),
            array(3,2,1),
            array(4,2,2),
            array(6,2,0)
        );
    }
}
