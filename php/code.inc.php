<?php

/*
 * A parser for zophcode, a bbcode like markup language.
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
 */

class replace {
    var $find;
    var $replace;

    function replace($replaces, $find, $replace) {
        $this->find=$find;
        $this->replace=$replace;
        array_push($replaces, $this);
    }
}
class smiley {
    var $smiley;
    var $file;
    var $description;
    function smiley($smileys, $smiley, $file, $description) {
        $this->smiley=$smiley;
        $this->file=$file;
        $this->description=$description;
        array_push($smileys, $this);
    }
    function get_image() {
        return "<img src=\"images/smileys/" . $this->file ."\" alt=\"" . $this->description . "\">";
    }

}   
class tag {
    var $find;              // The tag in zophCode, without [ ]
    var $replace;           // The tag in HTML without < >
    var $regexp;            // How to check the parameter
    var $param;             // How to translate parameter
    var $close=true;        // True if this tags needs closure, 
                            // false if it does not
    
    function tag($tags, $find, $replace, $regexp = null, $param = null,$close=true) {
        $this->find=$find;
        $this->replace=$replace;
        $this->regexp=$regexp;
        $this->param=$param;
        $this->close=$close;
        array_push($tags, $this);
    }

    function checkparam($value) {
        if(!empty($regexp)) {
            return preg_match($regexp, $value);
        } else {
            return true;
        }
    }

    function param($value) {
        if ($this->checkparam($value)) {
            return " " . str_replace("[param]", $value, $this->param);
        }
    }
}

class zophcode {
    var $message;
    var $allowed = array();
    var $replaces = array();
    var $smileys = array();
    var $tags = array();

    function zophcode($message, $allowed = null, 
        $replaces = null, $smileys = null, $tags = null) {
        if (!$replaces) {
            $this->replaces = get_replaces_array();
        } else {
            $this->replaces = $replaces;
        }

        if (!$smileys) {
            $this->smileys = get_smileys_array();
        } else {
            $this->smileys = $smileys;
        }
        if (!$tags) {
            $this->tags = get_tags_array();
        } else {
            $this->tags = $tags;
        }
        $this->allowed = $allowed;
        $this->message = $message;
    }
    
    function parse() {
        // This function parses a message using the replaces, smileys and tags
        // given in the function call.
        $message = $this->message;

        $stack=array(); // The stack is an array of currently open tags.
        list($find, $replace) =$this->get_replace_array();
        $message = preg_replace($find, $replace, $message);

        while(strlen($message)) {
            $plaintext="";
            $replace_param="";
            $opentag = strpos($message, "[", $start);
            
            if($opentag === false) {
                $return .= $message;
                $message = "";
            } else if($opentag > 0) {
                $plaintext=substr($message, 0,$opentag);
            }
            if(($opentag + 1)<= strlen($message)) {
                // This prevents a PHP error when the last char
                // of the message is a "["
                $closetag = strpos($message, "]", $opentag + 1);
            } else {
                $closetag=0;
            }

            $tag = substr($message, $opentag + 1 , $closetag - $opentag - 1);
            // Does the tag contain " " or another "["? 
            // In that case something is probably wrong... 
            // (such as "[b This is bold[/b]")
            
            $falseopen = (strpos($tag, "[") || strpos($tag, " "));
            
            $tag = explode("=", $tag);
            

            // Check if tag is a closing tag
            if (substr($tag[0], 0, 1) == "/") {
                $endtag = true;
                $tag[0] = substr($tag[0], 1, strlen($tag[0]) - 1);
            } else {
                $endtag = false;
            }
            if (!$this->allowed || in_array($tag[0], $this->allowed)) {
                // The array $allowed can be used to prevent users from using
                // certain tags in some positions.
                // This is used for example to limit the number of options
                // the user has while writing comments.
                $foundtag=findtag($this->tags,$tag[0]);
                if($endtag === true && $foundtag->close === false) {
                    // This is an endcode for a tag that does not have an endcode
                    // such as [br]. We'll just ignore it.
                    $message = substr($message, $closetag + 1);
                    $return .=$plaintext;
                } else if($foundtag->replace && !($falseopen)) {
                    if($endtag === false) {
                        // It is a valid tag.
                        if($foundtag->close) {
                            array_push($stack, $tag[0]);
                        }
                        if ($foundtag->param && $tag[1]) {
                            $replace_param = $foundtag->param($tag[1]);
                        }
                        $return .= $plaintext . 
                            "<" . $foundtag->replace . $replace_param . ">";
                    } else if (end($stack) == $tag[0]) {
                    
                        // It is a valid closing tag
                        // Check if the tag is open
                        array_pop($stack);
                        $return .= $plaintext . "</" . $foundtag->replace . ">";
                    } else {
                        // Tried to close a tag that wasn't open
                        // Ignore the tag and go on.
                        $return .= $plaintext;
                    }
                    // Take the just evaluated tag from the message
                    $message=substr($message, $closetag + 1);

                } else {
                    // Unknown tag, ignore and continue evaluating with the next character.
                    $return .=$plaintext;
                    $message=substr($message, $opentag + 1);

                }
            } else {
                // User has specified a tag he is not allowed to use
                // ignore it.
                $return .=$plaintext;
                $message=substr($message, $closetag + 1);
            }
        }
        while($tag = array_pop($stack)) {
            // Now close all tags that have not yet been closed.
            $foundtag=findtag($this->tags,$tag);
            $return .= "</" . $foundtag->replace . ">";
        }

        return $return;
    }

