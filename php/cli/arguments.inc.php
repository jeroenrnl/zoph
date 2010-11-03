<?php
/**
 * This file reads and interpretes the CLI arguments
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
 * This class reads and interpretes CLI arguments 
 */

class arguments {
    /**
     * Contains the non-interpreted arguments
     */
    private $arguments = array();
    /**
     * Contains the interpreted arguments, before lookup
     */
    private $processed = array();
    /**
     * Contains the interpreted arguments, after lookup
     */
    private $vars = array();

    public static $command="import";

    /**
     * Create a new instance of the class.
     * This construct also takes care of interpreting an d looking up of the
     * values
     */
    public function __construct() {
        global $argv;
        if(is_array($argv)) {
            // We don't care about the name of the script
            array_shift($argv);
            $this->arguments=$argv;
            if(count($this->arguments)===0) {
                $this->arguments[]="--help";
            }
        } else {
            echo "Could not read argument list";
            exit(cli::EXIT_CANNOT_ACCESS_ARGUMENTS);
        }
        $this->process();
        $this->lookup();
    }

    /**
     * Process the arguments
     * @todo This function contains a list of all arguments Zoph can understand
     * this really doesn't belong here and should be moved into a controller
     * part of the app.
     */
    private function process() {
        $argv=$this->arguments;
        $args=&$this->processed;

        $args["albums"]=array();
        $args["categories"]=array();
        $args["files"]=array();
        $args["people"]=array();
        $args["photographer"]="";
        $args["location"]="";
        $args["instance"]="";
        $args["fields"]=array();
        $args["path"]="";

        foreach($argv as $arg) {
            switch($arg) {
                case "--instance":
                case "-i":
                    $current=&$args["instance"];
                    break;


                case "--albums":
                case "--album":
                case "-a":
                    $current=&$args["albums"];
                    break;
                case "--category":
                case "--categories":
                case "-c":
                    $current=&$args["categories"];
                    break;
                case "--fields":
                case "--field":
                case "-f":
                    $current=&$args["fields"];
                    break;
                case "--import":
                    unset($current);
                    self::$command="import";
                    break;
                case "--place":
                case "--location":
                case "-l":
                    $current=&$args["location"];
                    break;
                case "--people":
                case "--persons":
                case "--person":
                case "-p":
                    $current=&$args["people"];
                    break;
                case "--photographer":
                case "-P":
                    $current=&$args["photographer"];
                    break;

                case "--thumbs":
                case "-t":
                    unset($current);
                    settings::$importThumbs=true;
                    break;
                case "--nothumbs":
                case "--no-thumbs":
                case "-n":
                    unset($current);
                    settings::$importThumbs=false;
                    break;
                case "--exif":
                case "--EXIF":
                    unset($current);
                    settings::$importExif=true;
                    break;
                case "--no-exif":
                case "--noEXIF":
                case "--noexif":
                case "--no-EXIF":
                    unset($current);
                    settings::$importExif=false;
                    break;
                case "--size":
                    unset($current);
                    settings::$importSize=true;
                    break;
                case "--nosize":
                case "--no-size":
                    unset($current);
                    settings::$importSize=false;
                    break;
                

                case "--update":
                case "-u":
                    unset($current);
                    self::$command="update";
                    break;
                case "--import":
                case "-I":
                    unset($current);
                    self::$command="import";
                    break;

                case "--useIds":
                case "--useids":
                case "--use-ids":
                case "--useid":
                case "--use-id":
                    unset($current);
                    settings::$importUseids=true;
                    break;

                case "--copy":
                    unset($current);
                    settings::$importCopy=true;
                    break;
                case "--move":
                    unset($current);
                    settings::$importCopy=false;
                    break;
                
                case "--dateddirs":
                case "--datedDirs":
                case "--dated":
                case "-d":
                    unset($current);
                    settings::$importDated=true;
                    break;
                case "--hierarchical":
                case "--hier":
                case "-H":
                    unset($current);
                    settings::$importDated=true;
                    settings::$importHier=true;
                    break;
                case "--no-dateddirs":
                case "--no-datedDirs":
                case "--no-dated":
                case "--nodateddirs":
                case "--nodatedDirs":
                case "--nodated":
                    unset($current);
                    settings::$importDated=false;
                    break;
                case "--no-hierarchical":
                case "--no-hier":
                case "--nohierarchical":
                case "--nohier":
                    unset($current);
                    settings::$importHier=false;
                    break;
                case "--path":
                    $current=&$args["path"];
                    break;
                case "-V":
                case "--version":
                    unset($current);
                    self::$command="version";
                    break;
                case "-h":
                case "--help":
                    unset($current);
                    self::$command="help";
                    break;
                case "-v":
                case "--verbose":
                    unset($current);
                    settings::$importVerbose++;
                    break;
                default:
                    if(substr($arg,0,1)=="-") {
                        echo "unknown argument: " . $arg . "\n";
                        exit(1);
                    } else if (is_null($current)) {
                        $args["files"][]=$arg;
                    } else if (!is_array($current)) {
                        $current=$arg;
                        unset($current);
                    } else {
                        $new=explode(",", $arg);
                        foreach($new as $n) {
                            $current[]=trim($n);
                        }
                        unset($current);
                    }
                    break;
            }
        }
        if(isset($args["fields"])) {
            foreach($args["fields"] as $f) {
                $field=explode("=", $f);
                $newfields[$field[0]]=$field[1];
            }
            $args["fields"]=$newfields;
        }

        if(settings::$importUseids==true && self::$command=="import") {
            self::$command="update";
        }
    }
    /**
     * Looks up the given parameters in the database and gives back ids
     */
    private function lookup() {
        $args=$this->processed;
        
        $vars=&$this->vars;
        foreach($args as $type=>$arg) {
            if(empty($arg) || empty($type)) {
                continue;
            }
            
            log::msg($type . "\t->\t" . $arg, log::DEBUG, log::IMPORT);

            switch($type) {
                case "albums":
                    foreach($arg as $name) {
                        $album=album::getByName($name);
                        if($album) {
                            $album_id=$album[0]->getId();
                            $vars["_album_id"][]=$album_id;
                        } else {
                            echo "Album not found: $name\n";
                            exit(cli::EXIT_ALBUM_NOT_FOUND);
                        }
                    }
                    break;
                case "categories":
                    foreach($arg as $name) {
                        $cat=category::getByName($name);
                        if($cat) {
                            $cat_id=$cat[0]->getId();
                            $vars["_category_id"][]=$cat_id;
                        } else {
                            echo "Category not found: $name\n";
                            exit(cli::EXIT_CAT_NOT_FOUND);
                        }
                    }
                    break;
                case "people":
                    foreach($arg as $name) {
                        $person=person::getByName($name);
                        if($person) {
                            $person_id=$person[0]->getId();
                            $vars["_person_id"][]=$person_id;
                        } else {
                            echo "Person not found: $name\n";
                            exit(cli::EXIT_PERSON_NOT_FOUND);
                        }
                    }
                    break;
                case "photographer":
                    $person=person::getByName($arg);
                    if($person) {
                        $person_id=$person[0]->getId();
                        $vars["photographer_id"]=$person_id;
                    } else {
                        echo "Person not found: $arg\n";
                        exit(cli::EXIT_PERSON_NOT_FOUND);
                    }
                    break;
                case "location":
                    $place=place::getByName($arg);
                    if($place) {
                        $place_id=$place[0]->getId();
                        $vars["location_id"]=$place_id;
                    } else {
                        echo "Place not found: $arg\n";
                        exit(cli::EXIT_PLACE_NOT_FOUND);
                    }
                    break;
                case "path":
                    $vars["_path"]=$arg;
                    break;
                case "fields":
                    foreach($arg as $field=>$value) {
                        $vars[$field]=$value;
                    }
                    break;
            }
        }
    }
    /**
     * Returns the list of files
     */
    public function getFiles() {
        return $this->processed["files"];
    }

    /**
     * Returns an array of variables, with keys.
     */
    public function getVars() {
        return $this->vars;
    }
    
}
?>
