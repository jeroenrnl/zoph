<?php
/*
 * Class that takes care of the translation of strings in Zoph. 
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
 * This file is based on the rtplang class written by Eric Seigne.
 *
 */

/**
 * This class contains a set of translations read from a file in the LANG_DIR
 * directory.
 * These files have the following format
 * # Zoph Language File - <language name>
 * # Optional comments
 * # English=Translation
 * # English=Translation
 * The file MUST be UTF8 encoded.
 * @author Jeroen Roos
 * @package Zoph
 */
class language {
    public $iso;
    public $name;
    private $filename;
    private $translations;
    private $base="en";

    /**
     * @param string iso ISO definition of the language, usually 2 letters or 
     * two letters dash two letters, for example nl en-ca.
     * This is also the name of the file it will try to read.
     */
    function __construct($iso) {
        $this->name=$iso;
        $this->filename=LANG_DIR. "/" . $iso;
        $this->iso=$iso;
    }

    /**
     * Open the file
     * @return filedescriptor file
     */
    private function open_file() {
        if (file_exists($this->filename) && is_readable($this->filename)) {
            try {
                $file=fopen($this->filename, "r");
            } catch (Exception $e) {
                if(DEBUG && 2) {
                    echo "Could not read language file $this->filename: " .
                        "<pre>" . $e->getMessage() . "</pre><br>";
                }
                return false;
            }
            return $file;
        } else {
            return false;
        }
    }

    /**
     * Read and parse the header of the file.
     * Unless DEBUG is on, nothing will be mentionned about files with
     * a wrong header, they will be silently ignored.
     * @return bool true|false
     */
    function read_header() {
        $file=$this->open_file();
        if(!$file) { return false; }
        $header=fgets($file);
        $zoph_header="# zoph language file - ";
        if(strtolower(substr($header,0,23))!=$zoph_header) {
            if(DEBUG && 2) {
                echo "Incorrect language header in <b>" . $this->filename .
                    "<b>:<br><pre>" . $header. "</pre>";
            }
            return false;
        } else {
            $this->name=substr($header,23);
            fclose($file);
            return true;
        }
    }

    /**
     * Read the strings from the file
     * @return bool true|false
     */
    function read() {
        $file=$this->open_file();
        if(!$file) { return false; }
        while ($line=fgets($file)) {
            if($line[0] == "#") {
                if(DEBUG && 2) {
                    echo "<b>" . $this->iso . "</b>:" . $line;
                }
            } else {
                $strings=explode("=",$line);
                $this->translations[$strings[0]]=$strings[1];
            }
        }              
        fclose($file);
        return true;
    
    }

    /**
     * Translate the given string
     * @param string The string to be translated
     * @param bool If true add [tr] before any string that cannot be
     *   translated.
     * @return string The translated string
     */
    function translate($string, $error = true) {
        $translation=$this->translations[$string];
        if($translation) {
            return $translation;
        } else {
            if($error && !($this->iso==$this->base)) {
                $tag = "<b>[tr]</b> ";
            }
            return $tag . $string;
       }
    }

    /**
     * Get all languages
     * @return array array of language objects
     */
    public static function get_all() {
        $langs=array();
        if(is_dir(LANG_DIR)) {
            $handle=opendir(LANG_DIR);
            while ($filename = trim(readdir($handle))) {
                if(!is_dir(LANG_DIR . "/" . $filename)) {
                    $lang=new language($filename);
                    if($lang->read_header()) {
                        $langs[]=$lang;
                    }
                }
            }
        }
        closedir($handle);
        return $langs;
    }

    /**
     * Check if file for a certain language exists
     * @param string ISO code for language
     * @return string null|iso
     */
    public static function exists($iso) {
        $file=LANG_DIR . '/' . $iso;
        if (file_exists($file) && is_file($file)) {
            return $iso;
        } else {
            return null;
        }
    }

    /**
     * Load the first available language, or fall back to a default
     * @param array Array of languages to try.
     * @return language language object
     */
    public static function load($langs) {
        array_push($langs, DEFAULT_LANG, "en");
        foreach ($langs as $l) {
            if(DEBUG && 2) {
                echo "Trying to load language: <b>" . $l . "</b>i<br>\n";
            }
            if(language::exists($l)) {
                $lang=new language($l);
                if($lang->read_header() && $lang->read()) {
                    if(DEBUG && 2) {
                        echo "Loaded language: <b>" . $l . "</b>i<br>\n\n";
                    }
                    return $lang;
                }
            }
        }
    }
   
    /**
     * Get HTTP_ACCEPT_LANG and interprete it
     * @return array array of languages in preference order
     */
    public static function http_accept() {
        global $HTTP_ACCEPT_LANGUAGE;
        $langs=array();
        $genlangs=array();

        $accept_langs=explode(",", $HTTP_ACCEPT_LANGUAGE);
        foreach ($accept_langs as $al) {
            # Some browers add a 'quality' identifier to indicate
            # the preference of this language, something like en;q=1.0
            $l=explode(";",$al);
            $langs[]=$l[0];

            # A user could select a "sublanguage" such as en-gb for British
            # English, or de-ch for Swiss German to make sure that 
            # Zoph offers these users English or German, unless the more
            # specific one is available (Zoph has a Canadian English
            # translation for example), we add both en-gb and en to the list
            if(strpos($l[0], "-")) {
                $genlang=explode("-", $l[0]);
                $genlangs[]=$genlang[0];
            }
        }
        
        $return=array_unique(array_merge($langs, $genlangs));
        if(DEBUG && 2) {
            echo "<b>HTTP_ACCEPT_LANGUAGE</b>: " . $HTTP_ACCEPT_LANGUAGE . "<br>\n";
            echo "<b>Zoph's interpretation</b>: ";
            print_r($return);
            echo "<br>\n";
        }
        return $return;
    }
}

/**
 * Translate the given string
 * @param string The string to be translated
 * @param bool If true add [tr] before any string that cannot be
 *   translated.
 * @return string The translated string
 */
function translate($str, $error=true){
  global $lang;
  return $lang->translate($str, $error);
}

?>
