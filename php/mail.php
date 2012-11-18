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

    if (conf::get("feature.annotate")) {
        $annotate = getvar("annotate");
        $annotate_vars = getvar("annotate_vars");
    } else {
        $annotate=0;
    }

    if ($annotate) {
        $skipcrumb = true;
        $photo=new annotatedPhoto($photo_id);
        if(!empty($annotate_vars)) {
            parse_str($annotate_vars, $vars);
        } else {
            $vars=$request_vars;
            $annotate_vars=http_build_query($vars, "&amp;");
        }
        $photo->setVars($vars);
    } else {
        $photo = new photo($photo_id);
    }

    $found = $photo->lookupForUser($user);

    if (!$found) {
        $msg = sprintf(translate("Could not find photo id %s."), $photo_id);
    }
    else {

        if ($_action == "mail") {
            try {
                $mail = new Mail_mime();
                $hdrs = array (
                    "X-Mailer" => "Html Mime Mail Class",
                    "X-Zoph-Version" => VERSION
                );
                $headers="";
                
                $size = getvar("_size");
                
                if($annotate) {
                    $file=$photo->get("name");
                    $size = $vars["_size"];
                } else if ($size == "full") {
                    $file = $photo->get("name");
                    $dir = conf::get("path.images") . $photo->get("path") . "/";
                } else {
                    $file = MID_PREFIX . "_" . $photo->get("name");
                    $dir = conf::get("path.images") . $photo->get("path") . "/" .
                        MID_PREFIX . "/";
                }

                if ($html) {
                    $html = "<center>\n"; 
                    $html .= "<img src=\"" . $file . "\"><br>\n";
                    $html .= str_replace("\n", "<br>\n", $message);
                    if($includeurl) {
                        $html .= "<a href=\"" . getZophURL() . "/photo.php?photo_id=" . $photo_id . "\">" . sprintf(translate("See this photo in %s"), conf::get("interface.title")) . "</a>";
                    }
                    $html .= "</center>\n";
                    
                    if($annotate) {
                        list($headers,$image)=$photo->display($size);
                        $mail->addHTMLImageFromString($image, $photo->get("name"), $headers["Content-type"]);
                    } else {
                        $mail->addHTMLImageFromFile($dir . "/" . $file, get_image_type($file));
                    }
                    $mail->setHTMLBody($html);
                    $mail->setTXTBody($message);
                } else {
                    if($includeurl) {
                        $message .= "\n" . sprintf(translate("See this photo in %s"), conf::get("interface.title")) . ": " . getZophURL() . "/photo.php?photo_id=" . $photo_id;
                    }
                    $mail->setTXTBody($message);
                    if($annotate) {
                        list($headers,$image)=$photo->display($size);
                        $mail->addAttachmentFromString($image, $photo->get("name"), $headers["Content-type"]);
                    } else {
                        $mail->addAttachmentFromFile($dir . "/" . $file, get_image_type($file));
                    }
                }
                $mail->setFrom("$from_name <$from_email>");

                if (strlen(conf::get("feature.mail.bcc")) > 0) {
                    $mail->addBcc(conf::get("feature.mail.bcc"));
                }
                $body = $mail->get();
                $hdrs = $mail->headers($hdrs);
                foreach($hdrs as $header => $content) {
                    $headers .= $header . ": " . $content . "\n";
                }
                if (mail($to_email,$subject, $body,$headers)) {
                    $msg = translate("Your mail has been sent.");
                } else {
                    $msg = translate("Could not send mail.");
                }
            } catch (MailException $e) {
                $msg = $e->getMessage();
            }
        }

    }

    $from_name = $user->person->getName();
    $from_email = $user->person->get_email();

    require_once("header.inc.php");
?>

          <h1>
  <?php if (conf::get("feature.annotate")) {
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
    if (isset($msg)) {
?>
            <?php echo $msg ?>
<?php
    }

    if ($found && $_action == "compose") {
        $body="";

        $subject = sprintf(translate("A Photo from %s"), conf::get("interface.title")) . ": " . $photo->get("name");
        $ea = $photo->get_email_array();

        if ($ea) {
            while (list($name, $value) = each($ea)) {
                if ($name && $value) {
                    $body .= "$name: $value\r\n";
                }
            }
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
            <input type="hidden" name="annotate_vars" value="<?php echo $annotate_vars ?>">
            <img src="image.php?photo_id=<?php echo $photo_id ?>&annotated=1&<?php echo $annotate_vars ?>" alt="<?= $photo->get("title") ? $photo->get("title") : $photo->get("name") ?>">
<?php
        } else {
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
