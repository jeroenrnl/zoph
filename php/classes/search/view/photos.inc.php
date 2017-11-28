<?php
/**
 * View for search page
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

namespace search\view;

use web\request;
use user;

/**
 * This view displays the search page
 */
class photos {

    /** * @var array request variables */
    private $vars;
    /** * @var request web request */
    private $request;

    /**
     * Create view
     * @param request web request
     */
    public function __construct(request $request) {
        $this->request=$request;
        $this->vars=$request->getRequestVars();
    }

    /**
     * Output view
     */
    public function view() {
        $request_vars=$this->vars;
        $request=$this->request;
        $user=user::getCurrent();
        ob_start();
        require("photos.php");
        return ob_get_clean();
    }
}
