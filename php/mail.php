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
    $includeurl = getvar("includeurl");
    $annotate = getvar("annotate");

    if (!ANNOTATE_PHOTOS) {
        $annotate = 0;
    }

    // image will have been deleted if sent
    if ($annotate) {
        $skipcrumb = true;
    }

    $photo = new photo($photo_id);
    $found = $photo->lookupForUser($user);

    if (!$found) {
        $msg = sprintf(translate("Could not find photo id %s."), $photo_id);
    }
    else {

        if ($_action == "mail") {

            $mail = new Mail_mime();
            $hdrs = array (
                "X-Mailer" => "Html Mime Mail Class",
                "X-Zoph-Version" => VERSION
            );
            $size = getvar("_size");

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
            if($includeurl) {
                $link = "\n" . sprintf(translate("See this photo in %s"), ZOPH_TITLE) . ": " . ZOPH_URL . "/photo.php?photo_id=" . $photo_id;
            }

            if ($html) {
                $html = "<center>\n"; 
                $html .= "<img src=\"" . $file . "\"><br>\n";
                $html .= str_replace("\n", "<br>\n", $message);
                if($includeurl) {
                    $html .= "<a href=\"" . ZOPH_URL . "/photo.php?photo_id=" . $photo_id . "\">" . sprintf(translate("See this photo in %s"), ZOPH_TITLE) . "</a>";
                }
                $html .= "</center>\n";

                $mail->addHTMLImage($dir . "/" . $file, get_image_type($file), $file);
                $mail->setHTMLBody($html);
                $mail->setTXTBody($message . $link);
            } else {
                $mail->setTXTBody($message . $link);
                $mail->addAttachment($dir . "/" . $file, get_image_type($file));
            }
            $mail->setFrom("$from_name <$from_email>");

            if (strlen(BCC_ADDRESS) > 0) {
                $mail->setBCC(BCC_ADDRESS);
            }
            $body = $mail->get();
            $hdrs = $mail->headers($hdrs);
            foreach($hdrs as $header => $content) {
                $headers .= $header . ": " . $content . "\n";
            }
            if (mail($to_email,$subject, $body,$headers)) {
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

    $from_name = $user->person->getName();
    $from_email = $user->person->get_email();

    require_once("header.inc.php");
?>

          <h1>
  <?php if (ANNOTATE_PHOTOS) {
?>
          <span class="actionlink"> 
            <a href="define_annotated_photo.php?photo_id=<?php echo $photo->get("photo_id") ?>"><?php echo translate("create annotated photo", 0) ?></a> 
          </span>
<?php
        }
?>
  <?php echo translate("email photo") ?>
          </h1>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="post">
      <div class="main">
<?php
    if ($msg) {
?>
            <?php echo $msg ?>
<?php
    }

    if ($found && $_action == "compose") {

        $subject = sprintf(translate("A Photo from %s"), ZOPH_TITLE) . ": " . $photo->get("name");
        $ea = $photo->get_email_array();

        if ($ea) {
            while (list($name, $value) = each($ea)) {
                if ($name && $value) {
                    $body .= "$name: $value\r\n";
                }
            }
        }

        if ($annotate) {
            $photo->annotate($request_vars, $user);
        }
?>
<input type="hidden" name="_action" value="mail">
<input type="hidden" name="photo_id" value="<?php echo $photo_id ?>">
<input type="hidden" name="annotate" value="<?php echo $annotate ?>">
       <label for="html"><?php echo translate("send as html") ?></label>
       <?php echo create_pulldown("html", "1", array("1" => translate("Yes",0), "0" => translate("No",0))) ?><br>
       <label for="toname"><?php echo translate("to (name)") ?></label>
       <?php echo create_text_input("to_name", $to_name, 24, 32) ?><br>
       <label for="toemail"><?php echo translate("to (email)") ?></label>
       <?php echo create_text_input("to_email", $to_email, 24, 32) ?><br>
       <label for="fromname"><?php echo translate("from (your name)") ?></label>
       <?php echo create_text_input("from_name", $from_name, 24, 32) ?><br>
       <label for="fromemail"><?php echo translate("from (your email)") ?></label>
       <?php echo create_text_input("from_email", $from_email, 24, 64) ?><br>
       <label for="subject"><?php echo translate("subject") ?></label>
       <?php echo create_text_input("subject", $subject, 48, 64) ?><br>
<?php
        if (!$annotate) {
?>
       <label for="size"><?php echo translate("send fullsize") ?></label>
       <?php echo create_pulldown("_size", "mid", array("full" => translate("Yes",0), "mid" => translate("No",0)) ) ?><br>
       <label for="includeurl"><?php echo translate("include URL") ?></label>
       <?php echo create_pulldown("includeurl", "1", array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?><br>
<?php
        }
?>
            <label for="message"><?php echo translate("message:") ?></label><br>
            <textarea name="message" class="email" cols="70" rows="5"><?php echo $body ?></textarea>
<?php
        if ($annotate) {
?>
            <img src="image.php?photo_id=<?php echo $photo_id ?>&annotated=1" alt="<?= $photo->get("title") ? $photo->get("title") : $photo->get("name") ?>">
<?php
        }
        else {
?>
            <?php echo $photo->get_midsize_img() ?>
<?php
        }
?>
            <input type="submit" name="_button" value="<?php echo translate("email", 0); ?>">
<?php
    }
?>
      </div>
</form>

<?php
    require_once("footer.inc.php");
?>
