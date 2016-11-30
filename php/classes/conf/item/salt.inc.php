<?php
/**
 * A conf\item\salt is a special kind of conf\item\text, which allows auto generation
 * of a secure salt string.
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

namespace conf\item;

use template\block;

/**
 * A conf\item\salt is a special kind of conf\item\text, which allows auto generation
 * of a secure salt string.
 * @package Zoph
 * @author Jeroen Roos
 */
class salt extends text {

    protected $regex="[a-zA-Z0-9]{10,40}";
    protected $size=40;

    public function display() {
        if ($this->internal) {
            return;
        }
        $id=str_replace(".", "_", $this->getName());
        $tpl=new block("confItemSalt", array(
            "label" => e(translate($this->getLabel(),0)),
            "name" => e($this->getName()),
            "id" => e($id),
            "value" => e($this->getValue()),
            "desc" => e(translate($this->getDesc(),0)),
            "hint" => e(translate($this->getHint(),0)),
            "regex" => e($this->regex),
            "size" => (int) $this->size,
            "req" => ($this->required ? "required" : "")
        ));
        return $tpl;
    }
}
