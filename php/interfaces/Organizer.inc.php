<?php
/**
 * Organizer interface
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
 * @package Zoph
 * @author Jeroen Roos
 */

/**
 * An Organizer is an item that can be used to organize photos
 * @package Zoph
 * @author Jeroen Roos
 */
interface Organizer {
    public function addPhoto(photo $photo);
    public function delete();
    public function getCoverphoto();
    public function getDetails();
    public function getDetailsXML(array $details);
    public function getPhotoCount();
    public function getTotalPhotoCount();
    public function getURL();
    public function removePhoto(photo $photo);
    public static function getByName($name);
    public static function getTopN();
}
