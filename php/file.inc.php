<?php
/**
 * Class that takes care of individual files
 *
 * This file is part of Zoph.
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
 */

/**
 * This class takes care of individual files
 * For now, this is only used in the import module of Zoph
 * in the future the photo class will be split into a "file" and "photo"
 * part, which will make things more flexible.
 * @author Jeroen Roos
 * @package Zoph
 */
class file {

    /**
     * @var string File name
     */
    private $name;
    private $path;
    /**
     * @var string type of file ("image", "archive", "ignore" ...)
     */
    public $type;
    /**
     * @var string Used when file is going to be copied or moved
     */
    private $destination;

    public function __construct($filename) {
        if(substr($filename,0,1)!="/") {
            $filename=getcwd() . "/" . $filename;
        }

        if(is_link($filename)) {
            if(@!stat($filename)) {
                throw new FileSymlinkProblemException("There's something wrong with symlink $filename\n");
            }
        } else if (is_dir($filename) && !settings::$importRecursive) {
            throw new FileDirectoryNotSupportedException("$filename is a directory\n");
        } 

        $this->name=basename($filename);
        $this->path=realpath(dirname($filename));
    }

    /**
     * Whether or not this file is a symlink
     */
    public function is_link() {
        return is_link($this);
    }

    /**
     * Returns the link destination. Contrary to the PHP readlink() function, 
     * this function recurses through the links until it has located a real 
     * file. So, in case a link points to a link, which points to a link, 
     * which points to... I guess you got it.
     * Also, it will simply return a file object if the file is not a link.
     */
    public function readlink() {
        if($this->is_link()) {
            $file=new file(readlink($this));
            return $file->readlink();
        } else {
            return $this;
        }
    }

    /**
     * This function returns the name of a file, referenced by a directory
     * and an MD5 hash of the filename.
     */
    public static function getFromMD5($dir, $md5) {
        $files=glob($dir . "/*");
        foreach($files as $file) {
            $f=realpath($file);
            
            log::msg($f . ": " . md5($f), log::DEBUG, log::IMPORT);
            if(md5($f) == $md5) {
                return new file($f);
            }
        }
    }

    /**
     * Returns full path + filename
     */
    public function __toString() {
        return $this->getPath() . "/" . $this->getName();
    }
   
    /**
     * Returns filename
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns full path
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * When a symlink is copied or moved, the name changes
     * this function returns the new name
     */
    public function getDestName() {
        return $this->destName;
    }

    /** 
     * This generates an MD5 for a filename, to uniquely identify a file
     * that is not (yet) in the database and therefore has no db key.
     */
    public function getMD5() {
        return md5($this->path . "/" . $this->name);
    }

    /**
     * Deletes a file after doing some checks
     * @param bool Also delete related files, such as thumbnails
     * @param bool Do not delete the referenced file, only related files
     * @todo 'related' files really should be part of the photo object.
     * @see photo
     */
    public function delete($thumbs=false, $thumbs_only=false) {
        log::msg("Deleting " . $this, log::NOTIFY, log::IMPORT);
        if(!$thumbs_only) {
            if(file_exists($this)) {
                if(!is_dir($this) && is_writable($this)) {
                    unlink($this);
                } else {
                    log::msg(sprintf(translate("Could not delete %s."), $this), log::ERROR, log::IMPORT);
                    return false;
                }
            }
        }
        if($thumbs) {
            $dir=dirname($this);
            $file=basename($this);
            $conv=get_converted_image_name($file);
            $midname=$dir . "/" . MID_PREFIX . "/" . 
                MID_PREFIX . "_" . $conv;
            $thumbname=$dir . "/" . THUMB_PREFIX . "/" . 
                THUMB_PREFIX . "_" . $conv;
            $mid=new file($midname);
            $mid->delete();
            $thumb=new file($thumbname);
            $thumb->delete();
            $ignore=new file($this . ".zophignore");
            $ignore->delete();
        }
    }

    /**
     * Set the destination for copy or move operations;
     * @param string destination of the file
     */
    public function setDestination($path) {
        $this->destPath="/" . cleanup_path($path) . "/";
        $this->destName=basename($this->readlink());
    }
    
