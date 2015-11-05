<?php
/**
 * Show overview of comments
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
 * @package Zoph
 * @author Jeroen Roos
 */
require_once "include.inc.php";

if (!conf::get("feature.comments")) {
    redirect("zoph.php");
}

require_once "header.inc.php";
?>
<h1>
  <?php echo translate("Comments") ?>
</h1>
<div class="main">
  <br>
<?php
$comments=comment::getRecords();
foreach ($comments as $comment) {
    $photo=$comment->getPhoto();
    if($user->get_permissions_for_photo($photo->get("photo_id")) || $user->is_admin()) {
        echo $comment->toHTML(true);
    }
}
require_once "footer.inc.php";
?>
