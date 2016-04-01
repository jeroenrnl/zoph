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
        $this->allowed = $allowed;
        $this->message = $message;
    }

    /**
     * Output zophcode parsed to HTML
     * @return string HTML-ized zophCode
     */
    public function __toString() {
        // This function parses a message using the replaces, smileys and tags
        // given in the function call.
        $message = $this->message;

        $return="";

        // The stack is an array of currently open tags.
        $stack=array();
        $message=replace::processMessage($message);
        $message=smiley::processMessage($message);

        // The array $allowed can be used to prevent users from using
        // certain tags in some positions.
        // This is used for example to limit the number of options
        // the user has while writing comments.
        tag::setAllowed($this->allowed);

        $return="";

        foreach (static::parseMessage($message) as $tag) {
            if (!$tag instanceof tag) {
                $return.=$tag;
            } else if ($tag->isAllowed()) {
                if (!$tag->isClosing() && $tag->needsClosing()) {
                    array_push($stack, $tag);
                } else if ($tag->isClosing()) {
                    if (end($stack)->getFind() == $tag->getFind()) {
                        array_pop($stack);
                    } else {
                        // Tried to close a tag that wasn't open
                        // Ignore the tag and go on.
                        continue;
                    }
                }

                $return.=$tag;
            }
        }
        while ($tag = array_pop($stack)) {
            // Now close all tags that have not yet been closed.
            $tag->setClosing();
            $return .= $tag;
        }

        return $return;
    }

    /**
     * Parse a zophCode message
     * This parser will tokenize the message into an array of strings and tag objects
     * @param string Message with zophCode
     * @return array Array of tokenized zophCode
     */
    private static function parseMessage($msg) {
        while (strlen($msg)) {
            $opentag = strpos($msg, "[", 0);
            if ($opentag === false) {
                yield $msg;
                $msg="";
            } else if ($opentag > 0) {
                yield substr($msg, 0, $opentag);
            }

            if (($opentag + 1)<= strlen($msg)) {
                // This prevents a PHP error when the last char
                // of the message is a "["
                $closetag = strpos($msg, "]", $opentag + 1);
            } else {
                $closetag = 0;
            }

            $tag=substr($msg, $opentag, $closetag - $opentag + 1);

            // Does the tag contain " " or another "["?
            // In that case something is probably wrong...
            // (such as "[b This is bold[/b]")

            if (!strpos($tag, "[") || strpos($tag, " ")) {
                yield tag::getFromString($tag);
            }

            $msg = substr($msg, $closetag + 1);
        }
    }
}
?>
