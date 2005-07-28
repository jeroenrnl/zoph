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
        $_action = "display";
    }

    $color_scheme_id = getvar("color_scheme_id");

    $color_scheme = new color_scheme($color_scheme_id);

    $obj = &$color_scheme;
    $redirect = "color_schemes.php";
    require_once("actions.inc.php");

    if ($_action == "update") {
        $user->prefs->load();
    }

    if ($action != "insert") {
        $color_scheme->lookup();
        $title = $color_scheme->get("name");
    }
    else {
        $title = translate("New Color Scheme");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
<?php
    if ($action == "display") {
?>
        <tr>
          <th><h1><?php echo translate("color scheme") ?></h1></th>
          <td class="actionlink">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="color_scheme.php?_action=edit&amp;color_scheme_id=<?php echo $color_scheme->get("color_scheme_id") ?>"><?php echo translate("edit") ?></a> |
            <a href="color_scheme.php?_action=delete&amp;color_scheme_id=<?php echo $color_scheme->get("color_scheme_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="color_scheme.php?_action=new"><?php echo translate("new") ?></a>
          ]
<?php
        }
        else {
            echo "&nbsp;";
        }
?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
<?php
        $colors = $color_scheme->get_display_array();
?>
        <tr>
          <td>
            <table class="colors">
                <tr>
                    <th class="fieldtitle"><?php echo translate("Name") ?></th>
                    <th><?php echo $color_scheme->get("name") ?></th>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
<?php
        while (list($name, $value) = each($colors)) {
            if ($name == "Name") { continue; }
?>
                <tr>
                  <th class="fieldtitle"><?php echo $name ?></th>
                  <td><?php echo $value ?></td>
                  <td style="width: 60px; background: #<?php echo $value ?>;">&nbsp;</td>
                </tr>
<?php
        } ?>
              </table></td></tr>
<?php    }
    else if ($action == "confirm") {
?>
        <tr>
          <th><h1><?php echo translate("delete color scheme") ?></h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $color_scheme->get("name")) ?>:
          </td>
          <td class="actionlink">[
            <a href="color_scheme.php?_action=confirm&amp;color_scheme_id=<?php echo $color_scheme->get("color_scheme_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="color_scheme.php?_action=display&amp;color_scheme_id=<?php echo $color_scheme->get("color_scheme_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
<?php
    }
    else {
        $colors = $color_scheme->get_edit_array();
?>
        <tr>
          <th><h1><?php echo translate("color scheme") ?></h1></th>
          <td class="actionlink">[
            <a href="color_schemes.php"><?php echo translate("return") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<form action="color_scheme.php">
      <table class="main">
        <tr>
         <td>
        <table class="colors">
        <tr>
          <td class="fieldtitle">Name</td>
          <td>
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="color_scheme_id" value="<?php echo $color_scheme->get("color_scheme_id") ?>">
<?php echo create_text_input("name", $color_scheme->get("name"), 16, 64) ?>
          </td>
        </tr>

<?php
        while (list($name, $value) = each($colors)) {
            if ($name == "Name") { continue; }
            $bg = preg_replace('/.*value="([^"]+)".*\n/', '$1', $value);
?>
        <tr>
          <td class="fieldtitle"><?php echo $name ?></td>
                <td><?php echo $value ?></td>
                <td style='width: 60px; <?php echo $action != "insert" ? " background: #$bg" : "" ?>' >&nbsp;</td>
        </tr>
<?php
        }
?>
</table></td></tr>
        <tr>
          <td colspan="2" class="center">
<input type="submit" value="<?php echo translate($action, 0) ?>">
          </td>
        </tr>
<?php
    }
?>
      </table>
<?php echo ( $action == "" || $action == "display" || $action == "delete" || $action == "confirm" ) ? "" : "</form>"; ?>
    </td>
  </tr>
</table>

<?php require_once("footer.inc.php"); ?>
