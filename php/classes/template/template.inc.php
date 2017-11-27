<?php
/**
 * Class that takes care of displaying templates
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

namespace template;

use log;
use page;
use photo;
use settings;
use user;
use Time;
use DateInterval;

use conf\conf;

/**
 * This class takes care of displaying templates
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class template {
    /** @var array javascript for this template */
    public $js=array();
    /** @var array variables that can be used in the template
                    all variables will be preceeded with $tpl_ */
    public $vars=array();
    /** @var string CSS style for this template */
    public $style="";
    /** @var string Javascript to be included in header (/
    public $script="";
    /** @var array CSS files to be included */
    public $css=array();
    /** @var string HTML title for the page to be displayed
    public $title="Zoph";

    /** @var array contains actionlinks */
    private $actionlinks=array();

    /**
     * @var array contains blocks or sub-templates that will be
     *              displayed inside this template by calling the
     *              getBlocks() function from within the template
     */
    private $blocks=array();

    /**
     * Create template object
     *
     * @param string Name of template (without path or extension)
     * @param array Array of variables that can be used in the template
     * @return template
     */
    public function __construct($template, $vars=null) {
        $tpl=conf::get("interface.template");
        $this->vars=$vars;
        if (preg_match("/^[A-Za-z0-9_\-]+$/", $tpl) &&
                preg_match("/^[A-Za-z0-9_\-]+$/", $template)) {
            $file="templates/" . $tpl . "/" . $template . ".tpl.php";
            if (!file_exists($file)) {
                $file="templates/default/" . $template . ".tpl.php";
            }
            $this->template=$file;

            $this->css[]="css.php";
        } else {
            log::msg("Illegal characters in template", log::FATAL, log::GENERAL);
        }
    }
    /**
     * Get image URL for specific template.
     * if the image does not exist in the current template, the default will be be returned
     * This enables template builders to only include the parts of the template that
     * have been changed
     * @param string image name
     * @return string relative image url
     */
    public static function getImage($image) {
        $tpl=conf::get("interface.template");
        if (preg_match("/^[A-Za-z0-9_\-\/\.]+$/", $image) && !preg_match("/\.\./", $image)) {
            $file="templates/" . $tpl . "/images/" . $image;
            if (!file_exists($file)) {
                $file="templates/default/images/" . $image;
            }
            return $file;
        } else {
            log::msg("Illegal characters in icon name", log::FATAL, log::GENERAL);
        }
    }

    /**
     * Print the template
     *
     * @return string
     */
    public function __toString() {
        if ($this->vars) {
            extract($this->vars, EXTR_PREFIX_ALL, "tpl");
        }
        if (!defined("ZOPH")) {
            define('ZOPH', true);
        }
        try {
            ob_start();
                include $this->template;
            return trim(ob_get_clean());
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    /**
     * Return the header section of the page
     *
     * @return string
     * @access private
     * @todo This should be in a template, cannot be done at the moment because
     *       there are so many pages not moved to the templating system.
     */
    private function getHead() {
        $html="";
        foreach ($this->js as $js_src) {
            $html.="    <script type='text/javascript' src='" . $js_src . "'>" .
                "</script>\n";
        }

        if (!empty($this->script)) {
            $html.="    <script type='text/javascript'>";
            $html.="        " . $this->script;
            $html.="    </script>";
        }

        foreach ($this->css as $css_href) {
            $html.="    <link type='text/css' rel='stylesheet' href='" .
                $css_href . "'>\n";
        }
        if (!empty($this->style)) {
            $html.="    <style>" . $this->style . "</style>\n";
        }
        if (!empty($title)) {
            $html.="    <title>" . $title . "</title>\n";
        }

        return $html;
    }


    /**
     * Add a block
     * @param block Block to be added
     */
    public function addBlock(block $block) {
        $this->blocks[]=$block;
    }

    /**
     * Add a page
     * A page can simply be added to the list of blocks as it can be displayed
     * with the __toString() function
     * @param page Page to be added
     */
    public function addPage(page $page) {
        $this->blocks[]=$page;
    }

    /**
     * Add multiple blocks
     * @param array Blocks to be added
     */
    public function addBlocks(array $blocks) {
        foreach ($blocks as $block) {
            $this->addBlock($block);
        }
    }

    /**
     * Get the blocks inside this template
     * @return array blocks
     */
    protected function getBlocks() {
        return $this->blocks;
    }

    /**
     * Display the blocks inside this template
     * @return string HTML code for the blocks
     */
    protected function displayBlocks() {
        $html="";
        foreach ($this->getBlocks() as $block) {
            $html.=$block;
        }
        return $html;
    }

    /**
     * Add an actionlink
     * @param string Title to be displayed
     * @param string URL
     */
    public function addActionlink($title, $link) {
        $this->actionlinks[$title]=$link;
    }

    /**
     * Add multiple actionlinks
     * @param array of actionlinks
     */
    public function addActionlinks(array $al) {
        foreach ($al as $title => $link) {
            $this->addActionlink($title, $link);
        }
    }

    /**
     * Markup an array of actionlinks using the actionlinks template
     * @param array Optional array of actionlinks, otherwise use the ones in the class
     */
    private function getActionlinks(array $actionlinks=null) {
        if ($actionlinks==null) {
            $actionlinks=$this->actionlinks;
        }
        if (is_array($actionlinks)) {
            return new block("actionlinks", array(
                "actionlinks" => $actionlinks)
            );
        }
    }

    /**
     * Create a link list
     * Creates a comma separated list of links from the given records.
     * The class of the records must implement the getLink function.
     * @param array Array of records to be displayed
     * @return string Comma separated links to records
     * @todo Could maybe better move into zophTable?
     * @todo Should check whether the object is of a supported class
     */
    public static function createLinkList(array $records) {
        $links = "";
        if ($records) {
            foreach ($records as $rec) {
                if ($links) {
                    $links .= ", ";
                }
                $links .= $rec->getLink();
            }
        }

        return $links;
    }

    /**
     * Creates an array to be used in the createPulldown methods.  The
     * values of the fields in the name_fields parameter are concatentated
     * together to construnct the titles of the selections.
     * @param array Records to be processed
     * @param array fields to use to contruct title
     * @return array Array that can be fed to the createPulldown methods.
     */
    public static function createSelectArray(array $records, array $name_fields, $addEmpty=false) {
        if (empty($records) || !$name_fields) {
            return array();
        }
        $sa=array();

        if ($addEmpty) {
            $sa[]="&nbsp;";
        }

        foreach ($records as $rec) {
            // this only makes sense when there is one key
            $id = $rec->getId();

            $name = "";
            foreach ($name_fields as $n) {
                if ($name) {
                    $name .= " ";
                }
                $name .= $rec->get($n);
            }

            $sa[$id] = $name;
        }

        return $sa;
    }

    /**
     * Create form input field
     * @param string name of the input
     * @param string initial value
     * @param int maximum length
     * @param string label to be added
     * @param int|null display size, will be set from maxlength if null
     */
    public static function createInput($name, $value, $maxlength, $label=null, $size=null, $hint=null) {
        if (!$size) {
            $size=$maxlength;
        }
        return new block("formInputText", array(
            "label"     => e($label),
            "name"      => e($name),
            "value"     => e($value),
            "size"      => (int) $size,
            "maxlength" => (int) $maxlength,
            "hint"      => e($hint),
        ));
    }

    /**
     * Create pulldown (select)
     * @param string name for select box
     * @param string current value
     * @param array array of options
     * @param bool autosubmit form after making a change
     */
    public static function createPulldown($name, $value, $selectArray, $autosubmit=false) {
        return new block("select", array(
            "name"  => $name,
            "id"    => preg_replace("/^_+/", "", $name),
            "options" => $selectArray,
            "value" => $value,
            "autosubmit"    => (bool) $autosubmit
        ));
    }

    /**
     * Create pulldown (select) to change the view
     * @param string name for select box
     * @param string current value
     * @param bool autosubmit form after making a change
     */
    public static function createViewPulldown($name, $value, $autosubmit=false) {
        return static::createPulldown($name, $value, array(
            "list" => translate("List", 0),
            "tree" => translate("Tree", 0),
            "thumbs" => translate("Thumbnails", 0)), (bool) $autosubmit);
    }

    /**
     * Create pulldown (select) to determine how the automatic thumbnail is selected
     * @param string name for select box
     * @param string current value
     * @param bool autosubmit form after making a change
     */
    public static function createAutothumbPulldown($name, $value, $autosubmit=false) {
        return  static::createPulldown($name, $value, array(
            "oldest" => translate("Oldest photo", 0),
            "newest" => translate("Newest photo", 0),
            "first" => translate("Changed least recently", 0),
            "last" => translate("Changed most recently", 0),
            "highest" => translate("Highest ranked", 0),
            "random" => translate("Random", 0)),
        (bool)$autosubmit);
    }

    /**
     * Create pulldown (select) that lists all photo fields
     * @param string name for select box
     * @param string current value
     */
    public static function createPhotoFieldPulldown($name, $value) {
        return  static::createPulldown($name, $value, translate(photo::getFields(), 0));
    }

    public static function createPhotoTextPulldown($name, $value) {
        return template::createPulldown($name, $value, array(
            "" => "",
            "album" => translate("album",0),
            "category" => translate("category",0),
            "person" => translate("person",0),
            "photographer" => translate("photographer",0)));
    }

    /**
     * Create pulldown (select) that lists photo fields for the import page
     * @param string name for select box
     * @param string current value
     */
    public static function createImportFieldPulldown($name, $value) {
        return  static::createPulldown($name, $value, translate(photo::getImportFields(), 0));
    }

    /**
     * Create comparison operator pulldown
     * @param string name for select box
     * @param string current value
     */
    public static function createOperatorPulldown($name, $value = "=") {
        return static::createPulldown($name, $value,
            array(
                "="     => "=",
                "!="    => "!=",
                ">"     => ">",
                ">="    => ">=",
                "<"     => "<",
                "<="    => "<=",
                "like" => translate("like",0),
                "not like" => translate("not like",0)
        ));
    }

    /**
     * Create inequality operator [less than/more than] pulldown
     * @param string name for select box
     * @param string current value
     */
    public static function createInequalityOperatorPulldown($name, $value = "") {
        return template::createPulldown($name, $value,
           array(">" => translate("less than"), "<" => translate("more than")));
    }

    /**
     * Create pulldown (select) with options "yes" and "no" (translated)
     * @param string name for select box
     * @param string current value
     */
    public static function createBinaryOperatorPulldown($name, $value = "=") {
        return  static::createPulldown($name, $value, array(
            "=" => "=",
            "!=" => "!="
        ));
    }

    public static function createPresentOperatorPulldown($name, $value = "=") {
        return template::createPulldown($name, $value,
            array(
                "=" => translate("is in photo",0),
                "!=" => translate("is not in photo",0)
        ));
    }


    /**
     * Create pulldown (select) with options "yes" and "no" (translated)
     * @param string name for select box
     * @param string current value
     */
    public static function createYesNoPulldown($name, $value) {
        return  static::createPulldown($name, $value, array(
            "0" => translate("No", 0),
            "1" => translate("Yes", 0)
        ));
    }

    /**
     * Create conjunction [and/or] pulldown
     * @param string name for select box
     * @param string current value
     */
    public static function createConjunctionPulldown($name, $value = "") {
        return template::createPulldown($name, $value,
            array("" => "", "and" => translate("and",0), "or" => translate("or",0)));
    }

    public static function createDaysAgoPulldown($name, $value) {
        $dt=new Time(date("Y-m-d"));
        $dateArray=Array("" => "");

        $day=new DateInterval("P1D");
        for ($i = 1; $i <= conf::get("interface.max.days"); $i++) {
            $dt->sub($day);
            $dateArray[$dt->format("Y-m-d")] = $i;
        }

        return template::createPulldown($name, $value, $dateArray);
    }

    /**
     * transforms a size in bytes into a human readable format using
     * Ki Mi Gi, etc. prefixes
     * Give me a call if your database grows bigger than 1024 Yobbibytes. :-)
     * @param int bytes number of bytes
     * @return string human readable filesize
     */
    public static function getHumanReadableBytes($bytes) {
        if ($bytes==0) {
            // prevents div by 0
            return "0B";
        } else {
            $prefixes=array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
            $length=floor(log($bytes,2)/10);
            return round($bytes/pow(2,10*($length)),1) . $prefixes[floor($length)] . "B";
        }
    }

    /**
     * Display warning about disabled Javascript
     */
    public static function showJSwarning() {
        $user=user::getCurrent();
        if (($user->prefs->get("autocomp_albums")) ||
            ($user->prefs->get("autocomp_categories")) ||
            ($user->prefs->get("autocomp_places")) ||
            ($user->prefs->get("autocomp_people")) ||
            ($user->prefs->get("autocomp_photographer")) &&
            conf::get("interface.autocomplete")) {

            $warning=new block("message", array(
                "class" => "warning",
                "text"  => translate("You have enabled autocompletion for one or more dropdown " .
                                     "boxes on this page, however, you do not seem to have Javascript " .
                                     "support. You should either enable javascript or turn autocompletion " .
                                     "off, or this page will not work as expected!")
            ));

            $noscript=new block("noscript");
            $noscript->addBlocks(array($warning));
            return $noscript;
        }
    }

    /**
     * Get all templates
     * Search the template directory for directory entries
     */
    public static function getAll() {
        $templates=array();
        foreach (glob(settings::$php_loc . "/templates/*", GLOB_ONLYDIR) as $tpl) {
            $tpl=basename($tpl);
            $templates[$tpl]=$tpl;
        }
        return $templates;
    }
}
