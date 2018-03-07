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
 * @author Jeroen Roos
 * @package Zoph
 */

use conf\conf;

/**
 * This class takes care of individual files
 * For now, this is only used in the import module of Zoph
 * in the future the photo class will be split into a "file" and "photo"
 * part, which will make things more flexible.
 */
class file {

    /** @var string File name */
    private $name;
    /** @var string Path where the file is located */
    private $path;
    /** @var string type of file ("image", "archive", "ignore" ...) */
    public $type;
    /** @var string Destination filename when copied or moved */
    private $destName;
    /** @var string Destination path when copied or moved */
    private $destPath;
    /** @var bool Make a backup if the destination file exists */
    public $backup=false;

    /**
     * Create a new file object from a filename
     * @param string filename
     */
    public function __construct($filename) {
        if (substr($filename, 0, 1)!="/") {
            $filename=getcwd() . "/" . $filename;
        }

        if (is_link($filename)) {
            if (!@stat($filename)) {
                throw new fileSymlinkProblemException(
                    "There's something wrong with symlink $filename\n");
            }
        } else if (is_dir($filename) && !conf::get("import.cli.recursive")) {
            throw new fileDirectoryNotSupportedException($filename . " is a directory\n");
        }

        $this->name=basename($filename);
        $this->path=realpath(dirname($filename));
    }

