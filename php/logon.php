<?php
    require_once("config.inc.php");
    require_once("zoph_table.inc.php");
#    require_once("rtplang.class.php");
    require_once("user.inc.php");


    $user = new user();
#    $rtplang = $user->load_language();

#    print $rtplang->lang_header();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link TYPE="text/css" REL="stylesheet" HREF="<?php echo CSS_SHEET ?>?logged_on=no">
<title><?php echo ZOPH_TITLE . ' - ' . "logon" ?></title>
</head>
<body>

<table class="page">
  <tr>
    <td>
      <table class="titlebar">
        <tr>
  <th colspan="2"><h1><?php echo "logon" ?><h1></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>

      <form action="zoph.php" method="POST">
      <table class="main">
        <tr>
          <td colspan="2">
            <h2><?php echo ZOPH_TITLE ?></h2>
          </td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <th><?php echo "username" ?></th>
          <td><input type="text" name="uname"></td>
        </tr>
        <tr>
          <th><?php echo "password" ?></th>
          <td><input type="password" name="pword"></td>
        </tr>
        <tr>
          <td colspan="2" class="center"><input type="submit" value="<?php echo "submit"; ?>"></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
      </table>
      </form>
    </td>
  </tr>
</table>

</body>
</html>
