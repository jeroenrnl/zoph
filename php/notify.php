<?php
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        header("Location: " . add_sid("zoph.php"));
    }

    require_once("htmlMimeMail.php");

    $title = translate("Notify");

    $user_id = getvar("user_id");

    $subject = getvar("subject");
    $message = getvar("message");

    if ($_action == "mail") {

        $to_name = getvar("to_name");
        $to_email = getvar("to_email");
        $from_name = getvar("from_name");
        $from_email = getvar("from_email");

        $mail = new htmlMimeMail(array('X-Mailer: Html Mime Mail Class'));
        $mail->setFrom("$from_name <$from_email>");
        $mail->setSubject($subject);
        
        if (strlen(BCC_ADDRESS) > 0) {
            $mail->setBCC(BCC_ADDRESS);
        }

        $mail->settext($message);

        if (!$mail->send(array("$to_name <$to_email>"), 'smtp')) {
            $msg .= translate("Could not send mail.");
        }
        else {
            $msg = translate("Your mail has been sent.");

            $setlastmodified = getvar("setlastmodified");
            if ($setlastmodified) {
                $u = new user($user_id);
                $u->set("lastnotify", "now()");
                $u->update();
            }
        }
    }
    else {
        $u = new user($user_id);
        $u->lookup();
        $u->lookup_person();

        $from_name = $user->person->get_name();
        $from_email = $user->person->get_email();

        $to_name = $u->person->get_name();
        $to_email = $u->person->get_email();
    }

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>

  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left">
  <?php echo translate("email") ?>
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

    if ($_action == "notify") {

        $showusername = getvar("showusername");
        $showpassword = getvar("showpassword");
        $shownewalbums = getvar("shownewalbums");

        $body = translate("Hi",0) . " " . $to_name . ",\n\n";

        if ($shownewalbums) {
            $date = $u->get_lastnotify();
            $body .= translate("I have enabled access to the following albums for you", 0) . ":\n\n";

            $albums = get_newer_albums($user_id, $date);   

            $album_list = array();
            while (list($id, $album) = each($albums)) {                         
                $album_path = '';
                $ancestors = $album->get_ancestors();
                if ($ancestors) {
                    while ($parent = array_pop($ancestors)) {
                        $album_path .= $parent->get("album") .  " > ";
                    }
                }
                $album_path .= $album->get("album");
                $album_list[] = $album_path;
            }

            sort($album_list);
            reset($album_list);
            $body .= implode("\n", $album_list) . "\n";

            $url = ZOPH_URL;
            if (empty($url)) {
                $url = get_url() . "login.php";
            }

            $body .= "\n" . translate("For accessing these Albums you may use this URL", 0) . ": " . $url . "\n";
        }

        if ($showusername) {
            $body .=
                translate("user name", 0) . ": " .
                $u->get('user_name') . "\n";
        }

        $body .= "\n" . translate("Regards", 0) . ",\n";
        $body .= $from_name;

        if (!$subject) {
            $subject = translate("New Albums on", 0) . " " . ZOPH_TITLE;
        }

        $message = $body;
    }

    if ($_action != "mail") {
?>
<form action="<?php echo $PHP_SELF ?>" method="POST">
<input type="hidden" name="_action" value="mail">
<?php
        if ($shownewalbums) {
?>
<input type="hidden" name="setlastmodified" value="1">
<?php
        }
?>
<input type="hidden" name="user_id" value="<?php echo $user_id ?>">
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
            <textarea name="message" cols="70" rows="15"><?php echo $message ?></textarea>
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
