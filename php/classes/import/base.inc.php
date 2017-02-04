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
namespace import;

use file;
use log;
use photo;
use settings;

use conf\conf;

use geo\track;

use DomDocument;


/**
 * This class holds the generalized functions importing images
 * to Zoph.
 *
 * @author Jeroen Roos
 * @package Zoph
 */
abstract class base {

    /**
     * Rotates a file based on the EXIF orientation flag
     *
     * Calls external program jhead for this.
     * @param string Filename
     */
    protected static function autorotate($file) {
        $cmd = "jhead -autorot " . escapeshellarg($file);
        exec($cmd, $output, $return);
        if ($return > 0) {
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
        $photos=array();

        $total=sizeof($files);
        $cur=0;

        if (isset($vars["_path"])) {
            $path=file::cleanupPath("/" . $vars["_path"] . "/");
            if (strpos($path, "..") !== false) {
                log::msg("Illegal characters in path", log::FATAL, log::IMPORT);
                die();
            }
        } else {
            $path="";
        }

        foreach ($files as $file) {
            static::progress($cur, $total);
            $cur++;

            if ($file instanceof photo) {
                $photo=$file;
                $file=$photo->file["orig"];
            } else if ($file instanceof file) {
                $photo=new photo();
            }

            $mime=$file->getMime();
            if (conf::get("import.cli.exif")===true && $mime=="image/jpeg") {
                $exif=process_exif($file);
                if ($exif) {
                    $photo->setFields($exif);
                }
            }
            if (isset($vars["rating"])) {
                $rating=$vars["rating"];
                if (!(is_numeric($rating) && (1 <= $rating) && ($rating <= 10))) {
                    unset($rating);
                }
                unset($vars["rating"]);
            }

            if (isset($vars["field"]) && is_array($vars["_field"])) {
                foreach ($vars["_field"] as $key => $field) {
                    $vars[$field]=$vars["field"][$key];
                }
                unset($vars["_field"]);
                unset($vars["field"]);
            }

            if ($vars) {
                $photo->setFields($vars);
            }

            if (strlen(trim($photo->get("date")))==0) {
                $date=date("Y-m-d", filemtime($file));
                log::msg("Photo has no date set, using filedate (" . $date . ").",
                    log::NOTIFY, log::IMPORT);
                $photo->set("date", $date);
            }

            if (strlen(trim($photo->get("time")))==0) {
                $time=date("H:i:s", filemtime($file));
                log::msg("Photo has no time set, using time from filedate (" . $time . ").",
                    log::NOTIFY, log::IMPORT);
                $photo->set("time", $time);
            }
            if (isset($photo->_path)) {
                $photo->set("path", $path . "/" . $photo->_path);
                unset($photo->_path);
            } else {
                $photo->set("path", $path);
            }

            try {
                $photo->import($file);
            } catch (FileException $e) {
                log::msg($e->getMessage(), log::FATAL);
            }

            if (conf::get("import.cli.thumbs")===true) {
                try {
                    $photo->thumbnail(false);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }

            if ($photo->insert()) {
                if (conf::get("import.cli.size")===true) {
                    $photo->updateSize();
                }
                $photo->update();
                $photo->updateRelations($vars, "_id");
                if (isset($rating)) {
                    $photo->rate($rating);
                }
                if (conf::get("import.cli.hash")===true) {
                    try {
                        $photo->getHash();
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                $photos[]=$photo;
            } else {
                echo translate("Insert failed.") . "<br>\n";
            }
        }
        return $photos;
    }

    /**
     * Import an XML file
     *
     * @param string MD5 hash of the filename to import
     *
     * This function tries to recognize the XML file by validating them against .xsd files
     * For now only GPX (1.0 and 1.1) files are recognized.
     */

    public static function XMLimport(file $file) {
        $xml=new DomDocument;
        $xml->Load($file);

        $schemas = array (
            "gpx 1.0" => "xml/gpx10.xsd",
            "gpx 1.1" => "xml/gpx11.xsd" );

        foreach ($schemas as $name => $schema) {
            if (@$xml->schemaValidate(settings::$php_loc . "/" . $schema)) {
                log::msg(basename($file) ." is a valid " . $name . " file", log::NOTIFY, log::IMPORT);
                $xmltype=$name;
            }
        }
        if (!isset($xmltype)) {
            throw ImportFileNotImportableException(basename($file) . " is not a known XML file.");
        } else {
            switch($name) {
            case "gpx 1.0":
            case "gpx 1.1":
                $track=track::getFromGPX($file);
                $track->insert();
                $file->delete();
                break;
            }
        }
    }

    /**
     * Progress bar
     * Does not display anything by default, but this function can be redefined
     * in a child class.
     *
     * @param int current
     * @param int total
     */
    public static function progress($cur, $total) {
        return 0;
    }

}

