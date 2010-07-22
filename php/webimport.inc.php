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
     * Creates the form with file selector, browse and upload button
     * @param int Number to separate concurrent uploads
     * @param string Combination of upload_id and upload_num
     */
    public static function browseForm($num, $upload_num) {
        $tpl=new template("uploadform", array(
            "action" => "import.php?upload=1",
            "onsubmit" => "zImport.startUpload(this, upload_id, num); return true",
            "num" => $num,
            "upload_num" => $upload_num));
        echo $tpl;
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
     * Handle an incoming upload
     *
     * This function will 'take' the incoming upload, fill the progressbar
     * to 100%, hand off the upload to the processUpload function and
     * finally launch a Javascript that will remove the iframe 10 seconds
     * after the upload finished.
     *
     * @param array PHP _FILE var with data about the uploaded file
     * @param string identifier of the upload in the browser window
     */
    public static function handleUpload($file, $upload_num) {
        
        self::processUpload($file);
        
        // show a 100% progressbar
        $tpl=new template("uploadprogressbar", array(
            "name" => $file["name"],
            "size" => get_human($file["size"]),
            "upload_num" => $upload_num,
            "complete" => 100,
            "width" => 300));
        echo $tpl;
?>
    <script type="text/javascript">
        // This removes the iframe after 10 seconds.
        frame=frameElement;
        frameparent=frame.parentNode;
        setTimeout('frameparent.removeChild(frame)', 10000);
    </script>

<?php

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
        $mime=get_mime($tmp_name);
        $type=get_filetype($mime);

        if(!$type) {
            log::msg("Illegal filetype: $mime", log::FATAL, log::IMPORT);
            return false;
        }

        $dir=realpath(IMAGE_DIR . "/" .IMPORT_DIR);
        $file=$dir . "/" . basename($filename);
        if(is_writable($dir)) {
            if(!file_exists($file)) {
                move_uploaded_file($tmp_name, $file);
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
        $mime=get_mime($file);
        $type=get_filetype($mime);
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
            log($e->getMessage(), log::FATAL, log::IMPORT);
            die;
        }
    }

    /**
     * Unpack archive of different types
     * *WARNING* this function is *not* safe to run on unchecked user-input
     * use processFile() as a wrapper for this function
     * @see processFile
     * @param string full path to file
     */
    private static function unpackArchive($file) { 
        $dir = IMAGE_DIR . "/" . IMPORT_DIR;
        $mime=get_mime($file);
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
        $files=self::getFiles($unpack_dir, true);
        foreach($files as $import_file) {
            if($import_file[0] == "image" or $import_file[0] == "archive") {
                $filename=basename($import_file[1]);
                rename($import_file[1], $dir . "/" . $filename);
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
            if($photo->thumbnail()) {
                log::msg("Thumb made succesfully.", log::DEBUG, log::IMPORT);
                return true;
            } else {
                log::msg("Thumb could not be made.", log::ERROR, log::IMPORT);
                touch($file . ".zophignore");
                return false;
            }
        $log=ob_end_clean();
        log::msg($log, log::WARNING, log::IMPORT);
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
        } else {
            // APC extension not available
            $progress['current']=0;
            $progress['total']=0;
            $progress['filename']="Unknown";
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
     * Returns the name of the root element of an XML-file for this
     * object.
     * @todo: should be changed into a static const.
     */
    public function xml_rootname() {
        return "importprogress";
    }

    /**
     * Returns the name of a node in an XML-file for this object.
     * @todo: should be changed into a static const.
     */
    public function xml_nodename() {
        return "import";
    }
    /**
     * Get files in a specific directory
     *
     * This function creates a list of files in a specific directory and
     * filters it on a given search string and filetypes.
     * @todo Maybe this belongs in the @see file class?
     * @param string The dir to search
     * @param bool Whether or not to descent into directories
     * @param string Search string
     */
    public static function getFiles($dir, $recursive = false, $search=null) {
        $files = scandir($dir);
        $return = array();

        foreach ($files as $file) {
            if($file{0}!=".") {
                if(is_dir($dir . "/" . $file) && $recursive) {
                    $return=array_merge($return,self::getFiles($dir . "/" . $file, true));
                } else if(is_null($search) or preg_match($search, $file)) {
                    if(!file_exists($dir . "/" . $file . ".zophignore")) {
                        $mime = get_mime($dir . "/" . $file);
                        $type = get_filetype($mime);
                    } else {
                        $type = "ignore";
                    }
                    if($type) {
                        $return[]=array($type, $dir . "/" . $file);
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Generate an XML file with thumbs in the import dir
     */
    public static function getThumbsXML() {
        $xml=new DOMDocument('1.0','UTF-8');
        $root=$xml->createElement("files");

        $files = self::getFiles(IMAGE_DIR . "/" . IMPORT_DIR);
        foreach ($files as $file) {
            unset($icon);
            unset($status);
            $name=basename($file[1]);
            $f=new file($file[1]);
            $md5=$f->getMD5();
           
            $type=$file[0];

            switch ($type) {
            case "image":
                $thumb=THUMB_PREFIX . "/" . THUMB_PREFIX . "_" . get_converted_image_name($name);
                $mid=MID_PREFIX . "/" . MID_PREFIX . "_" . get_converted_image_name($name);
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
            case "ignore":
                $icon="images/icons/" . ICONSET . "/error.png";
                $status="ignore";
                break;
            }

            $xmlfile=$xml->createElement("file");
            $xmlfile->setAttribute("name", $name);
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
     * @todo What is the _path lookup doing here?
     */
    public static function getFileList($vars) {
        $loaded=0;
        if(isset($vars["_path"])) {
            $path=cleanup_path("/" . $vars["_path"] . "/");
            if(strpos($path, "..")) {
                log::msg("Illegal characters in path", log::FATAL, log::IMPORT);
                die();
            }
        } else {
            $path="";
        }

        if(isset($vars["_import_image"])) {
            foreach($vars["_import_image"] as $md5) {
                $file=file::getFromMD5(IMAGE_DIR . "/" . IMPORT_DIR, $md5);
                if(!empty($file)) {
                   $files[]=$file;
                }
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
