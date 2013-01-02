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

/**
 * A class corresponding to the photos table.
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */
class photo extends zophTable {
    /** @var photographer */
    public $photographer;
    /** @var location */
    public $location;

    /**
     * @var For now this is only used during import, however, in the future, the photo object
     * will be split in a photo object, referencing one or more file objects.
     */
    public $file=array();

    /**
     * Create a new photo object
     * @param int photo_id
     */
    public function __construct($id = 0) {
        if($id && !is_numeric($id)) { die("photo_id must be numeric"); }
        parent::__construct("photos", array("photo_id"), array(""));
        $this->set("photo_id",$id);
    }

    /**
    * Display the image
    * @param string type of image to display mid, thumb or null for full-sized
    * @return array Return an array that contains:
    *               array headers: the headers
    *               string jpeg: the jpeg file
    * @todo only supports JPEG currently, should support more filetypes
    */
    public function display($type=null) {
        $headers=array();
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
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime) {
              header("HTTP/1.1 304 Not Modified");
              exit;
        }

        $image_type = get_image_type($image_path);
        if ($image_type) {
            $header["Content-Length"] = $filesize;
            $header["Content-Disposition"]="inline; filename=" . $name;
            $header["Last-Modified"]=$gmt_mtime;
            $header["Content-type"]=$image_type;
            $jpeg=file_get_contents($image_path);
            return array($header, $jpeg);
         }

