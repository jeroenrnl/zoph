<?php
/**
 * Unittests for relation class
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
 * Test relation class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class relationTest extends ZophDataBaseTestCase {
    
    /**
     * Test defining a relation
     */
    public function testDefineRelation() {
        user::setCurrent(new user(1));
        photoRelation::defineRelation(new photo(9), new photo(10), "ninth photo", "tenth photo");

        $photo=new photo(9);
        $photo->lookup();

        $related=$photo->getRelated();

        $photo2=array_pop($related);

        $this->assertEquals($photo2, new photo(10));
    }

    /**
     * Test redefining a relation
     */
    public function testReDefineRelation() {
        photoRelation::defineRelation(new photo(2), new photo(1), "a photo", "another photo");

        $rel=new photoRelation(new photo(1), new photo(2));
        $rel->lookup();


        $this->assertEquals("a photo", $rel->getDesc(new photo(2)));
        $this->assertEquals("another photo", $rel->getDesc(new photo(1)));
    }

    /**
     * Test retrieving relation from database
     * @dataProvider getRelations
     */
    public function testGetRelation($photo_id_1, $photo_id_2, $desc_1, $desc_2) {
        $photo_1=new photo($photo_id_1);
        $photo_1->lookup();
        $photo_2=new photo($photo_id_2);
        $photo_2->lookup();
        
        $rel=new photoRelation($photo_1, $photo_2);
        $rel->lookup();


        $this->assertEquals($desc_1, $rel->getDesc($photo_1));
        $this->assertEquals($desc_2, $rel->getDesc($photo_2));
    }

    /**
     * Test retrieving relation descriptions from database
     * @dataProvider getRelations
     */
    public function testGetRelationDescription($photo_id_1, $photo_id_2, $desc_1, $desc_2) {
        $photo_1=new photo($photo_id_1);
        $photo_1->lookup();
        $photo_2=new photo($photo_id_2);
        
        $desc=$photo_1->getRelationDesc($photo_2);

        $this->assertEquals($desc_2, $desc);
        
        $desc=$photo_2->getRelationDesc($photo_1);

        $this->assertEquals($desc_1, $desc);
    }

    /**
     * Test retrieving non-existant relation descriptions from database
     */
    public function testGetNonExistantRelationDescription() {
        $photo_1=new photo(8);
        $photo_1->lookup();
        $photo_2=new photo(9);
        
        $desc=$photo_1->getRelationDesc($photo_2);

        $this->assertNull($desc);
    }

    /**
     * Test deleting a relation
     * @dataProvider getRelationsToBeDeleted
     */
    public function testDeleteRelation($photo_id_1, $photo_id_2, array $rem_1, array $rem_2) {
        $photo_1=new photo($photo_id_1);
        $photo_1->lookup();
        $photo_2=new photo($photo_id_2);
        $photo_2->lookup();
        
        $rel=new photoRelation($photo_1, $photo_2);
        $rel->lookup();
        $rel->delete();

        $rel_1=$photo_1->getRelated();
        $act_rem_1=array();
        foreach($rel_1 as $r) {
            $act_rem_1[]=$r->getId();
        }
        $this->assertEquals($rem_1, $act_rem_1); 

        $rel_2=$photo_2->getRelated();
        $act_rem_2=array();
        foreach($rel_2 as $r) {
            $act_rem_2[]=$r->getId();
        }
        $this->assertEquals($rem_2, $act_rem_2); 

    }

    /**
     * Test if an exception is thrown when the description of
     * a photo is requested for a photo that is not in the relation
     * @expectedException RelationException
     */
    public function testErrorHandlingGetDesc() {
        $photo_1=new photo(1);
        $photo_1->lookup();
        $photo_2=new photo(2);
        $photo_2->lookup();
        
        $rel=new photoRelation($photo_1, $photo_2);
        $rel->lookup();

        $rel->getDesc(new photo(3));
   }

    /**
     * Test if an exception is thrown when the description of
     * a photo is requested for a photo that is not in the relation
     * @expectedException RelationException
     */
    public function testErrorHandlingSetDesc() {
        $photo_1=new photo(1);
        $photo_1->lookup();
        $photo_2=new photo(2);
        $photo_2->lookup();
        
        $rel=new photoRelation($photo_1, $photo_2);
        $rel->lookup();

        $rel->setDesc(new photo(3), "kaboom");
    }

    public function getRelations() {
        return array(
            array(1, 2, "first photo", "second photo"),
            array(1, 3, "first photo", "third photo"),
            array(1, 4, "first photo", "fourth photo"),
            array(2, 3, "second photo", "third photo"),
            array(5, 4, "fifth photo", "fourth photo"),
            array(6, 5, "sixth photo", "fifth photo")
        );
    }

    public function getRelationsToBeDeleted() {
        // photo_id_1, photo_id_2, 
        // array(remaining related for photo_id_1), array(remaining for photo_id_2)
        return array(
            array(1,2, array(3,4), array(3)),
            array(1,3, array(2,4), array(2)),
            array(6,5, array(), array(4))
        );
    }

}
