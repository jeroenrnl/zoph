<?php
/**
 * Notify users of e.g. new albums
 *
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
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
use conf\conf;
require_once "include.inc.php";

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

$title = translate("Notify");

$user_id = getvar("user_id");
if ($user_id > 0) {
    $u=new user($user_id);
    $u->lookup();
    $u->lookupPerson();
}

$subject = getvar("subject");
$message = getvar("message");

if ($_action == "mail") {

    $to_name = getvar("to_name");
    $to_email = getvar("to_email");
    $from_name = getvar("from_name");
    $from_email = getvar("from_email");

    $mail = new mailMime();
    $hdrs = array (
        "X-Mailer" => "Html Mime Mail Class",
        "X-Zoph-Version" => VERSION
    );
    $mail->setFrom(e($from_name) .  "<" . e($from_email) . ">");
    $mail->setSubject(e($subject));

    if (strlen(conf::get("feature.mail.bcc")) > 0) {
        $mail->setBCC(conf::get("feature.mail.bcc"));
    }

    $mail->setTXTBody(e($message));

    $body = $mail->get();
    $hdrs = $mail->headers($hdrs);
    foreach ($hdrs as $header => $content) {
        $headers .= $header . ": " . e($content) . "\n";
    }
    if (mail(e($to_email),"", $body,$headers)) {
        $msg = translate("Your mail has been sent.");

        $setlastmodified = getvar("setlastmodified");
        if ($setlastmodified) {
            if ($u instanceof user) {
                $u->set("lastnotify", "now()");
                $u->update();
            }
        }
    } else {
        $msg .= translate("Could not send mail.");
    }
} else {

    $from_name = $user->person->getName();
    $from_email = $user->person->getEmail();

    if ($u instanceof user) {
        $to_name = $u->person->getName();
        $to_email = $u->person->getEmail();
    }
}

require_once "header.inc.php";
?>

     <h1>
       <?php echo translate("email") ?>
     </h1>
  <div class="main">
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
<?php
if (isset($msg)) {
    echo $msg;
}

if ($_action == "notify") {

    $showusername = getvar("showusername");
    $showpassword = getvar("showpassword");
    $shownewalbums = getvar("shownewalbums");

    $body = translate("Hi",0) . " " . e($to_name) . ",\n\n";

    if ($shownewalbums) {
        $date = $u->getLastNotify();
        $body .= translate("I have enabled access to the following albums for you:",0) . "\n\n";

        $albums = album::getNewer($u, $date);

        $album_list = array();
        foreach ($album as $id => $album) {
            $album_path = '';
            $ancestors = $album->getAncestors();
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

        $url = getZophURL() . "login.php";

        $body .= "\n" .
        $body .= translate("For accessing these Albums you have to use this URL:",0);
        $body .= " " . $url . "\n";
    }

    if ($showusername) {
        $body .=
            translate("user name", 0) . ": " .
            e($u->get('user_name')) . "\n";
    }

    $body .= "\n" . translate("Regards,",0) . "\n";
    $body .= e($from_name);

    if (!$subject) {
        $subject = translate("New Albums on") . " " . conf::get("interface.title");
    }

    $message = $body;
} else if ($_action == "notifyuser") {
    $url = getZophURL() . "login.php";

    $subject = translate("Your Zoph Account", 0);
    $message =
        translate("Hi", 0) . " " . e($to_name) .  ",\n\n" .
        translate("I have created a Zoph account for you", 0) .
        ":\n\n" .  e($url) . "\n" .
        translate("user name", 0) . ": " .
        e($u->getName()) . "\n";

    $message .=
        "\n" . translate("Regards,", 0) . "\n" .
        e($user->person->getName());
}

if ($_action != "mail") {
    ?>
    <input type="hidden" name="_action" value="mail">
    <?php
    if (isset($shownewalbums)) {
        ?>
        <input type="hidden" name="setlastmodified" value="1">
        <?php
    }
    ?>
        <input type="hidden" name="user_id" value="<?php echo e($user_id) ?>">
        <label for="to_name"><?php echo translate("to (name)") ?>:</label>
        <?php echo create_text_input("to_name", e($to_name), 24, 32) ?><br>
        <label for="to_email"><?php echo translate("to (email)") ?>:</label>
        <?php echo create_text_input("to_email", e($to_email), 24, 32) ?><br>
        <label for="from_name"><?php echo translate("from (your name)") ?>:</label>
        <?php echo create_text_input("from_name", e($from_name), 24, 32) ?><br>
        <label for="from_email"><?php echo translate("from (your email)") ?>:</label>
        <?php echo create_text_input("from_email", e($from_email), 24, 64) ?><br>
        <label for="subject"><?php echo translate("subject") ?>:</label>
        <?php echo create_text_input("subject", e($subject), 48, 64) ?><br>

        <label for="message"><?php echo translate("message:") ?></label><br>
        <textarea name="message" class="email" cols="70" rows="15">
            <?php echo e($message) ?>
        </textarea>
        <br>
        <input type="submit" name="_button" value="<?php echo translate("email", 0); ?>">
        <br>
    <?php
}
?>
    </form>
</div>
<?php
require_once "footer.inc.php";
?>
