<?php
/*
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
 */

    require_once("include.inc.php");
    require_once("import.inc.php");

    $_image_local = getvar("_image_local");
    $_image_server = getvar("_image_server");
    $_path_a = getvar("_path_a");
    $_path_b = getvar("_path_b");

    $default_path = DEFAULT_DESTINATION_PATH;
    if (preg_match("/date(.*)/", $default_path)) {
        $default_path = preg_replace("/date\(([^)]*)\)/e", "date(\"\\1\")", $default_path);
    }
    if (!(CLIENT_WEB_IMPORT || SERVER_WEB_IMPORT) ||
        (!$user->is_admin() && !$user->get("import"))) {

        header("Location: " . add_sid("zoph.php"));
    }

    $photo = new photo();
        
    if ($_action == "import") {
        // actual processing is at the bottom

        $action = "display";
    }
    else {
        $action = "import";
    }

    $title = translate("Import");

require_once("header.inc.php");
?>
    <h1><?php echo translate("import photos") ?></h1>
    <?php echo check_js($user); ?>
    <div class="main">
        <form enctype="multipart/form-data" action="import.php" method="POST">
<?php
        if ($action == "display") {
?>
            <p><?php echo translate("Importing images...") ?></p>
<?php
        } else {
?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD ?>"> 
            <input type="hidden" name="_action" value="<?php echo $action ?>">
<?php
            if (CLIENT_WEB_IMPORT) {
?>
                <h2><?php echo translate("Importing a Local File") ?></h2>
                <p><?php echo translate("To upload and import a local file, browse to the file and specify the destination path (relative to the top level image dir) in which it should be placed.") ?></p>
<?
                if (UNZIP_CMD || UNTAR_CMD) {
?>
                    <p><?php echo translate("You can upload a single image or a zip or tar file of images.") ?></p>
<?
                }
?>
                <label for="image_local"><?php echo translate("file") ?></label>
                <input name="_image_local" id="image_local" type="file"><br>
<?php
                if (SHOW_DESTINATION_PATH || $user->is_admin()) {
?>
                    <label for="path_a"><?php echo translate("destination path") ?></label>
                    <?php echo create_text_input("_path_a", $default_path, 40, 256) ?>
<?php
                    if (USE_DATED_DIRS) {
?>
                        <span class="inputhint"><?php echo translate("Dated directory will be appended") ?></span><br>
<?php
                    }
                } else {
?>
                    <input type="hidden" name="_path_a" value="<?php echo $default_path ?>">
<?php
                }
            }
            if (SERVER_WEB_IMPORT) {
?>
                <h2><?php echo translate("Importing Files on the Server") ?></h2>
                <p><?php echo translate("To import images already on the server, specify the absolute path of a file name or directory. If a directory is specified, all images within the directory will be imported. If a destination path is given (relative to the top level image dir), the imported images will be copied there. Otherwise, they will not be moved.") ?></p>
                <label for="_image_server"><?php echo translate("file/directory") ?></label>
                <?php echo create_text_input("_image_server", "", 40, 256) ?><br>
<?php
                if (SHOW_DESTINATION_PATH || $user->is_admin()) {
?>
                    <label for="path_b"><?php echo translate("destination path") ?></label>
                    <?php echo create_text_input("_path_b", $default_path, 40, 256) ?>
<?php
                    if (USE_DATED_DIRS) {
?>
                        <span class="inputhint"><?php echo translate("Dated directory will be appended") ?></span><br>
<?php
                    } 
                } else {
?>
                    <input type="hidden" name="_path_b" value="<?php echo $default_path ?>">
<?php
                }
            }
?>
            <hr>
            <p><?php echo translate("Fields specified below will apply to all images imported.") ?></p>
            <label for="album"><?php echo translate("album") ?></label>
            <?php echo create_album_pulldown("_album", "", $user) ?><br>
            <label for="category"><?php echo translate("category") ?></label>
            <?php echo create_cat_pulldown("_category", "", $user) ?><br>
            <label for="title"><?php echo translate("title") ?></label>
            <?php echo create_text_input("title", "", 40, 64) ?>
            <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></span><br>
            <label for="location"><?php echo translate("location") ?></label>
            <?php echo create_place_pulldown("location_id", "", $user) ?><br>
            <label for="view"><?php echo translate("view") ?></label>
            <?php echo create_text_input("view", "", 40, 64) ?>
            <span class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></span><br>
            <label for="date"><?php echo translate("date") ?></label>
            <?php echo create_text_input("date", "", 12, 10) ?>
            <span class="inputhint">YYYY-MM-DD</span><br>
            <label for="rating"><?php echo translate("rating") ?></label>
            <?php echo create_rating_pulldown("") ?>
            <span class="inputhint">1 - 10</span><br>
            <label for="photographer"><?php echo translate("photographer") ?></label>
            <?php echo create_photographer_pulldown("photographer_id", "", $user) ?><br>
            <label for="level"><?php echo translate("level") ?></label>
            <?php echo create_text_input("level", "", 4, 2) ?>
            <span class="inputhint">1 - 10</span><br>
            <label for="description"><?php echo translate("description") ?></label>
            <textarea name="description" cols="60" rows="4"></textarea><br>
            <input type="submit" value="<?php echo translate($action, 0) ?>">
<?php
    } // end import fields
    
    
    flush();
    // do the import down here
    if ($_action == "import") {
        $album_id = getvar("_album");
        if($album_id && !$user->is_admin()) {
            $permissions=$user->get_album_permissions($album_id);
            if(!$permissions->get("writable")) {
                die(translate("No write permissions to this album."));
            }
        }

        // so directories are created with correct mode
        $oldumask = umask(IMPORT_UMASK);

        // wouldn't want someone to pass this in
        $tmp_path = null;

        $name = $HTTP_POST_FILES['_image_local']['name'];
        remove_magic_quotes($name);

        // may need to create the destination directory
        // before doing anything else
        $path = null;
        if ($name) { $path = $_path_a; }
        else if ($_image_server) { $path = $_path_b; }

        // reject these paths
        if (strpos($path, '..') === true) {
            echo translate("Invalid path") . ": $path<br>\n";
            $path = null;
        }

        $absolute_path = cleanup_path(IMAGE_DIR . $path);
        if (!running_on_windows()) {
            $absolute_path = "/" . $absolute_path;
        }

        if (file_exists($absolute_path) == false) {
            create_dir_recursive($absolute_path) or die(translate("File upload failed"));
        }

        if ($name) {
            $tmp_name = $HTTP_POST_FILES['_image_local']['tmp_name'];
            $file =  cleanup_path(IMAGE_DIR . "/" . $path . "/" . $name);
            if (!running_on_windows()) {
                $file = "/" . $file;
            }

            if (move_uploaded_file($tmp_name, $file)) {
                echo translate("Received file") . ": $file<br>\n";

                $ext = strtolower(file_extension($file));
                $expand = null;
                if ($ext == 'zip' && UNZIP_CMD) {
                    $expand = UNZIP_CMD;
                }
                else if ($ext == 'zip' && !UNZIP_CMD) {
                    echo "UNZIP_CMD" . translate("is not set.") . "<br>\n";
                }
                else if ($ext == 'tar' && UNTAR_CMD) {
                    $expand = UNTAR_CMD;
                }
                else if ($ext == 'tar' && !UNTAR_CMD) {
                    echo "UNTAR_CMD" . translate("is not set.") . "<br>\n";
                }

                if ($expand) {
                    $tmp_path = EXTRACT_DIR . '/zoph' . time();
                    create_dir($tmp_path) or die("Failed to create dir");

                    $cmd = 'cd ' . escapeshellarg($tmp_path) . ' && ' .
                        $expand . ' ' . escapeshellarg($file) . ' 2>&1';
                    //echo "$cmd<br>\n";

                    echo "<p>\n<pre>\n";
                    system($cmd);
                    echo "</pre>\n</p>\n";

                    echo translate("Reading directory") . ": $tmp_path<br>\n";
                    $images = get_files($tmp_path);
                }
                else if (valid_image($file)) { // single file
                    $images[] = $file;
                }
                else {
                    echo sprintf(translate("Skipping %s: Unsupported file type."), $file) . "<br>\n";
                }
            }
            else {
                echo translate("File upload failed") . "<br>\n";
                switch ($HTTP_POST_FILES['_image_local']["error"]) {
                case UPLOAD_ERR_INI_SIZE:
                    printf(translate("The uploaded file exceeds the upload_max_filesize directive (%s) in php.ini."), ini_get("upload_max_filesize"));
                   break;
                case UPLOAD_ERR_FORM_SIZE:
                    printf(translate("The uploaded file exceeds the MAX_UPLOAD setting in config.inc.php (%s)."), MAX_UPLOAD);
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo translate("The uploaded file was only partially uploaded.");
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo translate("No file was uploaded.");
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    translate("Missing a temporary folder.");
                    break;
                default:
                    translate("An unknown file upload error occurred.");
                }

            }
        }
        else if ($_image_server) {
            if (is_dir($_image_server)) {
                echo translate("Reading directory") . ": $_image_server<br>\n";
                $images = get_files($_image_server);
            }
            else if (valid_image($_image_server)) { // assume file name
                $images[] = $_image_server;
                $_image_server = dirname($_image_server);
            }
            else {
                echo sprintf(translate("Skipping %s: Unsupported file type."), $_image_server) . "<br>\n";
            }

            if (!$path) {
                $path = str_replace(IMAGE_DIR, '', $_image_server);
            }
        }

        if ($images != null) {
            $loaded = process_images($images, $path, $request_vars);

            if ($loaded >= 0) {
                echo "<p>" . sprintf(translate("%s images loaded."), $loaded) . "</p>\n";
                if ($expand && REMOVE_ARCHIVE) {
                    echo "<p>" . sprintf(translate("Deleting %s"),$file);
                    unlink($file); 
                }
            }
            else {
                echo "<p>" . translate("An error occurred.") . "</p>\n";
            }
        }

        if ($tmp_path) {
            system('rm -rf ' . escapeshellarg($tmp_path));
        }

        umask($oldumask);
    }
?>
     </form>
  </div>
  <br>
<?php require_once("footer.inc.php"); ?>
