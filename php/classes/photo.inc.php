<?php
/**
 * A class corresponding to the photos table.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */

use db\db;
use db\select;
use db\param;
use db\clause;
use db\selectHelper;

/**
 * A class corresponding to the photos table.
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class photo extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="photos";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("photo_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array();
    /** @var bool keep keys with insert. In most cases the keys are set by the
             db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="photo.php?photo_id=";

    /** @var photographer Photographer of this photo*/
    public $photographer;
    /** @var location Location where this photo was taken */
    public $location;

    /**
     * @var array For now this is only used during import, however, in the future, the photo object
     * will be split in a photo object, referencing one or more file objects.
     */
    public $file=array();

    /**
    * Display the image
    * @param string type of image to display mid, thumb or null for full-sized
    * @return array Return an array that contains:
    *               array headers: the headers
    *               string jpeg: the jpeg file
    * @todo only supports JPEG currently, should support more filetypes
    */
    public function display($type=null) {
        $header=array();
        $name = $this->get("name");
        $image_path = conf::get("path.images") . "/" . $this->get("path") . "/";

        if ($type) {
            $image_path .= $type . "/" . $type . "_";
        }
        $image_path .= $name;

        $mtime = filemtime($image_path);
        $filesize = filesize($image_path);
        $gmt_mtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        // we assume that the client generates proper RFC 822/1123 dates
        //   (should work for all modern browsers and proxy caches)
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime) {
              $header["http_status"]="HTTP/1.1 304 Not Modified";
              $jpeg=null;
        } else {
            $file=new file($image_path);
            $image_type=$file->getMime();
            if ($image_type) {
                $header["Content-Length"] = $filesize;
                $header["Content-Disposition"]="inline; filename=" . $name;
                $header["Last-Modified"]=$gmt_mtime;
                $header["Content-type"]=$image_type;
                $jpeg=file_get_contents($image_path);
            }
        }
        return array($header, $jpeg);

        /**
         * @todo error handling
         */
    }

    /**
     * Lookup a photo, considering access rights
     */
    public function lookup() {
        if (!$this->getId()) {
            return;
        }
        $qry = new select(array("p" => "photos"));

        $distinct=true;
        $qry->addFields(array("*"), $distinct);

        $where=new clause("p.photo_id=:photoid");
        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));

        $qry = selectHelper::expandQueryForUser($qry);

        $qry->where($where);

        $photo = $this->lookupFromSQL($qry);

        if ($photo) {
            $this->lookupPhotographer();
            $this->lookupLocation();
        }

        return $photo;
    }

    /**
     * Lookup a photo, ignoring access rights
     */
    public function lookupAll() {
        $qry = new select(array("p" => "photos"));
        $qry->where(new clause("p.photo_id=:photoid"));
        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));
        $photo = $this->lookupFromSQL($qry);
        return $photo;
    }


    /**
     * Lookup photographer of this photo
     */
    private function lookupPhotographer() {
        if ($this->get("photographer_id") > 0) {
            $this->photographer = new photographer($this->get("photographer_id"));
            $this->photographer->lookup();
        } else {
            $this->photographer=null;
        }
    }

    /**
     * Lookup location of this photo
     */
    private function lookupLocation() {
        if ($this->get("location_id") > 0) {
            $this->location = new place($this->get("location_id"));
            $this->location->lookup();
        } else {
            $this->location=null;
        }
    }

    /**
     * Delete this photo from database
     * does not delete the photo on disk
     */
    public function delete() {
        parent::delete(array(
            "photo_people",
            "photo_categories",
            "photo_albums",
            "photo_ratings",
            "photo_comments")
        );
    }

    /**
     * Update photo relations, such as albums, categories, etc.
     * @param array array of variables to update
     * @param string suffix for varnames
     */
    public function updateRelations(array $vars, $suffix = "") {

        $albums=album::getFromVars($vars, $suffix);
        $categories=category::getFromVars($vars, $suffix);
        $people=person::getFromVars($vars, $suffix);

        // Albums
        if (!empty($vars["_remove_album$suffix"])) {
            foreach ((array) $vars["_remove_album$suffix"] as $alb) {
                $this->removeFrom(new album($alb));
            }
        }

        if (isset($this->_album_id)) {
            $albums=array_merge($albums, $this->_album_id);
            unset($this->_album_id);
        }

        foreach ($albums as $album) {
            $this->addTo(new album($album));
        }

        // Categories
        if (!empty($vars["_remove_category$suffix"])) {
            foreach ((array) $vars["_remove_category$suffix"] as $cat) {
                $this->removeFrom(new category($cat));
            }
        }

        if (isset($this->_category_id)) {
            $categories=array_merge($categories, $this->_category_id);
            unset($this->_category_id);
        }

        foreach ($categories as $cat) {
            $this->addTo(new category($cat));
        }

        // People
        if (!empty($vars["_remove_person$suffix"])) {
            foreach ((array) $vars["_remove_person$suffix"] as $pers) {
                $this->removeFrom(new person($pers));
            }
        }

        if (isset($this->_person_id)) {
            $people=array_merge($people, $this->_person_id);
            unset($this->_person_id);
        }
        foreach ($people as $person) {
            $this->addTo(new person($person));
        }
    }

    /**
     * Updates the photo's dimensions and filesize
     */
    public function updateSize() {
        $file=$this->getFilePath();
        list($width, $height)=getimagesize($file);
        $size=filesize($file);
        $this->set("size", $size);
        $this->set("width", $width);
        $this->set("height", $height);
        $this->update();
    }

    /**
     * Rereads EXIF information from file and updates
     */
    public function updateEXIF() {
        $file=$this->getFilePath();
        $exif=process_exif ($file);
        if ($exif) {
            $this->setFields($exif);
            $this->update();
        }
    }

    /**
     * Gets last used position for people on a photo
     * @return int position
     */
    public function getLastPersonPos() {
        $qry=new select(array("pp" => "photo_people"));
        $qry->addFunction(array("pos" => "max(position)"));
        $qry->where(new clause("photo_id=:photoid"));
        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));
        $result=db::query($qry)->fetch(PDO::FETCH_ASSOC);
        return (int) $result["pos"];
    }

    /**
     * Add this photo to album, category, person or location
     * @param organizer album, category, person or location
     */
    public function addTo(organizer $org) {
        $org->addPhoto($this);
    }

    /**
     * Remove this photo from album, category, person or location
     * @param organizer album, category, person or location
     */
    public function removeFrom(organizer $org) {
        $org->removePhoto($this);
    }

    /**
     * Get a list of albums for this photo
     * @return array of albums
     */
    public function getAlbums() {
        $user=user::getCurrent();

        $qry=new select(array("a" => "albums"));
        $qry->join(array("pa" => "photo_albums"), "pa.album_id = a.album_id");
        $qry->addFields(array("album_id", "parent_album_id", "album"));

        $where=new clause("pa.photo_id=:photoid");

        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));
        $qry->addOrder("album");

        if (!$user->canSeeAllPhotos()) {
            $qry->join(array("gp" => "group_permissions"), "gp.album_id=a.album_id");
            $qry->join(array("gu" => "groups_users"), "gp.group_id=gu.group_id");
            $where->addAnd(new clause("gu.user_id=:userid"));
            $qry->addParam(new param(":userid", (int) $user->getId(), PDO::PARAM_INT));
            if ($user->canEditOrganizers()) {
                $subqry=new select(array("a" => "albums"));
                $subqry->addFields(array("album_id", "parent_album_id", "album"));
                $subqry->join(array("pa" => "photo_albums"), "pa.album_id = a.album_id");

                $subwhere=new clause("pa.photo_id=:subphotoid");
                $subqry->addParam(new param(":subphotoid", (int) $this->getId(), PDO::PARAM_INT));
                $subwhere->addAnd(new clause("a.createdby=:ownerid"));
                $subqry->addParam(new param(":ownerid", (int) $user->getId(), PDO::PARAM_INT));
                $subqry->where($subwhere);
                $qry->union($subqry);
            }
        }

        $qry->where($where);

        return album::getRecordsFromQuery($qry);
    }

    /**
     * Get a list of categories for this photo
     * @return array of categories
     */
    public function getCategories() {
        $qry=new select(array("c" => "categories"));
        $qry->join(array("pc" => "photo_categories"), "c.category_id = pc.category_id");
        $distinct=true;
        $qry->addFields(array("category_id"), $distinct);
        $qry->addFields(array("parent_category_id", "category"));

        $where=new clause("pc.photo_id=:photoid");

        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));
        $qry->addOrder("c.category");
        $qry->where($where);

        return category::getRecordsFromQuery($qry);
    }

    /**
     * Get a list of people on this photo
     * @return array of people
     */
    public function getPeople() {
        $qry=new select(array("p" => "people"));
        $qry->join(array("pp" => "photo_people"), "pp.person_id = p.person_id");
        $distinct=true;
        $qry->addFields(array("person_id"), $distinct);
        $qry->addFields(array("last_name", "first_name", "called"));

        $where=new clause("pp.photo_id=:photoid");

        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));
        $qry->addOrder("pp.position");
        $qry->where($where);

        return person::getRecordsFromQuery($qry);
    }

    /**
     * Get links to the people appearing on this photo
     * @return string
     */
    public function getPeopleLinks() {
        $people = $this->getPeople();
        $links=array();
        if ($people) {
            foreach ($people as $person) {
                $links[]=$person->getLink(0);
            }
        }
        return implode(", ", $links);
    }

    /**
     * Import a file into the database
     *
     * This function takes a file object and imports it inot the database as a new photo
     *
     * @param file The file to be imported
     */
    public function import(file $file) {
        $this->set("name", $file->getName());

        $newPath=$this->get("path") . "/";
        if (conf::get("import.dated")) {
            // This is not really validating the date, just making sure
            // no-one is playing tricks, such as setting the date to /etc/passwd or
            // something.
            $date=$this->get("date");
            if (!preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) {
                log::msg("Illegal date, using today's", log::ERROR, log::IMPORT);
                $date=date("Y-m-d");
            }

            if (conf::get("import.dated.hier")) {
                $newPath .= cleanup_path(str_replace("-", "/", $date));
            } else {
                $newPath .= cleanup_path(str_replace("-", ".", $date));
            }
        }
        $toPath="/" . cleanup_path(conf::get("path.images") . "/" . $newPath) . "/";

        $path=$file->getPath();
        create_dir_recursive($toPath . "/" . MID_PREFIX);
        create_dir_recursive($toPath . "/" . THUMB_PREFIX);

        if ($path ."/" != $toPath) {
            $file->setDestination($toPath);
            $files[]=$file;

            $newname=$file->getDestName();

            $midname=MID_PREFIX . "/" . MID_PREFIX . "_" . $newname;
            $thumbname=THUMB_PREFIX . "/" . THUMB_PREFIX . "_" . $newname;

            if (file_exists($path . "/". $thumbname)) {
                $thumb=new file($path . "/" . $thumbname);
                $thumb->setDestination($toPath . "/" . THUMB_PREFIX . "/");
                $files[]=$thumb;
            }
            if (file_exists($path . "/". $midname)) {
                $mid=new file($path . "/" . $midname);
                $mid->setDestination($toPath . "/" . MID_PREFIX . "/");
                $files[]=$mid;
            }

            try {
                foreach ($files as $file) {
                    if (conf::get("import.cli.copy")==false) {
                        $file->checkMove();
                    } else {
                        $file->checkCopy();
                    }
                }
            } catch (FileException $e) {
                echo $e->getMessage() . "\n";
                throw $e;
            }
            // We run this loop twice, because we only want to move/copy the
            // file if *all* files can be moved/copied.
            try {
                foreach ($files as $file) {
                    if (conf::get("import.cli.copy")==false) {
                        $new=$file->move();
                    } else {
                        $new=$file->copy();
                    }
                    $new->chmod();
                }
            } catch (FileException $e) {
                echo $e->getMessage() . "\n";
                throw $e;
            }
            $this->set("name", $newname);
        }
        // Update the db to the new path;
        $this->set("path", cleanup_path($newPath));
    }

    /**
     * Return the full path to the file on disk
     * @param string type of image to return (thumb, mid, or empty for full)
     * @return string full path.
     */
    public function getFilePath($type=null) {
        $image_path = conf::get("path.images") . DIRECTORY_SEPARATOR .
            $this->get("path") . DIRECTORY_SEPARATOR;

        if ($type==THUMB_PREFIX || $type==MID_PREFIX) {
            $image_path .= $type . DIRECTORY_SEPARATOR . $type . "_";
        }
        return $image_path . $this->get("name");
    }

    /**
     * Get an thumbnail image that links to this photo
     * @param string optional link instead of the default link to the photo page
     * @return block to display link
     */
    public function getThumbnailLink($href = null) {
        if (!$href) {
            $href = "photo.php?photo_id=" . (int) $this->getId();
        }
        return new block("link", array(
            "href" => $href,
            "link" => $this->getImageTag(THUMB_PREFIX),
            "target"    => ""
        ));
    }

    /**
     * Get a link to the fullsize version of this image
     * @param string What (text or image) to display
     * @return block to display link
     */
    public function getFullsizeLink($title) {
        $user=user::getCurrent();
        return new block("link", array(
            "href" => $this->getURL(),
            "link" => $title,
            "target" => ($user->prefs->get("fullsize_new_win") ? "_blank" : "")
        ));
    }

    /**
     * Get the URL to an image
     * @param string "mid" or "thumb"
     * @return string URL
     */
    public function getURL($type = null) {

        $url = "image.php?photo_id=" . (int) $this->getId();
        if ($type) {
            $url .= "&amp;type=" . $type;
        }

        return $url;
    }

    /**
     * Create an img tag for this photo
     * @param type type of image (thumb, mid or null for full)
     * @return block template block for image tag
     */
    public function getImageTag($type = null) {
        $this->lookup();

        $image_href = $this->getURL($type);

        $file=$this->getFilePath($type);

        list($width, $height, $filetype, $size)=getimagesize($file);

        $alt = e($this->get("title"));

        return new block("img", array(
            "src"   => $image_href,
            "class" => $type,
            "size"  => $size,
            "alt"   => $alt
        ));
    }

    /**
     * Stores the rating of a photo for a user
     * @param int rating
     */
    public function rate($rating) {
        rating::setRating((int) $rating, $this);
    }

    /**
     * Get average rating for this photo
     * @return float rating
     */
    public function getRating() {
        return rating::getAverage($this);
    }

    /**
     * Get rating for a specific user
     * @param user user
     * @return int rating
     */
    public function getRatingForUser(user $user) {
        $ratings=rating::getRatings($this, $user);
        $rating=array_pop($ratings);
        if ($rating instanceof rating) {
            return $rating->get("rating");
        }
    }

    /**
     * Get details about ratings
     */
    public function getRatingDetails() {
        return rating::getDetails($this);
    }

    /**
     * Get a GD image resource for this image
     */
    private function getImageResource() {
        $file = $this->getFilePath();
        $resource = null;
        $image_info = getimagesize($file);
        switch ($image_info[2]) {
        case IMAGETYPE_GIF:
            $resource = imagecreatefromgif ($file);
            break;
        case IMAGETYPE_JPEG:
            $resource = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $resource = imagecreatefrompng($file);
            break;
        default:
            break;
        }

        return $resource;
    }

    /**
     * Get the photographer for this photo
     */
    public function getPhotographer() {
        $this->lookup();
        return $this->photographer;
    }

    /**
     * Set photographer for this photo
     * @param photographer the photographer to assign to this photo
     */
    public function setPhotographer(photographer $pg) {
        $this->set("photographer_id", (int) $pg->getId());
        $this->lookupPhotographer();
        $this->update();
    }

    /**
     * Remove photographer
     */
    public function unsetPhotographer() {
        $this->set("photographer_id", 0);
        $this->update();
        $this->lookupPhotographer();
    }

    /**
     * Get the location for this photo
     */
    public function getLocation() {
        $this->lookup();
        return $this->location;
    }

    /**
     * Set the location for this photoa
     * @param place location to set
     */
    public function setLocation(place $loc) {
        $this->set("location_id", (int) $loc->getId());
        $this->update();
        $this->lookupLocation();
    }

    /**
     * Unset the location for this photo
     */
    public function unsetLocation() {
        $this->set("location_id", 0);
        $this->update();
        $this->lookupLocation();
    }

    /**
     * Create thumbsize and midsize image
     * @param bool force (re)create resized image even if it already exist
     */
    public function thumbnail($force=true) {
        $path=conf::get("path.images") . "/" . $this->get("path") . "/";

        $name=$this->get("name");
        $midname=MID_PREFIX . "/" . MID_PREFIX . "_" . $name;
        $thumbname=THUMB_PREFIX . "/" . THUMB_PREFIX . "_" . $name;

        if (!file_exists($path . $midname) || $force===true) {
            $this->createThumbnail(MID_PREFIX, MID_SIZE);
        }
        if (!file_exists($path . $thumbname) || $force===true) {
            $this->createThumbnail(THUMB_PREFIX, THUMB_SIZE);
        }
        return true;
    }

    /**
     * Create resized image
     * @param string prefix for newly created image
     * @param int size for largest size of width/height
     */
    private function createThumbnail($prefix, $size) {
        $img_src = $this->getImageResource();

        $image_info = getimagesize($this->getFilePath());
        $width = $image_info[0];
        $height = $image_info[1];

        if ($width >= $height) {
            $new_width = $size;
            $new_height = round(($new_width / $width) * $height);
        } else {
            $new_height = $size;
            $new_width = round(($new_height / $height) * $width);
        }

        $img_dst = imagecreatetruecolor($new_width, $new_height);
        flush();
        if (conf::get("import.resize")=="resize") {
            imagecopyresized($img_dst, $img_src, 0, 0, 0, 0,
                $new_width, $new_height, $width, $height);
        } else {
            imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0,
                $new_width, $new_height, $width, $height);
        }
        flush();
        $new_image = conf::get("path.images") . '/' . $this->get("path") . '/' . $prefix . '/' .
            $prefix . '_' .  $this->get("name");
        $dir=dirname($new_image);

        if (!is_writable($dir)) {
            throw new FileDirNotWritableException("Directory not writable: " . $dir);
        }

        if (!imagejpeg($img_dst, $new_image)) {
            throw new PhotoThumbCreationFailedException("Could not create " . $prefix . " image");
        }

        imagedestroy($img_dst);
        imagedestroy($img_src);
    }

    /**
     * Rotate image
     * @param int degrees (90, 180, 270)
     */
    public function rotate($deg) {
        if (!conf::get("rotate.enable") || !$this->get('name')) {
            return;
        }

        $dir = conf::get("path.images") . "/" . $this->get("path") . "/";
        $name = $this->get('name');

        $images[$dir . THUMB_PREFIX . '/' . THUMB_PREFIX . '_' .
            $name] =
            $dir . THUMB_PREFIX . '/rot_' . THUMB_PREFIX . '_' .
            $name;

        $images[$dir . MID_PREFIX . '/' . MID_PREFIX . '_' . $name] =
            $dir . MID_PREFIX . '/rot_' . MID_PREFIX . '_' . $name;

        $images[$dir . $name] = $dir . 'rot_' . $name;

        if (conf::get("rotate.backup")) {
            $backup_name = conf::get("rotate.backup.prefix") . $name;

            // file_exists() check from From Michael Hanke:
            // Once a rotation had occurred, the backup file won't be
            // overwritten by future rotations and the original file
            // is always preserved.
            if (!file_exists($dir . $backup_name)) {
                if (!@copy($dir . $name, $dir . $backup_name)) {
                    throw new FileCopyFailedException(
                        sprintf(translate("Could not copy %s to %s."), $name, $backup_name));
                }
            }
        }

        // make a system call to convert or jpegtran to do the rotation.
        while (list($file, $tmp_file) = each($images)) {
            if (!file_exists($file)) {
                throw new FileNotFoundException("Could not find " . $file);
            }
            switch (conf::get("rotate.command")) {
            case "jpegtran":
                $cmd = 'jpegtran -copy all -rotate ' .  escapeshellarg($deg) .
                    ' -outfile ' .  escapeshellarg($tmp_file) . ' ' .
                    escapeshellarg($file);
                break;
            case "convert":
            default:
                $cmd = 'convert -rotate ' . escapeshellarg($deg) . ' ' .
                    escapeshellarg($file) . ' ' . escapeshellarg($tmp_file);
            }

            $cmd .= ' 2>&1';

            $output = system($cmd);

            if ($output) { // error
                throw new ZophException(translate("An error occurred. ") . $output);
            }

            rename($tmp_file, $file);
        }

        // update the size and dimensions
        // (only if original was rotated)
        $this->update();
        $this->updateSize();
    }

    /**
     * Get an array of properties for this object, to display this info
     * @return array photo properties
     */
    public function getDisplayArray() {
        $date=$this->getReverseDate();

        $loclink="";
        if ($this->location instanceof place) {
            $loclink =new block("link", array(
                "href" => $this->location->getURL(),
                "link" => $this->location->getName(),
                "target" => ""
            ));
        }

        $pglink="";
        if ($this->photographer instanceof photographer) {
            $pglink =new block("link", array(
                "href" => $this->photographer->getURL(),
                "link" => $this->photographer->getName(),
                "target" => ""
            ));
        }

        return array(
            translate("title") => $this->get("title"),
            translate("location") => $loclink,
            translate("view") => $this->get("view"),
            translate("date") => create_date_link($date),
            translate("time") => $this->getTimeDetails(),
            translate("photographer") => $pglink
        );
    }

    /**
     * Get array of properties of this object, used to build mail message
     * @return array photo properties
     * @todo should probably be merged with getDisplayArray
     */
    public function getEmailArray() {
        return array(
            translate("title") => $this->get("title"),
            translate("location") => $this->location
                ? $this->location->get("title") : "",
            translate("view") => $this->get("view"),
            translate("date") => $this->get("date"),
            translate("time") => $this->get("time"),
            translate("photographer") => $this->photographer
                ? $this->photographer->getName() : "",
            translate("description") => $this->get("description")
        );
    }

    /**
     * Get array of (EXIF) camera data for this photo
     * @return array of EXIF data
     */
    public function getCameraDisplayArray() {
        return array(
            translate("camera make") => $this->get("camera_make"),
            translate("camera model") => $this->get("camera_model"),
            translate("flash used") => $this->get("flash_used"),
            translate("focal length") => $this->get("focal_length"),
            translate("exposure") => $this->get("exposure"),
            translate("aperture") => $this->get("aperture"),
            translate("compression") => $this->get("compression"),
            translate("iso equiv") => $this->get("iso_equiv"),
            translate("metering mode") => $this->get("metering_mode"),
            translate("focus distance") => $this->get("focus_dist"),
            translate("ccd width") => $this->get("ccd_width"),
            translate("comment") => $this->get("comment"));
    }

    /**
     * Get array of form fields to edit this photo
     * @return array of form fields
     */
    public function getEditArray() {
        return array(
            "Title" => create_text_input("title", $this->title),
            "Date" => create_text_input("date", $this->date_taken),
            "Photographer" => create_text_input("photographer",
                $this->photographer ? $this->photographer->getName() : ""),
            "Location" => create_text_input("location",
                $this->location ? $this->location->getName() : ""),
            "View" => create_text_input("view", $this->view),
            "Level" => create_text_input("level", $this->level, 4, 2));
    }

    /**
     * Get time this photo was taken, corrected with timezone information
     * @return string time
     */
    public function getTime() {
        $this->lookup();
        $loc=$this->location;
        if ($loc instanceof place) {
            $loc->lookup();
        }

        if ($loc && TimeZone::validate($loc->get("timezone"))) {
            $place_tz=new TimeZone($loc->get("timezone"));
        }
        if (TimeZone::validate(conf::get("date.tz"))) {
            $camera_tz=new TimeZone(conf::get("date.tz"));
        }

        if (!isset($place_tz) && isset($camera_tz)) {
            // Camera timezone is known, place timezone is not.
            $place_tz=$camera_tz;
        } else if (isset($place_tz) && !isset($camera_tz)) {
            // Place timezone is known, camera timezone is not.
            $camera_tz=$place_tz;
        } else if (!isset($place_tz) && !isset($camera_tz)) {
            $default_tz=new TimeZone(date_default_timezone_get());

            $place_tz=$default_tz;
            $camera_tz=$default_tz;
        }
        $place_time=$this->getCorrectedTime($camera_tz, $place_tz);

        return $place_time;
    }

    /**
     * Get date/time formatted as configured
     * @return array date, time
     */
    public function getFormattedDateTime() {
        $date_format=conf::get("date.format");
        $time_format=conf::get("date.timeformat");

        $place_time=$this->getTime();

        $date=$place_time->format($date_format);
        $time=$place_time->format($time_format);
        return array($date, $time);
    }

    /**
     * get time in UTC timezone
     * @return array date, time
     */
    public function getUTCtime() {
        $date_format=conf::get("date.format");
        $time_format=conf::get("date.timeformat");

        $default_tz=new TimeZone(date_default_timezone_get());

        $place_tz=new TimeZone("UTC");
        $camera_tz=$default_tz;

        if (TimeZone::validate(conf::get("date.tz"))) {
            $camera_tz=new TimeZone(conf::get("date.tz"));
        }

        $place_time=$this->getCorrectedTime($camera_tz, $place_tz);

        $date=$place_time->format($date_format);
        $time=$place_time->format($time_format);
        return array($date, $time);
    }

    /**
     * Returns the date in reverse, so it can be used for sorting
     */
    public function getReverseDate() {
        $date_format=("Y-m-d");

        $place_time=$this->getTime();

        $date=$place_time->format($date_format);
        return $date;
    }

    /**
     * Get corrected time, for given timezone.
     * Converts the time stored in the database from the 'camera timzone' to the place timezone
     * @param TimeZone camera timezone, the timezone the camera was set to when this photo was taken
     * @param TimeZone place timezone, the timezone of the location where this photo was taken
     * @return Time calculated time
     */
    private function getCorrectedTime(TimeZone $camera_tz, TimeZone $place_tz) {
        $camera_time=new Time(
            $this->get("date") . " " .
            $this->get("time"),
            $camera_tz);
        $place_time=$camera_time;
        $place_time->setTimezone($place_tz);
        $corr=$this->get("time_corr");
        if ($corr) {
            $place_time->modify($corr . " minutes");
        }

        return $place_time;
    }

    /**
     * Get an overview of the time details.
     * Shows the time of this photo and the timezones it uses
     * @return block template block.
     */
    private function getTimeDetails() {
        $tz=null;
        if (TimeZone::validate(conf::get("date.tz"))) {
            $tz=conf::get("date.tz");
        }

        $this->lookup();
        $place=$this->location;
        $place_tz=null;
        $location=null;
        if (isset($place)) {
            $place_tz=$place->get("timezone");
            $location=$place->get("title");
        }

        $datetime=$this->getFormattedDateTime();

        $tpl=new block("time_details", array(
            "photo_date" => $this->get("date"),
            "photo_time" => $this->get("time"),
            "camera_tz" => $tz,
            "corr" => $this->get("time_corr"),
            "location" => $location,
            "loc_tz" => $place_tz,
            "calc_date" => $datetime[0],
            "calc_time" => $datetime[1]
        ));
        return $tpl;
    }

    /**
     * Get comments for this photo
     * @return array of comments
     */
    public function getComments() {
        $qry=new select(array("pcom" => "photo_comments"));
        $distinct=true;
        $qry->addFields(array("comment_id"), $distinct);

        $where=new clause("pcom.photo_id=:photoid");

        $qry->addParam(new param(":photoid", (int) $this->getId(), PDO::PARAM_INT));
        $qry->where($where);

        return comment::getRecordsFromQuery($qry);
    }

    /**
     * Get Related photos
     * @return array related photos
     */
    public function getRelated() {
        $user=user::getCurrent();

        $allrelated=photoRelation::getRelated($this);

        if ($user->canSeeAllPhotos()) {
            return $allrelated;
        } else {
            $related=array();
            foreach ($allrelated as $photo) {
                if ($user->getPhotoPermissions($photo)) {
                    $related[]=$photo;
                }
            }
            return $related;
        }
    }

    /**
     * Get description for a specific related photo
     * @param photo photo to get relation for
     * @return string description
     */
    public function getRelationDesc(photo $photo) {
        return photoRelation::getDescForPhotos($this, $photo);
    }
    /**
     * Returns full EXIF information in a definitionlist
     * @return string HTML
     * @todo contains lots of HTML
     * @todo is a mess
     */
    public function exifToHTML() {
        if (exif_imagetype($this->getFilePath())==IMAGETYPE_JPEG) {
            $exif=read_exif_data($this->getFilePath());
            if ($exif) {
                $return="<dl class='allexif'>\n";

                foreach ($exif as $key => $value) {
                    if (!is_array($value)) {
                        $return .="    <dt>$key</dt>\n" .
                                  "    <dd>" .
                                          preg_replace("/[^[:print:]]/", "", $value) .
                                  "    </dd>\n";
                    } else {
                        $return .="    <dt>$key</dt>\n" .
                                  "    <dd>\n" .
                                  "        <dl>\n";
                        foreach ($value as $subkey => $subval) {
                            $return .= "     <dt>$subkey</dt>\n" .
                                       "     <dd>" .
                                               preg_replace("/[^[:print:]]/", "", $subval) .
                                       "     </dd>\n";
                        }
                        $return .= "         </dl>\n" .
                                   "    </dd>\n";
                    }
                }
                $return .= "</dl><br>";
            } else {
                $return=false;
            }
        } else {
            $return=false;
        }
        return $return;
    }

    /**
     * Get a short overview of this photo.
     * Used in popup-boxes on the map
     * @return string HTML
     * @todo contains HTML
     */
    public function getQuicklook() {
        $title=e($this->get("title"));
        $file=$this->get("name");

        if ($title) {
            $html="<h2>" . e($title) . "<\/h2><p>" . e($file) . "<\/p>";
        } else {
            $html="<h2>" . e($file) . "<\/h2>";
        }
        $html.=$this->getThumbnailLink()->toStringNoEnter() .
          "<p><small>" .
          $this->get("date") . " " . $this->get("time") . "<br>";
        if ($this->photographer) {
            $html.=translate("by", 0) . " " . $this->photographer->getLink(1) . "<br>";
        }
        $html.="<\/small><\/p>";
        return $html;
    }

    /**
     * Get Marker to be placed on map
     * @param string icon to be used.
     * @return marker instance of marker class
     */
    public function getMarker($icon="geo-photo") {
        $marker=map::getMarkerFromObj($this, $icon);
        if (!$marker instanceof marker) {
            $loc=$this->location;
            if ($loc instanceof place) {
                return $loc->getMarker();
            }
        } else {
            return $marker;
        }
    }

    /**
     * Get photos taken near this photo
     * @param int distance in km or miles
     * @param int limit maxiumum number of photos to return
     * @param string entity (km or miles)
     */
    public function getNear($distance, $limit=100, $entity="km") {
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        if ($lat && $lon) {
            return static::getPhotosNear(
                (float) $lat,
                (float) $lon,
                (float) $distance,
                (int) $limit,
                $entity
            );
        }
    }

    /**
     * Get photos taken near a lat/lon location
     * @param float latitude
     * @param float longitude
     * @param int distance
     * @param int limit maxiumum number of photos to return
     * @param string entity (km or miles)
     */
    public static function getPhotosNear($lat, $lon, $distance,
            $limit, $entity="km") {

        // If lat and lon are not set, don't bother trying to find
        // near photos
        if ($lat && $lon) {
            if ($entity=="miles") {
                $distance=(float) $distance * 1.609344;
            }
            $qry=new select(array("p" => "photos"));
            $qry->addFields(array("photo_id"));
            $qry->addFunction(array("distance" => "(6371 * acos(" .
                "cos(radians(:lat)) * cos(radians(lat)) * cos(radians(lon) - " .
                "radians(:lon)) + sin(radians(:lat2)) * sin(radians(lat))))"));
            $qry->having(new clause("distance <= :dist"));


            $qry->addParam(new param(":lat", (float) $lat, PDO::PARAM_STR));
            $qry->addParam(new param(":lat2", (float) $lat, PDO::PARAM_STR));
            $qry->addParam(new param(":lon", (float) $lon, PDO::PARAM_STR));
            $qry->addParam(new param(":dist", (float) $distance, PDO::PARAM_STR));

            if ($limit) {
                $qry->addLimit((int) $limit);
            }

            $qry->addOrder("distance");

            return static::getRecordsFromQuery($qry);
        } else {
            return null;
        }
    }

    /**
     * Get photos from filename
     * @param string filename
     * @param string path
     * @return array photo(s)
     */
    public static function getByName($file, $path=null) {
        $qry=new select(array("p" => "photos"));
        $qry->addFields(array("photo_id"));

        $where=new clause("name = :file");
        $qry->addParam(new param(":file", $file, PDO::PARAM_STR));

        if (!empty($path)) {
            $where->addAnd(new clause("path = :path"));
            $qry->addParam(new param(":path", $path, PDO::PARAM_STR));
        }

        $qry->where($where);

        return static::getRecordsFromQuery($qry);
    }

    /**
     * Calculate SHA1 hash for a file
     * @return string SHA1 hash
     */
    private function getHashFromFile() {
        $this->lookupAll();
        $file=$this->getFilePath();
        if (file_exists($file)) {
            return sha1_file($file);
        } else {
            throw new FileNotFoundException("File not found:" . $file);
        }
    }

    /**
     * Get hash for photo.
     * Returns the hash for a photo, either the file hash, or a salted hash that
     * can be used to share photos
     * @param string type file, full or mid
     * @return string hash
     */
    public function getHash($type="file") {
        $hash=$this->get("hash");
        if (empty($hash)) {
            try {
                $hash=$this->getHashFromFile();
                $this->set("hash", $hash);
                $this->update();
            } catch (Exception $e) {
                log::msg($e->getMessage(), log::ERROR, log::IMG);
            }
        }
        switch ($type) {
        case "file":
            $return=$hash;
            break;
        case "full":
            $return=sha1(conf::get("share.salt.full") . $hash);
            break;
        case "mid":
            $return=sha1(conf::get("share.salt.mid") . $hash);
            break;
        default:
            die("Unsupported hash type");
            break;
        }
        return $return;
    }

    /**
     * Set photo's lat/lon from a point object
     * @param point
     */
    public function setLatLon(point $point) {
        $this->set("lat", $point->get("lat"));
        $this->set("lon", $point->get("lon"));
    }

    /**
     * Try to determine the lat/lon position this photo was taken from one or all tracks;
     * @param track track to use or null to use all tracks
     * @param int maximum time the time can be off
     * @param bool Whether to interpolate between 2 found times/positions
     * @param int Interpolation max_distance: what is the maximum distance between two
     *            points to still interpolate
     * @param string km / miles entity in which max_distance is measured
     * @param int Interpolation maxtime Maximum time between to point to still interpolate
     */
    public function getLatLon(track $track=null, $maxtime=300, $interpolate=true,
            $int_maxdist=5, $entity="km", $int_maxtime=600) {

        date_default_timezone_set("UTC");
        $datetime=$this->getUTCTime();
        $utc=strtotime($datetime[0] . " " . $datetime[1]);

        $qry=new select(array("pt" => "point"));

        $where=new clause("datetime > :mintime");
        $where->addAnd(new clause("datetime < :maxtime"));

        $qry->addParam(new param(":mintime", date("Y-m-d H:i:s", $utc - $maxtime), PDO::PARAM_STR));
        $qry->addParam(new param(":maxtime", date("Y-m-d H:i:s", $utc + $maxtime), PDO::PARAM_STR));
        $qry->addParam(new param(":utc", date("Y-m-d H:i:s", $utc), PDO::PARAM_STR));

        if ($track) {
            $where->addAnd(new clause("track_id=:trackid"));
            $qry->addParam(new param(":trackid", (int) $track->getId(), PDO::PARAM_INT));
        }

        $qry->addOrder("abs(timediff(datetime, :utc)) ASC");
        $qry->addLimit(1);

        $qry->where($where);

        $points=point::getRecordsFromQuery($qry);
        if (sizeof($points) > 0 && $points[0] instanceof point) {
            $point=$points[0];
            $pointtime=strtotime($point->get("datetime"));
        } else {
            // can't get a point, don't bother trying to interpolate.
            $interpolate=false;
            $point=null;
        }

        if ($interpolate && ($pointtime != $utc)) {
            if ($utc>$pointtime) {
                $p1=$point;
                $p2=$point->getNext();
            } else {
                $p1=$point->getPrev();
                $p2=$point;
            }
            if ($p1 instanceof point && $p2 instanceof point) {
                $p3=point::interpolate($p1, $p2, $utc, $int_maxdist, $entity, $int_maxtime);
                if ($p3 instanceof point) {
                    $point=$p3;
                }
            }
        }
        return $point;
    }

    /**
     * Takes an array of photos and returns a subset
     *
     * @param array photos to return a subset from
     * @param array Array should contain first and/or last and/or random to determine
     *                           which subset(s)
     * @param int count Number of each to return
     * @return array subset of photos
     */
    public static function getSubset(array $photos, array $subset, $count) {
        $first=array();
        $last=array();
        $random=array();
        $begin=0;
        $end=null;

        $max=count($photos);

        if ($count>$max) {
            $count=$max;
        }

        if (in_array("first", $subset)) {
            $first=array_slice($photos, 0, $count);
            $max=$max-$count;
            $begin=$count;
        }
        if (in_array("last", $subset)) {
            $last=array_slice($photos, -$count);
            $max=$max-$count;
            $end=-$count;
        }

        if (in_array("random", $subset) && ($max > 0)) {
            $center=array_slice($photos, $begin, $end);

            $max=count($center);

            if ($max!=0) {
                if ($count>$max) {
                    $count=$max;
                }
                $random_keys=(array) array_rand($center, $count);
                foreach ($random_keys as $key) {
                    $random[]=$center[$key];
                }
            }
        }
        $subset=array_merge($first, $random, $last);

        // remove duplicates due to overlap:
        $clean_subset=array();
        foreach ($subset as $photo) {
            $clean_subset[$photo->get("photo_id")]=$photo;
        }

        return $clean_subset;
    }



    /**
     * Take an array of photos and remove photos with no valid timezone
     *
     * This function is needed for geotagging: for photos without a valid
     * timezone it is not possible to determine the UTC time, needed for geotagging.
     * @param array Array of photos
     * @return array Array of photos with a valid timezone
     */
    public static function removePhotosWithNoValidTZ(array $photos) {

        $gphotos=array();
        log::msg("Number of photos before valid timezone check: " .
            count($photos), log::DEBUG, log::GEOTAG);

        foreach ($photos as $photo) {
            $photo->lookup();
            $loc=$photo->location;
            if (get_class($loc)=="place") {
                $tz=$loc->get("timezone");
                if (TimeZone::validate($tz)) {
                    $gphotos[]=$photo;
                }
            }
        }
        log::msg("Number of photos after valid timezone check: " . count($gphotos),
            log::DEBUG, log::GEOTAG);
        return $gphotos;
    }

    /**
     * Take an array of photos and remove photos that already have lat/lon
     * information set.
     *
     * This function is needed for geotagging, so photos that have lat/lon
     * manually set will not be overwritten
     * @param array Array of photos
     * @return array Array of photos with no lat/lon info
     */
    public static function removePhotosWithLatLon($photos) {
        $gphotos=array();
        log::msg("Number of photos before overwrite check: " . count($photos),
            log::DEBUG, log::GEOTAG);
        foreach ($photos as $photo) {
            $photo->lookup();
            if (!($photo->get("lat") or $photo->get("lon"))) {
                $gphotos[]=$photo;
            }
        }
        log::msg("Number of photos after overwrite check: " . count($gphotos),
            log::DEBUG, log::GEOTAG);
        return $gphotos;
    }

    /**
     * Find a photo from a SHA1-hashed string
     * @param string hash
     * @param type of hash: file = filehash, full/mid salted has for sharing of photos
     * @return photo found photo
     */

    public static function getFromHash($hash, $type="file") {

        $qry=new select(array("p" => "photos"));

        if (!preg_match("/^[A-Za-z0-9]+$/", $hash)) {
            die("Illegal characters in hash");
        }
        switch ($type) {
        case "file":
            $where=new clause("hash=:hash");
            break;
        case "full":
            $qry->addParam(new param(":salt", conf::get("share.salt.full"), PDO::PARAM_STR));
            $where=new clause("sha1(CONCAT(:salt, hash))=:hash");
            break;
        case "mid":
            $qry->addParam(new param(":salt", conf::get("share.salt.mid"), PDO::PARAM_STR));
            $where=new clause("sha1(CONCAT(:salt, hash))=:hash");
            break;
        default:
            die("Unsupported hash type");
            break;
        }
        $qry->addParam(new param(":hash", $hash, PDO::PARAM_STR));
        $qry->where($where);

        $photos=static::getRecordsFromQuery($qry);
        if (is_array($photos) && sizeof($photos) > 0) {
            return $photos[0];
        } else {
            throw new PhotoNotFoundException("Could not find photo from hash");
        }
    }

    /**
     * Create a list of fields that can be used to sort photos on
     * @return array list of fields
     */
    public static function getFields() {
        return array(
            "" => "",
            "date" => "date",
            "time" => "time",
            "timestamp" => "timestamp",
            "name" => "file name",
            "path" => "path",
            "title" => "title",
            "view" => "view",
            "description" => "description",
            "width" => "width",
            "height" => "height",
            "size" => "size",
            "aperture" => "aperture",
            "camera_make" => "camera make",
            "camera_model" => "camera model",
            "compression" => "compression",
            "exposure" => "exposure",
            "flash_used" => "flash used",
            "focal_length" => "focal length",
            "iso_equiv" => "iso equiv",
            "metering_mode" => "metering mode"
        );
    }

    /**
     * Create a list of fields that can be specified during import
     * @return array list of fields
     */
    public static function getImportFields() {
        return array(
            "" => "",
            "time" => "time",
            "timestamp" => "timestamp",
            "aperture" => "aperture",
            "camera_make" => "camera make",
            "camera_model" => "camera model",
            "compression" => "compression",
            "exposure" => "exposure",
            "flash_used" => "flash used",
            "focal_length" => "focal length",
            "iso_equiv" => "iso equiv",
            "metering_mode" => "metering mode",
            "mapzoom" => "mapzoom"
        );
    }

    /**
     * Get accumulated disk size for all photos, as used on the info page
     * @return int size in bytes
     */
    public static function getTotalSize() {
        $qry=new select(array("p" => "photos"));
        $qry->addFunction(array("total" => "sum(size)"));
        return $qry->getCount();
    }

    /**
     * Get filesize for a set of photos
     * @param array Array of photos
     * @return int size in bytes
     */
    public static function getFilesize(array $photos) {
        $bytes=0;
        foreach ($photos as $photo) {
            $photo->lookup();
            $bytes+=$photo->get("size");
        }

        return $bytes;
    }
}
?>
