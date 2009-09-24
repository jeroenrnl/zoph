<?php
/*
 * This file is part of Zoph.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
    require_once("include.inc.php");

    if (!$user->is_admin()) {
        redirect(add_sid("zoph.php"));
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

        $mail = new Mail_mime();
        $hdrs = array (
            "X-Mailer" => "Html Mime Mail Class",
            "X-Zoph-Version" => VERSION
        );
        $mail->setFrom("$from_name <$from_email>");
        $mail->setSubject($subject);

        if (strlen(BCC_ADDRESS) > 0) {
            $mail->setBCC(BCC_ADDRESS);
        }

        $mail->setTXTBody($message);
        
        $body = $mail->get();
        $hdrs = $mail->headers($hdrs);
        foreach($hdrs as $header => $content) {
            $headers .= $header . ": " . $content . "\n";
        }
        if (mail($to_email,"", $body,$headers)) {
            $msg = translate("Your mail has been sent.");

            $setlastmodified = getvar("setlastmodified");
            if ($setlastmodified) {
                $u = new user($user_id);
                $u->set("lastnotify", "now()");
                $u->update();
            }
        } else {
            $msg .= translate("Could not send mail.");
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

    require_once("header.inc.php");
?>

         <h1>
           <?php echo translate("email") ?>
         </h1>
      <div class="main">
<form action="<?php echo $PHP_SELF ?>" method="POST">
<?php
    if ($msg) {
?>
            <?php echo $msg ?>
<?php
    }
?>
<?php
    if ($_action == "notify") {

        $showusername = getvar("showusername");
        $showpassword = getvar("showpassword");
        $shownewalbums = getvar("shownewalbums");

        $body = translate("Hi",0) . " " . $to_name . ",\n\n";

        if ($shownewalbums) {
            $date = $u->get_lastnotify();
            $body .= translate("I have enabled access to the following albums for you:",0) . "\n\n";

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

            $body .= "\n" . translate("For accessing these Albums you have to use this URL:",0) . " " . $url . "\n";
        }

        if ($showusername) {
            $body .=
                translate("user name", 0) . ": " .
                $u->get('user_name') . "\n";
        }

        $body .= "\n" . translate("Regards,",0) . "\n";
        $body .= $from_name;

        if (!$subject) {
            $subject = translate("New Albums on") . " " . ZOPH_TITLE;
        }

        $message = $body;
    }

    if ($_action != "mail") {
?>
        <input type="hidden" name="_action" value="mail">
<?php
        if ($shownewalbums) {
?>
            <input type="hidden" name="setlastmodified" value="1">
<?php
        }
?>
            <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
            <label for="to_name"><?php echo translate("to (name)") ?>:</label>
            <?php echo create_text_input("to_name", $to_name, 24, 32) ?><br>
            <label for="to_email"><?php echo translate("to (email)") ?>:</label>
            <?php echo create_text_input("to_email", $to_email, 24, 32) ?><br>
            <label for="from_name"><?php echo translate("from (your name)") ?>:</label>
            <?php echo create_text_input("from_name", $from_name, 24, 32) ?><br>
            <label for="from_email"><?php echo translate("from (your email)") ?>:</label>
            <?php echo create_text_input("from_email", $from_email, 24, 64) ?><br>
            <label for="subject"><?php echo translate("subject") ?>:</label>
            <?php echo create_text_input("subject", $subject, 48, 64) ?><br>
            
	        <label for="message"><?php echo translate("message:") ?></label><br>
            <textarea name="message" class="email" cols="70" rows="15"><?php echo $message ?></textarea><br>
            <input type="submit" name="_button" value="<?php echo translate("email", 0); ?>">
	        <br>
<?php
    }
?>
        </form>
    </div>
<?php
    require_once("footer.inc.php");
?>
