<?php

/**
 * A class for photographers, which is a special instance of a person.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 *
 * @package Zoph
 */

use db\select;
use db\param;
use db\clause;
use db\selectHelper;

/**
 * Photographer class
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class photographer extends person implements Organizer {

    /**
     * Add this person to a photo.
     * This records in the database that this person appears on the photo
     * @param photo Photo to add the person to
     */
    public function addPhoto(photo $photo) {
        $photo->setPhotographer($this);
    }

    /**
     * Remove person from a photo
     * @param photo photo to remove the person from
     */
    public function removePhoto(photo $photo) {
        $current=$photo->getPhotographer();
        if ($current instanceof photographer && $current->getId() == $this->getId()) {
            $photo->unsetPhotographer();
        }
    }

    /**
     * Return the number of photos this person has taken
     * @return int count
     */
    public function getPhotoCount() {
        $user=user::getCurrent();

        $ignore=null;
        $vars=array(
            "photographer_id" => $this->getId()
        );
        return get_photos($vars, 0, 1, $ignore, $user);
    }

    /**
     * Get all photographers
     * @param string search for names that begin with this string
     * @param bool also search first name
     * @return array list of photographer objects
     */
    public static function getAll($search = null, $search_first = false) {
        $where=null;
        $qry=new select(array("ppl" => "people"));

        if (!user::getCurrent()->canSeeAllPhotos()) {
            $ids=array();
            $subqry = new select(array("p" => "photos"));
            $subqry->addFunction(array("person_id" => "DISTINCT p.photographer_id"));
            $subqry->join(array("ppl" => "people"), "p.photographer_id=ppl.person_id");
            if ($search != null) {
                $where=static::getWhereForSearch($search, $search_first);
                $subqry->where($where);
                $subqry->addParam(new param(":search", $search, PDO::PARAM_STR));
                if ($search_first) {
                    $subqry->addParam(new param(":searchfirst", $search, PDO::PARAM_STR));
                }
            }
            $subqry = selectHelper::expandQueryForUser($subqry);

            $photographers=static::getRecordsFromQuery($subqry);

            if (sizeof($photographers) == 0) {
                return null;
            }

            foreach ($photographers as $photographer) {
                $ids[]=$photographer->getId();
            }

            $param=new param(":person_ids", $ids, PDO::PARAM_INT);
            $where=clause::InClause("person_id", $param);
            $qry->addParam($param);
        } else if ($search != null) {
            $qry->where(static::getWhereForSearch($search, $search_first));
            $qry->addParam(new param("search", $search, PDO::PARAM_STR));
            if ($search_first) {
                $qry->addParam(new param("searchfirst", $search, PDO::PARAM_STR));
            }
        }

        if ($where instanceof clause) {
            $qry->where($where);
        }

        $qry->addOrder("ppl.last_name")->addOrder("ppl.called")->addOrder("ppl.first_name");
        return static::getRecordsFromQuery($qry);
    }
}
?>
