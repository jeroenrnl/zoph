<?php
    require_once("include.inc.php");

    $_off = getvar("_off");
    $_pause = getvar("_pause");
    $_random = getvar("_random");

    if (!$_off)  { $_off = 0; }
    $offset = $_off;

    $thumbnails;
    $num_photos = get_photos($request_vars, $offset, 1, $thumbnails, $user);

    $num_thumbnails = sizeof($thumbnails);

    if  ($num_thumbnails) {
        if ($_random) {
            $title = translate("random photo ") . ($offset + 1);
        }
        else {
            $title = sprintf(translate("photo %s of %s"),  ($offset + 1) , $num_photos);
        }
    }
    else {
        header("Location: " . add_sid("photos.php?" . update_query_string($request_vars, "_off", 0)));
    }

    $newoffset = $offset + 1;
    $new_qs = str_replace("_off=$offset", "_off=$newoffset", $QUERY_STRING);
    if (!$_pause) {
        header("Refresh: $SLIDESHOW_TIME;URL=$PHP_SELF?$new_qs");
    }
    else {
        $new_qs = str_replace("_pause=1", "", $new_qs);
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
?>
<html>
<head>
<title>Zoph - Slideshow</title>
</head>
<body bgcolor="<?= $PAGE_BG_COLOR ?>" text="<?= $TEXT_COLOR ?>" link="<?= $LINK_COLOR ?>" vlink="<?= $VLINK_COLOR ?>">
<div align="center">

<table border="0" cellpadding="1" cellspacing="0" bgcolor="<?= $TABLE_BORDER_COLOR ?>"<?= $table_width ?>>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?= $TITLE_FONT_COLOR ?>"><?= $title ?></font></th>
          <td align="right"><font color="<?= $TITLE_FONT_COLOR ?>">
          [
<?php
    if ($_pause) {
?>
            <a href="<?= $PHP_SELF . '?' . $new_qs ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("continue") ?></font></a> |
<?php
    }
    else {
?>
            <a href="<?= $PHP_SELF . '?' . $QUERY_STRING . '&' . "_pause=1" ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("pause") ?></font></a> |
<?php
    }
?>
            <a href="photos.php?<?= str_replace("_off=$offset", "_off=0", $QUERY_STRING) ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("stop") ?></font></a> |
            <a href="photo.php?<?= $QUERY_STRING ?>"><font color="<?= $TITLE_FONT_COLOR ?>"><?php echo translate("open") ?></font></a>
          ]
          </font></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?=$TABLE_BG_COLOR?>">
<?php
    if ($num_thumbnails <= 0) {
?>
        <tr>
          <td align="center">
       <?php echo translate("No photos were found for this slideshow.") ?>
          </td>
        </tr>
<?php
    }
    else {
        $photo = $thumbnails[0];
        $photo->lookup();
?>
        <tr>
          <td colspan="2" align="center">
            <font size="-1">
            <?= $photo->get_fullsize_link($photo->get("name")) ?> :
            <?= $photo->get("width") ?> x <?= $photo->get("height") ?>,
         <?= $photo->get("size") ?> <?php echo translate("bytes") ?>
            </font>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <?= $photo->get_fullsize_link($photo->get_midsize_img()) ?>
          </td>
        </tr>
<?php
        if ($people_links = get_photo_person_links($photo)) {
?>
        <tr>
          <td colspan="2" align="center">
            <font size="-1">
            <?= $people_links ?>
            </font>
          </td>
        </tr>
<?php
        }
?>
<?= create_field_html($photo->get_display_array(), 2) ?>
<?php
        if ($photo->get("description")) {
?>
        <tr>
          <td colspan="2" align="center">
            <hr width="80%">
            <?= $photo->get("description") ?>
          </td>
        </tr>
<?php
        }
    } // if photos
?>
      </table>
    </td>
  </tr>
</table>
</div>

<?php require_once("footer.inc.php"); ?>