    /**
     * Whether or not this file is a symlink
     * @param bool whether or not this is a symlink
     */
    public function isLink() {
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
        if ($this->isLink()) {
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
        foreach ($files as $file) {
            $f=realpath($file);

            log::msg($f . ": " . md5($f), log::DEBUG, log::IMPORT);
            if (md5($f) == $md5) {
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
    public function delete($thumbs=false, $thumbsOnly=false) {
        log::msg("Deleting " . $this, log::NOTIFY, log::IMPORT);
        if (!$thumbsOnly && file_exists($this)) {
            if (!is_dir($this) && is_writable($this)) {
                unlink($this);
            } else {
                log::msg(sprintf(translate("Could not delete %s."), $this),
                    log::ERROR, log::IMPORT);
                return false;
            }
        }
        if ($thumbs) {
            $dir=dirname($this);
            $file=basename($this);
            $midname=$dir . "/" . MID_PREFIX . "/" .
                MID_PREFIX . "_" . $file;
            $thumbname=$dir . "/" . THUMB_PREFIX . "/" .
                THUMB_PREFIX . "_" . $file;
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
        $this->destPath="/" . file::cleanupPath($path) . "/";
        $this->destName=basename($this->readlink());
    }

    /**
     * Makes checks if a file can be found and read
     */
    public function check() {
        if (!file_exists($this)) {
            throw new fileNotFoundException("File not found: $this\n");
        }
        if (!is_readable($this)) {
            throw new fileNotReadableException("Cannot read file: $this\n");
        }
        if (!conf::get("import.cli.copy") && !is_writable($this)) {
            throw new fileNotWritableException("Cannot move file: $this\n");
        }
    }

    /**
    * Makes checks to see if a file can be copied
    */
    public function checkCopy() {
        // First checks are the same...
        $this->check();
        if (!is_writable($this->destPath)) {
            throw new fileDirNotWritableException("Directory not writable: " .
              $this->destPath);
        }
        if (file_exists($this->destPath . $this->destName)) {
            if ($this->backup) {
                $backupname=$this->destName;
                $counter=1;
                while (file_exists($this->destPath . $backupname)) {
                    // Find the . in the filename
                    $pos=strrpos($this->destName, ".") ?: strlen($file);
                    $backupname=substr($this->destName, 0, $pos) . "_" . $counter . substr($this->destName, $pos);
                    $counter++;
                }
                rename($this->destPath . $this->destName, $this->destPath . $backupname);
            } else {
                throw new fileExistsException("File already exists: " .
                    $this->destPath . $this->destName);
            }
        }
        return true;
    }

    /**
     * Makes checks if a file can be moved
     */
    public function checkMove() {
        // First checks are the same...
        $this->checkCopy();
        if (!is_writable($this)) {
            throw new fileNotWritableException("File is not writable: " . $this);
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
        if ($this->isLink()) {
            // in case of a link, we copy the link destination and delete the link
            $copy=$this->readlink();
            $copy->setDestination($destPath);
            $newfile=$copy->copy();
            unlink($this);
            return $newfile;
        } else {
            if (rename($this, $dest)) {
                return new file($dest);
            } else {
                throw new fileMoveFailedException("Could not move $this to $dest");
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
        if (copy($this, $dest)) {
            return new File($dest);
        } else {
            throw new fileCopyFailedException("Could not copy $this to $dest");
        }
    }

    /**
     * Changes the permissions for a file
     */
    public function chmod($mode = null) {
        if ($mode===null) {
            $mode=octdec(conf::get("import.filemode"));
        }
        if (!chmod($this, $mode)) {
            log::msg("Could not change permissions for <b>" . $this . "</b>",
                LOG::ERROR, LOG::IMPORT);
        }
    }

    /**
     * Gets MIME type for this file
     */
    public function getMime() {
        $fileinfo=new finfo(FILEINFO_MIME, conf::get("path.magic"));
        $mime=explode(";", $fileinfo->file($this->readlink()));
        log::msg("<b>" . $this->readlink() . "</b>: " . $mime[0], log::DEBUG, log::IMPORT);
        $this->setFiletype($mime[0]);
        return $mime[0];
    }

    /**
     * Gets type of file for this file
     */
    private function setFiletype($mime) {
        switch ($mime) {
            case "image/jpeg":
            case "image/png":
            case "image/gif":
                $type="image";
                break;
            case "application/x-bzip2":
            case "application/x-gzip":
            case "application/x-tar":
            case "application/zip":
                $type="archive";
                break;
            case "application/xml":
                $type="xml";
                break;
            case "directory":
                $type="directory";
                break;
            default:
            $type=false;
        }
        $this->type=$type;
        return $type;
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
            if ($filename[0]!=".") {
                if (is_dir($dir . "/" . $filename)) {
                    if ($recursive) {
                        $return=array_merge($return, static::getFromDir($dir . "/" . $filename, true));
                    }
                } else if (is_null($search) || preg_match($search, $filename)) {
                    $file=new file($dir . "/" . $filename);
                    if (!file_exists($dir . "/" . $filename . ".zophignore")) {
                        $file->getMime();
                    } else {
                        $file->type = "ignore";
                    }
                    if ($file->type) {
                        $return[]=$file;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Cleans up a path, by removing all double slashes, "/./",
     * leading and trailing slashes.
     */
    public static function cleanupPath($path) {
        $search = array("/(\/+)/", "/(\/\.\/)/", "/(\/$)/", "/(^\/)/");
        $replace = array("/", "/", "", "");
        return preg_replace($search, $replace, $path);
    }

    /**
     * Create a directory
     * @param string directory to create
     * @return bool true when succesful
     * @throws fileDirCreationFailedException when creation fails
     */
    private static function createDir($directory) {
        if (!file_exists($directory)) {
            if (@mkdir($directory, octdec(conf::get("import.dirmode")))) {
                if (!defined("CLI") || conf::get("import.cli.verbose")>=1) {
                    log::msg(translate("Created directory") . ": $directory", log::NOTIFY, log::GENERAL);
                }
                return true;
            } else {
                throw new fileDirCreationFailedException(
                    translate("Could not create directory") . ": $directory<br>\n");
            }
        }
    }

    /**
     * Recursively create directory
     * checks if the parent dir of the dir to be created exists and if not so, tries to
     * create it first
     * @param string directory to create
     * @return bool true when succesful
     */
    public static function createDirRecursive($directory) {
        $directory="/" . static::cleanupPath($directory);

        if (!file_exists(dirname($directory))) {
            static::createDirRecursive(dirname($directory));
        }
        try {
            static::createDir($directory);
        } catch (fileDirCreationFailedException $e) {
                log::msg($e->getMessage(), log::FATAL, log::GENERAL);
        }
    }
}
?>
