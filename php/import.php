<?php
/**
 * This file is the controller part of the import functions
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

require_once "include.inc.php";
if ((!conf::get("import.enable")) || (!$user->is_admin() && !$user->get("import"))) {
        redirect("zoph.php");
}

// Detect upload larger than upload_max_filesize.
if(isset($_GET["upload"]) && $_GET["upload"]==1 && $_POST==null) {
    echo WebImport::handleUploadErrors(UPLOAD_ERR_INI_SIZE);
    die();
}
$_action=getvar("_action");

$title = translate("Import");

if (empty($_action)) {
    require_once "header.inc.php";
}

session_write_close();

// Generate an id for the uploads so multiple simultanious uploads will
// not clash.
$upload_id=getvar("upload_id");
if(empty($upload_id)) {
    $upload_id=uniqid("zoph_");
} else {
    if(!preg_match("/^[A-Za-z0-9_]+$/", $upload_id)) {
        log::msg("Illegal characters in upload_id", log::FATAL, log::IMPORT);
    }
}

$num=escape_string(getvar("num"));
if($num && !is_numeric($num)) {
    log::msg("num must be numeric", log::FATAL, log::IMPORT);
} else if (!$num) {
    $num=1;
}


if(empty($_action)) {
    $javascript=
        "translate=new Array();\n" .
        "translate['retry']='" .trim(translate("retry", false)) . "';\n" .
        "translate['delete']='" .trim(translate("delete", false)) . "';\n" .
        "translate['import']='" .trim(translate("import", false)) . "';\n" .
        "upload_id='" . $upload_id ."';\n" .
        "num=" . $num . ";\n" .
        "parallel=" . (int) conf::get("import.parallel")  . ";\n";

    $tpl=new template("import", array(
        "upload_id" => $upload_id,
        "num" => $num,
        "javascript" => $javascript,
    ));
    $tpl->js=array("js/util.js", "js/xml.js", "js/import.js");
    echo $tpl;
    include "footer.inc.php";
} else if ($_action=="browse") {
    if(conf::get("import.upload")) {
        $upload_num = $upload_id . "_" . $num;

        $body=new block("uploadform", array(
            "progress"  => WebImport::getProgressName(),
            "action"    => "import.php?upload=1",
            "onsubmit"  => "zImport.startUpload(this, upload_id, num); return true",
            "num"       => $num,
            "upload_num" => $upload_num));

        $tpl=new template("html", array(
            "html_class" => "iframe_upload",
            "body" => $body));
        $tpl->js=array("js/import.js", "js/xml.js");
        $tpl->script="upload_id='" . $upload_id . "';" .
            "num='" . $num . "';";

        echo $tpl;
        $tpl=new template("uploadprogressbar", array(
            "name" => "",
            "size" => 0,
            "upload_num" => $upload_num,
            "complete"  => 0,
            "width" => 300));
        echo $tpl;
    } else {
        echo translate("Uploading photos has been disabled in configuration.");
    }
    ?>
      </body>
    </html>
    <?php
} else if ($_action=="upload") {
    if(conf::get("import.upload")) {
        if($_FILES["file"]) {
            $file=$_FILES["file"];
        }
        $upload_num=getvar(WebImport::getProgressName());

        WebImport::processUpload($file);

        $body=new template("uploadprogressbar", array(
            "name" => $file["name"],
            "size" => getHuman($file["size"]),
            "upload_num" => $upload_num,
            "complete" => 100,
            "width" => 300));

        $tpl=new template("html", array(
            "html_class" => "iframe_upload",
            "body" => $body,
            "body_attr" => "onload=\"zImport.deleteIframe('upload_" . $num . "');\""));
        $tpl->js=array("js/import.js", "js/xml.js");
        $tpl->style="div.uploadprogress { display: block; }";
        echo $tpl;
    }
} else if ($_action=="process") {
    $file=getvar("file");
    WebImport::processFile($file);
} else if ($_action=="retry") {
    $file=getvar("file");
    WebImport::retryFile($file);
} else if ($_action=="delete") {
    $file=getvar("file");
    WebImport::deleteFile($file);
} else if ($_action=="import") {
    $files=WebImport::getFileList($request_vars["_import_image"]);
    WebImport::photos($files, $request_vars);
}
