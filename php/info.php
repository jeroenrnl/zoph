<?php
    require_once("include.inc.php");

    $title = translate("About");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("about") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td colspan="2" align="center">
            <font size="+2"><strong>zoph</strong>
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
<p>
<?php echo sprintf(translate("Zoph version %s, released %s.", 0), VERSION, "13 December 2002") ?>
</p>
<p>
<?php echo translate("Written by Jason Geiger with thanks to the following for their contributions:", 0) ?>

Eric Seigne (internationalization),
David Baldwin (PHP 4.2 compatibility and fixes),
Francisco J. Montilla (bugfixes and improvements),
Jan Miczaika (importer),
Donald Gover (bugfixes),
Matthew MacIntyre (bugfixes),
David Moulton (improvements),
Alan Shutko (improvements),
Christian Hoenig (improvements),
Francesco Ciattaglia (translation),
Haavard Leonardo Lund (translation),
Alvaro González Crespo (translation),
Tetsuji Kyan (bugfixes),
Ian Kerr (bugfixes),
Mark Cooper (man page).
</p>
          </td>
        </tr>
<?php
    if ($user->is_admin()) {
?>
        <tr>
      <td width="50%">&nbsp;</td>
      <td width="50%">&nbsp;</td>
        </tr>
<?= create_field_html(get_zoph_info_array()) ?>
<?php
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
