<?php
/**
 * Selection class
 * A photo can be "selected" to be used in another part of Zoph,
 * for example to be set as a coverphoto or to define related photos
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

/**
 * Selection class
 * A photo can be "selected" to be used in another part of Zoph,
 * for example to be set as a coverphoto or to define related photos
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class selection {
    private $photos=array();

    /**
     * Create a new selection object
     * @param array $_SESSION should be passed here
     * @param array The links that need to be displayed with each photo
     *              This array MUST include a ["return"] that will
     *              be the url to which the user is redirected back
     *              afterwards.
     */
    public function __construct($session, $links) {
        if (!isset($session["selected_photo"])) {
            return;
        }

        $this->links=$links;

        foreach ($session["selected_photo"] as $photo_id) {
            $photo=new photo($photo_id);
            $photo->lookup();
            $this->photos[]=$photo;
        }
    }

    /**
     * Display the selection div
     */
    public function __toString() {
        $links=$this->links;
        $return=$links["return"];
        unset($links["return"]);

        $photos=array();

        foreach ($this->photos as $photo) {
            $actionlinks=array();
            foreach ($links as $title => $link) {
                $actionlinks[$title] = $link . $photo->getId() . "&amp;" . $return;
            }
            $actionlinks["x"] = "photo.php?_action=deselect&amp;photo_id=" . $photo->getId() . "&amp;" . $return;

            $tplActionlinks=new block("actionlinks",array(
                "actionlinks"   => $actionlinks
            ));

            $photos[]=array(
                "actionlinks"   => $tplActionlinks,
                "photo"         => $photo
            );

        }

        $tpl=new block("selection", array(
            "count"     => count($this->photos),
            "photos"    => $photos
        ));
        return (string) $tpl;
    }
}
