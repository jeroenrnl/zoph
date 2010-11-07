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
    private static $replaces=array();

    function __construct($find, $replace) {
        $this->find=$find;
        $this->replace=$replace;
    }

    public static function getArray() {
        if(empty(self::$replaces)) { 
            self::createArray(); 
        }
        return self::$replaces;
    }
        
    private static function createArray() {
        // Watch the order of these... putting &amp; at the end of the array
        // will make you end up with things like "&amp;lt;"...
        self::$replaces=array(
            new replace("&#40;", "("),  # Needed to revert anti
            new replace("&#41;", ")"),  # SQL injection-code
            new replace("&", "&amp;"),
            new replace("<", "&lt;"),
            new replace(">", "&gt;"),
            new replace("\n", "<br>")
        );
    }
}
class smiley {
    var $smiley;
    var $file;
    var $description;
    private static $smileys=array();

    public static function getArray() {
        if(empty(self::$smileys)) { 
            self::createArray(); 
        }
        return self::$smileys;
    }

        
    private static function createArray() {
        self::$smileys=array(
            new smiley(":D", "icon_biggrin.gif","Very Happy"),
            new smiley(":-D", "icon_biggrin.gif","Very Happy"),
            new smiley(":grin:", "icon_biggrin.gif","Very Happy"),
            new smiley(":)", "icon_smile.gif","Smile"),
            new smiley(":-)", "icon_smile.gif","Smile"),
            new smiley(":smile:", "icon_smile.gif","Smile"),
            new smiley(":(", "icon_sad.gif","Sad"),
            new smiley(":-(", "icon_sad.gif","Sad"),
            new smiley(":sad:", "icon_sad.gif","Sad"),
            new smiley(":o", "icon_surprised.gif","Surprised"),
            new smiley(":-o", "icon_surprised.gif","Surprised"),
            new smiley(":eek:", "icon_surprised.gif","Surprised"),
            new smiley(":shock:", "icon_eek.gif","Shocked"),
            new smiley(":?", "icon_confused.gif","Confused"),
            new smiley(":-?", "icon_confused.gif","Confused"),
            new smiley(":???:", "icon_confused.gif","Confused"),
            new smiley("8)", "icon_cool.gif","Cool"),
            new smiley("8-)", "icon_cool.gif","Cool"),
            new smiley(":cool:", "icon_cool.gif","Cool"),
            new smiley(":lol:", "icon_lol.gif","Laughing"),
            new smiley(":x", "icon_mad.gif","Mad"),
            new smiley(":-x", "icon_mad.gif","Mad"),
            new smiley(":mad:", "icon_mad.gif","Mad"),
            new smiley(":P", "icon_razz.gif","Razz"),
            new smiley(":-P", "icon_razz.gif","Razz"),
            new smiley(":razz:", "icon_razz.gif","Razz"),
            new smiley(":oops:", "icon_redface.gif","Embarassed"),
            new smiley(":cry:", "icon_cry.gif","Crying or Very sad"),
            new smiley(":evil:", "icon_evil.gif","Evil or Very Mad"),
            new smiley(":twisted:", "icon_twisted.gif","Twisted Evil"),
            new smiley(":roll:", "icon_rolleyes.gif","Rolling Eyes"),
            new smiley(":wink:", "icon_wink.gif","Wink"),
            new smiley(";)", "icon_wink.gif","Wink"),
            new smiley(";-)", "icon_wink.gif","Wink"),
            new smiley(":!:", "icon_exclaim.gif","Exclamation"),
            new smiley(":?:", "icon_question.gif","Question"),
            new smiley(":idea:", "icon_idea.gif","Idea"),
            new smiley(":arrow:", "icon_arrow.gif","Arrow"),
            new smiley(":|", "icon_neutral.gif","Neutral"),
            new smiley(":-|", "icon_neutral.gif","Neutral"),
            new smiley(":neutral:", "icon_neutral.gif","Neutral"),
            new smiley(":mrgreen:", "icon_mrgreen.gif","Mr. Green")
        );
    }    
    function __construct($smiley, $file, $description) {
        $this->smiley=$smiley;
        $this->file=$file;
        $this->description=$description;
    }

    function get_image() {
        return "<img src=\"images/smileys/" . $this->file ."\" alt=\"" . $this->description . "\">";
    }

    public static function getOverview() {
        $smileys=self::getArray();
        $html="<div class=\"smileys\">";
        foreach (self::$smileys as $smiley) {
            $html.="<div>";
            $html.=$smiley->get_image();
            $html.="<span>" . $smiley->smiley . "</span>";
            $html.="</div>";
        }
        $html.="<br></div>";
        return $html;
    }
            



}   
class tag {
    var $find;              // The tag in zophCode, without [ ]
    var $replace;           // The tag in HTML without < >
    var $regexp;            // How to check the parameter
    var $param;             // How to translate parameter
    var $close=true;        // True if this tags needs closure, 
                            // false if it does not
    
    private static $tags=array();

    public static function getArray() {
        if(empty(self::$tags)) { 
            self::createArray(); 
        }
        return self::$tags;
    }

        
    private static function createArray() {
        self::$tags=array(
            new tag("b", "b"),
            new tag("i", "i"),
            new tag("u", "u"),
            new tag("h1", "h1"),
            new tag("h2", "h2"),
            new tag("h3", "h3"),
            new tag("color", "span", "", "style=\"color: [param];\""),
            new tag("font", "span", "", "style=\"font-family: [param];\""),
            new tag("br", "br", null, null, false),
            new tag("background", "div", "", "class='background' style=\"background: [param];\""),
            new tag("photo", "a", "", "href=\"photo.php?photo_id=[param]\""),
            new tag("album", "a", "", "href=\"album.php?album_id=[param]\""),
            new tag("person", "a", "", "href=\"people.php?person_id=[param]\""),
            new tag("cat", "a", "", "href=\"category.php?category_id=[param]\""),
            new tag("link", "a", "", "href=\"[param]\""),
            new tag("place", "a", "", "href=\"places.php?parent_place_id=[param]\""),
            new tag("thumb", "img", "", "src=\"image_service.php?photo_id=[param]&type=thumb\"", false),
            new tag("mid", "img", "", "src=\"image_service.php?photo_id=[param]&type=mid\"", false)
        );
    }

    
    public function __construct($find, $replace, $regexp = null, $param = null,$close=true) {
        $this->find=$find;
        $this->replace=$replace;
        $this->regexp=$regexp;
        $this->param=$param;
        $this->close=$close;
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
    private $replaces = array();
    private $smileys = array();
    private $tags = array();

    function __construct($message, $allowed = null, 
        $replaces = null, $smileys = null, $tags = null) {
        if (!$replaces) {
            $this->replaces = replace::getArray();
        } else {
            $this->replaces = $replaces;
        }

        if (!$smileys) {
            $this->smileys = smiley::getArray();
        } else {
            $this->smileys = $smileys;
        }
        if (!$tags) {
            $this->tags = tag::getArray();
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



?>
