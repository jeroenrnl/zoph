<?php
namespace zophCode;

use template;

/**
 * This class is a helper class for zophCode
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

/**
 * Create smileys
 * @author Jeroen Roos
 * @package Zoph
 */
class smiley {
    public $smiley;
    public $file;
    public $description;
    private static $smileys=array();

    /**
     * Create a new smiley object
     * @param string smiley (e.g. ":-)")
     * @param string filename of smiley image
     * @param string short description
     */
    private function __construct($smiley, $file, $description) {
        $this->smiley=$smiley;
        $this->file=$file;
        $this->description=$description;
    }

    /**
     * Get an array of all smiley objects
     */
    public static function getArray() {
        if (empty(static::$smileys)) {
            static::createArray();
        }
        return static::$smileys;
    }

    /**
     * Fill the static $smileys.
     */
    private static function createArray() {
        static::$smileys=array(
            new smiley(":D", "icon_biggrin.gif", "Very Happy"),
            new smiley(":-D", "icon_biggrin.gif", "Very Happy"),
            new smiley(":grin:", "icon_biggrin.gif", "Very Happy"),
            new smiley(":)", "icon_smile.gif", "Smile"),
            new smiley(":-)", "icon_smile.gif", "Smile"),
            new smiley(":smile:", "icon_smile.gif", "Smile"),
            new smiley(":(", "icon_sad.gif", "Sad"),
            new smiley(":-(", "icon_sad.gif", "Sad"),
            new smiley(":sad:", "icon_sad.gif", "Sad"),
            new smiley(":o", "icon_surprised.gif", "Surprised"),
            new smiley(":-o", "icon_surprised.gif", "Surprised"),
            new smiley(":eek:", "icon_surprised.gif", "Surprised"),
            new smiley(":shock:", "icon_eek.gif", "Shocked"),
            new smiley(":?", "icon_confused.gif", "Confused"),
            new smiley(":-?", "icon_confused.gif", "Confused"),
            new smiley(":???:", "icon_confused.gif", "Confused"),
            new smiley("8)", "icon_cool.gif", "Cool"),
            new smiley("8-)", "icon_cool.gif", "Cool"),
            new smiley(":cool:", "icon_cool.gif", "Cool"),
            new smiley(":lol:", "icon_lol.gif", "Laughing"),
            new smiley(":x", "icon_mad.gif", "Mad"),
            new smiley(":-x", "icon_mad.gif", "Mad"),
            new smiley(":mad:", "icon_mad.gif", "Mad"),
            new smiley(":P", "icon_razz.gif", "Razz"),
            new smiley(":-P", "icon_razz.gif", "Razz"),
            new smiley(":razz:", "icon_razz.gif", "Razz"),
            new smiley(":oops:", "icon_redface.gif", "Embarassed"),
            new smiley(":cry:", "icon_cry.gif", "Crying or Very sad"),
            new smiley(":evil:", "icon_evil.gif", "Evil or Very Mad"),
            new smiley(":twisted:", "icon_twisted.gif", "Twisted Evil"),
            new smiley(":roll:", "icon_rolleyes.gif", "Rolling Eyes"),
            new smiley(":wink:", "icon_wink.gif", "Wink"),
            new smiley(";)", "icon_wink.gif", "Wink"),
            new smiley(";-)", "icon_wink.gif", "Wink"),
            new smiley(":!:", "icon_exclaim.gif", "Exclamation"),
            new smiley(":?:", "icon_question.gif", "Question"),
            new smiley(":idea:", "icon_idea.gif", "Idea"),
            new smiley(":arrow:", "icon_arrow.gif", "Arrow"),
            new smiley(":|", "icon_neutral.gif", "Neutral"),
            new smiley(":-|", "icon_neutral.gif", "Neutral"),
            new smiley(":neutral:", "icon_neutral.gif", "Neutral"),
            new smiley(":mrgreen:", "icon_mrgreen.gif", "Mr. Green")
        );
    }

    /**
     * Get the smiley
     * @todo contains HTML
     */
    public function __toString() {
        return "<img src=\"" . template::getImage("smileys/" . $this->file) .
            "\" alt=\"" . $this->description . "\">";
    }

    /**
     * Get an overview of all defined smileys
     * @todo contains HTML
     */
    public static function getOverview() {
        static::getArray();
        $html="<div class=\"smileys\">";
        foreach (static::$smileys as $smiley) {
            $html.="<div>";
            $html.=$smiley;
            $html.="<span>" . $smiley->smiley . "</span>";
            $html.="</div>";
        }
        $html.="<br></div>";
        return $html;
    }
}
?>
