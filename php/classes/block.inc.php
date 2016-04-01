<?php
/**
 * Class that takes care of displaying blocks
 *  A block is a template for a part of the screen,
 *  while a template is a full page.
 * @todo this separation is still ongoing and won't be finished
 *       until all HTML has moved into templates (and blocks).
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

/**
 * This class takes care of displaying blocks
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class block extends template {
    /**
     * Create block object
     *
     * @param string Name of template (without path or extension)
     * @param array Array of variables that can be used in the template
     * @return template
     */
    public function __construct($template, $vars=null) {
        $this->vars=$vars;
        if (!preg_match("/^[A-Za-z0-9_]+$/", $template)) {
            log::msg("Illegal characters in template", log::FATAL, log::GENERAL);
        } else {
            $this->template="templates/default/blocks/" . $template . ".tpl.php";
        }
    }

}
