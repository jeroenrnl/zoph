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
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("people") ?></h1></th>
          <td class="actionlink">
<?php
        if ($user->is_admin()) {
?>
          [
            <a href="person.php?_action=new"><?php echo translate("new") ?></a>
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
    for ($l = 'a'; $l <= 'z' && $l != 'aa'; $l++) {
        $title = $l;
        if ($l == $_l) {
            $title = "<span class=\"selected\">" . strtoupper($title) . "</span>";
        }
?>
            <a href="people.php?_l=<?php echo $l ?>"><?php echo $title ?></a> |
<?php
    }
?>
<?php //            <a href="people.php?_l=z"><?php echo $_l == "z" ? "<strong>Z</strong>" : "z" </a> | ?>
            <a href="people.php?_l=no%20last%20name"><?php echo translate("no last name") ?></a> |
            <a href="people.php?_l=all"><?php echo translate("all") ?></a>
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
          <td class="person">
            <a href="person.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo $p->get("last_name") ? $p->get("last_name") . ", " : "" ?><?php echo $p->get("first_name") ?></a>
          </td>
          <td class="actionlink">
            [ <a href="person.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo translate("view") ?></a> | <a href="photos.php?person_id=<?php echo $p->get("person_id") ?>"><?php echo translate("photos of") ?></a> | <a href="photos.php?photographer_id=<?php echo $p->get("person_id") ?>"><?php echo translate("photos by") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
    else {
?>
        <tr>
          <td colspan="2" class="center"><?php echo sprintf(translate("No people were found with a last name beginning with '%s'."), $_l) ?></td>
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
