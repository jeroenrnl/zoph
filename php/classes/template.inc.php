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

/**
 * This class takes care of displaying templates
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class template {
    public $js=array();
    public $style="";
    public $script="";
    public $css=array();
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
        if(preg_match("/^[A-Za-z0-9_\-]+$/", $tpl) && preg_match("/^[A-Za-z0-9_\-]+$/", $template)) {
            $file="templates/" . $tpl . "/" . $template . ".tpl.php";
            if(!file_exists($file)) {
                $file="templates/default/" . $template . ".tpl.php";
            }
            $this->template=$file;

            $this->css[]="css.php";
        } else {
            log::msg("Illegal characters in template", log::FATAL, log::GENERAL);
        }
    }
    /**
     * Get image URL for specific template
     * if the image does not exist in the current template, the default will be be returned
     * This enables template builders to only include the parts of the template that
     * have been changed
     * @param string image name
     * @return string relative image url
     */
    public static function getImage($image) {
        $tpl=conf::get("interface.template");
        if(preg_match("/^[A-Za-z0-9_\-\/\.]+$/", $image) && !preg_match("/\.\./", $image)) {
            $file="templates/" . $tpl . "/images/" . $image;
            if(!file_exists($file)) {
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
        if($this->vars) {
            extract($this->vars,  EXTR_PREFIX_ALL, "tpl");
        }
        if(!defined("ZOPH")) {
            define('ZOPH', true);
        }
        try {
            ob_start();
                include($this->template);
           return ob_get_clean();
        } catch(Exception $e) {
            echo $e->getMessage();
            die();
        }
    }
    
    /**
     * Return the template in a string
     * 
     * @return string
     */
    public function toString() {
        return sprintf("%s", $this);
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

        if(!empty($this->script)) {
            $html.="    <script type='text/javascript'>";
            $html.="        " . $this->script;
            $html.="    </script>";
        }    

        foreach($this->css as $css_href) {
            $html.="    <link type='text/css' rel='stylesheet' href='" . 
                $css_href . "'>\n";
        }
        if(!empty($this->style)) {
            $html.="    <style>" . $this->style . "</style>\n";
        }
        if(!empty($title)) {
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
        foreach($this->getBlocks() as $block) {
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
        foreach($al as $title => $link) {
            $this->addActionlink($title, $link);
        }
    }

    /**
     * Markup an array of actionlinks using the actionlinks template
     * @param array Optional array of actionlinks, otherwise use the ones in the class
     */
    private function getActionlinks(array $actionlinks=null) {
        if($actionlinks==null) {
            $actionlinks=$this->actionlinks;
        }
        if(is_array($actionlinks)) {
            $tpl=new template("actionlinks", array(
                "actionlinks" => $actionlinks)
            );
            return $tpl->toString();
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
                if ($links) { $links .= ", "; }
                $links .= $rec->getLink();
            }
        }

        return $links;
    }

    /**
     * Creates an array to be used in the create_pulldown methods.  The
     * values of the fields in the name_fields parameter are concatentated
     * together to construnct the titles of the selections.
     * @param array Records to be processed
     * @param array fields to use to contruct title
     * @return array Array that can be fed to the create_pulldown methods.
     */
    public static function createSelectArray(array $records, array $name_fields) {
        if (!$records || !$name_fields) { return; }

        foreach ($records as $rec) {
            // this only makes sense when there is one key
            $id = $rec->get($rec->primary_keys[0]);

            $name = "";
            foreach ($name_fields as $n) {
                if ($name) { $name .= " "; }
                $name .= $rec->get($n);
            }

            $sa[$id] = $name;
        }

        return $sa;
    }

    /**
     * Get all templates
     * Search the template directory for directory entries
     */
    public static function getAll() {
        $templates=array();
        foreach(glob(settings::$php_loc . "/templates/*", GLOB_ONLYDIR) as $tpl) {
            $tpl=basename($tpl);
            $templates[$tpl]=$tpl;
        }
        return $templates;
    }
}
