<?php
    require_once("include.inc.php");

    $parent_album_id = getvar("parent_album_id");

    if (!$parent_album_id) {
        $album = get_root_album();
    }
    else {
        $album = new album($parent_album_id);
    }
    $album->lookup($user);
    $ancestors = $album->get_ancestors();
    $children = $album->get_children($user);

    $photo_count = $album->get_total_photo_count($user);

    $title = $album->get("parent_album_id") ? $album->get("album") : translate("Albums");

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("albums") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
<?php
    if ($user->is_admin()) {
?>
            [ <a href="album.php?_action=new&parent_album_id=<?php echo $album->get("album_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a> ]
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <th align="left">
<?php
    if ($ancestors) {
        while ($parent = array_pop($ancestors)) {
?>
            <?php echo $parent->get_link() ?> &gt;
<?php
        }
    }
?>
            <?php echo $title ?>
          </th>
          <td align="right">
<?php
    if ($user->is_admin()) {
?>
          [
            <a href="album.php?_action=edit&album_id=<?php echo $album->get("album_id") ?>"><?php echo translate("edit") ?></a>
          ]
<?php
    }
    else {
        echo "&nbsp;\n";
    }
?>
          </td>
        </tr>
<?php
    if ($album->get("album_description")) {
?>
        <tr>
          <td colspan="2">
            <?php echo $album->get("album_description") ?>
          </td>
        </tr>
<?php
    }
?>
        <tr>
          <td>
<?php
    $fragment = translate("in this album");
    if ($photo_count > 0) {
        if (!$album->get("parent_album_id")) { // root album
            $fragment = translate("available");
        }
        else {
            if ($children) {
                $fragment .= " " . translate("or its children");
            }
        }

    if ($photo_count > 1) {
      echo sprintf(translate("There are %s photos"), $photo_count);
      echo " $fragment.";
    }
    else {
      echo sprintf(translate("There is %s photo"), $photo_count);
      echo " $fragment.";
    }
?>
          </td>
          <td align="right">
            [ <a href="photos.php?album_id=<?php echo $album->get_branch_ids($user) ?>"><?php echo translate("view photos") ?></a> ]
          </td>
<?php
    }
    else {
?>
          <?php echo translate("There are no photos") ?> <?php echo $fragment ?>.
          </td>
          <td align="right">&nbsp;</td>
<?php
    }
?>
        </tr>
<?php
    if ($children) {
?>
        <tr>
          <td colspan="2">
<?php
        foreach($children as $a) {
?>
            <li>
            <a href="albums.php?parent_album_id=<?php echo $a->get("album_id") ?>"><?php echo $a->get("album") ?></a>
            </li>
<?php
        }
?>
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
<?php
    require_once("footer.inc.php");
?>
