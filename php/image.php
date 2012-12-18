<?php
/**
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
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 * @author Alan Shutko
 */
    session_cache_limiter("public");
    require_once("variables.inc.php");
    $hash = getvar("hash");
    $annotated = getvar('annotated');
    define("IMAGE_PHP", 1);
    require_once("include.inc.php");

    $photo_id = getvar("photo_id");
    $type = getvar("type");
    
    if(($type=="import_thumb" || $type=="import_mid") && ($user->is_admin() || $user->get("import"))) {
    
        $md5 = getvar("file");
        $file = file::getFromMD5(conf::get("path.images") . "/" . conf::get("path.upload"), $md5);
        
        $photo = new photo();
        $photo->set("name", basename($file));
        $photo->set("path", conf::get("path.upload"));
        if($type=="import_thumb") {
            $type="thumb";
        } else if ($type=="import_mid") {
            $type="mid";
        }
        $found=true;
    } else if (conf::get("share.enable") && !empty($hash)) {
        try {
            $photo=photo::getFromHash($hash, "full");
            $photo->lookup();
            $found = true;
        } catch(PhotoNotFoundException $e) {
            try {
                $photo=photo::getFromHash($hash, "mid");
                $photo->lookup();
                $type="mid";
                $found = true;
            } catch(PhotoNotFoundException $e) {
                /** @todo This should be changed into a nicer error display; */
                die($e->getMessage());
            }
        }
    } else if (conf::get("feature.annotate") && $annotated) {
        $photo = new annotatedPhoto($photo_id);
        $found = $photo->lookupForUser($user);
        $photo->setVars($request_vars);
        if(getvar("_size")=="mid") {
            $type=MID_PREFIX;
        }
    } else if ($type==MID_PREFIX || $type==THUMB_PREFIX || empty($type)) {
        $photo = new photo($photo_id);
        $found = $photo->lookupForUser($user);
    } else {
        die("Illegal type");
    }
    if ($found) {
        $name = $photo->get("name");
        $image_path = conf::get("path.images") . "/" . $photo->get("path") . "/";
        $watermark_file="";
        
        if(!$user->is_admin() && conf::get("watermark.enable")) {
            $permissions = $user->get_permissions_for_photo($photo_id);
            $watermark = $permissions->get("watermark_level");
            $photolevel=$photo->get("level");
            if($photolevel > $watermark) {
                $photo=new watermarkedPhoto($photo_id);
                $photo->lookup();
            }
        } else {
        }

        list($headers, $image)=$photo->display($type);

        foreach($headers as $label=>$value) {
            header($label . ": " . $value);
        }
        echo $image;
        exit;
    }
    require_once("header.inc.php");
?>
          <h1>
            <?php echo translate("error") ?>
          </h1>
      <div class="main">
            <?php echo translate("The image you requested could not be displayed.") ?>
</div>
<?php
    require_once("footer.inc.php");
?>
