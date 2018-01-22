<?php
/**
 * Controller for groups
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

namespace group;

use generic\controller as genericController;
use group;
use web\request;
use user;

/**
 * Controller for groups
 */
class controller extends genericController {

    /** @var Where to redirect after actions */
    public $redirect="groups.php";

    /**
     * Create a controller using a web request
     * @param request request
     */
    public function __construct(request $request) {
        parent::__construct($request);
        $group = new group($this->request["group_id"]);
        $group->lookup();
        $this->setObject($group);
        $this->doAction();
    }

    /**
     * Action: update
     * The update action processes a form as generated after the "edit" action.
     * The subsequently called view displays the object.
     * takes care of adding and removing members of the group
     */
    protected function actionUpdate() {
        $this->object->setFields($this->request->getRequestVars());
        if (isset($this->request["_member"]) && ((int) $this->request["_member"] > 0)) {
            $this->object->addMember(new user((int) $this->request["_member"]));
        }

        if (is_array($this->request["_removeMember"])) {
            foreach ($this->request["_removeMember"] as $user_id) {
                $this->object->removeMember(new user((int) $user_id));
            }
        }
        $this->object->update();
        $this->view = "update";
    }

    /**
     * Action: insert
     * The insert action processes a form as generated after the "new" action.
     * The subsequently called view displays a form to make more changes to the group.
     * this is a change from the generic controller, because group access rights can only
     * be modified after insertion.
     */
    protected function actionInsert() {
        parent::actionInsert();
        $this->view="update";
    }
}
