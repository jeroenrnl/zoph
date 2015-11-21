<?php
/**
 * A class representing an anonymous user of Zoph.
 * An anonymous user is a user that is not logged in
 * it is currently used for the 'share this photo' feature.
 * This is basicly a wrapper around the user object returning
 * null or false to prevent an anonymous user to gain extra
 * privileges.
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
 * @todo These requires should be removed once all classes can be autoloaded
 */
require_once "util.inc.php";
require_once "variables.inc.php";
require_once "prefs.inc.php";

/**
 * A class representing an anonymous user of Zoph.
 * An anonymous user is a user that is not logged in
 * it is currently used for the 'share this photo' feature.
 * This is basicly a wrapper around the user object returning
 * null or false to prevent an anonymous user to gain extra
 * privileges
 *
 * @package Zoph
 * @author Jeroen Roos
 */
final class anonymousUser extends user {

    /**
     * Create a new anonymousUser object
     * Fill 'prefs' with empty prefs object to prevent
     * lookups to go wrong.
     */
    public function __construct() {
        $this->prefs=new prefs();
    }

    /**
     * Return a bogus id
     */
    public function getId() {
        return 0;
    }

    /**
     * Return a bogus person id
     */
    public function lookupPerson() {
        return false;
    }

    /**
     * Fake preferences lookup
     */
    public function lookupPrefs() {
        return false;
    }

    /**
     * Anonymous user is never admin
     */
    function isAdmin() {
        return false;
    }

    /**
     * Anonymous users don't get notified.
     */
    function getLastNotify() {
        return 0;
    }

    /**
     * No link for anonymous users.
     */
    function getLink() {
        return false;
    }

    /**
     * No URL for anonymous users.
     */
    function getURL() {
        return false;
    }

    /**
     * Return a standard name
     * at this moment this is used nowhere...
     */
    function getName() {
        return("Anonymous User");
    }

    /**
     * No groups for user
     */
    function getGroups() {
        return 0;
    }

    /**
     * Get albums user can see
     * Anonymous user has no albums permissions
     * always return null
     * @param album unused, only for compatibility with @see user object
     */
    function getAlbumPermissions(album $album) {
        return null;
    }

    /**
     * Get permissions for specific photo.
     * No permissions for anonymous user
     * @param photo unused, only for compatibility with @see user object
     */
    function getPhotoPermissions(photo $photo) {
        return new group_permissions(0,0);
    }

    /**
     * Get array for display
     * Anonymous user doesn't get displayed, so return empty array.
     */
    function getDisplayArray() {
        return array();
    }

    /**
     * At this moment, anonynmous users only get photos
     * and no text, so no need load any language strings
     * @param bool Force loading - unused, only for compatibility with @see user object
     */
    function loadLanguage($force = 0) {
        return null;
    }
}
