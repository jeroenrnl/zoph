<?php
    require_once("config.inc.php");
    require_once("zoph_table.inc.php");
#    require_once("rtplang.class.php");
    require_once("user.inc.php");


    $user = new user();
#    $rtplang = $user->load_language();

#    print $rtplang->lang_header();
?>
<title><?php echo ZOPH_TITLE . ' - ' . "logon" ?></title>
</head>
<body bgcolor="<?=$PAGE_BG_COLOR?>">

<div align="center">

<table border="0" cellpadding="1" cellspacing="0" bgcolor="<?= $TABLE_BORDER_COLOR ?>" width="480">
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left" colspan="2"><?php echo "logon" ?></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
        <tr>
          <td colspan="2">
            <font size="+2"><strong><?= ZOPH_TITLE ?></strong></font>
          </td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
<form action="zoph.php" method="POST">
        <tr>
          <th><?php echo "username" ?></th>
          <td><input type="text" name="uname"></td>
        </tr>
        <tr>
          <th><?php echo "password" ?></th>
          <td><input type="password" name="pword"></td>
        </tr>
        <tr>
          <td colspan="2" align="center"><input type="submit" value="<?php echo "submit"; ?>"></td>
        </tr>
</form>
      </table>
    </td>
  </tr>
</table>

</div>

</body>
</html>
