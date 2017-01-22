<?php
/**
 * View for editting permissions
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

namespace permissions\view;

use album;
use conf\conf;
use group;
use template\block;

/**
 * View for editting permissions
 */
class edit {

    /**
     * @var group|album Object to operate on
     */
    private $object;

    /**
     * Create view from object
     * @param group|album Object to operate on
     */
    public function __construct($obj) {
        $this->object=$obj;
    }

    /**
     * Output view
     */
    public function view() {
        $accessLevelAll=new block("formInputText", array(
            "label" => null,
            "name"  => "access_level_all",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));
        $wmLevelAll=new block("formInputText", array(
            "label" => null,
            "name"  => "watermark_level_all",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));
        $accessLevelNew=new block("formInputText", array(
            "label" => null,
            "name"  => "access_level_new",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));
        $wmLevelNew=new block("formInputText", array(
            "label" => null,
            "name"  => "watermark_level_new",
            "size"  => 4,
            "maxlength"  => 2,
            "value" => "5"
        ));

        $class = get_class($this->object);
        $edit = $this->object instanceof album ? "group" : "album";
        $gp = new block("editPermissions", array(
            "watermark"         => conf::get("watermark.enable"),
            "edit"              => $edit,
            "fixed"             => get_class($this->object),
            "id"                => $this->object->getId(),
            "edit_id"           => $edit . "_id",
            "accessLevelAll"    => $accessLevelAll,
            "wmLevelAll"        => $wmLevelAll,
            "accessLevelNew"    => $accessLevelNew,
            "wmLevelNew"        => $wmLevelNew,
            "permissions"       => $this->object->getPermissionArray()
        ));
        return $gp;
    }
}
