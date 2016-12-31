<?php
/**
 * A confItemSelect defines a configuration item that is defined using a selectbox
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
 * A confItemSelect defines a configuration item that is defined using a selectbox
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class select extends item {
    /** @var array list of options */
    private $options=array();

    /** @var bool translate options */
    private $translate=true;

    /**
     * Add an option
     * @param string key
     * @param string description
     */
    public function addOption($key, $desc) {
        $this->options[$key]=$desc;
    }

    /**
     * Add multiple options
     * @param array array of options
     */
    public function addOptions(array $options) {
        foreach ($options as $key=>$desc) {
            $this->addOption($key, $desc);
        }
    }

    /**
     * Get array of options
     * @return array options
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Set whether or not the options must be translated
     * @param bool translate yes/no
     */
    public function setOptionsTranslate($translate) {
        $this->translate=$translate;
    }

    /**
     * Check value
     * check if a specific value is legal for this option
     * @param string value
     * @return bool
     */
    public function checkValue($value) {
        return array_key_exists($value, $this->options);
    }

    /**
     * Display this option through template
     * @return block template block
     */
    public function display() {
        if ($this->internal) {
            return;
        }
        $params=array(
            "label"     => e(translate($this->getLabel(), 0)),
            "name"      => e($this->getName()),
            "value"     => e($this->getValue()),
            "desc"      => e(translate($this->getDesc(), 0)),
            "unmet"     => $this->displayUnmetRequirements(),
            "hint"      => e(translate($this->getHint(), 0)),
            "enabled"   => (bool) $this->requirementsMet()
        );
        if ($this->translate) {
            $params["options"] = translate($this->getOptions(), 0);
        } else {
            $params["options"] = $this->getOptions();
        }
        $tpl=new block("confItemSelect", $params);
        return $tpl;
     }
}
