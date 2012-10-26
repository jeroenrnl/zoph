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
     * Default command
     */
    public static $command="import";

    /**
     * Create a new instance of the class.
     * This construct also takes care of interpreting an d looking up of the
     * values
     */
    public function __construct() {
        global $argv;

        /* Set defaults to what is configured in web interface */
        settings::$importDated=conf::get("import.dated");
        settings::$importHier=conf::get("import.dated.hier");

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
        $args["photographer"]=array();
        $args["location"]=array();
        $args["instance"]="";
        $args["fields"]=array();
        $args["path"]="";
        $args["dirpattern"]="";

        /* For new albums, categories, places, people */
        
        $parent=0;
        $args["palbum"]=array();
        $args["pcat"]=array();
        $args["pplace"]=array();

        /*
          Used short arguments: A D H I N P V a c d f h i l n p r t u v w
        */

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
                    $cur_parent=&$args["palbum"];
                    break;

                case "--category":
                case "--categories":
                case "-c":
                    $current=&$args["categories"];
                    $cur_parent=&$args["pcat"];
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
                    $cur_parent=&$args["pplace"];
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

                case "--parent":
                    $current=&$parent;
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
                case "--hash":
                    unset($current);
                    settings::$importHash=true;
                    break;
                case "--no-hash":
                    unset($current);
                    settings::$importHash=false;
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
                case "--new":
                case "-N":
                    unset($current);
                    self::$command="new";
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
                
                case "-A":
                case "--autoadd":
                case "--auto-add":
                    settings::$importAutoadd=true;
                    break;

                case "-w":
                case "--add-always":
                case "--addalways":
                    settings::$importAddAlways=true;
                    break;
                
                case "-r":
                case "--recursive":
                    settings::$importRecursive=true;
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
                case "-D":
                case "--path":
                    $current=&$args["path"];
                    break;

                case "--dirpattern":
                    $current=&$args["dirpattern"];
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
                    } else if (!isset($current) || is_null($current)) {
                        $args["files"][]=$arg;
                    } else if (!is_array($current)) {
                        $current=$arg;
                        if(isset($cur_parent)) {
                            $cur_parent[]=trim($parent);
                        }
                        unset($current);
                        unset($cur_parent);
                    } else {
                        $new=explode(",", $arg);
                        foreach($new as $n) {
                            $current[]=trim($n);
                            if(isset($cur_parent)) {
                                $cur_parent[]=trim($parent);
                            }
                        }
                        if($arg!==$parent) {
                            $parent=0;
                        }
                        unset($current);
                        unset($cur_parent);
                    }
                    break;
            }
        }
        if(isset($args["fields"])) {
            $newfields=array();
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
                        if(self::$command=="new" || (settings::$importAutoadd && !album::getByName($name))) {
                            $parent=array_shift($args["palbum"]);
                            // this is a string comparison because the trim() in process() changes
                            // everything into a string...
                            if($parent==="0") {
                                if(settings::$importAddAlways) {
                                    $parent_id=album::getRoot()->getId();
                                } else {
                                    echo "No parent for album $name\n";
                                    exit(cli::EXIT_NO_PARENT);
                                }
                            } else {
                                $palbum=album::getByName($parent);
                                if($palbum) {
                                    $parent_id=$palbum[0]->getId();
                                } else {
                                    echo "Album not found: $parent\n";
                                    exit(cli::EXIT_ALBUM_NOT_FOUND);
                                }
                            }
                            $vars["_new_album"][]=array("parent" => $parent_id, "name" => $name);
                        } else {
                            $album=album::getByName($name);
                            if($album) {
                                $album_id=$album[0]->getId();
                                $vars["_album_id"][]=$album_id;
                            } else {
                                echo "Album not found: $name\n";
                                exit(cli::EXIT_ALBUM_NOT_FOUND);
                            }
                        }
                    }
                    break;
                case "categories":
                    foreach($arg as $name) {
                        if(self::$command=="new" || (settings::$importAutoadd && !category::getByName($name))) {
                            $parent=array_shift($args["pcat"]);
                            // this is a string comparison because the trim() in process() changes
                            // everything into a string...
                            if($parent==="0") {
                                if(settings::$importAddAlways) {
                                    $parent_id=category::getRoot()->getId();
                                } else {
                                    echo "No parent for category $name\n";
                                    exit(cli::EXIT_NO_PARENT);
                                }
                            } else {
                                $pcat=category::getByName($parent);
                                if($pcat) {
                                    $parent_id=$pcat[0]->getId();
                                } else {
                                    echo "Category not found: $parent\n";
                                    exit(cli::EXIT_CAT_NOT_FOUND);
                                }
                            }
                            $vars["_new_cat"][]=array("parent" => $parent_id, "name" => $name);
                        } else {
                            $cat=category::getByName($name);
                            if($cat) {
                                $cat_id=$cat[0]->getId();
                                $vars["_category_id"][]=$cat_id;
                            } else {
                                echo "Category not found: $name\n";
                                exit(cli::EXIT_CAT_NOT_FOUND);
                            }
                        }
                    }
                    break;
                case "people":
                    foreach($arg as $name) {
                        if(self::$command=="new" || (settings::$importAutoadd && !person::getByName($name))) {
                            $vars["_new_person"][]=$name;
                        } else {
                            $person=person::getByName($name);
                            if($person) {
                                $person_id=$person[0]->getId();
                                $vars["_person_id"][]=$person_id;
                            } else {
                                echo "Person not found: $name\n";
                                exit(cli::EXIT_PERSON_NOT_FOUND);
                            }
                        }
                    }
                    break;
                case "photographer":
                    foreach($arg as $name) {
                        if(self::$command=="new" || (settings::$importAutoadd && !person::getByName($name))) {
                            $vars["_new_photographer"][]=$name;
                        } else {
                            $person=person::getByName($name);
                            if($person) {
                                $person_id=$person[0]->getId();
                                $vars["photographer_id"]=$person_id;
                            } else {
                                echo "Person not found: $name\n";
                                exit(cli::EXIT_PERSON_NOT_FOUND);
                            }
                        }
                    }
                    break;
                case "location":
                    foreach($arg as $name) {
                        if(self::$command=="new" || (settings::$importAutoadd && !place::getByName($name))) {
                            $parent=array_shift($args["pplace"]);
                            // this is a string comparison because the trim() in process() changes
                            // everything into a string...
                            if($parent==="0") {
                                if(settings::$importAddAlways) {
                                    $parent_id=place::getRoot()->getId();
                                } else {
                                    echo "No parent for location $name\n";
                                    exit(cli::EXIT_NO_PARENT);
                                }
                            } else {
                                $pplace=place::getByName($parent);
                                if($pplace) {
                                    $parent_id=$pplace[0]->getId();
                                } else {
                                    echo "Location not found: $parent\n";
                                    exit(cli::EXIT_PLACE_NOT_FOUND);
                                }
                            }
                            $vars["_new_place"][]=array("parent" => $parent_id, "name" => $name);
                        } else {
                            $place=place::getByName($arg[0]);
                            if($place) {
                                $place_id=$place[0]->getId();
                                $vars["location_id"]=$place_id;
                            } else {
                                echo "Place not found: $arg[0]\n";
                                exit(cli::EXIT_PLACE_NOT_FOUND); 
                            }
                        }
                    }
                    break;
                case "path":
                    $vars["_path"]=$arg;
                    break;
                case "dirpattern":
                    if(!preg_match("/^[aclpDP]+$/", $arg)) {
                        echo "Illegal characters in --dirpattern, allowed are: aclpDP\n";
                        exit(cli::EXIT_ILLEGAL_DIRPATTERN);
                    } else {
                        $vars["_dirpattern"]=$arg;
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
     * Returns an array of variables, with keys.
     */
    public function getVars() {
        return $this->vars;
    }
    
}
?>