        /**
         * @todo error handling
         */
    }

    /**
     * Get the id of this photo
     */
    public function getId() {
        return (int) $this->get("photo_id");
    }

    /**
     * Lookup a photo, considering access rights
     */
    public function lookup() {
        $user=user::getCurrent();
        if (!$this->get("photo_id")) { return; }

        if ($user->is_admin()) {
            $sql = "SELECT * FROM " . DB_PREFIX . "photos " .
                "WHERE photo_id = '" . escape_string($this->get("photo_id")) . "'";
        } else {
            $sql =
                "select p.* from " .
                DB_PREFIX . "photos as p JOIN " .
                DB_PREFIX . "photo_albums as pa " .
                "ON p.photo_id = pa.photo_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE p.photo_id = '" . escape_string($this->get("photo_id")) . "'" .
                " AND gu.user_id = '" . escape_string($user->get("user_id")) . "'" .
                " AND gp.access_level >= p.level " .
                "LIMIT 0, 1";
        }

        $success = $this->lookupFromSQL($sql);

        if ($success) {
            $this->lookupPhotographer();
            $this->lookupLocation();
        }

        return $success;
    }

    /**
     * Lookup photographer of this photo
     */
    private function lookupPhotographer() {
        if ($this->get("photographer_id") > 0) {
            $this->photographer = new person($this->get("photographer_id"));
            $this->photographer->lookup();
        }
    }

    /**
     * Lookup location of this photo
     */
    private function lookupLocation() {
        if ($this->get("location_id") > 0) {
            $this->location = new place($this->get("location_id"));
            $this->location->lookup();
        }
    }

    /**
     * Delete this photo from database
     * does not delete the photo on disk
     */
    public function delete() {
        parent::delete(array("photo_people", "photo_categories", "photo_albums"));
    }

    /** 
     * Update photo relations, such as albums, categories, etc.
     */
    public function updateRelations($vars, $suffix = "", user $user=null) {
        $albums=array();
        $categories=array();
        $people=array();
        

        // Albums
        if(isset($vars["_album" . $suffix])) {
            if(is_array($vars["_album" . $suffix])) {
                $albums=$vars["_album" . $suffix];
            } else {
                $albums[]=$vars["_album" . $suffix];
            }
        }

        if (!empty($vars["_remove_album$suffix"])) {
            foreach((array) $vars["_remove_album$suffix"] as $alb) {
                $this->removeFrom(new album($alb));
            }
        }
        
        if(isset($this->_album_id)) {
            $albums=array_merge($albums,$this->_album_id);
            unset($this->_album_id);
        }
        
        if(isset($albums)) {
            foreach($albums as $album) {
                $this->addTo(new album($album));
            }
        }

        // Categories
        if(isset($vars["_category" . $suffix])) {
            if(is_array($vars["_category" . $suffix])) {
                $categories=$vars["_category" . $suffix];
            } else {
                $categories[]=$vars["_category" . $suffix];
            }
        }

        if (!empty($vars["_remove_category$suffix"])) {
            foreach((array) $vars["_remove_category$suffix"] as $cat) {
                $this->removeFrom(new category($cat));
            }
        }

        if(isset($this->_category_id)) {
            $categories=array_merge($categories,$this->_category_id);
            unset($this->_category_id);
        }

        if(isset($categories)) {
            foreach($categories as $cat) {
                $this->addTo(new category($cat));
            }
        }

        // People
        if(isset($vars["_person" . $suffix])) {
            if(is_array($vars["_person" . $suffix])) {
                $people=$vars["_person" . $suffix];
            } else {
                $people[]=$vars["_person" . $suffix];
            }
        }
        
        if (!empty($vars["_remove_person$suffix"])) {
            foreach((array) $vars["_remove_person$ysuffix"] as $pers) {
                $this->removeFrom(new person($pers));
            }
        }

        if(isset($this->_person_id)) {
            $people=array_merge($people,$this->_person_id);
            unset($this->_person_id);
        }
        
        if(isset($people)) {
            foreach($people as $person) {
                $this->addTo(new person($person));
            }
        } 
    }
    
    /**
     * Updates the photo's dimensions and filesize
     */
    public function updateSize() {
        $file=$this->get_file_path();
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
        $file=$this->get_file_path();
        $exif=process_exif($file);
        if($exif) {
            $this->setFields($exif);
            $this->update();
        }
    }

    /**
     * Gets last used position for people on a photo
     * @return int position
     */
    public function getLastPersonPos() {
        $sql =
            "SELECT max(position) AS pos FROM " . DB_PREFIX . "photo_people " .
            "WHERE photo_id = '" . escape_string($this->get("photo_id")) . "';";
        $result=fetch_array(query($sql));
        return (int) $result["pos"];
    }

    public function addTo(organizer $org) {
        $org->addPhoto($this);
    }

    public function removeFrom(organizer $org) {
        $org->removePhoto($this);
    }

    function lookup_albums($user = null) {

        if ($user && !$user->is_admin()) {
            $sql =
                "SELECT al.album_id, al.parent_album_id, al.album FROM " .
                DB_PREFIX . "albums AS al JOIN " .
                DB_PREFIX . "photo_albums AS pa " .
                "ON al.album_id = pa.album_id JOIN " .
                DB_PREFIX . "group_permissions as gp " .
                "ON pa.album_id = gp.album_id JOIN " .
                DB_PREFIX . "groups_users as gu " .
                "ON gp.group_id = gu.group_id " .
                "WHERE pa.photo_id = '" .
                escape_string($this->get("photo_id")) . "'" .
                " AND gu.user_id = '" .
                escape_string($user->get("user_id")) . "' " .
                " AND gp.access_level >= " .
                escape_string($this->get("level")) .
                " ORDER BY al.album";
        }
        else {
            $sql =
                "select al.album_id, al.parent_album_id, al.album from " .
                DB_PREFIX . "photo_albums as pa, " .
                DB_PREFIX . "albums as al " .
                "where pa.photo_id = '" .
                escape_string($this->get("photo_id")) . "'" .
                " and pa.album_id = al.album_id order by al.album";
        }

        return album::getRecordsFromQuery("album", $sql);
    }

    function lookup_categories($user = null) {
        $sql =
            "select cat.category_id, cat.parent_category_id, cat.category from " .
            DB_PREFIX . "photo_categories as pc, " .
            DB_PREFIX . "categories as cat " .
            "where pc.photo_id = '" . escape_string($this->get("photo_id")) . "'" .
            " and pc.category_id = cat.category_id order by cat.category";

        return album::getRecordsFromQuery("category", $sql);
    }

    function lookup_people() {
        $sql =
            "select psn.person_id, psn.last_name, " .
            "psn.first_name, psn.called from " .
            DB_PREFIX . "photo_people as pp, " .
            DB_PREFIX . "people as psn " .
            "where pp.photo_id = '" .
            escape_string($this->get("photo_id")) . "'" .
            " and pp.person_id = psn.person_id order by pp.position";

        return album::getRecordsFromQuery("person", $sql);
    }
    /**
     * Import a file into the database
     *
     * This function takes a file object and imports it inot the database as a new photo
     *
     * @param file The file to be imported
     */
    function import($file) {
        $this->set("name", $file->getName());
        
        $newPath=$this->get("path") . "/";
        if(conf::get("import.dated")) {
            // This is not really validating the date, just making sure
            // no-one is playing tricks, such as setting the date to /etc/passwd or
            // something.
            $date=$this->get("date");
            if(!preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $date)) {
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
        
        if($path ."/" != $toPath) {
            $file->setDestination($toPath);
            $files[]=$file;

            $newname=$file->getDestName();

            $midname=MID_PREFIX . "/" . MID_PREFIX . "_" . $newname;
            $thumbname=THUMB_PREFIX . "/" . THUMB_PREFIX . "_" . $newname;
            
            if(file_exists($path . "/". $thumbname)) {
                $thumb=new file($path . "/" . $thumbname);
                $thumb->setDestination($toPath . "/" . THUMB_PREFIX . "/");
                $files[]=$thumb;
            }
            if(file_exists($path . "/". $midname)) {
                $mid=new file($path . "/" . $midname);
                $mid->setDestination($toPath . "/" . MID_PREFIX . "/");
                $files[]=$mid;
            }
        
            try {
                foreach($files as $file) {
                    if(conf::get("import.cli.copy")==false) {
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
                foreach($files as $file) {
                    if(conf::get("import.cli.copy")==false) {
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

    function get_file_path() {
        return conf::get("path.images") . "/" . $this->get("path") . "/" . $this->get("name");
    }

    function get_midsize_img() {
        return $this->get_image_tag(MID_PREFIX);
    }

    function get_thumbnail_link($link = null) {
        if (!$link) {
            $link = "photo.php?photo_id=" . $this->get("photo_id");
        }
        return "            <a href=\"$link\">" . $this->get_image_tag(THUMB_PREFIX) . "</a>";
    }

    function get_fullsize_link($title) {
        $user=user::getCurrent();
        $image = $this->getURL();
        $newwin = ($user->prefs->get("fullsize_new_win") ? "target=\"_blank\"" : "");
        return "<a href=\"$image\" $newwin>$title</a>";
    }
    
    /**
     * Get the URL to an image
     * @param string "mid" or "thumb"
     * @return string URL
     */
    public function getURL($type = null) {

        $url = "image.php?photo_id=" . $this->get("photo_id");
        if ($type) {
            $url .= "&amp;type=" . $type;
        }

        if (SID) {
            $url .= "&amp;" . SID;
        }
        return $url;
    }

    public function getDirectLink() {
        
    }

    function get_image_tag($type = null) {

        $image_href = $this->getURL($type);

        if (!$image_href) {
            return "";
        }

        $size_string = "";

        $width = $this->get("width");
        $height = $this->get("height");

        if ($type) {
            if ($type == THUMB_PREFIX) {
                $max_side = THUMB_SIZE;
            }
            else if ($type == MID_PREFIX) {
                $max_side = MID_SIZE;
            }

            if ($max_side) {
                if (!$width || !$height) {
                    // pick some reasonable values
                    $width = $max_side;
                    $height = (int)round(0.75 * $width);
                }
                else if ($width >= $height) {
                    $height = (int)round(($max_side/$width) * $height);
                    $width = $max_side;
                }
                else {
                    $width = (int)round(($max_side/$height) * $width);
                    $height = $max_side;
                }
            }
        }

        $size_string = " width=\"$width\" height=\"$height\"";
        $alt = escape_string($this->get("title"));
        return "<img src=\"$image_href\" class=\"" . $type . "\" " . $size_string . " alt=\"$alt\"" . ">";
    }

    function get_rating($user) {

        $photo_id = $this->get("photo_id");
        $user_id=$user->get("user_id");

        if ($user->get("allow_multirating")) {
            // This user is allowed to rate the same photoe  multiple 
            // times, however we will allow only one from the same IP
            $where = " and ipaddress = '" . 
                escape_string($_SERVER["REMOTE_ADDR"])."' ";
        } else {
            $where="";
        }

        $query =
            "select rating from " . DB_PREFIX . "photo_ratings " .
            "where user_id = '" . escape_string($user_id) . "'" .
            " and photo_id = '". escape_string($this->get("photo_id")) . "'" .
            $where;

        $result = query($query, "Rating lookup failed");

        $rating = null;
        if ($row = fetch_array($result)) {
            $rating = $row[0];
        }

        return $rating;
    }

    /**
     * Stores the rating of a photo for a user and updates the
     * average rating.
     *
     * This function from Jan Miczaika
     */
    function rate($user, $rating) {
        $where="";
        if (!$user || !$rating) {
            return null;
        }
        $user_id=$user->get("user_id");
        if(!($user->is_admin() || $user->get("allow_rating"))) {
            return;
        }

        $photo_id = $this->get("photo_id");

        if ($user->get("allow_multirating")) {
            // This user is allowed to rate the same photoe  multiple 
            // times, however we will allow only one from the same IP
            $where = " and ipaddress = '" . 
                escape_string($_SERVER["REMOTE_ADDR"])."' ";
        }

        $query =
            "select * from " . DB_PREFIX . "photo_ratings " .
            "where user_id = '" . escape_string($user_id) . "'" .
            " and photo_id = '". escape_string($photo_id) . "'" .
            $where;

        $result = query($query, "Rating lookup failed");

        //if the user has already voted, update the vote, else insert a new one

        if (num_rows($result) > 0) {
            $query =
                "update " . DB_PREFIX . "photo_ratings " .
                "set rating = '" . escape_string($rating) . "', " .
                " ipaddress = '" . escape_string($_SERVER["REMOTE_ADDR"])."' " .
                "where user_id = '" . escape_string($user_id) . "'" .
                " and photo_id = '". escape_string($photo_id) . "'" .
                $where . " LIMIT 1";
                // The limit makes sure only 1 vote is updated, this is 
                // needed if you ever change the allow_multirating to
                // 'no' and there already have been multiple votes
                // by this user. It will, however, simply update the first
                // vote it encounters...
        }
        else {
            $query =
                "insert into " . DB_PREFIX . "photo_ratings " .
                "(photo_id, user_id, ipaddress, rating) values " .
                " ('" . escape_string($photo_id) . "', '" .
                escape_string($user_id) . "', '" .
                escape_string($_SERVER["REMOTE_ADDR"])."', '" .
                escape_string($rating) . "')";
        }

        $result = query($query, "Rating input failed");

        //now recalculate the average, and input it in the photo table
        $this->recalculate_rating();
    }

    /**
     * Recalculate the rating for this photo
     * @todo Should update the object, not the database
     */
    function recalculate_rating() {
        $photo_id = $this->get("photo_id");
        $query = "select avg(rating) from " . DB_PREFIX . "photo_ratings ".
            " where photo_id = '" . escape_string($photo_id) . "'";


        $result = query($query, "Rating recalculation failed");

        $row = fetch_array($result);

        $avg = (round(100 * $row[0])) / 100.0;
        
        if($avg == 0) {
            $avg = "null";
        }
      
        $query = "update " . DB_PREFIX . "photos set rating = $avg" .
            " where photo_id = '" . escape_string($photo_id) . "'";

        $result = query($query, "Inserting average rating failed");

        return $avg;
    }

    function delete_rating($rating_id) {
        if(!is_numeric($rating_id)) { 
            die("<b>rating_id</b> must be numeric!"); 
        }
        $sql = "DELETE FROM " . DB_PREFIX . "photo_ratings WHERE " .
            "rating_id = " . escape_string($rating_id);
        query($sql);
        $this->recalculate_rating();
        return;
    }

    function get_image_resource() {
        $file = $this->get_file_path();
        $img_src = null;
        $image_info = getimagesize($file);
        switch ($image_info[2]) {
            case 1:
                $img_src = imagecreatefromgif($file);
                break;
            case 2:
                $img_src = imagecreatefromjpeg($file);
                break;
            case 3:
                $img_src = imagecreatefrompng($file);
                break;
            default:
                break;
        }

        return $img_src;
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
        $this->lookupLocation();
    }

    /**
     * Unset the location for this photo
     */
    public function unsetLocation() {
        $this->set("location_id", 0);
        $this->lookupLocation();
    }


    function thumbnail($force=true) {
        $path=conf::get("path.images") . "/" . $this->get("path") . "/";

        $name=$this->get("name");
        $midname=MID_PREFIX . "/" . MID_PREFIX . "_" . $name;
        $thumbname=THUMB_PREFIX . "/" . THUMB_PREFIX . "_" . $name;
        
        if(!file_exists($path . $midname) || $force===true) {
            if(!$this->create_thumbnail(MID_PREFIX, MID_SIZE)) {
                throw new PhotoThumbCreationFailedException("Could not create " . MID_PREFIX . " image");
            }
        }
        if(!file_exists($path . $thumbname) || $force===true) {
            if(!$this->create_thumbnail(THUMB_PREFIX, THUMB_SIZE)) {
                throw new PhotoThumbCreationFailedException("Could not create " . THUMB_PREFIX . " image");
            }
        }
        return true;
    }

    function create_thumbnail($prefix, $size) {
        $img_src = $this->get_image_resource();
        
        $image_info = getimagesize($this->get_file_path());
        $width = $image_info[0];
        $height = $image_info[1];

        if ($width >= $height) {
            $new_width = $size;
            $new_height = round(($new_width / $width) * $height);
        }
        else {
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

        if(!is_writable($dir)) {
            throw new FileDirNotWritableException("Directory not writable: " . $dir);
        }

        $image_type = get_image_type($new_image);

        // a little fast a loose but usually ok
        $func = "image" . substr($image_type, strpos($image_type, '/') + 1);

        $return = 1;
        if (!$func($img_dst, $new_image)) {
            $return = 0;
        }

        imagedestroy($img_dst);

        imagedestroy($img_src);

        return $return;
    }

    function rotate($deg) {
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
                if (!copy($dir . $name, $dir . $backup_name)) {
                    throw new FileCopyFailedException(
                        sprintf(translate("Could not copy %s to %s."), $name, $backup_name));
                    return;
                }
            }
        }

        // make a system call to convert or jpegtran to do the rotation.
        // in the future, use PHP's imagerotate() function,
        // but it only appears >= 4.3.0 (and is buggy at the moment)
        while (list($file, $tmp_file) = each($images)) {

            /*
              From Michael Hanke:
              This is buggy, because non-quadratic images are truncated
              The function goodrotate checks if images are nonquadratic

              This is not being used because, as Michael says,

              "I haven't found a reasonable way to preserve the exif-data
               stored in the original jpeg file. imagejpeg() (the gd
               function) doesn't write it into the exported image file.
               ... I propose to stick to 'convert' which keeps the exif
               metadata as it is."

              $imrot = @imagecreatefromjpeg($file);
              $new_image = $this->goodrotate($imrot, $deg);
              imagejpeg($new_image, $tmp_file, 95);
            */

            switch(conf::get("rotate.command")) {
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

            //echo "$cmd<br>\n";
            $output = system($cmd);

            if ($output) { // error
                throw new ZophException(translate("An error occurred. ") . $output);
            }

            if (!rename($tmp_file, $file)) {
                throw new FileRenameException(
                    sprintf(translate("Could not rename %s to %s."), $tmp_file, $file));
            }

        }

        // update the size and dimensions
        // (only if original was rotated)
        $this->update();
        $this->updateSize();
    }

    function getDisplayArray() {
        $datetime=$this->get_time(null, "Y-m-d");

        return array(
            translate("title") => $this->get("title"),
            translate("location") => $this->location
                ? $this->location->getLink() : "",
            translate("view") => $this->get("view"),
            translate("date") => create_date_link($datetime[0]),
            translate("time") => $this->get_time_details(),
            translate("photographer") => $this->photographer
                ? $this->photographer->getLink() : ""
        );
    }

    function get_email_array() {
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

    function get_camera_display_array() {
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

    function get_time($timezone=null, $date_format = null, $time_format = null) { 
        if(is_null($date_format)) {
            $date_format=conf::get("date.format");
        }
        if(is_null($time_format)) {
            $time_format=conf::get("date.timeformat");
        }

        if(TimeZone::validate($timezone)) {
            $place_tz=new TimeZone($timezone);
        } else { 
            $this->lookup();
            $loc=$this->location;
            if($loc && TimeZone::validate($loc->get("timezone"))) {
                $place_tz=new TimeZone($loc->get("timezone"));
            } 
        }
        if(TimeZone::validate(conf::get("date.tz"))) {
            $camera_tz=new TimeZone(conf::get("date.tz"));
        }    
            
        if(!isset($place_tz) && isset($camera_tz)) {
            // Camera timezone is known, place timezone is not.
            $place_tz=$camera_tz;
        } else if (isset($place_tz) && !isset($camera_tz)) {
            // Place timezone is known, camera timezone is not.
            $camera_tz=$place_tz;
        } else if (!isset($place_tz) && !isset($camera_tz)) {
            // Neither are set
            $camera_tz=new TimeZone(date_default_timezone_get());
            $place_tz=$camera_tz;
        }
        
        $camera_time=new Time(
            $this->get("date") . " " .
            $this->get("time"),
            $camera_tz);
        $place_time=$camera_time;
        $place_time->setTimezone($place_tz);
        $corr=$this->get("time_corr");
        if($corr) {
            $place_time->modify($corr . " minutes");
        }
        
        $date=$place_time->format($date_format);
        $time=$place_time->format($time_format);
        return array($date,$time);
    }

    function get_time_details() {
        $tz=null;
        if(TimeZone::validate(conf::get("date.tz"))) {
            $tz=conf::get("date.tz");
        }
        
        $this->lookup();
        $place=$this->location;
        $place_tz=null;
        $location=null;
        if(isset($place)) {
            $place_tz=$place->get("timezone");
            $location=$place->get("title");
        }
       
        $datetime=$this->get_time();

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

    function get_rating_details() {
        $rating=$this->get("rating");

        $sql="SELECT rating_id, user_id, rating, ipaddress, timestamp FROM " .
            DB_PREFIX . "photo_ratings WHERE photo_id=" .
            escape_string($this->get("photo_id"));

        $result=query($sql); 
        $ratings=array();
        while($row=fetch_assoc($result)) {
            $this_user=new user($row["user_id"]);
            $this_user->lookup();
            $row["user_name"]=$this_user->getName();
            $row["user_url"]=$this_user->getURL();
            $ratings[]=$row;
        }
        
        $tpl=new block("rating_details",array(
            "rating" => $rating,
            "ratings" => $ratings,
            "photo_id" => $this->get("photo_id")
        ));


        return $tpl;
    }
    function get_comments() {
        $sql = "select comment_id from " . DB_PREFIX . "photo_comments where" .
            " photo_id = " .  $this->get("photo_id");
        $comments=comment::getRecordsFromQuery("comment", $sql);
        return $comments;
    }

    function get_related() {
        $sql = "select photo_id_1 as photo_id from " . 
            DB_PREFIX . "photo_relations where" .
            " photo_id_2 = " .  $this->get("photo_id") .
            " union select photo_id_2 as photo_id from " . 
            DB_PREFIX . "photo_relations where" .
            " photo_id_1 = " .  $this->get("photo_id");
        $related=photo::getRecordsFromQuery("photo", $sql);
        return $related;
    }

    function check_related($photo_id) {
        $related=$this->get_related();
        foreach($related as $rel_photo) {
            if ($rel_photo->get("photo_id") == $photo_id) {
                return true;
            }
        }
        return false;
    }
    function get_relation_desc($photo_id_2) {
        $sql = "select desc_1 from " . DB_PREFIX . "photo_relations where" .
            " photo_id_2 = " . escape_string($this->get("photo_id")) . " and " .
            " photo_id_1 = " . escape_string($photo_id_2) . 
            " union select desc_2 from " . DB_PREFIX . "photo_relations where" .
            " photo_id_1 = " . escape_string($this->get("photo_id")) . " and " .
            " photo_id_2 = " . escape_string($photo_id_2) . " limit 1";
        $result=query($sql, "Could not get description for related photo:");
        $result=fetch_row($result);
        return $result[0];
    }
    
    function create_relation($photo_id_2, $desc_1 = null, $desc_2 = null) {
        $sql = "insert into " . DB_PREFIX . "photo_relations values (" .
            escape_string($this->get("photo_id")) . "," .
            escape_string($photo_id_2) . "," .
            "\"" . escape_string($desc_1) . "\"," .
            "\"" . escape_string($desc_2) . "\")";
        $result=query($sql, "Could not create relation");
        }
        
    function update_relation($photo_id_2, $desc_1 = null, $desc_2 = null) {
        $photo_id_1=escape_string($this->get("photo_id"));
        $photo_id_2=escape_string($photo_id_2);
        $sql = "update " . DB_PREFIX . "photo_relations set" .
            " desc_1=\"" . escape_string($desc_1) . "\"," .
            " desc_2=\"" . escape_string($desc_2) . "\"" .
            " where photo_id_1=" . $photo_id_1 .
            " and photo_id_2=" . $photo_id_2;
        query($sql, "Could not update relation:");
        // A relation may be the other way around...
        $sql = "update " . DB_PREFIX . "photo_relations set" .
            " desc_2=\"" . escape_string($desc_1) . "\"," .
            " desc_1=\"" . escape_string($desc_2) . "\"" .
            " where photo_id_2=" . $photo_id_1 .
            " and photo_id_1=" . $photo_id_2;
        query($sql, "Could not update relation:");
    }
    
    function delete_relation($photo_id_2) {
        $ids="(" . escape_string($this->get("photo_id")) . "," .
            escape_string($photo_id_2) . ")"; 
        $sql = "delete from " . DB_PREFIX . "photo_relations" .
            " where photo_id_1 in " . $ids .
            " and photo_id_2 in " . $ids;
        $result=query($sql, "Could not delete relation:");
    }    
    
    function exif_to_html() {
        if (exif_imagetype($this->get_file_path())==IMAGETYPE_JPEG) {
            $exif=read_exif_data($this->get_file_path());
            if ($exif) {
                $return="<dl class='allexif'>\n";

                foreach($exif as $key => $value) {
                    if(!is_array($value)) {
                        $return .="    <dt>$key</dt>\n" .
                                  "    <dd>" . preg_replace("/[^[:print:]]/", "", $value) . "</dd>\n";
                    } else {
                        $return .="    <dt>$key</dt>\n" .
                                  "    <dd>\n" .
                                  "        <dl>\n";
                        foreach ($value as $subkey => $subval) {
                            $return .= "            <dt>$subkey</dt>\n" .
                                       "            <dd>" . preg_replace("/[^[:print:]]/", "", $subval) . "</dd>\n";
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

    function getQuicklook() {
        $title=e($this->get("title"));
        $file=$this->get("name");

        if($title) {
            $html="<h2>" . e($title) . "<\/h2><p>" . e($file) . "<\/p>";
        } else {
            $html="<h2>" . e($file) . "<\/h2>";
        }    
        $html.=$this->get_thumbnail_link() .
          "<p><small>" . 
          $this->get("date") . " " . $this->get("time") . "<br>";
        if($this->photographer) {
            $html.=translate("by",0) . " " . $this->photographer->getLink(1) . "<br>";
        }
        $html.="<\/small><\/p>";
        return $html;
    }

    /**
     * Get Marker to be placed on map
     * @param string icon to be used.
     * @return marker instance of marker class
     */
    function getMarker($icon="geo-photo") {
        $marker=map::getMarkerFromObj($this, $icon); 
        if(!$marker instanceof marker) {
            $loc=$this->location;
            if($loc instanceof place) {
                return $loc->getMarker(); 
            }
        } else {
            return $marker;
        }
    }

    /**
     * Get photos taken near this photo
     */
    public function get_near($distance, $limit=100, $entity="km") { 
        $lat=$this->get("lat");
        $lon=$this->get("lon");
        if($lat && $lon) {
            return self::getPhotosNear((float) $lat, (float) $lon, (float) $distance, (int) $limit, $entity);
        }
    }

    /**
     * Get photos taken near a lat/lon location
     */
    public static function getPhotosNear($lat, $lon, $distance, 
            $limit, $entity="km") { 
            
        // If lat and lon are not set, don't bother trying to find
        // near photos
        if($lat && $lon) {
            $lat=(float) $lat;
            $lon=(float) $lon;

            if($entity=="miles") {
                $distance=(float) $distance * 1.609344;
            }
            if($limit) {
                $lim=" limit 0,". (int) $limit;
            }
            $sql="select photo_id, (6371 * acos(" .
                "cos(radians(" . $lat . ")) * " .
                "cos(radians(lat) ) * cos(radians(lon) - " .
                "radians(" . $lon . ")) +" . 
                "sin(radians(" . $lat . ")) * " .
                "sin(radians(lat)))) AS distance from " .
                DB_PREFIX . "photos " .
                "having distance <= " . $distance . 
                " order by distance" . $lim;

            $near=photo::getRecordsFromQuery("photo", $sql);
            return $near;
        } else {
            return null;
        }
    }

    public static function getByName($file, $path=null) {
        $sql="SELECT photo_id FROM " . DB_PREFIX . "photos " .
            "WHERE name=\"" . escape_string($file) ."\"";
        if(!empty($path)) {
            $sql .= " AND path='" . escape_string($path) ."'";
        }
        return photo::getRecordsFromQuery("photo", $sql);
    }

    public function getHashFromFile() {
        $file=$this->get_file_path();
        if(file_exists($file)) {
            return sha1_file($file);
        } else {
            throw new FileNotFoundException("File not found:" . $file);
        }
    }

    public function getHash($type="file") {
        $hash=$this->get("hash");
        if(empty($hash)) {
            try {
                $hash=$this->getHashFromFile();
                $this->set("hash", $hash);
                $this->update();
            } catch (Exception $e) {
                log::msg($e->getMessage(), log::ERROR, log::IMG);
            }
        }
        switch($type) {
            case "file":
                return $hash;
                break;
            case "full":
                return sha1(conf::get("share.salt.full") . $hash);
                break;
            case "mid":
                return sha1(conf::get("share.salt.mid") . $hash);
                break;
            default:
                die("Unsupported hash type");
                break;
        }
    }

    /**
     * Set photo's lat/lon from a point object
     *
     * @param point
     */
    public function setLatLon(point $point) {
       $this->set("lat", $point->get("lat"));
       $this->set("lon", $point->get("lon"));
    }

    /**
     * Try to determine the lat/lon position this photo was taken from one or all tracks;
     *
     * @param track track to use or null to use all tracks
     * @param int maximum time the time can be off
     * @param bool Whether to interpolate between 2 found times/positions
     * @param int Interpolation max_distance: what is the maximum distance between two points to still interpolate
     * @param string km / miles entity in which max_distance is measured
     * @param int Interpolation maxtime Maximum time between to point to still interpolate
     */
    public function getLatLon(track $track=null, $max_time=300, $interpolate=true, 
            $int_maxdist=5, $entity="km", $int_maxtime=600) {

        date_default_timezone_set("UTC");
        $datetime=$this->get_time("UTC");
        $utc=strtotime($datetime[0] . " " . $datetime[1]);
        
        $mintime=$utc-$max_time;
        $maxtime=$utc+$max_time;
        
        if($track) {
            $track_id=$track->getId();
            $where=" AND track_id=" . escape_string($track_id);
        } else {
            $where="";
        }
        
        $sql="SELECT * FROM " . DB_PREFIX . "point" .
            " WHERE datetime > \"" . date("Y-m-d H:i:s", $mintime) . "\" AND" .
            " datetime < \"" . date("Y-m-d H:i:s", $maxtime)  . "\"" .
            $where .
            " ORDER BY abs(timediff(datetime,\"" . date("Y-m-d H:i:s", $utc) . "\")) ASC" .
            " LIMIT 1";

        $points=point::getRecordsFromQuery("point", $sql);
        if(sizeof($points) > 0 && $points[0] instanceof point) {
            $point=$points[0];
            $pointtime=strtotime($point->get("datetime"));
        } else {
            // can't get a point, don't bother trying to interpolate.
            $interpolate=false;
            $point=null;
        }
        
        if($interpolate && ($pointtime != $utc)) {
            if($utc>$pointtime) {
                $p1=$point;
                $p2=$point->getNext();
            } else {
                $p1=$point->getPrev();
                $p2=$point;
            }
            if($p1 instanceof point && $p2 instanceof point) {
                $p3=point::interpolate($p1,$p2,$utc,$int_maxdist, $entity, $int_maxtime);
                if($p3 instanceof point) {
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
     * @param array Array should contain first and/or last and/or random to determine which subset(s)
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
        
        if($count>$max) {
            $count=$max;
        }

        if(in_array("first", $subset)) {
            $first=array_slice($photos, 0, $count);
            $max=$max-$count;
            $begin=$count;
        }
        if(in_array("last", $subset)) {
            $last=array_slice($photos, -$count);
            $max=$max-$count;
            $end=-$count;
        }

        if(in_array("random", $subset) && ($max > 0)) {
            $center=array_slice($photos,$begin,$end);
            $max=count($center);

            if($max!=0) {
                if($count>$max) {
                    $count=$max;
                }
                $random_keys=array_rand($center, $count);
                if(is_array($random_keys)) {
                    foreach($random_keys as $key) {
                        $random[]=$center[$key];
                    }
                } else {
                    $random[]=$center[$random_keys];
                }
            }
        }
        $subset=array_merge($first,$random,$last);

        // remove duplicates due to overlap:
        $clean_subset=array();
        foreach($subset as $photo) {
            $clean_subset[$photo->get("photo_id")]=$photo;
        }

        return $clean_subset;
    }
        
    /**
     * Take an array of photos and remove photos with no valid timezone
     *
     * This function is needed for geotagging: for photos without a valid timezone it is not possible to
     * determine the UTC time, needed for geotagging.
     * @param array Array of photos
     * @return array Array of photos with a valid timezone
     */
    public static function removePhotosWithNoValidTZ(array $photos) {

        $gphotos=array();
        log::msg("Number of photos before valid timezone check: " . count($photos), log::DEBUG, log::GEOTAG);

        foreach($photos as $photo) {
            $photo->lookup();
            $loc=$photo->location;
            if(get_class($loc)=="place") {
                $tz=$loc->get("timezone");
                if(TimeZone::validate($tz)) {
                    $gphotos[]=$photo;
                }
            }
        }
        log::msg("Number of photos after valid timezone check: " . count($gphotos), log::DEBUG, log::GEOTAG);
        return $gphotos;
    }
    
    /**
     * Take an array of photos and remove photos that already have lat/lon 
     * information set
     *
     * This function is needed for geotagging, so photos that have lat/lon 
     * manually set will not be overwritten
     * @param array Array of photos
     * @return array Array of photos with no lat/lon info
     */
    public static function removePhotosWithLatLon($photos) {
        $gphotos=array();
        log::msg("Number of photos before overwrite check: " . count($photos), log::DEBUG, log::GEOTAG);
        foreach($photos as $photo) {
            $photo->lookup();
            if(!($photo->get("lat") or $photo->get("lon"))) {
                $gphotos[]=$photo;
            }
        }
        log::msg("Number of photos after overwrite check: " . count($gphotos), log::DEBUG, log::GEOTAG);
        return $gphotos;
    }

    /**
     * Gets the total count of records in the table
     * @todo Can be removed when minimum PHP version is 5.3 
     */
    public static function getCount($dummy=null) {
        return parent::getCount("photo");
    }
    
    /**
     * Find a photo from a SHA1-hashed string
     * @param string hash
     * @return photo found photo
     */

    public static function getFromHash($hash, $type="file") {
        if(!preg_match("/^[A-Za-z0-9]+$/", $hash)) {
            die("Illegal characters in hash");
        }
        switch($type) {
            case "file":
                $where="WHERE hash=\"" . escape_string($hash) . "\";";
                break;
            case "full":
                $salt=conf::get("share.salt.full");
                $where="WHERE sha1(CONCAT('" . $salt . "', hash))=" .
                   "\"" . escape_string($hash) . "\";";
                break;
            case "mid":
                $salt=conf::get("share.salt.mid");
                $where="WHERE sha1(CONCAT('" . $salt . "', hash))=" .
                   "\"" . escape_string($hash) . "\";";
                break;
            default:
                die("Unsupported hash type");
                break;
        }


        $sql="SELECT * FROM " . DB_PREFIX . "photos " . $where;

        $photos=photo::getRecordsFromQuery("photo", $sql);
        if(is_array($photos) && sizeof($photos) > 0) {
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

        
}

function get_photo_sizes_sum() {
    $sql = "select sum(size) from " . DB_PREFIX . "photos";
    return photo::getCountFromQuery($sql);
}

function get_filesize($photos, $human=false) {
    $bytes=0;
    foreach($photos as $photo) {
//    var_dump($photo);   #->get("size");
        $photo->lookup();
        $bytes+=$photo->get("size");
    }

    if($human) {
        return get_human($bytes);
    } else {
        return $bytes;
    }
}
function createRatingGraph() {
    $user=user::getCurrent();

    $ratings=array();
    $value_array=array();
    $html="";

    if ($user->is_admin()) {
        $sql = "SELECT FLOOR(rating+0.5), COUNT(*) FROM " . DB_PREFIX . "photos " .
            "GROUP BY FLOOR(rating+0.5) ORDER BY FLOOR(rating+0.5)";
    } else {
        $sql = "SELECT FLOOR(ph.rating+0.5), " . 
            "COUNT(DISTINCT ph.photo_id) AS COUNT FROM " .
            DB_PREFIX . "photos AS ph JOIN " .
            DB_PREFIX . "photo_albums AS pa " .
            "ON ph.photo_id = pa.photo_id JOIN " .
            DB_PREFIX . "group_permissions AS gp " .
            "ON pa.album_id = gp.album_id JOIN " .
            DB_PREFIX . "groups_users AS gu " .
            "ON gp.group_id = gu.group_id " .
            "WHERE gu.user_id = '" . 
            escape_string($user->get("user_id")) .
            "' AND gp.access_level >= ph.level " .
            "GROUP BY floor(rating+0.5) ORDER BY floor(rating+0.5)";
    }

    $result = query($sql, "Rating grouping failed");

    while ($row = fetch_array($result)) {
    	$ratings[($row[0] ? $row[0] : translate("Not rated"))]=$row[1];
	}
    if(!empty($ratings)) {
        $html="<h3>" . translate("photo ratings") . "</h3>";
        $legend=array(translate("rating"),translate("count"));
        while (list($range, $count) = each($ratings)) {
            if($range>0) {
                $min_rating=$range-0.5;
                $max_rating=$range+0.5;
                $link =
                  "search.php?rating%5B0%5D=" . $min_rating . 
                  "&amp;_rating_op%5B0%5D=%3E%3D" .
                  "&amp;rating%5B1%5D=" . $max_rating . 
                  "&amp;_rating_op%5B1%5D=%3C&amp;_action=" . translate("search");
            } else {
            $link = "photos.php?rating=null";
        }  
            $row=array($range, $link, $count);
            $value_array[]=$row;
        }
    }
    if(!empty($value_array)) {
        $html.=create_bar_graph($legend, $value_array, 150);
    } else {
        $html.=translate("No photo was found.") . "\n";
    }
    
    return $html;
}

/*
 * Rotates (non-quadratic) images correctly using imagerotate().
 * It is currently not being used because it apparently does not
 * preserve exif info.
 *
 * This function provided by Michael Hanke, who found it on php.net.
 *
 *
 * (c) 2002 php at laer dot nu
 * Function to rotate an image
 */
function goodrotate($src_img, $degrees = 90) {
    // angles = 0deg
    $degrees %= 360;
    if($degrees == 0) {
        $dst_img = $src_image;
    } else if ($degrees == 180) {
        $dst_img = imagerotate($src_img, $degrees, 0);
    } else {
        $width = imagesx($src_img);
        $height = imagesy($src_img);
        if ($width > $height) {
           $size = $width;
        } Else {
           $size = $height;
        }
        $dst_img = imagecreatetruecolor($size, $size);
        imagecopy($dst_img, $src_img, 0, 0, 0, 0, $width, $height);
        $dst_img = imagerotate($dst_img, $degrees, 0);
        $src_img = $dst_img;
        $dst_img = imagecreatetruecolor($height, $width);
        if ((($degrees == 90) && ($width > $height)) || (($degrees == 270) && ($width < $height))) {
            imagecopy($dst_img, $src_img, 0, 0, 0, 0, $size, $size);
        }
        if ((($degrees == 270) && ($width > $height)) || (($degrees == 90) && ($width < $height))) {
            imagecopy($dst_img, $src_img, 0, 0, $size - $height, $size - $width, $size, $size);
        }
    }
    return $dst_img;
}

?>
