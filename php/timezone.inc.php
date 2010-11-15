<?php

/*
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

    class TimeZone extends DateTimeZone {
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
    }

    class Time extends DateTime {
        function __construct($datetime, $tz=null) {
            try {
                if(valid_tz($tz->getName())) {
                    parent::__construct($datetime,$tz);
                } else {
                    parent::__construct($datetime);
                }
           } catch (Exception $e){
                echo "<b>Invalid time</b><br>";
                log::msg("<pre>" . $e->getMessage() . "</pre>", log::DEBUG, log::GENERAL);
           }
        }
    }

function get_tz_select_array() {
    $zones=DateTimeZone::listIdentifiers();
    array_unshift($zones, "");
    return $zones;
}

function get_tz_key($tz) {
    return array_search($tz,get_tz_select_array());
}

function guess_tz($lat, $lon) {
    if(minimum_version("5.1.2") && class_exists("XMLReader")) {
        $xml=new XMLReader();
        @$xml->open("http://ws.geonames.org/timezone?lat=" . 
            $lat . "&lng=" . $lon) or $failed=true;
        
        if (!$failed) {
            while($xml->read() && !$tz) {
                if($xml->name=="timezoneId") {
                    $xml->read();
                    $tz=$xml->value;
                }
            }
            return $tz;
        } else {
            $error=error_get_last();
            log::msg("Could not connect to Geonames site: " . 
                $error["message"], log::ERROR, log::GENERAL);
            return null;
        }
    } else {
        return null;
    }
}
function create_timezone_pulldown($name, $value=null, $user=null) {
    $id=preg_replace("/^_+/", "", $name);
    if($value) {
        $text=$value;
    } else {
        $text="";
    }

    if(AUTOCOMPLETE && JAVASCRIPT) {
        $html="<input type=hidden id='" . $id . "' name='" . $name. "'" .
            " value='" . $value . "'>";
        $html.="<input type=text id='_" . $id . "' name='_" . $name. "'" .
            " value='" . $text . "' class='autocomplete'>";
    } else {
        $html=create_pulldown("timezone_id", get_tz_key($value), get_tz_select_array());
    }
    return $html;
}


function valid_tz($tz) {
    // Checks if $tz contains a valid timezone string
    $tzones=DateTimeZone::listIdentifiers();
    return array_search($tz, $tzones);
}
    
?>
