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
 */

class language {
    public $iso;
    public $name;
    private $filename;
    private $translations;
    private $base="en";

    function __construct($iso) {
        $this->name=$iso;
        $this->filename=LANG_DIR. "/" . $iso;
        $this->iso=$iso;
    }

    private function open_file() {
        if (file_exists($this->filename)) {
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

    function read_header() {
        $file=$this->open_file();
        $header=fgets($file);
        $zoph_header="# zoph language file - ";
        if(strtolower(substr($header,0,23))!=$zoph_header) {
            if(DEBUG && 2) {
                echo "Incorrect language header in <b>" . $this->filename .
                    "<b>:<br><pre>" . $header. "</pre>";
            }
        } else {
            $this->name=substr($header,23);
            fclose($file);
            return true;
        }
    }

    function read() {
        $file=$this->open_file();
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
    }        
        
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

    public static function exists($iso) {
        $file=LANG_DIR . '/' . $iso;
        if (file_exists($file) && is_file($file)) {
            return $iso;
        } else {
            return null;
        }
    }

}

function translate($str, $error=true){
  global $lang;
  return $lang->translate($str, $error);
}

?>
