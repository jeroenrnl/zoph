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
require_once "variables.inc.php";
$hash = getvar("hash");
$annotated = getvar('annotated');
$type = getvar("type");
if ($type == "background") {
    define("IMAGE_BG", 1);
}
define("IMAGE_PHP", 1);
require_once "include.inc.php";

$photo_id = getvar("photo_id");

if (($type=="import_thumb" || $type=="import_mid") &&
    ($user->isAdmin() || $user->get("import"))) {

    $md5 = getvar("file");
    $file = file::getFromMD5(conf::get("path.images") . "/" . conf::get("path.upload"), $md5);

    $photo = new photo();
    $photo->set("name", basename($file));
    $photo->set("path", conf::get("path.upload"));
    if ($type=="import_thumb") {
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
    $found = $photo->lookup();
    $photo->setVars($request_vars);
    if (getvar("_size")=="mid") {
        $type=MID_PREFIX;
    }
} else if ($type==MID_PREFIX || $type==THUMB_PREFIX || empty($type)) {
    $photo = new photo($photo_id);
    $found = $photo->lookup();
} else if ($type=="background") {
    if (conf::get("interface.logon.background.album")) {
        $album=new album(conf::get("interface.logon.background.album"));
        $photos=$album->getPhotos();
        $photo=$photos[array_rand($photos)];
        $photo->lookup();
        redirect("image.php?hash=" . $photo->getHash("full"));
    } else {
        $templates=array(
            conf::get("interface.template"),
            "default"
        );
        foreach ($templates as $template) {
            $bgs=glob(settings::$php_loc . "/templates/" . $template . "/images/backgrounds/*.{jpg,JPG}", GLOB_BRACE);
            if (sizeof($bgs) > 0) {
                $image=$bgs[array_rand($bgs)];
                redirect("templates/" . $template . "/images/backgrounds/" . basename($image));
            }
        }
    }
    exit;
} else {
    die("Illegal type");
}
if ($found) {
    $watermark_file="";
    if (!$user->isAdmin() && conf::get("watermark.enable")) {
        $permissions = $user->getPhotoPermissions($photo);
        $watermark = $permissions->get("watermark_level");
        $photolevel=$photo->get("level");
        if ($photolevel > $watermark) {
            $photo=new watermarkedPhoto($photo_id);
            $photo->lookup();
        }
    }

    list($headers, $image)=$photo->display($type);

    foreach ($headers as $label=>$value) {
        if ($label=="http_status") {
            // http status codes do not have a label
            header($value);
        } else {
            header($label . ": " . $value);
        }
    }

    if (!is_null($image)) {
        echo $image;
    }
    exit;
}
require_once "header.inc.php";
?>
  <h1>
    <?php echo translate("error") ?>
  </h1>
  <div class="main">
  <?php echo translate("The image you requested could not be displayed.") ?>
</div>
<?php
require_once "footer.inc.php";
?>
