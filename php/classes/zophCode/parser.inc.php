<?php
/**
 * The class parser is the parser for zophcode, a bbcode like markup language.
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
 * @author Jeroen Roos
 * @package Zoph
 */

namespace zophCode;

/**
 * This class can be used to create a block of 'zophcode'
 *
 * zophcode is very similar to bbcode
 * @author Jeroen Roos
 * @package Zoph
 */
class parser {
    /** @var string the message to be parsed */
    private $message;
    /** @var array Array of tags that can be used in this message */
    private $allowed = array();
    /** @var array Array of replace objects, containing all known problematic strings */
    private $replaces = array();
    /** @var array Array of smiley objects, containing all known smileys */
    private $smileys = array();
    /** @var array Array of tag objects, containing all known tags */
    private $tags = array();

    /**
     * Create a new zophcode object
     *
     * @param string Zophcode to parse
     * @param array Allowed tags. This can be used to limit functionality.
     * @see replace
     * @see smiley
     * @see tag
     */
    public function __construct($message, array $allowed = null) {
        $this->replaces = replace::getArray();
        $this->smileys = smiley::getArray();
        $this->tags = tag::getArray();
        $this->allowed = $allowed;
        $this->message = $message;
    }

    /**
     * Output zophcode parsed to HTML
     */
    public function __toString() {
        // This function parses a message using the replaces, smileys and tags
        // given in the function call.
        $message = $this->message;

        $return="";

        $stack=array(); // The stack is an array of currently open tags.
        list($find, $replace) =$this->get_replace_array();
        $message = preg_replace($find, $replace, $message);

        while (strlen($message)) {
            $plaintext="";
            $replace_param="";
            $opentag = strpos($message, "[", 0);

            if ($opentag === false) {
                $return .= $message;
                $message = "";
            } else if ($opentag > 0) {
                $plaintext=substr($message, 0, $opentag);
            }
            if (($opentag + 1)<= strlen($message)) {
                // This prevents a PHP error when the last char
                // of the message is a "["
                $closetag = strpos($message, "]", $opentag + 1);
            } else {
                $closetag=0;
            }

            $tag = substr($message, $opentag + 1, $closetag - $opentag - 1);
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
                $foundtag=tag::getFromName($tag[0]);
                if ($foundtag && $endtag === true && $foundtag->close === false) {
                    // This is an endcode for a tag that does not have an endcode
                    // such as [br]. We'll just ignore it.
                    $message = substr($message, $closetag + 1);
                    $return .=$plaintext;
                } else if ($foundtag && $foundtag->replace && !($falseopen)) {
                    if ($endtag === false) {
                        // It is a valid tag.
                        if ($foundtag->close) {
                            array_push($stack, $tag[0]);
                        }
                        if ($foundtag->param && $tag[1]) {
                            $replace_param = $foundtag->addParam($tag[1]);
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
        while ($tag = array_pop($stack)) {
            // Now close all tags that have not yet been closed.
            $foundtag=tag::getFromName($tag);
            $return .= "</" . $foundtag->replace . ">";
        }

        return $return;
    }

    /**
     * This function takes an array of 'replace' objects and
     * an array of 'smiley' objects and return 2 arrays that
     * can be fed to preg_replace */
    public function get_replace_array() {
        $find=array();
        $replace=array();
        foreach ($this->replaces as $repl) {
            array_push($find, "/" . preg_quote($repl->find) . "/");
            array_push($replace, $repl->replace);
        }
        foreach ($this->smileys as $smiley) {
            array_push($find, "/" . preg_quote($smiley->smiley) . "/");
            array_push($replace, $smiley);
        }
        return array($find, $replace);
    }
}
?>
