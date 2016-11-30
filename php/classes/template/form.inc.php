<?php
/**
 * Class that takes care of displaying a form
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

/**
 * This class takes care of displaying forms
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class form extends block {
    /**
     * Add a form field INPUT type text
     * @param string name
     * @param string current / initial value
     * @param string label text for label
     * @param string input hint
     * @param int size of the field
     */
    public function addInputText($name, $value, $label=null, $hint=null, $maxlength=32, $size=null) {
        if (!$size) {
            $size=$maxlength;
        }
        $this->addBlock(new block("formInputText", array(
            "name"  => $name,
            "value" => e($value),
            "label" => e($label),
            "hint"  => e($hint),
            "size"  => (int) $size,
            "maxlength"  => (int) $maxlength
        )));
    }

    /**
     * Add a form field INPUT type password
     * @param string name
     * @param string label text for label
     * @param string input hint
     * @param int size of the field
     */
    public function addInputPassword($name, $label=null, $hint=null, $size=32) {
        $this->addBlock(new block("formInputPassword", array(
            "name"  => $name,
            "label" => e($label),
            "hint"  => e($hint),
            "size"  => (int) $size
        )));
    }

    /**
     * Add a form field INPUT type hidden
     * @param string name
     * @param string value
     */
    public function addInputHidden($name, $value) {
        $this->addBlock(new block("formInputHidden", array(
            "name"  => $name,
            "value" => e($value),
        )));
    }

    /**
     * Add a form field INPUT type checkbox
     * @param string name
     * @param bool checked
     * @param string label text for label
     * @param string input hint
     */
    public function addInputCheckbox($name, $checked, $label, $hint=null) {
        $this->addBlock(new block("formInputCheckbox", array(
            "name"  => $name,
            "checked" => $checked,
            "label" => e($label),
            "hint"  => e($hint),
        )));
    }

    /**
     * Add a form field TEXTAREA
     * @param string name
     * @param string current / initial value
     * @param string label text for label
     * @param int columns
     * @param int rows
     */
    public function addTextarea($name, $value, $label=null, $cols=40, $rows=4) {
        $this->addBlock(new block("formTextarea", array(
            "name"  => $name,
            "value" => e($value),
            "label" => e($label),
            "cols"  => (int) $cols,
            "rows"  => (int) $rows
        )));
    }

    /**
     * Add a form field dropdown
     * this function is not actually creating the dropdown, but
     * acts as a wrapper around the dropdown, to add a label
     * @param string name
     * @param block dropdown
     * @param string label text for label
     */
    public function addPulldown($name, block $dropdown, $label) {
        $this->addBlock(new block("formPulldown", array(
            "name"      => $name,
            "dropdown"  => $dropdown,
            "label"     => $label
        )));
    }
}