    function get_replace_array() {
        // This function takes an array of 'replace' objects and
        // an array of 'smiley' objects and return 2 arrays that
        // can be fed to preg_replace
        $find=array();
        $replace=array();
        foreach ($this->replaces as $repl) {
            array_push($find, "/" . preg_quote($repl->find) . "/");
            array_push($replace, $repl->replace);
        }
        foreach ($this->smileys as $smiley) {
            array_push($find, "/" . preg_quote($smiley->smiley) . "/");
            array_push($replace, $smiley->get_image());
        }
        return array($find, $replace);
    }
}

function findtag($tags,$findtag) {
    // Check if tag is a valid tag.
    foreach($tags as $tag) {
        if($tag->find == $findtag) {
            return $tag;
        }
    }
}

function get_smiley_overview() {
    $smileys=get_smileys_array();
    $html="<div class=\"smileys\">";
    foreach ($smileys as $smiley) {
        $html.="<div>";
        $html.=$smiley->get_image();
        $html.="<span>" . $smiley->smiley . "</span>";
        $html.="</div>";
    }
    $html.="<br></div>";
    return $html;
}
        



function get_smileys_array() {
    $smileys=array();
    new smiley(&$smileys, ":D", "icon_biggrin.gif","Very Happy");
    new smiley(&$smileys, ":-D", "icon_biggrin.gif","Very Happy");
    new smiley(&$smileys, ":grin:", "icon_biggrin.gif","Very Happy");
    new smiley(&$smileys, ":)", "icon_smile.gif","Smile");
    new smiley(&$smileys, ":-)", "icon_smile.gif","Smile");
    new smiley(&$smileys, ":smile:", "icon_smile.gif","Smile");
    new smiley(&$smileys, ":(", "icon_sad.gif","Sad");
    new smiley(&$smileys, ":-(", "icon_sad.gif","Sad");
    new smiley(&$smileys, ":sad:", "icon_sad.gif","Sad");
    new smiley(&$smileys, ":o", "icon_surprised.gif","Surprised");
    new smiley(&$smileys, ":-o", "icon_surprised.gif","Surprised");
    new smiley(&$smileys, ":eek:", "icon_surprised.gif","Surprised");
    new smiley(&$smileys, ":shock:", "icon_eek.gif","Shocked");
    new smiley(&$smileys, ":?", "icon_confused.gif","Confused");
    new smiley(&$smileys, ":-?", "icon_confused.gif","Confused");
    new smiley(&$smileys, ":???:", "icon_confused.gif","Confused");
    new smiley(&$smileys, "8)", "icon_cool.gif","Cool");
    new smiley(&$smileys, "8-)", "icon_cool.gif","Cool");
    new smiley(&$smileys, ":cool:", "icon_cool.gif","Cool");
    new smiley(&$smileys, ":lol:", "icon_lol.gif","Laughing");
    new smiley(&$smileys, ":x", "icon_mad.gif","Mad");
    new smiley(&$smileys, ":-x", "icon_mad.gif","Mad");
    new smiley(&$smileys, ":mad:", "icon_mad.gif","Mad");
    new smiley(&$smileys, ":P", "icon_razz.gif","Razz");
    new smiley(&$smileys, ":-P", "icon_razz.gif","Razz");
    new smiley(&$smileys, ":razz:", "icon_razz.gif","Razz");
    new smiley(&$smileys, ":oops:", "icon_redface.gif","Embarassed");
    new smiley(&$smileys, ":cry:", "icon_cry.gif","Crying or Very sad");
    new smiley(&$smileys, ":evil:", "icon_evil.gif","Evil or Very Mad");
    new smiley(&$smileys, ":twisted:", "icon_twisted.gif","Twisted Evil");
    new smiley(&$smileys, ":roll:", "icon_rolleyes.gif","Rolling Eyes");
    new smiley(&$smileys, ":wink:", "icon_wink.gif","Wink");
    new smiley(&$smileys, ";)", "icon_wink.gif","Wink");
    new smiley(&$smileys, ";-)", "icon_wink.gif","Wink");
    new smiley(&$smileys, ":!:", "icon_exclaim.gif","Exclamation");
    new smiley(&$smileys, ":?:", "icon_question.gif","Question");
    new smiley(&$smileys, ":idea:", "icon_idea.gif","Idea");
    new smiley(&$smileys, ":arrow:", "icon_arrow.gif","Arrow");
    new smiley(&$smileys, ":|", "icon_neutral.gif","Neutral");
    new smiley(&$smileys, ":-|", "icon_neutral.gif","Neutral");
    new smiley(&$smileys, ":neutral:", "icon_neutral.gif","Neutral");
    new smiley(&$smileys, ":mrgreen:", "icon_mrgreen.gif","Mr. Green"); 
    return $smileys;
}

