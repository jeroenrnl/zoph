<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $title = translate("Color Schemes");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("color schemes") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">[
            <a href="color_scheme.php?_action=new"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<?php
    $color_schemes = get_records("color_scheme", "name");

    if ($color_schemes) {
        foreach($color_schemes as $cs) {
?>
        <tr>
          <td>
            <?= $cs->get("name") ?>
          </td>
          <td align="right">
            [ <a href="color_scheme.php?color_scheme_id=<?= $cs->get("color_scheme_id") ?>"><?php echo translate("view") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>
<?php
    require_once("footer.inc.php");
?>
