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
     * @param array CLI arguments
     */
    public function __construct(array $argv) {
        // We don't care about the name of the script
        array_shift($argv);
        $this->arguments=$argv;
        if(count($this->arguments)===0) {
            $this->arguments[]="--help";
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
                conf::set("import.cli.thumbs", true);
                break;
            case "--nothumbs":
            case "--no-thumbs":
            case "-n":
                conf::set("import.cli.thumbs", false);
                break;
            case "--exif":
            case "--EXIF":
                conf::set("import.cli.exif", true);
                break;
            case "--no-exif":
            case "--noEXIF":
            case "--noexif":
            case "--no-EXIF":
                conf::set("import.cli.exif", false);
                break;
            case "--size":
                conf::set("import.cli.size", true);
                break;
            case "--nosize":
            case "--no-size":
                conf::set("import.cli.size", false);
                break;
            case "--hash":
                conf::set("import.cli.hash", true);
                break;
            case "--no-hash":
                conf::set("import.cli.hash", false);
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
                conf::set("import.cli.useids", true);
                break;

            case "--copy":
                conf::set("import.cli.copy", true);
                break;
            case "--move":
                conf::set("import.cli.copy", false);
                break;
            
            case "-A":
            case "--autoadd":
            case "--auto-add":
                conf::set("import.cli.add.auto", true);
                break;

            case "-w":
            case "--add-always":
            case "--addalways":
                conf::set("import.cli.add.always", true);
                break;
            
            case "-r":
            case "--recursive":
                conf::set("import.cli.recursive", true);
                break;

            
            case "--dateddirs":
            case "--datedDirs":
            case "--dated":
            case "-d":
                conf::set("import.dated", true);
                break;
            case "--hierarchical":
            case "--hier":
            case "-H":
                conf::set("import.dated", true);
                conf::set("import.dated.hier", true);
                break;
            case "--no-dateddirs":
            case "--no-datedDirs":
            case "--no-dated":
            case "--nodateddirs":
            case "--nodatedDirs":
            case "--nodated":
                conf::set("import.dated", false);
                break;
            case "--no-hierarchical":
            case "--no-hier":
            case "--nohierarchical":
            case "--nohier":
                conf::set("import.dated.hier", false);
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
                $verbose=conf::get("import.cli.verbose");
                conf::set("import.cli.verbose", ++$verbose);
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

        if(conf::get("import.cli.useids")==true && self::$command=="import") {
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

            log::msg($type . "\t->\t" . implode(",", (array) $arg), log::DEBUG, log::IMPORT);
            switch($type) {
            case "albums":
                foreach($arg as $name) {
                    if(self::$command=="new" || (conf::get("import.cli.add.auto") && !album::getByName($name))) {
                        $parent=array_shift($args["palbum"]);
                        // this is a string comparison because the trim() in process() changes
                        // everything into a string...
                        if($parent==="0") {
                            if(conf::get("import.cli.add.always")) {
                                $parent_id=album::getRoot()->getId();
                            } else {
                                throw new CliNoParentException("No parent for album " . $name);;
                            }
                        } else {
                            $palbum=album::getByName($parent);
                            if($palbum) {
                                $parent_id=$palbum[0]->getId();
                            } else {
                                throw new AlbumNotFoundException("Album not found: $parent");
                            }
                        }
                        $vars["_new_album"][]=array("parent" => $parent_id, "name" => $name);
                    } else {
                        $album=album::getByName($name);
                        if($album) {
                            $album_id=$album[0]->getId();
                            $vars["_album_id"][]=$album_id;
                        } else {
                            throw new AlbumNotFoundException("Album not found: $parent");
                        }
                    }
                }
                break;
            case "categories":
                foreach($arg as $name) {
                    if(self::$command=="new" || (conf::get("import.cli.add.auto") && !category::getByName($name))) {
                        $parent=array_shift($args["pcat"]);
                        // this is a string comparison because the trim() in process() changes
                        // everything into a string...
                        if($parent==="0") {
                            if(conf::get("import.cli.add.always")) {
                                $parent_id=category::getRoot()->getId();
                            } else {
                                throw new CliNoParentException("No parent for category " . $name);;
                            }
                        } else {
                            $pcat=category::getByName($parent);
                            if($pcat) {
                                $parent_id=$pcat[0]->getId();
                            } else {
                                throw new CategoryNotFoundException("Category not found: $parent");
                            }
                        }
                        $vars["_new_cat"][]=array("parent" => $parent_id, "name" => $name);
                    } else {
                        $cat=category::getByName($name);
                        if($cat) {
                            $cat_id=$cat[0]->getId();
                            $vars["_category_id"][]=$cat_id;
                        } else {
                            throw new CategoryNotFoundException("Category not found: $parent");
                        }
                    }
                }
                break;
            case "people":
                foreach($arg as $name) {
                    if(self::$command=="new" || (conf::get("import.cli.add.auto") && !person::getByName($name))) {
                        $vars["_new_person"][]=$name;
                    } else {
                        $person=person::getByName($name);
                        if($person) {
                            $person_id=$person[0]->getId();
                            $vars["_person_id"][]=$person_id;
                        } else {
                            throw new PersonNotFoundException("Person not found: $name");
                        }
                    }
                }
                break;
            case "photographer":
                $name=$arg;
                if(self::$command=="new" || 
                  (conf::get("import.cli.add.auto") && !person::getByName($name))) {
                    $vars["_new_photographer"][]=$name;
                } else {
                    $person=person::getByName($name);
                    if($person) {
                        $person_id=$person[0]->getId();
                        $vars["photographer_id"]=$person_id;
                    } else {
                        throw new PersonNotFoundException("Person not found: $name");
                    }
                }
                break;
            case "location":
                foreach($arg as $name) {
                    if(self::$command=="new" || (conf::get("import.cli.add.auto") && !place::getByName($name))) {
                        $parent=array_shift($args["pplace"]);
                        // this is a string comparison because the trim() in process() changes
                        // everything into a string...
                        if($parent==="0") {
                            if(conf::get("import.cli.add.always")) {
                                $parent_id=place::getRoot()->getId();
                            } else {
                                throw new CliNoParentException("No parent for location " . $name);;
                            }
                        } else {
                            $pplace=place::getByName($parent);
                            if($pplace) {
                                $parent_id=$pplace[0]->getId();
                            } else {
                                throw new PlaceNotFoundException("Location not found: $parent");
                            }
                        }
                        $vars["_new_place"][]=array("parent" => $parent_id, "name" => $name);
                    } else {
                        $name=$arg[0];
                        $place=place::getByName($name);
                        if($place) {
                            $place_id=$place[0]->getId();
                            $vars["location_id"]=$place_id;
                        } else {
                            throw new PlaceNotFoundException("Location not found: $name");
                        }
                    }
                }
                break;
            case "path":
                $vars["_path"]=$arg;
                break;
            case "dirpattern":
                if(!preg_match("/^[aclpDP]+$/", $arg)) {
                    throw new CliIllegalDirpatternException("Illegal characters in --dirpattern, allowed are: aclpDP");
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
