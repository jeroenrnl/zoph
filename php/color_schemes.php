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

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $title = translate("Color Schemes");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
          <h1>
          <span class="actionlink">
            <a href="color_scheme.php?_action=new"><?php echo translate("new") ?></a>
          </span>
          <?php echo translate("color schemes") ?>
          </h1>
      <div class="main">
<?php
    $color_schemes = get_records("color_scheme", "name");

    if ($color_schemes) {
        foreach($color_schemes as $cs) {
?>
          <span class="actionlink">
            <a href="color_scheme.php?color_scheme_id=<?php echo $cs->get("color_scheme_id") ?>"><?php echo translate("view") ?></a>
          </span> 
            <?php echo $cs->get("name") ?>
          <br>
<?php
        }
    }
?>
</div>
<?php
    require_once("footer.inc.php");
?>
