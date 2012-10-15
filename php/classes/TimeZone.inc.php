<?php
/**
 * TimeZone class, extension of the standard PHP DateTimeZone class
 * Adds several Zoph-specific timezone-related functions.
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
 * TimeZone class, extension of the standard PHP DateTimeZone class
 */
class TimeZone extends DateTimeZone {
    /**
     * Get an XML document describing all the known timezones
     * used to create autocomplete dropdowns
     * @param string Partial timezone name to filter timezones
     * @return string XML document
     */
    function get_xml($search) {
        $xml = new DOMDocument('1.0','UTF-8');
        $rootnode=$xml->createElement("zones");

        $zones=$this->listIdentifiers();
        array_unshift($zones, "&nbsp;");
        $len=strlen($search);
        foreach($zones as $id => $tz) {
            $tzshort=strtolower(substr($tz,0,$len));
            if(strtolower($search)==$tzshort) {
                $newchild=$xml->createElement("tz");
                $key=$xml->createElement("key");
                $title=$xml->createElement("title");
                $key->appendChild($xml->createTextNode($id));
                $title->appendChild($xml->createTextNode($tz));
                $newchild->appendChild($key);
                $newchild->appendChild($title);

                $rootnode->appendChild($newchild);
            }
        }
        $xml->appendChild($rootnode);
        return $xml->saveXML();
    }

    /**
     * Get array to build html select box
     * @return array zones
     */
    public static function getSelectArray() {
        $zones=self::listIdentifiers();
        array_unshift($zones, "");
        return $zones;
    }

    /**
     * Get array of timezones with timezone names as key
     * @return array zones with names as key
     */
    public static function getTzArray() {
        $zones=self::getSelectArray();
        $zones=array_values($zones);
        $zones=array_combine($zones, $zones);
        return $zones;
    }

    /**
     * Get Key from timezone name
     * @param string timezone
     * @return string key
     */
    public static function getKey($tz) {
        return array_search($tz,self::getSelectArray());
    }

    /**
     * Create Pulldown menu for timezone selection
     * @param string name for the html document
     * @param string current value
     * @param user not used, for compatibility with createPulldown functions
     *             in other objects
     * @return string HTML code to display pulldown
     * @todo Returns HTML!
     */
    public static function createPulldown($name, $value=null, $user=null) {
        $id=preg_replace("/^_+/", "", $name);
        if($value) {
            $text=$value;
        } else {
            $text="";
        }

        if(conf::get("interface.autocomplete")) {
            $html="<input type=hidden id='" . $id . "' name='" . $name. "'" .
                " value='" . $value . "'>";
            $html.="<input type=text id='_" . $id . "' name='_" . $name. "'" .
                " value='" . $text . "' class='autocomplete'>";
        } else {
            $html=create_pulldown("timezone_id", self::getKey($value), self::getSelectArray());
        }
        return $html;
    }

    /**
     * Validate a timezone name
     * @param string Timezone name
     * @return bool
     */
    public static function validate($tz) {
        // Checks if $tz contains a valid timezone string
        $tzones=DateTimeZone::listIdentifiers();
        return array_search($tz, $tzones);
    }

    /**
     * Guess timezone based on lat & lon
     * Uses the geonames project
     * @param float latitude
     * @param float longitude
     * @return string timezone
     */
    public static function guess($lat, $lon) {
        if(minimum_version("5.1.2") && class_exists("XMLReader")) {
            $failed=false;
            $xml=new XMLReader();
            @$xml->open("http://ws.geonames.org/timezone?lat=" . 
                $lat . "&lng=" . $lon) or $failed=true;
            
            if (!$failed) {
                while($xml->read()) {
                    if($xml->name=="timezoneId") {
                        $xml->read();
                        return $xml->value;
                    }
                }
            } else {
                $error=error_get_last();
                log::msg("Could not connect to Geonames site: " . 
                    $error["message"], log::ERROR, log::GENERAL);
            }
            return null;
        }
    }    
}

?>
