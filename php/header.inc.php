<?php
// Very important, rtp must send header and html start tags !
print $rtplang->lang_header();
?>
<link TYPE="text/css" REL="stylesheet" HREF="<?php echo CSS_SHEET ?>">

<title><?php echo ZOPH_TITLE . ($title ? " - $title" : "") ?></title>
</head>
<body>

<table class="page">
  <tr class="menu">
    <td class="menu">
      <table class="menu">
        <tr>
          <td class="menu">&nbsp;</td>

<?php
    $tabs = array(
        translate("home", 0) => "zoph.php",
        translate("albums", 0) => "albums.php",
        translate("categories", 0) => "categories.php");

    if ($user->is_admin() || $user->get("browse_people")) {
        $tabs[translate("people", 0)] = "people.php";
    }

    if ($user->is_admin() || $user->get("browse_places")) {
        $tabs[translate("places", 0)] = "places.php";
    }

    $tabs[translate("photos", 0)] = "photos.php";

    if ($user->get("lightbox_id")) {
        $tabs[translate("lightbox", 0)] = "photos.php?album_id=" .
            $user->get("lightbox_id");
    }

    $tabs[translate("search",0)] = "search.php";

    if ((CLIENT_WEB_IMPORT || SERVER_WEB_IMPORT) &&
        ($user->is_admin() || $user->get("import"))) {

        $tabs[translate("import", 0)] = "import.php";
    }

    if ($user->is_admin()) {
        $tabs[translate("users", 0)] = "users.php";
    }

    $tabs += array(
        translate("reports", 0) => "reports.php",
        translate("prefs", 0) => "prefs.php",
        translate("about", 0) => "info.php");

    if ($user->get("user_id") == DEFAULT_USER) {
        $tabs[translate("logon", 0)] = "zoph.php?_action=logout";
    }
    else {
        $tabs[translate("logout", 0)] = "zoph.php?_action=logout";
    }

    if (strpos($PHP_SELF, "/") === false) {
        $self = $PHP_SELF;
    }
    else {
        $self = substr(strrchr($PHP_SELF, "/"), 1);
    }

    while (list($label, $page) = each($tabs)) {
        if ($page == $self) {
            $class = "selectedtab";
        }
        else {
             $class = "tab";
        }
?>
          <td class="<?php echo $class ?>">
            <a href="<?php echo $page ?>"><?php echo $label ?></a>
          </td>
<?php
    }
?>
        </tr>
      </table>
    </td>
  </tr>
<?php
require_once("breadcrumbs.inc.php");
?>
