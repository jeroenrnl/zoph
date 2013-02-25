<?php
/*
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
 */
    require_once("include.inc.php");

    $tpl=new template("main", array(
        "title" => translate("Reports")
    ));

    $top_albums = album::getTopN();
    if ($top_albums) {
        $block_albums=new block("report", array(
            "title"     => translate("Most Populated Albums"),
            "lines"     => $top_albums
        ));
        $tpl->addBlock($block_albums);
    }
    
    $top_categories = category::getTopN();
    if ($top_categories) {
        $block_categories=new block("report", array(
            "title"     => translate("Most Populated Categories"),
            "lines"     => $top_categories
        ));
        $tpl->addBlock($block_categories);
    }

    $top_people = person::getTopN();
    if ($top_people) {
        $block_people=new block("report", array(
            "title"     => translate("Most Photographed People"),
            "lines"     => $top_people
        ));
        $tpl->addBlock($block_people);

    }
    $top_places = place::getTopN();
    if ($top_places) {
        $block_places=new block("report", array(
            "title"     => translate("Most Photographed Places"),
            "lines"     => $top_places
        ));
        $tpl->addBlock($block_places);
    }
    
    $graph=new block("graph_bar", array(
        "title"     => translate("photo ratings", 0),
        "class"     => "ratings",
        "value_label" => translate("rating",0 ),
        "count_label" => translate("count",0 ),
        "rows"      => rating::getGraphArray()
    ));

    $tpl->addBlock($graph);

    echo $tpl;
?>
