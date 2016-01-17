<?php
/**
 * Display and modify breadcrumbs
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
 * @author Jason Geiger
 * @author Jeroen Roos
 */

if ($user->prefs->get("show_breadcrumbs")) {

    breadcrumb::init();

    // can probably be removed
    if(!empty($tpl_title)) {
        $title=$tpl_title;
    }

    if(!isset($_action)) {
        $_action="";
    }

    new breadcrumb($title, $_action);

    $_clear_crumbs = getvar("_clear_crumbs");
    $_crumb = getvar("_crumb");

    if ($_clear_crumbs) {
        breadcrumb::eat(0);
    } else if ($_crumb) {
        breadcrumb::eat($_crumb);
    }

    echo breadcrumb::display();


}
?>
