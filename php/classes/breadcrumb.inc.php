<?php
/**
 * Display and modify breadcrumbs
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

use template\block;

class breadcrumb {

    /** @var string title of the crumb */
    private $title;
    /** @var string url of the crumb */
    /** @todo should be named $url */
    private $link;
    /** @var array Current breadcrumbs */
    private static $crumbs=array();


    /**
     * Add a crumb
     * Crumbs are the path a user followed through Zoph's web GUI and can be
     * used to easily go back to an earlier visited page
     * only add a crumb if a title was set and if there is either no
     * action or a safe action ("edit", "delete", etc would be unsafe)
     * @param string title
     * @param string action (display, edit, delete, etc.)
     */
    public function __construct($title, $action) {
        $user=user::getCurrent();
        $link=htmlentities($_SERVER["REQUEST_URI"]);
        $page=array_reverse(explode("/",$_SERVER['PHP_SELF']));
        $page=$page[0];

        $crumbActions=array("", "display", "search", translate("search"), "notify", "compose", "new");
        if ($user->prefs->get("auto_edit") && $page=="photo.php") {
            $crumbActions[]="edit";
        }

        $numCrumbs = count(static::$crumbs);

        if (isset($title) && $numCrumbs < 100 && in_array($action, $crumbActions, true)) {
            if ($numCrumbs == 0 || (!strpos($link, "_crumb="))) {

                // if title is the same remove last and add new
                if ($numCrumbs > 0 && static::getLast()->getTitle()==$title) {
                    static::eat();
                } else {
                    $numCrumbs++;
                }

                $question = strpos($link, "?");
                if ($question > 0) {
                    $link =
                        substr($link, 0, $question) ."?_crumb=$numCrumbs&amp;" .
                        substr($link, $question + 1);
                } else {
                    $link .= "?_crumb=$numCrumbs";
                }

                $this->title=$title;
                $this->link=$link;
                static::$crumbs[] = $this;
            }
        }
    }

    public function getTitle() {
        return $this->title;
    }

    public function getLink() {
        return $this->link;
    }

    /**
     * This function reads the crumbs from the session, and makes sure it is updated
     */
    public static function init() {
        if (isset($_SESSION["crumbs"])) {
            static::$crumbs=$_SESSION["crumbs"];
        }
        $_SESSION["crumbs"]=&static::$crumbs;
    }


    /**
     * construct the link for clearing the crumbs (the 'x' on the right)
     */
    public static function getClearURL() {
        if ($_POST) {
            $clear_url=$_SERVER["PHP_SELF"] . "?" . getvar("_qs");
        } else {
            $clear_url = htmlentities($_SERVER["REQUEST_URI"]);
        }

        if (strpos($clear_url, "clear_crumbs") == 0) {
            if (strpos($clear_url, "?") > 0) {
                $clear_url .= "&amp;";
            } else {
                $clear_url .= "?";
            }

            $clear_url .= "_clear_crumbs=1";
        }
        return $clear_url;
    }

    /**
     * Eat a crumb
     * A crumb is 'eaten' when a user clicks on the link
     * it means that the crumbs at the end are removed up to the place
     * where the user went back to
     * @param int number of crumbs to eat
     */
    public static function eat($num = -1) {
        if (count(static::$crumbs) > 0) {
            if ($num < 0) {
                $num = count(static::$crumbs) - 1;
            }
            static::$crumbs = array_slice(static::$crumbs, 0, $num);
        }
    }

    /**
     * Get the last crumb
     */
    public static function getLast() {
        if (count(static::$crumbs) > 0) {
            return end(static::$crumbs);
        }
    }

    public static function display() {
        $user=user::getCurrent();

        $max_crumbs=$user->prefs->get("num_breadcrumbs");
        if (($num_crumbs = count(static::$crumbs)) > $max_crumbs) {
            $crumbs=array_slice(static::$crumbs, $num_crumbs - $max_crumbs);
            $class="firstdots";
        } else {
            $crumbs=static::$crumbs;
            $class="";
        }
        $tpl=new block("breadcrumbs", array(
            "crumbs"    =>  $crumbs,
            "class"     =>  $class,
            "clearURL"  =>  static::getClearURL()
        ));

        return $tpl;
    }

}
?>
