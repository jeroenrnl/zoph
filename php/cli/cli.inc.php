<?php
/**
 * Controller for the CLI
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
 * Controller class for the CLI
 */
class cli {
    /**
     * Defines the API version between the /bin/zoph binary and the files in the webroot
     * these must be equal.
     */
    const API=2;

    const EXIT_NO_PROBLEM       = 0;
    const EXIT_NO_ARGUMENTS     = 1;
    const EXIT_NO_FILES         = 2;
    
    const EXIT_IMAGE_NOT_FOUND  = 10;
    const EXIT_PERSON_NOT_FOUND = 20;
    const EXIT_PLACE_NOT_FOUND  = 30;
    const EXIT_ALBUM_NOT_FOUND  = 40;
    const EXIT_CAT_NOT_FOUND    = 50;
    const EXIT_NOT_IN_CWD       = 61;
    const EXIT_ILLEGAL_DIRPATTERN = 62;
    const EXIT_NO_PARENT        = 80;

    // 90 - 97  are also defined in /bin/zoph, as global constants.
    const EXIT_INI_NOT_FOUND    = 90;
    const EXIT_INSTANCE_NOT_FOUND    = 91;

    const EXIT_CLI_USER_NOT_ADMIN    = 95;
    const EXIT_CLI_USER_NOT_VALID    = 96;
    const EXIT_CLI_USER_NOT_DEFINED    = 97;

    const EXIT_API_NOT_COMPATIBLE    = 99;

    const EXIT_CANNOT_ACCESS_ARGUMENTS    = 250;
    const EXIT_UNKNOWN_ERROR    = 254;

    /**
     * @var The user that is doing the import
     */
    private $user;
    /**
     * @var Commandline arguments
     */
    private $args;
    /**
     * List of files to be imported
     */
    private $files=array();
    private $photos=array();

    /**
     * Create cli object
     * @param User user doing the import
     * @param int API version of the executable script. This is used to check if the executable 
     *            script is compatible with the scripts in php directory
     */
    public function __construct($user, $api) {
        if($api != self::API) {
            echo "This Zoph installation is not compatible with the Zoph executable you are running.\n";
            exit(self::EXIT_API_NOT_COMPATIBLE);
        }
        $this->user=$user;

        if(!$user->is_admin()) {
            echo "CLI_USER must be an admin user\n";
            exit(self::EXIT_CLI_USER_NOT_ADMIN);
        }
        $user->prefs->load();
        $lang=$user->load_language();
        $this->args=new arguments;
    }

    /**
     * Run the CLI
     */
    public function run() {
        $this->processFiles();
        switch(arguments::$command) {
        case "import":
            $vars=$this->args->getVars();
            if(!isset(settings::$importThumbs)) {
                settings::$importThumbs=true;
            }
            if(!isset(settings::$importExif)) {
                settings::$importExif=true;
            }
            if(!isset(settings::$importSize)) {
                settings::$importSize=true;
            }
            if(!isset(settings::$importAutoadd)) {
                settings::$importAutoadd=false;
            } else {
                $vars=$this->addNew();
            }
            if(is_array($this->files) && sizeof($this->files)>0) {
                if(!isset($vars["_dirpattern"])) {
                    $photos=array();
                    foreach($this->files as $file) {
                        $photo=new photo();
                        $photo->file["orig"]=$file;
                        $photos[]=$photo;
                    }
                } else {
                    $photos=$this->processDirpattern();
                }
                CliImport::photos($photos, $vars);
            } else {
                echo "Nothing to do, exiting\n";
                exit(self::EXIT_NO_FILES);
            }


            break;
        case "update":
            if(!isset(settings::$importThumbs)) {
                settings::$importThumbs=false;
            }
            if(!isset(settings::$importExif)) {
                settings::$importExif=false;
            }
            if(!isset(settings::$importSize)) {
                settings::$importSize=false;
            }
            if(is_array($this->photos) && sizeof($this->photos)>0) {
                $total=sizeof($this->photos);
                $cur=0;
                foreach($this->photos as $photo) {
                    cliimport::progress($cur, $total);
                    $cur++;
                    $photo->lookup();
                    $photo->setFields($this->args->getVars());
                    $photo->update();
                    $photo->updateRelations($this->args->getVars(), "_id");
                    if(settings::$importThumbs===true) {
                        $photo->thumbnail(true);
                    }
                    if(settings::$importExif===true) {
                        $photo->updateEXIF();
                    }
                    if(settings::$importSize===true) {
                        $photo->updateSize();
                    }
                    if(settings::$importHash===true) {
                        $photo->getHash();
                    }
                }
            } else {
                echo "Nothing to do, exiting\n";
                exit(self::EXIT_NO_FILES);
            }
            break;
        case "new":
            $this->addNew();
            break;
        case "config":
            $vars=$this->args->getVars();
            $name=$vars["_configitem"];
            $default=isset($vars["_configdefault"]);
            $item=conf::getItemByName($name);

            if($default) {
                $value=$item->getDefault();
            } else {
                $value=$vars["_configvalue"];
            }

            if(settings::$importVerbose > 0) {
                echo "Setting config \"$name\" to \"$value\""  . ( $default ? " (default)" : "" ) . "\n";
            }


            $item->setValue($value);
            $item->update();
 
            
            break;
        case "dumpconfig":
            $conf=conf::getAll();
            foreach ($conf as $name=>$item) {
                foreach ($item as $citem) {
                    if($citem instanceof confItemBool) {
                        $value=( $citem->getValue() ? "true": "false" );
                    } else {
                        $value=$citem->getValue();
                    }
                    echo $citem->getName() . ": " . $value . "\n";
                }
            }
            break;

        default:
            echo "Unknown command, please file a bug\n";
            exit(self::EXIT_UNKNOWN_ERROR);
        }

    }

