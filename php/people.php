<?php
    require_once("include.inc.php");

    if (!$user->is_admin() && !$user->get("browse_people")) {
        header("Location: " . add_sid("zoph.php"));
    }

    $_l = getvar("_l");

    if (empty($_l)) {
        if (DEFAULT_SHOW_ALL) {
            $_l = "all";
        }
        else {
            $_l = "a";
        }
    }

    $title = translate("People");
    //$table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("people") ?></font></th>
          <td align="right"><font color="<?php echo $TITLE_FONT_COLOR ?>">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="person.php?_action=new"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
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
          <td colspan="2" align="center">[
<?php
    for ($l = 'a'; $l < 'z'; $l++) {
        $title = $l;
        if ($l == $_l) {
            $title = "<strong>" . strtoupper($title) . "</strong>";
        }
?>
            <a href="people.php?_l=<?php echo $l ?>"><?php echo $title ?></a> |
<?php
    }
?>
            <a href="people.php?_l=z"><?php echo $_l == "z" ? "<strong>Z</strong>" : "z" ?></a> |
            <a href="people.php?_l=no%20last%20name"><?php echo translate("no last name") ?></a> |
            <a href="people.php?_l=all"><?php echo translate("all") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<?php
    $constraints = null;
    if ($_l == "all") {
        // no contraint
    }
    else if ($_l == "no last name") {
        $constraints["last_name#1"] = "null";
        $ops["last_name#1"] = "is";
        $constraints["last_name#2"] = "''";
    }
    else {
        $constraints["lower(last_name)"] = "$_l%";
        $ops["lower(last_name)"] = "like";
    }

    $ppl = get_people($constraints, "or", $ops);

    if ($ppl) {
        foreach($ppl as $p) {
?>
        <tr>
          <td>
            <a href="person.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo $p->get("last_name") ? $p->get("last_name") . ", " : "" ?><?php echo $p->get("first_name") ?></a>
          </td>
          <td align="right">
            [ <a href="person.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo translate("view") ?></a> | <a href="photos.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo translate("photos of") ?></a> | <a href="photos.php?photographer_id=<?php echo $p->get("person_id") ?>"><?php echo translate("photos by") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
    else {
?>
        <tr>
          <td colspan="2" align="center"><?php echo sprintf(translate("No people were found with a last name beginning with '%s'."), $_l) ?></td>
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
