<!---- begin edit_place.inc ---->
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo $_action ?> <?php echo translate("place") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">[
            <a href="places.php"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("return") ?></font></a> |
            <a href="place.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
          ]</font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
<form action="place.php" method="GET">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="place_id" value="<?php echo $place->get("place_id") ?>">
        <tr>
          <td><?php echo translate("title") ?></td>
          <td><?php echo create_text_input("title", $place->get("title"), 40, 40) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("address") ?></td>
          <td><?php echo create_text_input("address", $place->get("address"), 40, 40) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("address continued") ?></td>
          <td><?php echo create_text_input("address2", $place->get("address2"), 40, 40) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "64") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("city") ?></td>
          <td><?php echo create_text_input("city", $place->get("city"), 32, 32) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("state") ?></td>
          <td><?php echo create_text_input("state", $place->get("state"), 16, 32) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("zip") ?></td>
          <td><?php echo create_text_input("zip", $place->get("zip"), 10, 10) ?></td>
          <td><font size="-1"><?php echo translate("zip or zip+4") ?></font></td>
        </tr>
        <tr>
          <td><?php echo translate("country") ?></td>
          <td><?php echo create_text_input("country", $place->get("country"), 32, 32) ?></td>
          <td><font size="-1"><?php echo sprintf(translate("%s chars max"), "32") ?></font></td>
        </tr>
        <tr>
          <td valign="top"><?php echo translate("notes") ?></td>
          <td colspan="2"><textarea name="notes" cols="40" rows="4"><?php echo $place->get("notes") ?></textarea></td>
        </tr>
        <tr>
          <td colspan="3" align="center"><input type="submit" value="<?php echo translate($action, 0) ?>"></td>
        </tr>
</form>
<!---- end edit_person.inc ---->
