<?php
    if ($SHOW_BREADCRUMBS) {

    $_clear_crumbs = getvar("_clear_crumbs");
    $_crumb = getvar("_crumb");

    // construct the link for clearing the crumbs (the 'x' on the right)
    $clear_url = htmlentities($REQUEST_URI);
    if (strpos($clear_url, "?") > 0) {
        $clear_url .= "&amp;";
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
    if (!$skipcrumb && $title && count($user->crumbs) < MAX_CRUMBS &&
        (!$_action || ($_action == "display" || $_action == "search" ||
        $_action == "notify" || $_action == "compose"))) {

        $user->add_crumb($title, htmlentities($REQUEST_URI));
    }

    if (!$user->crumbs) {
        $crumb_string = "&nbsp;";
    }
    else if (($num_crumbs = count($user->crumbs)) > $MAX_CRUMBS_TO_SHOW) {
        $crumb_string = "<li class=\"firstdots\">" .  implode(" <li>",
            array_slice($user->crumbs, $num_crumbs - $MAX_CRUMBS_TO_SHOW));
    }
    else {
        $crumb_string = "<li class=\"first\">" . implode("<li>", $user->crumbs);
    }
?>
  <tr>
    <td id="breadcrumb">
    <ul><?php echo $crumb_string ?></ul>
          <div class="actionlink"><a href="<?php echo $clear_url ?>">x</a></div>
    </td>
  </tr>
<?php
    }
?>
