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
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("places") ?></h1></th>
          <td class="actionlink">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="place.php?_action=new"><?php echo translate("new") ?></a>
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
        <tr>
          <td class="letter" colspan="2">[
<?php
    for ($l = 'a'; $l < 'z'; $l++) {
        $title = $l;
        if ($l == $_l) {
            $title = "<span class=\"selected\">" . strtoupper($title) . "</span>";
        }
?>
            <a href="places.php?_l=<?php echo $l ?>"><?php echo $title ?></a> |
<?php
    }
?>
            <a href="places.php?_l=z"><?php echo $_l == "z" ? "<strong>Z</strong>" : "z" ?></a> |
            <a href="places.php?_l=no%20city"><?php echo translate("no city") ?></a> |
            <a href="places.php?_l=all"><?php echo translate("all") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
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
          <td class="place">
            <?php echo $p->get("city") ? $p->get("city") : "&nbsp;" ?>
          </td>
<?php
        if ($user->is_admin() || $user->get("detailed_people")) {
?>
          <td>
            <?php echo $p->get("address") ? $p->get("address") : "&nbsp;" ?>
          </td>
<?php
        }
?>
          <td>
            <?php echo $p->get("title") ? "\"" . $p->get("title") . "\"" : "&nbsp;" ?>
          </td>
          <td class="actionlink">
            [ <a href="place.php?place_id=<?php echo $p->get("place_id") ?>"><?php echo translate("view") ?></a> | <a href="photos.php?location_id=<?php echo $p->get("place_id") ?>"><?php echo translate("photos at") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
    else {
?>
        <tr>
          <td class="center"><?php echo sprintf(translate("No places were found in a city beginning with '%s'."), $_l) ?></td>
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
