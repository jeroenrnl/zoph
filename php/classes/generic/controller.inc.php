<?php
/**
 * Generic Controller
 * Handles basic form actions, such as confirm, delete, edit, insert, new and update
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
 * @author Jason Geiger
 */

namespace generic;

use breadcrumb;

use web\request;

use zophTable;

/**
 * Generic Controller
 * Handles basic form actions, such as confirm, delete, edit, insert, new and update
 */
abstract class controller {

    /** @var request holds request */
    protected   $request;
    /** @var zophTable holds object to operate on */
    protected   $object;
    /** @var string where to redirect after action */
    public      $redirect   = "zoph.php";
    /** @var array Actions that can be used in this controller */
    protected   $actions    = array("confirm", "delete", "display", "edit", "insert", "new", "update");
    /** @var string view to call after action */
    protected   $view       = "display";

    /**
     * Create a new controller from a web request
     * @param web\request Request to proces
     */
    public function __construct(request $request) {
        $this->request=$request;
    }

    /**
     * Set the object to operate on
     * @param zophTable object to operate on
     */
    public function setObject(zophTable $obj) {
        $this->object=$obj;
    }

    /**
     * Do the action as set in the request
     * in the current mode of operation, no authorization checking is needed,
     * because currently, all controllers can only be called by an admin user
     * in the future, when more of Zoph is controlled by controllers, additional
     * authorization checking needs to be added
     */
    public function doAction() {
        $action=$this->request["_action"];

        /** @todo This needs more authorization checking */
        if (in_array($action, $this->actions)) {
            $function = "action" . ucwords($action);
            $this->$function();
        } else {
            $this->actionDisplay();
        }
    }

    /**
     * Action: edit
     * The edit action calls a view that will allow the user to update the
     * current object.
     */
    protected function actionEdit() {
        $this->view = "update";

    }

    /**
     * Action: update
     * The update action processes a form as generated after the "edit" action.
     * The subsequently called view displays the object.
     */
    protected function actionUpdate() {
        $this->object->setFields($this->request->getRequestVars());
        $this->object->update();
        $this->view = "display";

    }

    /**
     * Action: new
     * The new action calls a view that displays a form that allows the user
     * to create a new object.
     */
    protected function actionNew() {
        $this->object->setFields($this->request->getRequestVars());
        $this->view = "insert";
    }

    /**
     * Action: insert
     * The insert action processes a form as generated after the "new" action.
     * The subsequently called view displays the object.
     */
    protected function actionInsert() {
        $this->object->setFields($this->request->getRequestVars());
        $this->object->insert();
        $this->view = "display";

    }

    /**
     * Action: delete
     * The delete action asks for confirmation of a delete of the current object
     */
    protected function actionDelete() {
        $this->view = "confirm";

    }

    /**
     * Action: confirm
     * The confirm action is called when the user confirms the delete
     * this deletes the object and then redirects the user back the the
     * last page he visited before the delete.
     */
    protected function actionConfirm() {
        $this->object->delete();

        breadcrumb::eat();
        $crumb = breadcrumb::getLast();
        if ($crumb instanceof breadcrumb) {
            $this->redirect=$crumb->getLink();
        }

        $this->view = "redirect";
    }

    /**
     * The display action displays the object
     */
    protected function actionDisplay() {
        $this->view = "display";
    }

    /**
     * get View
     * each of the actions dictate a subsequent view in the workflow,
     * the view can be called by this function
     * currently, it simply returns a name, in the future an action View object
     * may be returned.
     */
    public function getView() {
        return $this->view;
    }

    /**
     * Get the object to operate on
     */
    public function getObject() {
        return $this->object;
    }
}
