<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        $_action = "display";
    }

    $place_id = getvar("place_id");

    $place = new place($place_id);

    $obj = &$place;
    $redirect = "places.php";
    require_once("actions.inc.php");

    if ($action != "insert") {
        $place->lookup();
        $title = $place->get("title") ? $place->get("title") : $place->get("city");
    }
    else {
        $title = translate("New Place");
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
<?php
    if ($action == "display") {

        $vars["location_id"] = $place->get("place_id");
        $photos_at = get_photos($vars, 0, 1, $ignore, $user);
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("place") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="place.php?_action=edit&place_id=<?php echo $place->get("place_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("edit") ?></font></a> |
            <a href="place.php?_action=delete&place_id=<?php echo $place->get("place_id") ?>"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete") ?></font></a> |
            <a href="place.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <td align="left">
<?php
    if ($user->get("detailed_places")) {
?>
            <?php echo $place->to_html() ?>
<?php
    }
    else {
?>
            <?php echo $title ?>
<?php
    }
?>
          </td>
          <td align="right" valign="top">[
            <a href="photos.php?location_id=<?php echo $place->get("place_id") ?>"><?php echo "$photos_at " . translate("photos at") ?></a>
          ]</td>
        </tr>
<?php
        if ($user->get("detailed_places") && $place->get("notes")) {
?>
        <tr>
          <td colspan="2"><?php echo $place->get("notes") ?></th>
        </tr>
<?php
        }
    }
    else if ($action == "confirm") {
?>
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("delete place") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
        <tr>
          <td>
            <?php echo sprintf(translate("Confirm deletion of '%s'"), $title) ?>:
          </td>
          <td align="right">[
            <a href="place.php?_action=confirm&place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("delete") ?></a> |
            <a href="place.php?_action=display&place_id=<?php echo $place->get("place_id") ?>"><?php echo translate("cancel") ?></a>
          ]</td>
        </tr>
<?php
    }
    else {
require_once("edit_place.inc.php");
    }
?>
      </table>
    </td>
  </tr>
</table>

</div>

<?php require_once("footer.inc.php"); ?>