    /**
     * Makes checks if a file can be found and read
     */
    public function check() {
        if(!file_exists($this)) {
            throw new FileNotFoundException("File not found: $this\n");
        }
        if(!is_readable($this)) {
            throw new FileNotReadableException("Cannot read file: $this\n");
        }
        if (!settings::$importCopy && !is_writable($this)) {
            throw new FileNotWritableException("Cannot move file: $this\n");
        }
    }

    /**
    * Makes checks to see if a file can be copied
    */
    public function checkCopy() {
        // First checks are the same...
        $this->check();
        if(!is_writable($this->destPath)) {
            throw new FileDirNotWritableException("Directory not writable: " . 
              $this->destPath);
        }
        if(file_exists($this->destPath . $this->destName)) {
            throw new FileExistsException("File already exists: " . $this->destPath . $this->destName);
        }
        return true;
    }

    /**
     * Makes checks if a file can be moved
     */
    public function checkMove() {
        // First checks are the same...
        $this->checkCopy();
        if(!is_writable($this)) {
            throw new FileNotWritableException("File is not writable: " . $this);
        }
        return true;
    }

    /**
     * Moves a file
     */
    public function move() {
        $destPath=$this->destPath;
        $destName=$this->destName;
        $dest=$destPath . "/" . $destName;

        log::msg("Going to move $this to $dest", LOG::DEBUG, LOG::GENERAL);
        $this->checkMove();
        if($this->is_link()) {
            // in case of a link, we copy the link destination and delete the link
            $copy=$this->readlink();
            $copy->setDestination($destPath);
            $newfile=$copy->copy();
            unlink($this);
            return $newfile;
        } else {
            if(rename($this, $dest)) {
                return new file($dest);
            } else {
                throw new FileMoveFailedException("Could not move $this to $dest");
            }
        }
    }

    /**
     * Copies a file
     */
    public function copy() {
        $destPath=$this->destPath;
        $destName=$this->destName;
        $dest=$destPath . "/" . $destName;
        
        $this->checkCopy();
        if(copy($this, $dest)) {
            return new File($dest);
        } else {
            throw new FileCopyFailedException("Could not copy $this to $dest");
        }
    }

    /**
     * Changes the permissions for a file
     */
    public function chmod($mode = null) {
        if($mode===null) {
            if(!defined("FILE_MODE") || !is_numeric(FILE_MODE)) {
                define('FILE_MODE', 0644);
                log::msg("FILE_MODE is not set correctly in config.inc.php, using default (0644)", LOG::WARN, LOG::GENERAL);
            }
            $mode=FILE_MODE;
        }
        if(!chmod($this, FILE_MODE)) {
            log::msg("Could not change permissions for <b>" . $this . "</b>", LOG::ERROR, LOG::IMPORT);
        }
    }

    /**
     * Gets MIME type for this file
     */
    public function getMime() {
        $fileinfo=new finfo(FILEINFO_MIME, MAGIC_FILE);
        $mime=explode(";", $fileinfo->file($this->readlink()));
        log::msg("<b>" . $file . "</b>: " . $mime[0], log::DEBUG, log::IMPORT);
        $this->type=get_filetype($mime[0]);
        return $mime[0];
    }
    
    /**
     * Get files in a specific directory
     *
     * This function creates a list of files in a specific directory and
     * filters it on a given search string and filetypes.
     * @param string The dir to search
     * @param bool Whether or not to descent into directories
     * @param string Search string
     */
    public static function getFromDir($dir, $recursive = false, $search=null) {
        $files = scandir($dir);
        $return = array();

        foreach ($files as $filename) {
            if($filename[0]!=".") {
                if(is_dir($dir . "/" . $filename)) {
                    if($recursive) {
                        $return=array_merge($return,self::getFromDir($dir . "/" . $filename, true));
                    }
                } else if(is_null($search) or preg_match($search, $filename)) {
                    $file=new file($dir . "/" . $filename);
                    if(!file_exists($dir . "/" . $filename . ".zophignore")) {
                        $file->getMime();
                    } else {
                        $file->type = "ignore";
                    }
                    if($file->type) {
                        $return[]=$file;
                    }
                }
            }
        }
        return $return;
    }
}
?>