function get_replaces_array() {
    // Watch the order of these... putting &amp; at the end of the array
    // will make you end up with things like "&amp;lt;"...
    $replaces=array();
    new replace(&$replaces, "&#40;", "(");  # Needed to revert anti
    new replace(&$replaces, "&#41;", ")");  # SQL injection-code
    new replace(&$replaces, "&", "&amp;");
    new replace(&$replaces, "<", "&lt;");
    new replace(&$replaces, ">", "&gt;");
    new replace(&$replaces, "\n", "<br>");
    return $replaces;
}
function get_tags_array() {
    $tags=array();
    new tag(&$tags, "b", "b");
    new tag(&$tags, "i", "i");
    new tag(&$tags, "u", "u");
    new tag(&$tags, "h1", "h1");
    new tag(&$tags, "h2", "h2");
    new tag(&$tags, "h3", "h3");
    new tag(&$tags, "color", "span", "", "style=\"color: [param];\"");
    new tag(&$tags, "font", "span", "", "style=\"font-family: [param];\"");
    new tag(&$tags, "br", "br", null, null, false);
    new tag(&$tags, "background", "div", "", "class='background' style=\"background: [param];\"");
    new tag(&$tags, "photo", "a", "", "href=\"photo.php?photo_id=[param]\"");
    new tag(&$tags, "album", "a", "", "href=\"album.php?album_id=[param]\"");
    new tag(&$tags, "person", "a", "", "href=\"people.php?person_id=[param]\"");
    new tag(&$tags, "cat", "a", "", "href=\"category.php?category_id=[param]\"");
    new tag(&$tags, "link", "a", "", "href=\"[param]\"");
    new tag(&$tags, "place", "a", "", "href=\"places.php?parent_place_id=[param]\"");
    new tag(&$tags, "thumb", "img", "", "src=\"image_service.php?photo_id=[param]&type=thumb\"", false);
    new tag(&$tags, "mid", "img", "", "src=\"image_service.php?photo_id=[param]&type=mid\"", false);
    return $tags;
}

?>
