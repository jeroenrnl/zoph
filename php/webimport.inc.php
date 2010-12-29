<?php
/**
 * Class for importing via the webinterface, extends 'general' Import class.
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
 * This class holds all the functions for uploading and importing images
 * to Zoph via the web interface.
 */
class WebImport extends Import {

    private $upload_id;

    /**
     * Create object, used to track progress of upload
     * @return WebImport The created object
     * @param string generated upload_id
     */
    function __construct($upload_id) {
        $this->upload_id=$upload_id;
    }

    /**
     * Import photos
     *
     * Takes an array of files and an array of vars and imports them in Zoph
     * @param Array Files to be imported
     * @param  Array Vars to be applied to the photos.
     */
    public static function photos(Array $files, Array $vars) {
        settings::$importThumbs=false; // thumbnails have already been created, no need to repeat...
        settings::$importExif=true;
        settings::$importSize=true;
        parent::photos($files, $vars);
    }
    
    /**
     * Return a translated, textual error message from a PHP upload error
     *
     * @param int PHP upload error
     */
    public static function handleUploadErrors($error) {
        $errortext=translate("File upload failed") . "<br>";
        switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
            $errortext.=sprintf(translate("The uploaded file exceeds the upload_max_filesize directive (%s) in php.ini."), ini_get("upload_max_filesize"));
            $errortext.=" " . sprintf(translate("This may also be caused by the max_post_size (%s) in php.ini."), ini_get("max_post_size"));
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $errortext.=sprintf(translate("The uploaded file exceeds the MAX_UPLOAD setting in config.inc.php (%s)."), MAX_UPLOAD);
            break;
        case UPLOAD_ERR_PARTIAL:
            $errortext.=translate("The uploaded file was only partially uploaded.");
            break;
        case UPLOAD_ERR_NO_FILE:
            $errortext.=translate("No file was uploaded.");
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errortext.=translate("Missing a temporary folder.");
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errortext.=translate("Failed to write to disk");
            break;
        case UPLOAD_ERR_EXTENSION:
            $errortext.=translate("A PHP extension stopped the upload. Don't ask me why.");
            break;
        default:
            $errortext.=translate("An unknown file upload error occurred.");
        }
        return $errortext;
    }

    /**
     * Process uploaded file
     * 
     * Catches the uploaded file, runs some checks and moves it into the
     * upload directory.
     * @param array PHP _FILE var with data about the uploaded file
     */
    public static function processUpload($file) {
        $filename=$file["name"];
        $tmp_name=$file["tmp_name"];
        
        $error=$file["error"];

        if($error) {
            // should do some nicer printing to this error some time
            log::msg(self::handleUploadErrors($error), log::FATAL, log::IMPORT);
            return false;
        }

        $file=new file($tmp_name);
        $mime=$file->getMime();

        if(!$file->type) {
            log::msg("Illegal filetype: $mime", log::FATAL, log::IMPORT);
            return false;
        }

        $dir=realpath(IMAGE_DIR . "/" .IMPORT_DIR);
        if($dir === false) {
            log::msg(IMAGE_DIR . "/" .IMPORT_DIR . " does not exist, creating...", log::WARN, log::IMPORT);
            create_dir_recursive(IMAGE_DIR . "/" .IMPORT_DIR);
            $dir=realpath(IMAGE_DIR . "/" .IMPORT_DIR);
            if($dir === false) {
                log::msg(IMAGE_DIR . "/" .IMPORT_DIR . " does not exist, and I can not create it.", log::WARN, log::FATAL);
            }
        }

        $dest=$dir . "/" . basename($filename);
        if(is_writable($dir)) {
            if(!file_exists($dest)) {
                move_uploaded_file($tmp_name, $dest);
            } else {
                log::msg("A file named <b>" . $filename . 
                    "</b> already exists in <b>" . $dir . "</b>", log::FATAL, log::IMPORT);
            }
        } else {
            log::msg("Directory <b>" . $dir . "</b> is not writable", 
                log::FATAL, log::IMPORT);
            return false;
        }
        return true;
    }
    
    /**
     * Processes a file
     *
     * Depending on file type it will eithe launch a resize or an unpack
     * function.
     * This function is called from a javascript call
     * @param string MD5 hash of the file <b>name</b>.
     */
    public static function processFile($md5) {
        // continue when hitting fatal error.
        log::$stopOnFatal=false;

        $dir=IMAGE_DIR . "/" . IMPORT_DIR . "/";
        $file=file::getFromMD5($dir, $md5);
        
        if($file instanceof file) {
            $mime=$file->getMime();
            $type=$file->type;
        } else {
            $type="unknown (file not found)";
        }

        switch($type) {
        case "image":
            if($mime=="image/jpeg" && IMPORT_AUTOROTATE) {
                self::autorotate($file);
            }
            self::resizeImage($file);
            break;
        case "archive":
            return self::unpackArchive($file);
            break;
        case "xml":
            return self::XMLimport($file);
            break;
        default:
            log::msg("Unknown filetype " . $type .
                 " for file" . $file, log::FATAL, log::IMPORT);
            return false;
            break;
        }
    }
    /**
     * Automatically rotate images based on EXIF tag.
     * @param string filename
     */
    protected static function autorotate($file) {
        try {
            parent::autorotate($file);
        } catch (ImportAutorotException $e) {
            touch($file . ".zophignore");
            log::msg($e->getMessage(), log::FATAL, log::IMPORT);
            die;
        }
    }

    /**
     * Unpack archive of different types
     * *WARNING* this function is *not* safe to run on unchecked user-input
     * use processFile() as a wrapper for this function
     * @see processFile
     * @param string full path to file
     * @todo unpack_dir should be removed when done
     */
    private static function unpackArchive(file $file) { 
        $dir = IMAGE_DIR . "/" . IMPORT_DIR;
        $mime=$file->getMime();
        switch($mime) {
        case "application/zip":
            $extr = UNZIP_CMD;
            $msg = "UNZIP_CMD";
            break;
        case "application/x-tar":
            $extr = UNTAR_CMD;
            $msg = "UNTAR_CMD";
            break;
        case "application/x-gzip":
            $extr = UNGZ_CMD;
            $msg = "UNGZ_CMD";
            break;
        case "application/x-bzip2":
            $extr = UNBZ_CMD;
            $msg = "UNBZ_CMD";
            break;
        }
        if (!$extr || $extr == $msg) {
            log::msg("To be able to process an archive of type " . $mime . ", you need to set " . $msg . " in config.inc.php to a program that can unpack this file.", log::FATAL, log::IMPORT);
            touch($file . ".zophignore");
            return false;
        }
        $upload_id=uniqid("zoph_");
        $unpack_dir=$dir . "/" . $upload_id;
        $unpack_file=$unpack_dir . "/" . basename($file);
        ob_start();
            mkdir($unpack_dir);
            rename($file, $unpack_file);

            $cmd = "cd " . escapeshellarg($unpack_dir) . " && " . 
                $extr . " " .  escapeshellarg($unpack_file) . " 2>&1";
            system($cmd);
            if(file_exists($unpack_file)) {
                unlink($unpack_file);
            }
        $output=ob_end_clean();
        log::msg($output, log::NOTIFY, log::IMPORT);
        $files=file::getFromDir($unpack_dir, true);
        foreach($files as $import_file) {
            $type=$import_file->type;
            if($type == "image" or $type == "archive") {
                $import_file->setDestination($dir);
                try {
                    $import_file->move();
                } catch (fileException $e) {
                    echo $e->getMessage() . "<br>\n";
                }
            }
        }
    }
    
    /**
     * Resize an image before import
     *
     * @param string filename
     */
    private static function resizeImage($file) {
        log::msg("resizing" . $file, log::DEBUG, log::IMPORT);
        $photo = new photo();

        $photo->set("path", IMPORT_DIR);
        $photo->set("name", basename($file));
        
        ob_start();
            $dir=IMAGE_DIR . "/" . IMPORT_DIR;
            $thumb_dir=$dir. "/" . THUMB_PREFIX;
            $mid_dir=$dir . "/" . MID_PREFIX;
            if(!file_exists($thumb_dir)) {
                mkdir($thumb_dir);
            } else if (!is_dir($thumb_dir)) {
                log::msg("Cannot create " . $thumb_dir . ", file exists.", log::FATAL, log::IMPORT);
            }
            if(!file_exists($mid_dir)) {
                mkdir($mid_dir);
            } else if (!is_dir($mid_dir)) {
                log::msg("Cannot create " . $mid_dir . ", file exists.", log::FATAL, log::IMPORT);
            }
            try {
                $photo->thumbnail();
            } catch (Exception $e) {
                echo "Thumb could not be made: " . $e->getMessage();
                touch($file . ".zophignore");
            }
            log::msg("Thumb made succesfully.", log::DEBUG, log::IMPORT);
        $log=ob_get_contents();
        ob_end_clean();
        echo $log;
    }

    /**
     * Get XML indicating progress of a certain upload
     * This requires the APC PHP extension.
     * If it is not available, it will always return 0 / unknown
     */
    function get_xml() {
        $xml = new DOMDocument('1.0','UTF-8');
        $rootnode=$xml->createElement($this->xml_rootname());
        $node=$xml->createElement($this->xml_nodename());
            

        if(function_exists("apc_fetch")) {
            $progress=apc_fetch("upload_" . $this->upload_id);
            if($progress===false) {
                $progress['current']=0;
                $progress['total']=0;
                // probably something wrong with APC settings
                if(ini_get("apc.enabled")==false) {
                    $progress['filename']="Enable apc.enabled in PHP.ini";
                } else if(ini_get("apc.rfc1867")==false) {
                    $progress['filename']="Enable apc.rfc1867 in PHP.ini";
                }
            }     
        } else {
            // APC extension not available
            $progress['current']=0;
            $progress['total']=0;
            $progress['filename']="APC extension not available";
        }
        $id=$xml->createElement("id");
        $current=$xml->createElement("current");
        $total=$xml->createElement("total");
        $filename=$xml->createElement("filename");
        
        $id->appendChild($xml->createTextNode($this->upload_id));
        $current->appendChild($xml->createTextNode($progress['current']));
        $total->appendChild($xml->createTextNode($progress['total']));
        $filename->appendChild($xml->createTextNode($progress['filename']));

        $node->appendChild($id);
        $node->appendChild($current);
        $node->appendChild($total);
        $node->appendChild($filename);

        $rootnode->appendChild($node);
        $xml->appendChild($rootnode);
        return $xml->saveXML();
    }
    
    /**
     * XML Root element
     *
     * Returns the name of the root element of an XML-file for this
     * object.
     * @todo should be changed into a static const.
     */
    public function xml_rootname() {
        return "importprogress";
    }

    /**
     * XML Node name
     *
     * Returns the name of a node in an XML-file for this object.
     * @param:
     * @todo should be changed into a static const.
     */
    public function xml_nodename() {
        return "import";
    }

    /**
     * Generate an XML file with thumbs in the import dir
     */
    public static function getThumbsXML() {
        $xml=new DOMDocument('1.0','UTF-8');
        $root=$xml->createElement("files");

        $files = file::getFromDir(IMAGE_DIR . "/" . IMPORT_DIR);
        foreach ($files as $file) {
            unset($icon);
            unset($status);
            
            $md5=$file->getMD5();
           
            $type=$file->type;
            
            switch ($type) {
            case "image":
                $thumb=THUMB_PREFIX . "/" . THUMB_PREFIX . "_" . get_converted_image_name($file->getName());
                $mid=MID_PREFIX . "/" . MID_PREFIX . "_" . get_converted_image_name($file->getName());
                if(file_exists(IMAGE_DIR . "/" . IMPORT_DIR . "/" . $thumb) &&
                  file_exists(IMAGE_DIR . "/" . IMPORT_DIR . "/" . $mid)) {
                    $status="done";
                } else {
                    $icon="images/icons/" . ICONSET . "/pause.png";
                    $status="waiting";
                }
                break;
            case "archive":
                $icon="images/icons/" . ICONSET . "/archive.png";
                $status="waiting";
                break;
            case "xml":
                $icon="images/icons/" . ICONSET . "/tracks.png";
                $status="done";
                break;
            case "ignore":
                $icon="images/icons/" . ICONSET . "/error.png";
                $status="ignore";
                break;
            }

            $xmlfile=$xml->createElement("file");
            $xmlfile->setAttribute("name", $file->getName());
            $xmlfile->setAttribute("type",$type);
            $xmlmd5=$xml->createElement("md5", $md5);
            $xmlfile->appendChild($xmlmd5);
            if(!empty($icon)) {
                $xmlicon=$xml->createElement("icon", $icon);
                $xmlfile->appendChild($xmlicon);
            }
            if(!empty($status)) {
                $xmlstatus=$xml->createElement("status", $status);
                $xmlfile->appendChild($xmlstatus);
            }
            $root->appendChild($xmlfile);
        }
        $xml->appendChild($root);
        echo $xml->saveXML();
    }

    /**
     * Retry making of thumbnails
     *
     * This function reacts to a click on the "retry" link in the thumbnail
     * list on the import page. It looks up which file is referenced by the
     * supplied MD5 and deleted thumbnail, mid and 'ignore" files, this will
     * cause the webinterface to retry making thumbnail and midsize images
     *
     * @param string md5 hash of the filename
     */

    public static function retryFile($md5) {
        $dir=IMAGE_DIR . "/" . IMPORT_DIR;

        $file=file::getFromMD5($dir, $md5);
        // only delete "related files", not the referenced file.
        $file->delete(true, true);
    }
    
    /**
     * Delete a file
     *
     * Deletes a file referenced by the MD5 hash of the filename and all
     * related files, such as thumbnail, midsize images and "ignore" files.
     * @param string md5 hash of the filename
     */
    public static function deleteFile($md5) {
        $dir=IMAGE_DIR . "/" . IMPORT_DIR;

        $file=file::getFromMD5($dir, $md5);
        $file->delete(true);
    }
    /**
     * Get a file list from a list of MD5 hashes.
     *
     * Take a list of MD5 hashes (in $vars["_import_image"]) and return an 
     * array of @see file objects
     * @param Array $vars
     */
    public static function getFileList(Array $import) {
        $loaded=0;
        foreach($import as $md5) {
            $file=file::getFromMD5(IMAGE_DIR . "/" . IMPORT_DIR, $md5);
            if(!empty($file)) {
                $files[]=$file;
            }
        }
        if(is_array($files)) {
            return $files;
        } else {
            log::msg("No files specified", log::FATAL, log::IMPORT);
            return false;
        }
    }
}

?>
