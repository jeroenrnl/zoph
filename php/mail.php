<?php
    require_once("include.inc.php");
    require_once("class.html.mime.mail.inc.php");
    $title = translate("E-Mail Photo");

    $photo_id = getvar("photo_id");
    $html = getvar("html");
    $to_name = getvar("to_name");
    $to_email = getvar("to_email");
    $from_name = getvar("from_name");
    $from_email = getvar("from_email");
    $subject = getvar("subject");
    $message = getvar("message");

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

            define('CRLF', "\n", TRUE);
            define('MAIL_MIMEPART_CRLF', CRLF, TRUE);
            //define('MAIL_MIMEPART_CRLF', "\n", TRUE);

            $mail = new html_mime_mail(array('X-Mailer: Html Mime Mail Class'));

            $text = $body;

            if ($html) {
                $html = str_replace("\n", "<br>\n", $body);
                $html =
                    "<center>\n" .
                    "<img src=\"" . MID_PREFIX . "_" . $photo->get("name") .
                    "\"><br>\n" .
                    $html .  "</center>\n";

                $dir = IMAGE_DIR . $photo->get("path") . "/" . MID_PREFIX . "/";

                $mail->add_html($html, $text, $dir);
            }
            else {
                $mail->add_text($text);

                $file = $mail->get_file($photo->get_image_href(MID_PREFIX, 1));
                $mail->add_attachment($file, $photo->get("name"), get_image_type($photo->get("name")));
            }


            if (!$mail->build_message()) {
                $msg .= translate("Could not build message.");
            }
            else if (!$mail->send($to_name, $to_email, $from_name, $from_email, $subject)) {
                $msg .= translate("Could not send mail.");
            }
            else {
                $msg = translate("Your mail has been sent.");
            }
            //$msg .= '<br><PRE>' . htmlentities($mail->get_rfc822($to_name, $to_email, $from_name, $from_email, $subject)) . '</PRE>';
        }

    }

    $from_name = $user->person->get_name();

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>

  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TITLE_BG_COLOR?>">
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
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo$TABLE_BG_COLOR?>">
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
?>
        <tr>
          <td align="right">
<form action="<?php echo $PHP_SELF ?>" method="post">
<input type="hidden" name="_action" value="mail">
<input type="hidden" name="photo_id" value="<?php echo $photo_id ?>">
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
        <tr>
          <td colspan="2" align="center">
            <?php echo translate("message:") ?><br>
            <textarea name="message" cols="70" rows="5"><?php echo $body ?></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <?php echo $photo->get_midsize_img() ?>
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
