<?php
/**
 * Class that holds all functions for importing and uploading
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
require_once("exif.inc.php");

/**
 * This class holds the generalized functions importing images
 * to Zoph.
 */
abstract class Import {
    
    /**
     * Rotates a file based on the EXIF orientation flag
     *
     * Calls external program jhead for this.
     * @param string Filename
     */

    protected static function autorotate($file) {
         $cmd = "jhead -autorot " . escapeshellarg($file);
         exec($cmd, $output, $return);
         if($return > 0) {
            $msg=implode($output, "<br>");
            throw new ImportAutorotException($msg);
         }
    }

    /**
     * Import photos
     *
     * Takes an array of files and an array of vars and imports them in Zoph
     * @param Array Files to be imported
     * @param  Array Vars to be applied to the photos.
     */
    public static function photos(Array $files, Array $vars) {
        $total=sizeof($files);
        $cur=0;

        if(isset($vars["_path"])) {
            $path=cleanup_path("/" . $vars["_path"] . "/");
            if(strpos($path, "..") !== false) {
                log::msg("Illegal characters in path", log::FATAL, log::IMPORT);
                die();
            }
        } else {
            $path="";
        }

        foreach($files as $file) {
            self::progress($cur, $total);
            $cur++;
            $mime=$file->getMime();
            $photo=new photo();
            if(settings::$importExif===true && $mime=="image/jpeg") {
                $exif=process_exif($file);
                if($exif) {
                    $photo->set_fields($exif);
                }
            }
            if ($vars) {
                $photo->set_fields($vars);
            }
            
            if(strlen(trim($photo->get("date")))==0) {
                $date=date("Y-m-d", filemtime($file));
                log::msg("Photo has no date set, using filedate (" . $date . ").", log::NOTIFY, log::IMPORT);
                $photo->set("date", $date);
            }

            if(strlen(trim($photo->get("time")))==0) {
                $time=date("H:i:s", filemtime($file));
                log::msg("Photo has no time set, using time from filedate (" . $time . ").", log::NOTIFY, log::IMPORT);
                $photo->set("time", $time);
            }

            $photo->set("path", $path);
            try {
                $photo->import($file);
            } catch (FileException $e) {
                echo $e->getMessage();
                die();
            }
           
            if(settings::$importThumbs===true) {
                try {
                    $photo->thumbnail(false);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            if ($photo->insert()) {
                if(settings::$importSize===true) {
                    $photo->updateSize();
                }
                $photo->update($vars);
                $photo->updateRelations($vars);

            } else {
                echo translate("Insert failed.") . "<br>\n";
            }
        }
    }
    /**
     * Displays a progressbar
     * 
     * This is a bit of a hack because PHP 5.2 and before do not support late static binding. For now, 
     * this  method figures out whether it's in the CLI or not and then call the cliImport method. 
     * This is a bit dirty, but it works
     *
     * @todo as soon as anything before PHP 5.3 is deprecated, this should be replaced by late 
     * static binding.
     */
    public static function progress($cur, $total) {
        if(defined("CLI")) {
            cliImport::progress($cur, $total);
        }
    }

}

class ImportException extends ZophException {}
class ImportAutorotException extends ImportException {}
class ImportFileNotInPathException extends ImportException {}
class ImportFileNotFoundException extends ImportException {}
class ImportIdIsNotNumericException extends ImportException {}
class ImportMultipleMatchesException extends ImportException {}
class ImportFileNotImportableException extends ImportException {}
?>
