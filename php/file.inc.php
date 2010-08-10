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
     * File name
     */
    private $name;
    private $path;

    private $destination;

    public function __construct($name) {
        $this->name=basename($name);
        $this->path=realpath(dirname($name));
    }
    
    public function __toString() {
        return $this->path . "/" . $this->name;
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
     * Set the destination for copy or move operations;
     * @param string destination of the file
     */
    public function setDestination($path) {
        $this->destination="/" . cleanup_path($path) . "/";
    }

    /**
     * Makes checks to see if a file can be copied
     */
    public function checkCopy() {
        $dest=$this->destination;
        if(!is_writable($dest)) {
            throw new FileDirNotWritableException("Directory not writable: " . $dest);
        }
        if(!file_exists($this)) {
            throw new FileSourceNotFoundException("File not found: " . $this);
        }
        if(file_exists($dest . $this->name)) {
            throw new FileExistsException("File already exists: " . $dest . $this->name);
        }
        if(!is_readable($this)) {
            throw new FileNotReadableException("Cannot read file: " . $this);
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

    public function move() {
        $dest=$this->destination;
        log::msg("Going to move $this to $dest", LOG::DEBUG, LOG::GENERAL);
        $this->checkMove();
        if(rename($this, $dest . "/" . $this->name)) {
            $this->path=realpath($dest);
        } else {
            throw new FileMoveFailedException("Could not move $this to $dest");
        }
        return true;
    }

    public function copy() {
        $dest=$this->destination;
        $this->checkCopy();
        if(copy($this, $dest . "/" . $this->name)) {
            return new File($dest . "/" . $this->name);
        } else {
            throw new FileCopyFailedException("Could not copy $this to $dest");
        }
    }

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
    
}

class FileException extends ZophException {}
class FileDirNotWritableException extends FileException {}
class FileSourceNotFoundException extends FileException {}
class FileExistsException extends FileException {}
class FileNotReadableException extends FileException {}
class FileNotWritableException extends FileException {}
class FileMoveFailedException extends FileException {}
class FileCopyFailedException extends FileException {}
