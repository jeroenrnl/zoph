<?php
/**
 * Controller for searches
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
 * @author Jeroen Roos
 */

namespace search;

use generic\controller as genericController;
use search;
use web\request;
use user;

/**
 * Controller for searches
 */
class controller extends genericController {

    /** @var array Actions that can be used in this controller */
    protected   $actions    = array("confirm", "delete", "display", "edit", "insert", "new", "update", "search");

    /** @var Where to redirect after actions */
    public $redirect="search.php";

    /**
     * Create a controller using a web request
     * @param request request
     */
    public function __construct(request $request) {
        parent::__construct($request);
        if (isset($this->request["search_id"])) {
            $search = new search($this->request["search_id"]);
            $search->lookup();
        } else if ($this->request["_action"]=="new") {
            $vars=$request->getRequestVarsClean();
            unset($vars["_action"]);
            unset($vars["_crumb"]);
            $urlVars=array();

            foreach ($vars as $key => $val) {
                // Change key#0 into key[0]:
                $key=preg_replace("/\#([0-9]+)/", "[$1]", $key);
                // Change key[0]-children into key_children[0] because everything
                // after ] in a variable name is lost fix for bug#2890387
                $key=preg_replace("/\[(.+)\]-([a-z]+)/", "_$2[$1]", $key);
                $urlVars[]=e($key) . "=" . e($val);
            }
            $url = implode("&amp;", $urlVars);
            $search=new search();
            $search->set("search", $url);
            $search->set("owner", user::getCurrent()->getId());
        } else {
            $search=new search();
            $this->request["_action"]="display";
        }
        $this->setObject($search);
        $this->doAction();
    }

    /**
     * Do action 'search'
     */
    public function actionSearch() {
        $this->view="photos";
    }
}
