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

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
require_once("header.inc.php");
?>
          <h1><?php echo translate("import photos") ?></h1>
  <div class="main">
      <form enctype="multipart/form-data" action="import.php" method="POST">
      <table id="import">
<?php
    if ($action == "display") {
?>
        <tr>
          <td><?php echo translate("Importing images...") ?></td>
        </tr>
<?php
    }
    else {
?>
<tr><td>
<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
<input type="hidden" name="_action" value="<?php echo $action ?>">
</td></tr>
<?php
        if (CLIENT_WEB_IMPORT) {
?>
        <tr>
          <td colspan="3">
            <h2><?php echo translate("Importing a Local File") ?></h2><br>
            <?php echo translate("To upload and import a local file, browse to the file and specify the destination path (relative to the top level image dir) in which it should be placed.") ?>
<?
            if (UNZIP_CMD || UNTAR_CMD) {
?>
            <?php echo translate("You can upload a single image or a zip or tar file of images.") ?>
<?
            }
?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("file") ?></td>
          <td class="field" colspan="2"><input name="_image_local" type="file"></td>
        </tr>
<?php
            if (SHOW_DESTINATION_PATH || $user->is_admin()) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("destination path") ?></td>
          <td class="field" colspan="2"><?php echo create_text_input("_path_a", $default_path, 40, 256) ?>
<?php
           if (USE_DATED_DIRS) {
             echo "<br>\n" . translate("Dated directory will be appended"); 
           }
?>
          </td>
        </tr>
<?php
            }
            else {
?>
        <tr>
          <td>
            <input type="hidden" name="_path_a" value="<?php echo $default_path ?>">
          </td>
        </tr>
<?php
            }
        }

        if (SERVER_WEB_IMPORT) {
?>
        <tr>
          <td colspan="3">
            <h2><?php echo translate("Importing Files on the Server") ?></h2><br>
            <?php echo translate("To import images already on the server, specify the absolute path of a file name or directory.  If a directory is specified, all images within the directory will be imported.  If a destination path is given (relative to the top level image dir), the imported images will be copied there.  Otherwise, they will not be moved.") ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("file/directory") ?></td>
          <td class="field" colspan="2"><?php echo create_text_input("_image_server", "", 40, 256) ?></td>
        </tr>
<?php
            if (SHOW_DESTINATION_PATH || $user->is_admin()) {
?>
        <tr>
          <td class="fieldtitle"><?php echo translate("destination path") ?></td>
          <td class="field" colspan="2"><?php echo create_text_input("_path_b", $default_path, 40, 256) ?>
<?php
          if (USE_DATED_DIRS) {
            echo "<br>" . translate("Dated directory will be appended");
          }
?>
          </td>
        </tr>
<?php
            }
            else {
?>
        <tr>
          <td>
            <input type="hidden" name="_path_b" value="<?php echo $default_path ?>">
          </td>
        </tr>
<?php
            }
        }
?>
        <tr>
          <td colspan="3">
            <hr>
            <?php echo translate("Fields specified below will apply to all images imported.") ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("album") ?></td>
          <td class="field" colspan="2">
            <?php echo create_pulldown("_album", "", get_albums_select_array($user)) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("category") ?></td>
          <td class="field" colspan="2">
            <?php echo create_pulldown("_category", "", get_categories_select_array($user)) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("title") ?></td>
          <td class="field"><?php echo create_text_input("title", "", 40, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("location") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("location_id", "", get_places_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("view") ?></td>
          <td class="field"><?php echo create_text_input("view", "", 40, 64) ?></td>
          <td class="inputhint"><?php echo sprintf(translate("%s chars max"), "64") ?></td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("date") ?></td>
          <td class="field"><?php echo create_text_input("date", "", 12, 10) ?></td>
          <td class="inputhint">YYYY-MM-DD</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("rating") ?></td>
          <td class="field">
            <?php echo create_rating_pulldown("") ?>
          </td>
          <td class="inputhint">1 - 10</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("photographer") ?></td>
          <td class="field" colspan="2">
<?php echo create_smart_pulldown("photographer_id", "", get_people_select_array()) ?>
          </td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("level") ?></td>
          <td class="field"><?php echo create_text_input("level", "", 4, 2) ?></td>
          <td class="inputhint">1 - 10</td>
        </tr>
        <tr>
          <td class="fieldtitle"><?php echo translate("description") ?></td>
          <td class="field" colspan="2">
            <textarea name="description" cols="60" rows="4"></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3">
            <input type="submit" value="<?php echo translate($action, 0) ?>">
          </td>
        </tr>
<?php
    } // end import fields
    
    
    flush();

    // do the import down here
    if ($_action == "import") {
        echo "<tr>\n<td>\n";
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

        if ($path) {
            // reject these paths
            if (strpos($path, '..') === true) {
                echo translate("Invalid path") . ": $path<br>\n";
                $path = null;
            }

            $absolute_path = IMAGE_DIR . $path;

            if (file_exists($absolute_path) == false) {
                if(mkdir($absolute_path, DIR_MODE)) {
                    echo translate("Created directory") . ": $absolute_path<br>\n";
                }
                else {
                    echo translate("Could not create directory") . ": $absolute_path<br>\n";
                    $path = null;
                }
            }
        }

        if ($name && $path) {
            $tmp_name = $HTTP_POST_FILES['_image_local']['tmp_name'];
            $file = IMAGE_DIR . $path . '/' . $name;

            if (move_uploaded_file($tmp_name, $file)) {
                echo translate("Received file") . ": $file<br>\n";

                $ext = strtolower(file_extension($file));
                $expand = null;
                if ($ext == 'zip' && UNZIP_CMD) {
                    $expand = UNZIP_CMD;
                }
                else if ($ext == 'zip' && !UNZIP_CMD) {
                    echo translate("UNZIP_CMD is not set.") . "<br>\n";
                }
                else if ($ext == 'tar' && UNTAR_CMD) {
                    $expand = UNTAR_CMD;
                }
                else if ($ext == 'tar' && !UNTAR_CMD) {
                    echo translate("UNTAR_CMD is not set.") . "<br>\n";
                }

                if ($expand) {
                    $full_path = IMAGE_DIR . $path;

                    $tmp_path = EXTRACT_DIR . '/zoph' . time();
                    if (!mkdir($tmp_path, DIR_MODE)) {
                        echo translate("Could not create directory") . ": $tmp_path<br>\n";
                        return;
                    }

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
		echo translate("A possible cause is the upload_max_filesize variable in php.ini") . "<br>\n";
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
                    echo "<p>deleting " . $file;
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
        echo "</td>\n</tr>\n";
    }
?>
      </table>
     </form>
  </div>
<?php require_once("footer.inc.php"); ?>
