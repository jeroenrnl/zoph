<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $album_id = getvar("album_id");

    $album = new album($album_id);

    $obj = &$album;
    $redirect = "albums.php";
    require_once("actions.inc.php");

    if ($action == "display") {
        header("Location: " . add_sid("albums.php?parent_album_id=" . $album->get("album_id")));
    }

    if ($action != "insert") {
        $album->lookup();
        $title = $album->get("album");
    }
    else {
        $title = translate("New Album");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
<?php
    if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete album") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s' and its subalbums:"), $album->get("album")) ?>
          </td>
          <td align="right">[
            <a href="album.php?_action=confirm&album_id=<?= $album->get("album_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="album.php?_action=edit&album_id=<?= $album->get("album_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
<?php
    }
    else {
?>
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("album") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">[
            <a href="albums.php"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a> |
            <a href="album.php?_action=delete&album_id=<?= $album->get("album_id") ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<form action="album.php">
<input type="hidden" name="_action" value="<?= $action ?>">
<input type="hidden" name="album_id" value="<?= $album->get("album_id") ?>">
<?= create_field_html($album->get_edit_array()) ?>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="<?php echo translate($action, 0) ?>">
    </td>
  </tr>
</form>
      </table>
    </td>
  </tr>
<?php
    }
?>
</table>

</div>
<?php
    require_once("footer.inc.php");
?>
