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
        if($photo->getPhotographer() == $this) {
            $photo->unsetPhotographer();
        }

    }
   
    /**
     * Name of XML root tag
     * @todo phase out in favour of class constant
     */
    public function xml_rootname() {
        return "people";
    }

    /**
     * Name of XML node tag
     * @todo phase out in favour of class constant
     */
    public function xml_nodename() {
    /**
     * Name of XML root tag
     * @todo phase out in favour of class constant
     */
        return "person";
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

        $where=get_where_for_search(" and ", $search, $search_first);
        if ($user->is_admin()) {
            $sql =
                "SELECT DISTINCT ppl.* FROM " .
                DB_PREFIX . "people AS ppl, " .
                DB_PREFIX . "photos AS ph " .
                "WHERE ppl.person_id = ph.photographer_id " . $where . 
                "ORDER BY ppl.last_name, ppl.called, ppl.first_name";
        } else {
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

    /**
     * Create a pulldown for photographers
     * @param string name (and adapted from that, id) for the input tag
     * @param string current value
     * @return string HTML
     * @todo returns HTML
     */
    public static function createPulldown($name, $value=null) {
        $user=user::getCurrent();
        $text="";

        $id=preg_replace("/^_+/", "", $name);
        if($value) {
            $person=new person($value);
            $person->lookup();
            $text=$person->getName();
        }
        if($user->prefs->get("autocomp_photographer") && conf::get("interface.autocomplete")) {
            $html="<input type=hidden id='" . e($id) . "' name='" . e($name) . "'" .
                " value='" . e($value) . "'>";
            $html.="<input type=text id='_" . e($id) . "' name='_" . e($name) . "'" .
                " value='" . e($text) . "' class='autocomplete'>";
        } else {
            $html=create_pulldown($name, $value, get_people_select_array($user));
        }
        return $html;
    }
}
?>
