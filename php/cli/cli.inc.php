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
    private $files;


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
        if(settings::$importUseids===false) {
            $this->processFiles();
        }
            

        switch(arguments::$command) {
        case "import":
            if(is_array($this->files)) {
                CliImport::photos($this->files, $this->args->getVars());
            } else {
                exit(self::EXIT_NO_FILES);
            }
            break;
        case "update":
        case "updatethumbs":
        case "updateexif":
            var_dump($this->files);
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
                if(substr($file,0,1)!="/") {
                    $file=getcwd() . "/" . $file;
                }
                if(!file_exists($file)) {
                    throw new Exception("File not found: $file\n");
                } 
                if(!is_readable($file)) {
                    throw new Exception("Cannot read file: $file\n");
                }
                if (!settings::$importCopy && !is_writable($file)) {
                    throw new Exception("Cannot write file: $file\n");
                }
                $this->files[]=$file;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
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
