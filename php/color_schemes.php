<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    $title = translate("Color Schemes");
    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table class="titlebar">
        <tr>
          <th><h1><?php echo translate("color schemes") ?></h1></th>
          <td class="actionlink">[
            <a href="color_scheme.php?_action=new"><?php echo translate("new") ?></a>
          ]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table class="main">
<?php
    $color_schemes = get_records("color_scheme", "name");

    if ($color_schemes) {
        foreach($color_schemes as $cs) {
?>
        <tr>
          <td>
            <?php echo $cs->get("name") ?>
          </td>
          <td class="actionlink">
            [ <a href="color_scheme.php?color_scheme_id=<?php echo $cs->get("color_scheme_id") ?>"><?php echo translate("view") ?></a> ]
          </td>
        </tr>
<?php
        }
    }
?>
      </table>
    </td>
  </tr>
</table>

<?php
    require_once("footer.inc.php");
?>
