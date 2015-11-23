<?php
/**
 * A class representing a group of people.
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
 * @author Jeroen Roos
 * @package Zoph
 */

use db\select;
use db\selectHelper;
use db\insert;
use db\delete;
use db\param;
use db\clause;

/**
 * A class representing a group of people
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class circle extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="circles";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("circle_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array("circle_name");
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="people.php?circle_id=";

    public function getName() {
        return $this->get("circle_name");
    }

    /**
     * Get display array
     * Get an array of properties to display
     * @return array properties
     */
    public function getDisplayArray() {
        return array(
            translate("circle") => $this->getName(),
            translate("description") => $this->get("description"),
            translate("members") => implode("<br>", $this->getMemberLinks())
        );
    }

    /**
     * Automatically select a coverphoto for this circle
     * It selects the coverphoto by FIRST getting the photos with the most people on it and
     * only then picking the oldest, newest, etc.
     * @param string how to select a coverphoto: oldest, newest, first, last, random, highest
     * @return photo coverphoto
     */
    public function getAutoCover($autocover=null) {
        $people=new select(array("cp" => "circles_people"));
        $people->addFields(array("person_id"));
        $people->where(new clause("circle_id=:circleid"));
        $people->addParam(new param(":circleid", (int) $this->getId(), PDO::PARAM_INT));

        $peopleIds=$people->toArray();
        $param=new param(":personIds", (array) $peopleIds, PDO::PARAM_INT);

        $qry=new select(array("p" => "photos"));
        $qry->addFields(array(
            "photo_id"  => "p.photo_id",
            "rating"    => "ar.rating"
        ));
        $qry->addFunction(array("count" => "count(person_id)"));

        $qry->join(array("ppl" => "photo_people"), "p.photo_id=ppl.photo_id");
        $qry->join(array("ar" => "view_photo_avg_rating"), "p.photo_id=ar.photo_id");

        $qry->addOrder("count DESC");
        $qry->addGroupBy("photo_id");
        $qry->addLimit(1);

        $qry->addParam($param);
        $qry->where(clause::InClause("ppl.person_id", $param));

        $qry=selectHelper::getAutoCoverOrder($qry, $autocover);
 
        $coverphotos=photo::getRecordsFromQuery($qry);
        $coverphoto=array_shift($coverphotos);
        if ($coverphoto instanceof photo) {
            $coverphoto->lookup();
            return $coverphoto;
        }
    }

    /**
     * Automatically select a coverphoto for this circle
     */
    public function getPeopleCount() {
        return sizeof($this->getMembers());
    }

    /**
     * Get members of this circle
     * @return array of people
     */
    public function getMembers() {
        $qry=new select(array("cp" => "circles_people"));
        $qry->addFields(array("person_id"));
        $qry->where(new clause("circle_id=:circleid"));
        $qry->addParam(new param(":circleid", (int) $this->getId(), PDO::PARAM_INT));

        return person::getRecordsFromQuery($qry);
    }

    /**
     * Add a member to a circle
     * @param person Person to addd
     */
    public function addMember(person $person) {
        $qry=new insert(array("cp" => "circles_people"));
        $qry->addParams(array(
            new param(":circle_id", (int) $this->getId(), PDO::PARAM_INT),
            new param(":person_id", (int) $person->getId(), PDO::PARAM_INT)
        ));

        $qry->execute();

    }

    /**
     * Remove a person from a circle
     * @param person Person to remove
     */
    public function removeMember(person $person) {

        $qry=new delete(array("cp" => "circles_people"));

        $where=new clause("circle_id=:circleid");
        $where->addAnd(new clause("person_id=:personid"));

        $qry->addParams(array(
            new param(":circleid", (int) $this->getId(), PDO::PARAM_INT),
            new param(":personid", $person->getId(), PDO::PARAM_INT)
        ));

        $qry->where($where);

        $qry->execute();
    }

    /**
     * Get an array of people that are NOT a member of this circle
     * @return array of people
     */
    public function getNonMembers() {
        $personIds=array();
        $memberIds=array();

        $people=person::getAll();
        $members=$this->getMembers();

        foreach ($people as $person) {
            $personIds[]=$person->getId();
        }
        if ($members) {
            foreach ($members as $member) {
                $memberIds[]=$member->getId();
            }
            $nonMemberIds=array_diff($personIds, $memberIds);
        } else {
            $nonMemberIds=$personIds;
        }

        $nonMembers=array();

        foreach ($nonMemberIds as $id) {
            $nonMembers[]=new person($id);
        }
        return $nonMembers;

    }

    /**
     * Create a pulldown to add new members to this circle
     * @param string name for the pulldown field
     * @return template Pulldown
     */
    public function getNewMemberPulldown($name) {
        $valueArray=array();

        $newMembers=$this->getNonMembers();
        $valueArray[0]=null;
        foreach ($newMembers as $nm) {
            $nm->lookup();
            $valueArray[$nm->getId()]=$nm->getName();
        }
        return template::createPulldown($name, null, $valueArray);
    }

    /**
     * Get links to all members of this group
     * @return array array of links
     */
    public function getMemberLinks() {
        $links=array();
        $members=$this->getMembers();
        if ($members) {
            foreach ($members as $member) {
                $member->lookup();
                $links[]=$member->getLink();
            }
        }
        return $links;
    }
}

?>
