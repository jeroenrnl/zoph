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
    const API=1;

    const EXIT_NO_PROBLEM       = 0;
    const EXIT_NO_ARGUMENTS     = 1;
    const EXIT_NO_FILES         = 2;
    
    const EXIT_IMAGE_NOT_FOUND  = 10;
    const EXIT_PERSON_NOT_FOUND = 20;
    const EXIT_PLACE_NOT_FOUND  = 30;
    const EXIT_ALBUM_NOT_FOUND  = 40;
    const EXIT_CAT_NOT_FOUND    = 50;

    // These two are also defined in /bin/zoph, as global constants.
    const EXIT_INI_NOT_FOUND    = 90;
    const EXIT_INSTANCE_NOT_FOUND    = 91;


    const EXIT_CLI_USER_NOT_ADMIN    = 95;

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
     * @param int API version of the executable script. This is used to check if the executable script is compatible with the scripts in php directory
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
            if(is_array($this->files) && sizeof($this->files)>0) {
                CliImport::photos($this->files, $this->args->getVars());
            } else {
                echo "Nothing to do, exiting\n";
                exit(self::EXIT_NO_FILES);
            }
            break;
        case "update":
            if(is_array($this->photos) && sizeof($this->photos)>0) {
                foreach($this->photos as $photo) {
                    $photo->update($this->args->getVars());
                    $photo->updateRelations($this->args->getVars());
                 }
            } else {
                echo "Nothing to do, exiting\n";
                exit(self::EXIT_NO_FILES);
            }

            break;
        case "updatethumbs":
        case "updateexif":
            var_dump($this->fileIds);
            break;
        case "version":
            echo self::showVersion();
            break;
        case "help":
            echo self::showHelp();
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
        foreach($files as $file) {
            try {
                if(arguments::$command=="import") {
                    if(substr($file,0,1)!="/") {
                            $file=getcwd() . "/" . $file;
                    }
                    if(!file_exists($file)) {
                        throw new ImportFileNotFoundException("File not found: $file\n");
                    } 
                    if(!is_readable($file)) {
                        throw new Exception("Cannot read file: $file\n");
                    }
                    if (!settings::$importCopy && !is_writable($file)) {
                        throw new Exception("Cannot write file: $file\n");
                    }
                    $this->files[]=$file;
                } else {
                    if(settings::$importUseids) {
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
                            // @todo: should be caught
                        }
                    } else {
                        $this->photos[]=$this->lookupFile($file);
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                // @todo: zophImport.pl had a --ignoreerror option, should return! Maybe as --stoponerror or something
                // 
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
        
    private function lookupFile($file) { 
        $filename=basename($file);
        $path=dirname($file);
        if($path==".") {
            // No path given
            unset($path);
        }

        if(substr($path,0,2)=="./") {
            // Path relative to the current dir given, change into absolute path
            $path="/" . cleanup_path(getcwd() . "/" . $path);
        }

        if($path[0]=="/") {
            // absolute path given

            $path="/" . cleanup_path($path);
            
            // check if path is in IMAGE_DIR
            if(substr($path, 0, strlen(IMAGE_DIR))!=IMAGE_DIR) {
                throw new ImportFileNotInPathException($file ." is not in IMAGE_DIR (" . IMAGE_DIR . "), skipping.\n");
                // @todo: should be caught
            } else {
                $path=substr($path, strlen(IMAGE_DIR));
                if($path[0]=="/") {
                    // IMAGE_DIR didn't end in '/', let's cut it off
                    $path=substr($path, 1);
                }
            }
        } else {
            $path=cleanup_path($path);
        }
        $photos=photo::getByName($filename, $path);
        if(sizeof($photos)==0) {
            throw new ImportFileNotFoundException($file ." not found.\n");
            // @todo: should be caught
        } else if (sizeof($photos)==1) {    
            return $photos[0];
        } else {
            throw new ImportMultipleMatchesException("Multiple files named " . $file ." found.\n");
            // @todo: should be caught
        }
    }

    /**
     * Show help
     * @todo should actually do something
     */
    private static function showHelp() {
        echo "Help is not available yet\n";
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
