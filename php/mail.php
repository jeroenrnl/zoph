<?php
    require_once("include.inc.php");
    require_once("htmlMimeMail.php");

    $title = translate("E-Mail Photo");

    $photo_id = getvar("photo_id");
    $html = getvar("html");
    $to_name = getvar("to_name");
    $to_email = getvar("to_email");
    $from_name = getvar("from_name");
    $from_email = getvar("from_email");
    $subject = getvar("subject");
    $message = getvar("message");

    $annotate = getvar("annotate");

    if (!ANNOTATE_PHOTOS) {
        $annotate = 0;
    }

    // image will have been deleted if sent
    if ($annotate) {
        $skipcrumb = true;
    }

    $photo = new photo($photo_id);
    $found = $photo->lookup($user);

    if (!$found) {
        $msg = sprintf(translate("Could not find photo id %s."), $photo_id);
    }
    else {

        $subject = sprintf(translate("A Photo from %s"), ZOPH_TITLE) . ": " . $photo->get("name");

        $ea = $photo->get_email_array();
        if ($ea) {
            while (list($name, $value) = each($ea)) {
                if ($name && $value) {
                    $body .= "$name: $value\n";
                }
            }
        }

        if ($_action == "mail") {

            $mail = new htmlMimeMail(array('X-Mailer: Html Mime Mail Class'));

            $text = $body;
            $size = getvar("_size");

            if ($html) {

                if ($annotate) {
                    $file = $photo->get_annotated_file_name($user);
                    $dir = ANNOTATE_TEMP_DIR . "/";
                }
                else if ($size == "full") {
                    $file = $photo->get("name");
                    $dir = IMAGE_DIR . $photo->get("path") . "/";
                }
                else {
                    $file = MID_PREFIX . "_" . $photo->get("name");
                    $dir = IMAGE_DIR . $photo->get("path") . "/" .
                        MID_PREFIX . "/";
                }

                $html = str_replace("\n", "<br>\n", $body);
                $html =
                    "<center>\n" .
                    "<img src=\"" . $file .  "\"><br>\n" .
                    $html .  "</center>\n";

                $mail->sethtml($html, $text, $dir);
            }
            else {
                $mail->settext($text);

                if ($annotate) {
                    $file = ANNOTATE_TEMP_DIR . "/" .
                        $photo->get_annotated_file_name($user);
                }
                else if ($size == "full") {
                    $file = $photo->get_image_href(null, 1);
                }
                else {
                    $file = $photo->get_image_href(MID_PREFIX, 1);
                }

                $mail_file = $mail->getFile($file);
                $mail->addAttachment($mail_file, $photo->get("name"), get_image_type($file));
            }

            $mail->setFrom("$from_name <$from_email>");
            $mail->setSubject($subject);
            
            if (strlen(BCC_ADDRESS) > 0) {
                $mail->setBCC(BCC_ADDRESS);
            }

            if ($mail->send(array("$to_name <$to_email>"), 'smtp')) {
                $msg = translate("Your mail has been sent.");

                if ($annotate) {
                    unlink(ANNOTATE_TEMP_DIR . "/" . $photo->get_annotated_file_name($user));
                }
            }
            else {
                $msg = translate("Could not send mail.");
            }
        }

    }

    $from_name = $user->person->get_name();
    $from_email = $user->person->get_email();

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>

  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left">
  <?php echo translate("email photo") ?>
          </th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<?php
    if ($msg) {
?>
        <tr>
          <td colspan="2" align="center">
            <?php echo $msg ?>
          </td>
        </tr>
<?php
    }

    if ($found && $_action == "compose") {
        if ($annotate) {
            $photo->annotate($request_vars, $user);
        }

        if (ANNOTATE_PHOTOS) {
?>
        <tr>
          <td align="center" colspan="2">
            <a href="define_annotated_photo.php?photo_id=<?php echo $photo->get("photo_id") ?>"><?php echo translate("create annotated photo", 0) ?></a>
          </td>
        </tr>
<?php
        }
?>
        <tr>
          <td align="right">
<form action="<?php echo $PHP_SELF ?>" method="post">
<input type="hidden" name="_action" value="mail">
<input type="hidden" name="photo_id" value="<?php echo $photo_id ?>">
<input type="hidden" name="annotate" value="<?php echo $annotate ?>">
       <?php echo translate("send as html") ?>
          </td>
          <td>
            <?php echo create_pulldown("html", "1", array("1" => translate("Yes",0), "0" => translate("No",0))) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("to (name)") ?></td>
          <td>
            <?php echo create_text_input("to_name", $to_name, 24, 32) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("to (email)") ?></td>
          <td>
            <?php echo create_text_input("to_email", $to_email, 24, 32) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("from (your name)") ?></td>
          <td>
            <?php echo create_text_input("from_name", $from_name, 24, 32) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("from (your email)") ?></td>
          <td>
            <?php echo create_text_input("from_email", $from_email, 24, 64) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("subject") ?></td>
          <td>
            <?php echo create_text_input("subject", $subject, 48, 64) ?>
          </td>
        </tr>
<?php
        if (!$annotate) {
?>
        <tr>
          <td align="right"><?php echo translate("send fullsize") ?></td>
          <td align="left"><?php echo create_pulldown("_size", "mid", array("full" => translate("Yes",0), "mid" => translate("No",0)) ) ?></td>
        </tr>
<?php
        }
?>
        <tr>
          <td colspan="2" align="center">
            <?php echo translate("message:") ?><br>
            <textarea name="message" cols="70" rows="5"><?php echo $body ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
<?php
        if ($annotate) {
?>
            <img src="image_service.php?photo_id=<?php echo $photo_id ?>&annotated=1" alt="<?= $photo->get("title") ? $photo->get("title") : $photo->get("name") ?>">
<?php
        }
        else {
?>
            <?php echo $photo->get_midsize_img() ?>
<?php
        }
?>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="_button" value="<?php echo translate("email", 0); ?>">
</form>
          </td>
        </tr>
<?php
    }
?>
      </table>
    </td>
  </tr>
</table>

<?php
    require_once("footer.inc.php");
?>
