<?php
    if ($SHOW_BREADCRUMBS) {

    $_clear_crumbs = getvar("_clear_crumbs");
    $_crumb = getvar("_crumb");

    // construct the link for clearing the crumbs (the 'x' on the right)
    $clear_url = $REQUEST_URI;
    if (strpos($clear_url, "?") > 0) {
        $clear_url .= "&";
    }
    else {
        $clear_url .= "?";
    }
    $clear_url .= "_clear_crumbs=1";

    if ($_clear_crumbs) {
        $user->eat_crumb(0);
    }
    else if ($_crumb) {
        $user->eat_crumb($_crumb);
    }

    // only add a crumb if a title was set and if there is either no
    // action or a safe action ("edit", "delete", etc would be unsafe)
    if ($title && count($user->crumbs) < MAX_CRUMBS &&
        (!$_action || ($_action == "display" || $_action == "search"))) {

        $user->add_crumb($title, $REQUEST_URI);
    }

    if (!$user->crumbs) {
        $crumb_string = "&nbsp;";
    }
    else if (($num_crumbs = count($user->crumbs)) > $MAX_CRUMBS_TO_SHOW) {
        $crumb_string = " ... &gt; " .  implode(" &gt; ",
            array_slice($user->crumbs, $num_crumbs - $MAX_CRUMBS_TO_SHOW));
    }
    else {
        $crumb_string = implode(" &gt; ", $user->crumbs);
    }
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$BREADCRUMB_BG_COLOR?>">
        <tr>
          <td><font size="-1">
<?= $crumb_string ?>
          </font></td>
          <td align="right"><font size="-2">
          <a href="<?= $clear_url ?>">x</a>
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
    }
?>
