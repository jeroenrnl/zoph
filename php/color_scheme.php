<?php
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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
<?php
    if ($action == "display") {
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("color scheme") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="color_scheme.php?_action=edit&color_scheme_id=<?= $color_scheme->get("color_scheme_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("edit") ?></font></a> |
            <a href="color_scheme.php?_action=delete&color_scheme_id=<?= $color_scheme->get("color_scheme_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a> |
            <a href="color_scheme.php?_action=new"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]
<?php
        }
        else {
            echo "&nbsp;";
        }
?>
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<?php
        $colors = $color_scheme->get_display_array();
?>
        <tr>
          <td align="right" width="50%"><?php echo translate("Name") ?></td>
          <td width="50%"><?= $color_scheme->get("name") ?></td>
        </tr>
<?php
        while (list($name, $value) = each($colors)) {
            if ($name == "Name") { continue; }
?>
        <tr>
          <td align="right"><?= $name ?></td>
          <td>
            <table width="50%">
              <tr>
                <td width="50%"><?= $value ?></td>
                <td width="50%" bgcolor="#<?= $value ?>">&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>
<?php
        }
    }
    else if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete color scheme") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $color_scheme->get("name")) ?>:
          </td>
          <td align="right">[
            <a href="color_scheme.php?_action=confirm&color_scheme_id=<?= $color_scheme->get("color_scheme_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="color_scheme.php?_action=display&color_scheme_id=<?= $color_scheme->get("color_scheme_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
<?php
    }
    else {
        $colors = $color_scheme->get_edit_array();
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("color scheme") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">[
            <a href="color_schemes.php"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td align="right" width="50%">Name</td>
          <td width="50%">
<form action="color_scheme.php">
<input type="hidden" name="_action" value="<?= $action ?>">
<input type="hidden" name="color_scheme_id" value="<?= $color_scheme->get("color_scheme_id") ?>">
<?= create_text_input("name", $color_scheme->get("name"), 16, 64) ?>
          </td>
        </tr>
<?php
        while (list($name, $value) = each($colors)) {
            if ($name == "Name") { continue; }
            $bg = preg_replace('/.*value="([^"]+)".*\n/', '$1', $value);
?>
        <tr>
          <td align="right"><?= $name ?></td>
          <td>
            <table width="50%">
              <tr>
                <td width="50%"><?= $value ?></td>
                <td width="50%"<?php $action != "insert" ? " bgcolor=\"#$bg\"" : "" ?>>&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>
<?php
        }
?>
        <tr>
          <td colspan="2" align="center">
<input type="submit" value="<?php echo translate($action, 0) ?>">
</form>
          </td>
        </tr> 
<?php
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>

<?php require_once("footer.inc.php"); ?>
