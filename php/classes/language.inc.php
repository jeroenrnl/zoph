<?php
/**
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
 * @author Jeroen Roos
 * @package Zoph
 */

/**
 * This class contains a set of translations read from a file in the self::LANG_DIR
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
    private $translations=array();

    /**
     * @var This defines what the base language is, the language the strings in the
     * sourcecode are in.
     */
    public static $base="en";
    public static $base_name="English";

    const LANG_DIR="lang";

    /**
     * @param string iso ISO definition of the language, usually 2 letters or 
     * two letters dash two letters, for example nl en-ca.
     * This is also the name of the file it will try to read.
     */
    function __construct($iso) {
        $this->name=$iso;
        $this->filename=self::LANG_DIR. "/" . $iso;
        $this->iso=strtolower($iso);
    }

    /**
     * Open the file
     * @return filedescriptor file
     */
    private function openFile() {
        if (file_exists($this->filename) && is_readable($this->filename)) {
            try {
                $file=fopen($this->filename, "r");
            } catch (Exception $e) {
                log::msg("Could not read language file $this->filename: " .
                        "<pre>" . $e->getMessage() . "</pre>", log::ERROR, log::LANG);
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
    function readHeader() {
        $file=$this->openFile();
        if(!$file) { return false; }
        $header=fgets($file);
        $zoph_header="# zoph language file - ";
        if(strtolower(substr($header,0,23))!=$zoph_header) {
            log::msg("Incorrect language header in <b>" . 
                $this->filename . "</b>", log::ERROR, log::LANG);
            log::msg("<pre>" . $header. "</pre>", log::DEBUG, log::LANG);
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
        $file=$this->openFile();
        if(!$file) { return false; }
        while ($line=fgets($file)) {
            if($line[0] == "#") {
                log::msg("<b>" . $this->iso . "</b>:" . $line, log::MOREDEBUG, log::LANG);
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
     * @param string|array The string or array to be translated
     * @param bool If true add [tr] before any string that cannot be
     *   translated.
     * @return string The translated string
     */
    function translate($string, $error = true) {
        $tag="";
        if(is_array($string)) {
            return $this->translateArray($string, $error);
        }
        if(array_key_exists($string, $this->translations)) {
            return trim($this->translations[$string]);
        } else {
            if($error && !($this->iso==self::$base)) {
                $tag = "<b>[tr]</b> ";
            }
            return $tag . $string;
       }
    }

    /**
     * Translate an array
     * translates all the values in an array, not the keys.
     * @param array The array to be translated
     * @param bool If true add [tr] before any string that cannot be
     *   translated.
     * @return string The translated array 
     */
    private function translateArray($array, $error = true) {
        $tr=array();
        foreach($array as $key=>$string) {
            $tr[$key]=translate($string, $error);
        }
        return $tr;
    }
            
    /**
     * Get all languages
     * @return array array of language objects
     */
    public static function getAll() {
        $langs=array();
        $dir=settings::$php_loc . "/" . self::LANG_DIR;
        if(is_dir($dir) && is_readable($dir)) {
            foreach(glob($dir . "/*") as $filename) {
                if(!is_dir($filename)  && is_readable($filename)) {
                    $iso=basename($filename);
                    if($iso == strtolower($iso)) {
                        # making filename lowercase, so we won't include
                        # any capitalized filenames... Zoph will not able
                        # to find them back later...
                        # is isocode nl file NL Nl or nl?
                        $lang=new language($iso);
                        if($lang->readHeader()) {
                            $langs[$iso]=$lang;
                        }
                    } else {
                        log::msg("Language files should have lowercase names, cannot open <b>" . $filename . "</b>", log::WARN, log::LANG);
                    }
                } else {
                    log::msg("Cannot read <b>" . $filename . "</b>, skipping. ", log::ERROR, log::LANG);
                }
            }
        } else {
            log::msg("Cannot read language dir!", log::WARN, log::LANG);
        }    
        $base_lang=new language(self::$base);
        $base_lang->name=self::$base_name;
        $langs[self::$base]=$base_lang;
        ksort($langs);
        return $langs;
    }

    /**
     * Check if file for a certain language exists
     * @param string ISO code for language
     * @return string null|iso
     */
    public static function exists($iso) {
        $file=self::LANG_DIR . '/' . $iso;
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
        array_push($langs, conf::get("interface.language"), self::$base);
        foreach ($langs as $l) {
            log::msg("Trying to load language: <b>" . $l . "</b>", log::DEBUG, log::LANG);
            if(self::exists($l)) {
                $lang=new language($l);
                if($lang->readHeader() && $lang->read()) {
                    log::msg("Loaded language: <b>" . $l . "</b><br>", log::DEBUG, log::LANG);
                    return $lang;
                }
            } else if ($l==self::$base) {
                # If it is the base language, no file needs to exist
                log::msg("Using base language: <b>" . $l . "</b>", log::NOTIFY, log::LANG);

                $lang=new language($l);
                return $lang;
            }

        }
        log::msg("No languages found, falling back to default: <b>" .
            self::$base . "</b>", log::NOTIFY, log::LANG);
        return new language(self::$base);
    }
   
    /**
     * Get HTTP_ACCEPT_LANG and interprete it
     * @return array array of languages in preference order
     */
    public static function httpAccept() {
        $langs=array();
        $genlangs=array();
        $return=array();
        if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $accept_langs=explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            foreach ($accept_langs as $al) {
                # Some browers add a 'quality' identifier to indicate
                # the preference of this language, something like en;q=1.0
                $l=explode(";",$al);
                $langs[]=strtolower($l[0]);

                # A user could select a "sublanguage" such as en-gb for British
                # English, or de-ch for Swiss German to make sure that 
                # Zoph offers these users English or German, unless the more
                # specific one is available (Zoph has a Canadian English
                # translation for example), we add both en-gb and en to the list
                if(strpos($l[0], "-")) {
                    $genlang=explode("-", $l[0]);
                    $genlangs[]=strtolower($genlang[0]);
                }
            }
            
            $return=array_unique(array_merge($langs, $genlangs));
            log::msg("<b>Client accepts language(s):</b>: " . $_SERVER["HTTP_ACCEPT_LANGUAGE"], log::DEBUG, log::LANG);
            log::msg("<b>Zoph's interpretation</b>: " . implode(", ", $return), log::DEBUG, log::LANG);
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
    if($lang instanceof language) {
        return $lang->translate($str, $error);
    } else {      
        return $str;
    }
}
?>
