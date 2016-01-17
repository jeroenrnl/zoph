<?php
/**
 * Migrate configuration from old constants-based config to new
 * database-based config.
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
 * @package ZophContrib
 * @author Jeroen Roos
 */

require_once("include.inc.php");

if (!isset($user)) {
    $user=user::getCurrent();
}

if (!$user->is_admin()) {
    die("Must be admin");
}


$convert = array(
    "interface.title" => "ZOPH_TITLE",
    "interface.width" => "DEFAULT_TABLE_WIDTH",
    "interface.autocomplete" => "AUTOCOMPLETE",
    "interface.language" => "DEFAULT_LANG",
    "interface.user.default" => "DEFAULT_USER",
    "interface.user.cli" => "CLI_USER",
    "interface.max.days" => "MAX_DAYS_PAST",
    "url.http" => "ZOPH_URL",
    "url.https" => "ZOPH_SECURE_URL",
    "path.images" => "IMAGE_DIR",
    "path.upload" => "IMPORT_DIR",
    "path.magic" => "MAGIC_FILE",
    "path.unzip" => "UNZIP_CMD",
    "path.untar" => "UNTAR_CMD",
    "path.ungz" => "UNGZ_CMD",
    "path.unbz" => "UNBZ_CMD",
    "maps.provider" => "MAPS",
    "maps.geocode" => "GEOCODE",
    "maps.key.cloudmade" => "CLOUDMADE_KEY",
    "import.enable" => "IMPORT",
    "import.upload" => "UPLOAD",
    "import.maxupload" => "MAX_UPLOAD",
    "import.parallel" => "IMPORT_PARALLEL",
    "import.rotate" => "IMPORT_AUTOROTATE",
    "import.resize" => "IMPORT_RESIZE",
    "import.dated" => "USE_DATED_DIRS",
    "import.dated.hier" => "HIER_DATED_DIRS",
    "watermark.enable" => "WATERMARKING",
    "watermark.file" => "WATERMARK",
    "watermark.pos.x" => "WM_POSX",
    "watermark.pos.y" => "WM_POSY",
    "watermark.transparency" => "WM_TRANS",
    "rotate.enable" => "ALLOW_ROTATIONS",
    "rotate.command" => "ROTATE_CMD",
    "rotate.backup" => "BACKUP_ORIGINAL",
    "rotate.backup.prefix" => "BACKUP_PREFIX",
    "share.enable" => "SHARE",
    "share.salt.full" => "SHARE_SALT_FULL",
    "share.salt.mid" => "SHARE_SALT_MID",
    "feature.download" => "DOWNLOAD",
    "feature.comments" => "ALLOW_COMMENTS",
    "feature.mail" => "EMAIL_PHOTOS",
    "feature.mail.bcc" => "BCC_ADDRESS",
    "feature.annotate" => "ANNOTATE_PHOTOS",
    "feature.rating" => "ALLOW_RATINGS",
    "date.tz" => "CAMERA_TZ",
    "date.guesstz" => "GUESS_TZ",
    "date.format" => "DATE_FORMAT",
    "date.timeformat" => "TIME_FORMAT"
);

?>
<h1>Migrate config</h1>

<?php
foreach ($convert as $newname => $oldname) {
    $newconfig=conf::getItemByName($newname);
    if (defined($oldname)) {
        $oldconfig=constant($oldname);

        if ($newconfig->checkValue($oldconfig)) {
            $newconfig->setValue($oldconfig);
            echo $oldname . " --> " . $newname . "<br>\n";
        } else {
            echo "<b>" . $oldname . " could not be converted, " . e($oldconfig) . " is not a valid value for " . $newname . "</b>, default (" . $newconfig->getDefault() . ") has been used<br>\n";
        }
    }
    $newconfig->update();
}

// Some specials:

$convert = array(
    "import.filemode" => "FILE_MODE",
    "import.dirmode" => "DIR_MODE"
);

foreach ($convert as $newname => $oldname) {
    $newconfig=conf::getItemByName($newname);
    if (defined($oldname)) {
        $oldconfig="0" . decoct(constant($oldname));

        if ($newconfig->checkValue($oldconfig)) {
            $newconfig->setValue($oldconfig);
            echo $oldname . " --> " . $newname . "<br>\n";
        } else {
            echo "<b>" . $oldname . " could not be converted, " . e($oldconfig) . " is not a valid value for " . $newname . "</b>, default (" . $newconfig->getDefault() . ") has been used<br>\n";
        }
    }
    $newconfig->update();
}

$ssl_force=conf::getItemByName("ssl.force");
if (defined(FORCE_SSL_LOGIN) && FORCE_SSL_LOGIN) {
    $ssl_force->setValue("login");
} else if (defined(FORCE_SSL) && FORCE_SSL) {
    $ssl_force->setValue("always");
} else {
    $ssl_force->setValue("never");
}
$ssl_force->update();
echo "FORCE_SSL / FORCE_SSL_LOGIN --> ssl.force<br>\n";

$convert = array(
    "interface.sort.order" => "DEFAULT_ORDER",
    "interface.sort.dir" => "DEFAULT_DIRECTION"
);

foreach ($convert as $newname => $oldname) {
    $oldconfig=$$oldname;
    $newconfig=conf::getItemByName($newname);

    if ($newconfig->checkValue($oldconfig)) {
        $newconfig->setValue($oldconfig);
        echo "$" . $oldname . " --> " . $newname . "<br>\n";
    } else {
        echo "<b>$" . $oldname . " could not be converted, " . e($oldconfig) . " is not a valid value for " . $newname . "</b>, default (" . $newconfig->getDefault() . ") has been used<br>\n";
    }
    $newconfig->update();
}
?>
<p style="font-size: large"><b>You should delete <tt>migrate_config.php</tt> now.</b></p>
