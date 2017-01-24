<?php
/**
 * Controller for permissions
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

namespace permissions;

use album;
use conf\conf;
use generic\controller as genericController;
use group;
use permissions;
use web\request;

/**
 * Controller for permissions
 */
class controller extends genericController {
    protected $actions=array("updatealbums", "updategroups");

    /**
     * Create controller from web\request
     */
    public function __construct(request $request) {
        parent::__construct($request);
        if ($this->request["_action"]=="updatealbums") {
            $this->setObject(new group($this->request["group_id"]));
        } else if ($this->request["_action"]=="updategroups") {
            $this->setObject(new album($this->request["album_id"]));
        }
        $this->doAction();
    }

    /**
     * Process changes to group permissions
     */
    protected function actionUpdategroups() {
        // Check if the "Grant access to all groups" checkbox is ticked
        if ($this->request["_access_level_all_checkbox"]) {
            $groups = group::getAll();
            foreach ($groups as $group) {
                $permissions = new permissions($group->getId(), $this->object->getId());
                $permissions->setFields($this->request->getRequestVars(), "", "_all");
                if (!conf::get("watermark.enable")) {
                    $permissions->set("watermark_level", 0);
                }
                $permissions->insert();
            }
        }

        $groups = $this->object->getPermissionArray(true);
        foreach ($groups as $group) {
            $group->lookup();
            $id=$group->getId();

            if (isset($this->request["_remove_permission_group__$id"])) {
                $permissions = new permissions($id, $this->object->getId());
                $permissions->delete();
            } else {
                $permissions = new permissions();
                $permissions->setFields($this->request->getRequestVars(), "", "__$id");
                $permissions->update();
            }
        }
        // Check if new album should be added
        if ($this->request["group_id_new"]) {
            $permissions = new permissions();
            $permissions->setFields($this->request->getRequestVars(), "", "_new");

            if (!conf::get("watermark.enable")) {
                $permissions->set("watermark_level", 0);
            }
            $permissions->insert();
        }

        $this->view="album";

    }

    /**
     * Process changes to album permissions
     */
    protected function actionUpdatealbums() {
        // Check if the "Grant access to all albums" checkbox is ticked
        if ($this->request["_access_level_all_checkbox"]) {
            $albums = album::getAll();
            foreach ($albums as $alb) {
                $permissions = new permissions($this->object->getId(), $alb->getId());
                $permissions->setFields($this->request->getRequestVars(), "", "_all");
                if (!conf::get("watermark.enable")) {
                    $permissions->set("watermark_level", 0);
                }
                $permissions->insert();
            }
        }

        $albums = $this->object->getAlbums();
        foreach ($albums as $album) {
            $album->lookup();
            $id=$album->getId();

            if (isset($this->request["_remove_permission_album__$id"])) {
                $permissions = new permissions($this->object->getId(), $id);
                $permissions->delete();
            } else {
                $permissions = new permissions();
                $permissions->setFields($this->request->getRequestVars(), "", "__$id");
                $permissions->update();
            }
        }
        // Check if new album should be added
        if ($this->request["album_id_new"]) {
            $permissions = new permissions();
            $permissions->setFields($this->request->getRequestVars(), "", "_new");

            if (!conf::get("watermark.enable")) {
                $permissions->set("watermark_level", 0);
            }
            $permissions->insert();
        }

        $this->view="group";

    }

}
