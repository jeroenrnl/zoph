<?php
    require_once("include.inc.php");

    if (!$user->is_admin() && !$user->get("browse_places")) {
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

    $title = translate("Places");
    //$table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("places") ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="place.php?_action=new"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("new") ?></font></a>
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
        <tr>
          <td colspan="2" align="center">[
<?php
    for ($l = 'a'; $l < 'z'; $l++) {
        $title = $l;
        if ($l == $_l) {
            $title = "<strong>" . strtoupper($title) . "</strong>";
        }
?>
            <a href="places.php?_l=<?= $l ?>"><?= $title ?></a> |
<?php
    }
?>
            <a href="places.php?_l=z"><?= $_l == "z" ? "<strong>Z</strong>" : "z" ?></a> |
            <a href="places.php?_l=no%20city"><?php echo translate("no city") ?></a> |
            <a href="places.php?_l=all"><?php echo translate("all") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<?php
    $constraints = null;
    if ($_l == "all") {
        // no constraint
    }
    else if ($_l == "no city") {
        $constraints["city#1"] = "null";
        $ops["city#1"] = "is";
        $constraints["city#2"] = "''";
    }
    else {
        $constraints["lower(city)"] = "$_l%";
        $ops["lower(city)"] = "like";
    }

    $plcs = get_places($constraints, "or", $ops);

    if ($plcs) {
        foreach($plcs as $p) {
?>
        <tr>
          <td>
            <?= $p->get("city") ? $p->get("city") : "&nbsp;" ?>
          </td>
<?php
        if ($user->is_admin() || $user->get("detailed_people")) {
?>
          <td>
            <?= $p->get("address") ? $p->get("address") : "&nbsp;" ?>
          </td>
<?php
        }
?> 
          <td>
            <?= $p->get("title") ? "\"" . $p->get("title") . "\"" : "&nbsp;" ?>
          </td>
          <td align="right">
            [ <a href="place.php?place_id=<?= $p->get("place_id") ?>"><?php echo translate("view") ?></a> | <a href="photos.php?location_id=<?= $p->get("place_id") ?>"><?php echo translate("photos at") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
    else {
?>
        <tr>
          <td align="center"><?php echo sprintf(translate("No places were found in a city beginning with '%s'."), $_l) ?></td>
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
