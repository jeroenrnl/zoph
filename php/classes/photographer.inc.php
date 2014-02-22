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
        if($current instanceof photographer && $current->getId() == $this->getId()) {
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

    public static function getAll($search = null, $search_first = null) {
        $user=user::getCurrent();

        $where=self::getWhereForSearch("", $search, $search_first);
        if ($user->is_admin()) {
            if($where!="") {
                $where="WHERE " . $where;
            }
            $sql =
                "SELECT * FROM " .
                DB_PREFIX . "people AS ppl " .
                $where . 
                " ORDER BY last_name, called, first_name";
        } else {
            if($where!="") {
                $where="AND " . $where;
            }
            $sql =
                "SELECT DISTINCT ppl.* FROM " .
                DB_PREFIX . "people AS ppl " .
                "WHERE person_id in " .
                "(SELECT photographer_id FROM " .
                DB_PREFIX . "photos AS ph JOIN " .
                DB_PREFIX . "photo_albums AS pa " .
                "ON pa.photo_id = ph.photo_id JOIN " .
                DB_PREFIX . "group_permissions AS gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users AS gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE gu.user_id = '" . (int) $user->getId() . "' " .
                $where .
                " AND gp.access_level >= ph.level)" .
                " ORDER BY ppl.last_name, ppl.called, ppl.first_name";
        }

        return static::getRecordsFromQuery($sql);
    }
}
?>
