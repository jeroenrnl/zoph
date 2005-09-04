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

    $title = translate("About");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("about") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="info">
      <col><col>
        <tr>
          <td colspan="2">
            <h2>zoph</h2>
          </td>
        </tr>
        <tr>
          <td colspan="2">
<p>
<?php echo translate("Zoph stands for <strong>z</strong>oph <strong>o</strong>rganizes <strong>ph</strong>otos.", 0) ?>

<?php echo translate("Zoph is free software.", 0) ?>
</p>
<p>
<?php echo sprintf(translate("Releases and documentation can be found at %s.", 0), "<a href=\"http://www.nother.net/zoph/\">http://www.nother.net/zoph/</a>") ?>

<?php echo sprintf(translate("Send feedback to %s.", 0), "<a href=\"mailto:zoph@nother.net\">zoph@nother.net</a>") ?>
</p>
          </td>
        </tr>
<?php
    if ($user->is_admin()) {
?>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
<?php echo create_field_html(get_zoph_info_array()) ?>
<?php
    }
?>
        <tr>
          <td colspan="2">
            <hr>
          </td>
        </tr>
        <tr>
          <td colspan="2">
<p>
<?php echo sprintf(translate("Zoph version %s, released %s.", 0), VERSION, "September 2005") ?>
</p>
<p>
<?php echo translate("Written by Jason Geiger with thanks to the following for their contributions:", 0) ?>
</p>
<?php include('credits.html'); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<?php
    require_once("footer.inc.php");
?>
