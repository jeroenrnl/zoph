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
        if(isset($vars["_album_id"])) {
            $albums=$vars["_album_id"];
        }
        if(isset($vars["_category_id"])) {
            $categories=$vars["_category_id"];
        }
        if(isset($vars["_person_id"])) {
            $people=$vars["_person_id"];
        }
        if(isset($vars["_path"])) {
            $path=$vars["_path"];
        }
      
        foreach($files as $file) {
            $photo=new photo();
            $exif=process_exif($file);
            if($exif) {
                $photo->set_fields($exif);
            }
            if ($vars) {
                $photo->set_fields($vars);
            }

            $photo->set("path", $path);
            $image_info= getimagesize($file);
            $width= $image_info[0];
            $height= $image_info[1];
            $size=filesize($file);
            try {
                $photo->import($file);
            } catch (FileException $e) {
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
                $photo->set("size", $size);
                $photo->set("width", $width);
                $photo->set("height", $height);
                $photo->update($vars);
                
                if(isset($albums)) {
                    foreach($albums as $album) {
                        $photo->add_to_album($album);
                    }
                }
                if(isset($categories)) {
                    foreach($categories as $cat) {
                        $photo->add_to_category($cat);
                    }
                }   
                if(isset($people)) {
                    $pos=1;
                    foreach($people as $person) {
                        $photo->add_to_person($person, $pos);
                        $pos++;
                    }
                }   
            } else {
                echo translate("Insert failed.") . "<br>\n";
            }
        }
    } 
}

class ImportException extends ZophException {}
class ImportAutorotException extends ImportException {}

?>