    /**
     * Check list of files
     */
    private function processFiles() {
        $files=$this->args->getFiles();

        foreach($files as $filename) {
            try {
                if(arguments::$command=="import") {
                    
                    $file=new file($filename);
                    $file->check();

                    $mime=$file->getMime();
                    if($file->type=="directory" && settings::$importRecursive) {
                        $this->files=array_merge($this->files, file::getFromDir($file, true));
                    } else if($file->type!="image") {
                        throw new ImportFileNotImportableException("$file is not an image\n");
                    } else {
                        $this->files[]=$file;
                    }
                } else {
                    if(settings::$importUseids) {
                        $file=$filename;
                        if(is_numeric($file)) {
                            $this->photos[]=$this->lookupFileById($file);
                        } else if (preg_match("/^[0-9]+-[0-9]+$/", $file)) {
                            list($start, $end) = explode("-",$file);
                            foreach (range($start, $end) as $id) {
                                try {
                                    $this->photos[]=$this->lookupFileById($id);
                                } catch (ImportException $e) {
                                    echo $e->getMessage();
                                }
                             }
                        } else {
                            throw new ImportIdIsNotNumericException("$file is not numeric, but --useids is set.\n");
                        }
                    } else {
                        $this->photos[]=$this->lookupFile($filename);
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Looks up a photo by photo_id
     */
    private function lookupFileById($id) {
        $photo=new photo((int) $id);
        $count=$photo->lookup();
        if($count==1) {
            return $photo;
        } else if ($count==0) {
            throw new ImportFileNotFoundException("No photo with id $id was found\n");
        } else {
            throw new ImportMultipleMatchesException("Multiple photos with id $id were found. This is probably a bug");
        }
    }
        
    /**
     * Looks up a file by filename
     * @todo Maybe this should be moved into the file object?
     */
    private function lookupFile($file) {
        $filename=basename($file);
        $path=dirname($file);
        if($path==".") {
            // No path given
            //unset($path);
            $path="./";
        }

        if(substr($path,0,2)=="./") {
            // Path relative to the current dir given, change into absolute path
            $path="/" . cleanup_path(getcwd() . "/" . $path);
        }
        if($path[0]=="/") {
            // absolute path given

            $path="/" . cleanup_path($path) . "/";
            
            // check if path is in conf::get("path.images")
            if(substr($path, 0, strlen(conf::get("path.images")))!=conf::get("path.images")) {
                throw new ImportFileNotInPathException($file ." is not in the images path (" . conf::get("path.images") . "), skipping.\n");
            } else {
                $path=substr($path, strlen(conf::get("path.images")));
                if($path[0]=="/") {
                    // conf::get("path.images") didn't end in '/', let's cut it off
                    $path=substr($path, 1);
                }
            }
        } else {
            $path=cleanup_path($path);
        }
        $photos=photo::getByName($filename, $path);
        if(sizeof($photos)==0) {
            throw new ImportFileNotFoundException($file ." not found.\n");
        } else if (sizeof($photos)==1) {    
            return $photos[0];
        } else {
            throw new ImportMultipleMatchesException("Multiple files named " . $file ." found.\n");
        }
    }
    
    /**
     * Add albums, categories, places, people that should be added because of --new or --autoadd
     * if $vars is given, 
     */
    public function addNew() {
        $vars=$this->args->getVars();
        $newvars=array();
        $return_vars=array();

        foreach($vars as $var=>$array) {
            switch($var) {
            case "_new_album":
                $newvars["_album_id"]=array();
                foreach($array as $new) {
                    $album=new album();
                    $album->set("album", $new["name"]);
                    $album->set("parent_album_id", (int) $new["parent"]);
                    $album->insert();
                    $newvars["_album_id"][]=$album->getId();
                }
                break;
            case "_new_cat":
                $newvars["_category_id"]=array();
                foreach($array as $new) {
                    $cat=new category();
                    $cat->set("category", $new["name"]);
                    $cat->set("parent_category_id", (int) $new["parent"]);
                    $cat->insert();
                    $newvars["_category_id"][]=$cat->getId();
                }
                break;
            case "_new_place":
                foreach($array as $new) {
                    $place=new place();
                    $place->set("title", $new["name"]);
                    $place->set("parent_place_id", (int) $new["parent"]);
                    $place->insert();
                    $newvars["location_id"]=$place->getId();
                }
                break;
            case "_new_person":
                $newvars["_person_id"]=array();
                foreach($array as $new) {
                    $person=new person();
                    $person->setName($new);
                    $person->insert();
                    $newvars["_person_id"][]=$person->getId();
                }
                break;
            case "_new_photographer":
                foreach($array as $new) {
                    $person=new person();
                    $person->setName($new);
                    $person->insert();
                    $newvars["photographer_id"]=$person->getId();
                }
            default:
                $return_vars[$var]=$array;
            }
        }
        foreach($newvars as $name=>$array) {
            if(array_key_exists($name, $return_vars) && is_array($return_vars[$name])) {
                $return_vars[$name]=array_merge($return_vars[$name], $array);
            }
            $return_vars[$name]=$array;
        }
        return($return_vars);
    }

    /**
     * Process the --dirpattern setting
     */
    public function processDirpattern() {
        $vars=$this->args->getVars();

        $patt=str_split($vars["_dirpattern"]);

        $cur=getcwd();
        $curlen=strlen($cur);
        $files=array();
        foreach($this->files as $file) {
            if(substr($file, 0, $curlen) != $cur) {
                echo "Sorry, --dirpattern can only be used when importing files under the current dir\n";
                echo "i.e. do not use absolute paths or '../' when specifying --dirpattern.\n";
                die(self::EXIT_PATH_NOT_IN_CWD);
            }
            $filename=substr($file, $curlen + 1);
            $dirs=explode("/", $filename);
            array_pop($dirs);
            
            $photo=new photo();
            $photo->file["orig"]=$file;

            $counter=0;
            foreach($dirs as $dir) {
                if(isset($patt[$counter])) {
                    switch($patt[$counter]) {
                    case "a":
                        // album
                        $album=album::getByName($dir);
                        if($album[0] instanceof album) {
                            if(!is_array($photo->_album_id)) {
                                $photo->_album_id=array();
                            }
                            $photo->_album_id[]=$album[0]->getId();
                        } else {
                            echo "Album not found: " . $dir . "\n";
                            die(self::EXIT_ALBUM_NOT_FOUND);
                        }
                        break;
                    case "c":
                        // category
                        $cat=category::getByName($dir);
                        if($cat[0] instanceof category) {
                            if(!is_array($photo->_category_id)) {
                                $photo->_category_id=array();
                            }
                            $photo->_category_id[]=$cat[0]->getId();
                        } else {
                            echo "Category not found: " . $dir . "\n";
                            die(self::EXIT_CAT_NOT_FOUND);
                        }
                        break;
                    case "l":
                        // location
                        $place=place::getByName($dir);
                        if ($place[0] instanceof place) {
                           $photo->set("location_id", $place[0]->getId());
                        } else {
                            echo "Place not found: " . $dir . "\n";
                            die(self::EXIT_PLACE_NOT_FOUND);
                        }
                        break;
                    case "p":
                        // person
                        $person=person::getByName($dir);
                        if($person[0] instanceof person) {
                            if(!is_array($photo->_person_id)) {
                                $photo->_person_id=array();
                            }
                            $photo->_person_id[]=$person[0]->getId();
                        } else {
                            echo "Person not found: " . $dir . "\n";
                            die(self::EXIT_PERSON_NOT_FOUND);
                        }
                        break;
                    case "D":
                        // dir / path
                        $path=$photo->_path;
                        if(!empty($path)) {
                            $path .= "/";
                        }
                        $photo->_path=$path . $dir;
                        break;
                    case "P":
                        // photographer
                        $person=person::getByName($dir);
                        if($person[0] instanceof person) {
                            $photo->set("photographer_id", $person[0]->getId());
                        } else {
                            echo "Person not found: " . $dir . "\n";
                            die(self::EXIT_PERSON_NOT_FOUND);
                        }
                        break;
                    default:
                        // should never happen...
                        die(self::EXIT_UNKNOWN_ERROR);
                    }
                }
                $counter++;
            }
            $photos[]=$photo;
        }
        return $photos;
    }
    /**
     * Show help
     */
    private static function showHelp() {
        echo "zoph " . VERSION . "\n";
        echo <<<END
Usage: zoph [OPTIONS] [IMAGE ...]
OPTIONS:
    --instance "INSTANCE"

    --import
    --update
    --version
    --help

    --album "ALBUM"
    --category "CATEGORY"
    --photographer "FIRST_NAME LAST_NAME"
    --location "PLACE"
    --person "FIRST_NAME LAST_NAME"
    --field "FIELD=VALUE"

    --[no-]thumbs
    --[no-]exif
    --[no-]size
    --useids
    --move 
    --copy
    --[no-]dateddirs
    --[no-]hierarchical
    --path

END;
        exit(self::EXIT_NO_PROBLEM);
    }

    /**
     * Tells user which Zoph version is being used
     */
    private static function showVersion() {
        echo "Zoph v" . VERSION . ".\n";
        exit(self::EXIT_NO_PROBLEM);
    }
}
?>
