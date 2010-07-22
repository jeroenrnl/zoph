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

        $args["command"]="import";
        $args["thumbs"]=true;
        $args["copy"]=false;
        $args["useids"]=false;
        
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
                    $args["thumbs"]=true;
                    break;
                case "--nothumbs":
                case "--no-thumbs":
                case "-n":
                    unset($current);
                    $args["thumbs"]=false;
                    break;


                case "--update":
                case "-u":
                    unset($current);
                    $args["command"]="update";
                    break;
                case "--import":
                case "-I":
                    unset($current);
                    $args["command"]="import";
                    break;
                case "--useIds":
                case "--useids":
                case "--use-ids":
                case "--useid":
                case "--use-id":
                    unset($current);
                    $args["useids"]=true;
                    break;
                case "--update-thumbs":
                case "--updateThumbs":
                case "--updatethumbs":
                    unset($current);
                    $args["command"]="updatethumbs";
                    break;
                case "--update-exif":
                case "--updateEXIF":
                case "--updateexif":
                    unset($current);
                    $args["command"]="updateexif";
                    break;


                case "--copy":
                    unset($current);
                    $args["copy"]=true;
                    break;
                case "--move":
                    unset($current);
                    $args["copy"]=false;
                    break;

                case "--dateddirs":
                case "--datedDirs":
                case "-d":
                    unset($current);
                    $args["dated"]=true;
                    break;
                case "--hierarchical":
                case "--hier":
                case "-H":
                    unset($current);
                    $args["dated"]=true;
                    $args["hier"]=true;
                    break;
                case "--no-dateddirs":
                case "--no-datedDirs":
                case "--nodateddirs":
                case "--nodatedDirs":
                    unset($current);
                    $args["dated"]=false;
                    break;
                case "--no-hierarchical":
                case "--no-hier":
                case "--nohierarchical":
                case "--nohier":
                    unset($current);
                    $args["hier"]=false;
                    break;
                case "--path":
                    $current=&$args["path"];
                    break;
                case "-V":
                case "--version":
                    unset($current);
                    $args["command"]="version";
                    break;
                case "-h":
                case "--help":
                    unset($current);
                    $args["command"]="help";
                    break;
                case "-v":
                case "--verbose":
                    unset($current);
                    $args["verbose"]++;
                    break;
                default:
                    if(substr($arg,0,1)=="-") {
                        echo "unknown argument: " . $arg . "\n";
                        exit(1);
                    } else if (is_null($current)) {
                        if($arg{0}=="/") {
                            $args["files"][]=$arg;
                        } else {
                            $args["files"][]=getcwd() . "/" .$arg;
                        }
                            
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

            if(isset($args["fields"])) {
                foreach($args["fields"] as $f) {
                    $field=explode("=", $f);
                    $newfields[$field[0]]=$field[1];
                }
                $args["fields"]=$newfields;
            }

            if($args["useids"]==true && $args["command"]=="import") {
                $args["command"]="update";
            }
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
            switch($type) {
                case "albums":
                    foreach($arg as $name) {
                        $album=album::getByName($name);
                        if($album) {
                            $album_id=$album[0]->getId();
                            $vars["_album_id"][]=$album_id;
                        } else {
                            echo "Album not found: $name\n";
                            continue;
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
                            continue;
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
                            continue;
                        }
                    }
                    break;
                case "photographer":
                    $person=person::getByName($arg);
                    if($person) {
                        $person_id=$person[0]->getId();
                        $vars["photographer_id"][]=$person_id;
                    } else {
                        echo "Person not found: $arg\n";
                        continue;
                    }
                    break;
                case "location":
                    $place=place::getByName($arg);
                    if($place) {
                        $place_id=$place[0]->getId();
                        $vars["location_id"][]=$place_id;
                    } else {
                        echo "Place not found: $arg\n";
                        continue;
                    }
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
     * Returns the chosen command
     */
    public function getCommand() {
        return $this->processed["command"];
    }
    /**
     * Returns an array of variables, with keys.
     */
    public function getVars() {
        return $this->vars;
    }
    
    /**
     * Returns an array of variables, with keys.
     */
    public function getSwitches() {
        return array(
            "thumbs" => $this->processed["thumbs"],
            "copy" => $this->processed["copy"],
            "dated" => $this->processed["dated"],
            "hier" => $this->processed["hier"],
            "useids" => $this->processed["useids"]
        );
    }
}
?>
