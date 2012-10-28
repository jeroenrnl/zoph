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
    /** Contains the non-interpreted arguments */
    private $arguments = array();
    /** Contains the interpreted arguments, before lookup */
    private $processed = array();
    /** Contains the interpreted arguments, after lookup */
    private $vars = array();
    
    /** Default command */
    public static $command="import";

    /**
     * Create a new instance of the class.
     * This construct also takes care of interpreting and looking up of the
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
          Used short arguments: A C D H I N P V a c d f h i l n p r t u v w
        */

        for($i=0; $i<sizeof($argv); $i++) {
            switch($argv[$i]) {
                case "--instance":
                case "-i":
                    $args["instance"]=$argv[++$i];
                    break;


                case "--albums":
                case "--album":
                case "-a":
                    $albums=explode(",",$argv[++$i]);
                    foreach($albums as $album) {
                        $args["albums"][]=trim($album);
                        if(isset($parent)) {
                            $args["palbum"][]=trim($parent);
                        }
                    }
                    $parent=0;
                    break;

                case "--category":
                case "--categories":
                case "-c":
                    $cats=explode(",",$argv[++$i]);
                    foreach($cats as $cat) {
                        $args["categories"][]=trim($cat);
                        if(isset($parent)) {
                            $args["pcat"][]=trim($parent);
                        }
                    }
                    $parent=0;
                    break;

                case "--config":
                case "-C":
                    self::$command="config";
                    $args["_configitem"]=$argv[++$i];
                    if(isset($argv[$i+1])) {
                        $args["_configvalue"]=$argv[++$i];
                    } else {
                        $args["_configdefault"]=true;
                    }
                    break;
                case "--dumpconfig":
                    self::$command="dumpconfig";
                    break;
                case "--fields":
                case "--field":
                case "-f":
                    $args["fields"][]=$argv[++$i];
                    break;
                case "--import":
                    self::$command="import";
                    break;
                case "--place":
                case "--location":
                case "-l":
                    // Multiple locations are possible when using --new
                    $locs=explode(",",$argv[++$i]);
                    foreach($locs as $loc) {
                        $args["location"][]=trim($loc);
                        if(isset($parent)) {
                            $args["pplace"][]=trim($parent);
                        }
                    }
                    $parent=0;
                    break;
                case "--people":
                case "--persons":
                case "--person":
                case "-p":
                    $people=explode(",",$argv[++$i]);
                    foreach($people as $person) {
                        $args["people"][]=trim($person);
                    }
                    break;
                case "--photographer":
                case "-P":
                    $args["photographer"]=$argv[++$i];
                    break;

                case "--parent":
                    $parent=$argv[++$i];
                    break;

                case "--thumbs":
                case "-t":
                    settings::$importThumbs=true;
                    break;
                case "--nothumbs":
                case "--no-thumbs":
                case "-n":
                    settings::$importThumbs=false;
                    break;
                case "--exif":
                case "--EXIF":
                    settings::$importExif=true;
                    break;
                case "--no-exif":
                case "--noEXIF":
                case "--noexif":
                case "--no-EXIF":
                    settings::$importExif=false;
                    break;
                case "--size":
                    settings::$importSize=true;
                    break;
                case "--nosize":
                case "--no-size":
                    settings::$importSize=false;
                    break;
                case "--hash":
                    settings::$importHash=true;
                    break;
                case "--no-hash":
                    settings::$importHash=false;
                    break;


                case "--update":
                case "-u":
                    self::$command="update";
                    break;
                case "--import":
                case "-I":
                    self::$command="import";
                    break;
                case "--new":
                case "-N":
                    self::$command="new";
                    break;

                case "--useIds":
                case "--useids":
                case "--use-ids":
                case "--useid":
                case "--use-id":
                    settings::$importUseids=true;
                    break;

                case "--copy":
                    settings::$importCopy=true;
                    break;
                case "--move":
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
                    settings::$importDated=true;
                    break;
                case "--hierarchical":
                case "--hier":
                case "-H":
                    settings::$importDated=true;
                    settings::$importHier=true;
                    break;
                case "--no-dateddirs":
                case "--no-datedDirs":
                case "--no-dated":
                case "--nodateddirs":
                case "--nodatedDirs":
                case "--nodated":
                    settings::$importDated=false;
                    break;
                case "--no-hierarchical":
                case "--no-hier":
                case "--nohierarchical":
                case "--nohier":
                    settings::$importHier=false;
                    break;
                case "-D":
                case "--path":
                    $args["path"]=$argv[++$i];
                    break;

                case "--dirpattern":
                    $args["dirpattern"]=$argv[++$i];
                    break;

                case "-V":
                case "--version":
                    self::$command="version";
                    break;
                case "-h":
                case "--help":
                    self::$command="help";
                    break;
                case "-v":
                case "--verbose":
                    settings::$importVerbose++;
                    break;
                default:
                    if(substr($argv[$i],0,1)=="-") {
                        echo "unknown argument: " . $argv[$i] . "\n";
                        exit(1);
                    } else {
                        $args["files"][]=$argv[$i];
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
                case "_configitem":
                case "_configvalue":
                case "_configdefault":
                    $vars[$type]=$arg;
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
