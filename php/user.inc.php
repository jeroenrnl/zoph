<?php

/*
 * A class representing a user of Zoph.
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
 */
class user extends zoph_table {

    var $person;
    var $prefs;
    var $crumbs;
    var $lang; // holds translations

    function user($id = 0) {
        parent::zoph_table("users", array("user_id"), array("user_name"));
        $this->set("user_id", $id);
    }

    function insert() {
        parent::insert();
        $this->prefs = new prefs($this->get("user_id"));
        $this->prefs->insert();
    }

    function delete() {
        parent::delete(null, array("prefs"));
    }

    function lookup_person() {
        $this->person = new person($this->get("person_id"));
        $this->person->lookup();
    }

    function lookup_prefs() {
        $this->prefs = new prefs($this->get("user_id"));
        $this->prefs->lookup();
    }

    function is_admin() {
        return $this->get("user_class") == 0;
    }

    function get_lastnotify() {
        return $this->get("lastnotify");
    }

    function get_album_permissions($album_id) {
        $ap = new album_permissions($this->get("user_id"), $album_id);
        if ($ap->lookup()) {
            return $ap;
        }

        return null;
    }


    function get_permissions_for_photo($photo_id) {

        // do ordering to grab entry with most permissions
        $sql =
            "select ap.* from " .
            DB_PREFIX . "photo_albums as pa, " .
            DB_PREFIX . "album_permissions as ap, " .
            DB_PREFIX . "photos as ph " .
            "where ap.user_id = '" . escape_string($this->get("user_id")) . "'".
            " and ap.album_id = pa.album_id" .
            " and pa.photo_id = ph.photo_id" .
            " and ph.photo_id = '" . escape_string($photo_id) . "'" .
            " and ap.access_level >= ph.level " .
            "order by ap.access_level desc, writable desc " .
            "limit 0, 1";

        $aps = get_records_from_query("album_permissions", $sql);
        if ($aps && sizeof($aps) >= 1) {
            return $aps[0];
        }

        return null;
    }

    function get_display_array() {
        $da = array(
            translate("username") => $this->get("user_name"),
            translate("person") => get_link("person", $this->get("person_id")),
            translate("class") =>
                $this->get("user_class") == 0 ? "Admin" : "User",
            translate("can browse people") => $this->get("browse_people") == 1
                ? translate("Yes") : translate("No"),
            translate("can browse places") => $this->get("browse_places") == 1
                ? translate("Yes") : translate("No"),
            translate("can view details of people") =>
                $this->get("detailed_people") == 1
                ? translate("Yes") : translate("No"),
            translate("can view details of places") =>
                $this->get("detailed_places") == 1
                ? translate("Yes") : translate("No"),
            translate("can import") =>
                $this->get("import") == 1
                ? translate("Yes") : translate("No"),
            translate("can download zipfiles") =>
                $this->get("download") == 1
                ? translate("Yes") : translate("No"),
            translate("can leave comments") =>
                $this->get("leave_comments") == 1
                ? translate("Yes") : translate("No"),
            translate("last login") =>
                $this->get("lastlogin"),
            translate("last ip address") =>
                $this->get("lastip"));

        if ($this->get("lightbox_id")) {
            $lightbox = new album($this->get("lightbox_id"));
            $lightbox->lookup();

            if ($lightbox->get("album")) {
                $da[translate("lightbox album")] = $lightbox->get("album");
            }
        }

        return $da;
    }

    function load_language($force = 0) {

        global $HTTP_ACCEPT_LANGUAGE;

        if (!$force && $this->lang != null) {
            return $this->lang;
        }

        if ($this->prefs != null && $this->prefs->get("language") != null) {
            $iso = $this->prefs->get("language");

            // instead of lang_exists() which requires the language name
            if (file_exists(LANG_DIR . '/' . $iso)) {
                $application_lang = "$iso";
            }
        }

        // check browser list if there is no pref (or an invalid one)
        if (!isset($application_lang) && isset($HTTP_ACCEPT_LANGUAGE)) {
            $isotab = explode(",", $HTTP_ACCEPT_LANGUAGE);

            for ($i = 0; $i < count($isotab) && !isset($application_lang); $i++) {
                $iso = substr(trim($isotab[$i]), 0, 2);

                // instead of lang_exists (which requires the language name
                if (file_exists(LANG_DIR . '/' . $iso)) {
                    $application_lang = "$iso";
                }
            }
        }

        // default to English
        if(!isset($application_lang)) {
          $application_lang = "en";
        }

        $this->lang = new rtplang("lang", "en", "en", $application_lang);
        return $this->lang;
    }

    function add_crumb($title, $link) {
        $numCrumbs = count($this->crumbs);
        if ($numCrumbs == 0 || (!strpos($link, "_crumb="))) {

            // if title is the same remove last and add new
            if ($numCrumbs > 0 &&
                strpos($this->crumbs[$numCrumbs - 1], ">$title<")) {

                $this->eat_crumb();
            }
            else {
                $numCrumbs++;
            }

            $question = strpos($link, "?");
            if ($question > 0) {
                $link =
                    substr($link, 0, $question) ."?_crumb=$numCrumbs&amp;" .
                    substr($link, $question + 1);
            }
            else {
                $link .= "?_crumb=$numCrumbs";
            }

            $this->crumbs[] = "<a href=\"$link\">$title</a>";
        }
    }

    function eat_crumb($num = -1) {
        if ($this->crumbs && count($this->crumbs) > 0) {
            if ($num < 0) { $num = count($this->crumbs) - 1; }
            $this->crumbs = array_slice($this->crumbs, 0, $num);
        }
    }

    function get_last_crumb() {
        if ($this->crumbs && count($this->crumbs) > 0) {
            return html_entity_decode($this->crumbs[count($this->crumbs) - 1]);
        }
    }

}

function get_users($order = "user_name") {
    return get_records("user", $order);
}

?>
