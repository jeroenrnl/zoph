<?php
/**
 * View for search page
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

namespace search\view;

use album;
use category;
use conf\conf;
use group;
use geo\map;
use geo\marker;
use search;
use template\template;
use template\block;
use web\request;

/**
 * This view displays the search page
 */
class display {

    /**
     * @var request variables
     */
    private $vars;

    /**
     * Create view
     * @param request web request
     */
    public function __construct(request $request) {
        $this->vars=$request->getRequestVars();
    }

    /**
     * Output view
     */
    public function view() {
        if (conf::get("maps.provider")) {
            $map = new map();
            $map->setEditable();

            if (isset($this->vars["lat"]) && isset($this->vars["lon"])) {
                $map->addMarker(new marker($this->vars["lat"], $this->vars["lon"], null, null, null));
            }
        }

        $search = new template("main", array(
            "title" => translate("Search"),
            "map"   => isset($map) ? $map : null
        ));
        $form = new block("searchForm", array(
            "submit"        => translate("search")
        ));

        foreach ($this->getSearchTerms() as $param => $term) {
            $form->addBlocks($this->buildTerm($param, $term));

        }

        if (conf::get("maps.provider")) {
            $form->addBlock($this->buildMapTerm());
        }
        $search->addBlocks(array($form));

        $search->addBlocks(array(search::getList()));

        return $search;
    }

    /**
     * Get an array of search terms, this array is used to build the search page
     * @return array elements ('search terms') to build search page with
     */
    private function getSearchTerms() {
        return array(
            "date"  =>  array(
                "label" => translate("photos taken"),
                "op"    => array("template\\template", "createInequalityOperatorPulldown"),
                "value" => array("template\\template", "createDaysAgoPulldown"),
                "value_text"    => translate("days ago")
            ),
            "timestamp"  =>  array(
                "label" => translate("photos modified"),
                "op"    => array("template\\template", "createInequalityOperatorPulldown"),
                "value" => array("template\\template", "createDaysAgoPulldown"),
                "value_text"    => translate("days ago")
            ),
            "album_id"     => array(
                "label" => translate("album"),
                "op"    => array("template\\template", "createBinaryOperatorPulldown"),
                "value" => array("album", "createPullDown"),
                "child" => "album_id_children",
                "child_label"   => translate("include sub-albums")
            ),
            "category_id"     => array(
                "label" => translate("category"),
                "op"    => array("template\\template", "createBinaryOperatorPulldown"),
                "value" => array("category", "createPullDown"),
                "child" => "category_id_children",
                "child_label"   => translate("include sub-categories")
            ),
            "location_id"     => array(
                "label" => translate("location"),
                "op"    => array("template\\template", "createBinaryOperatorPulldown"),
                "value" => array("place", "createPullDown"),
                "child" => "location_id_children",
                "child_label"   => translate("include sub-locations")
            ),
            "rating"     => array(
                "label" => translate("rating"),
                "op"    => array("template\\template", "createOperatorPulldown"),
                "value" => array("rating", "createPullDown"),
            ),
            "person_id"     => array(
                "label" => translate("person"),
                "op"    => array("template\\template", "createPresentOperatorPulldown"),
                "value" => array("person", "createPullDown"),
            ),
            "photographer_id"     => array(
                "label" => translate("photographer"),
                "op"    => array("template\\template", "createBinaryOperatorPulldown"),
                "value" => array("photographer", "createPullDown"),
            ),
            "field"     => array(
                "label" => array("template\\template", "createPhotoFieldPulldown"),
                "op"    => array("template\\template", "createOperatorPulldown"),
            ),
            "text"     => array(
                "label" => array("template\\template", "createPhotoTextPulldown"),
                "op"    => array("template\\template", "createTextOperatorPulldown"),
            ));
    }

    /**
     * construct template blocks from searchTerms and the GET / POST parameters given to the page
     * @param string parameter to build searchTerm for
     * @param array searchTerm array with fields
     *      label       : label for the searchterm
     *      op          : template containing the operator (=, >, <, etc)
     *      value       : template for value of the field (usually a dropdown)  * optional
     *      value_text  : text to add after the value                           * optional
     *      child       : tickbox for 'include children'                        * optional
     *      child_text  : text for the tickbox                                  * optional
     */
    private function buildTerm($param, array $term) {
        $blocks=array();
        $count = isset($this->vars[$param]) ? sizeof($this->vars[$param]) - 1: 0;
        for ($i = 0; $i <= $count; $i++) {
            $conj   = isset($this->vars["_${param}_conj"][$i])  ? $this->vars["_${param}_conj"][$i] : null;
            $op     = isset($this->vars["_${param}_op"][$i])    ? $this->vars["_${param}_op"][$i]   : null;
            $value  = isset($this->vars[$param][$i])            ? $this->vars[$param][$i]           : null;
            $value  = $value == "+"                             ? ""                                : $value;
            if (is_array($term["label"])) {
                $labelVal = isset($this->vars["_${param}"][$i]) ? $this->vars["_${param}"][$i]      : null;
                $label = call_user_func($term["label"], "_${param}[$i]", $labelVal);

                $value = template::createInput("${param}[$i]", $value, 20);
            } else {
                $label = $term["label"];
                $value = call_user_func($term["value"], "${param}[$i]", $value);
            }
            $templateParams=array(
                "inc"   => ($i == $count) ?  $param . "[" . ($i + 1) ."]": false,
                "label" => $label,
                "conj"  => template::createConjunctionPulldown("_${param}_conj[$i]", $conj),
                "op"    => call_user_func($term["op"], "_${param}_op[$i]", $op),
                "value" => $value,
                "value_text"    => isset($term["value_text"]) ? $term["value_text"] : null,
            );
            if (isset($term["child"])) {
                $children = isset($this->vars["_${term["child"]}"][$i]);
                $templateParams += array(
                    "child" => "_${term["child"]}[$i]",
                    "child_checked" => $children ? "checked" : "",
                    "child_label"   => $term["child_label"]
                );
            }
            $blocks[]=new block("searchTerm", $templateParams);
        }
        return $blocks;
    }

    /**
     * Build search term to search using the map
     */
    private function buildMapTerm() {
        $conj   = isset($this->vars["_latlon_conj"])        ? $this->vars["_latlon_conj"]       : null;
        $value  = isset($this->vars["_latlon_distance"])    ? $this->vars["_latlon_distance"]   : null;
        $entity = isset($this->vars["_latlon_entity"])      ? $this->vars["_latlon_entity"]     : "km";
        $lat    = isset($this->vars["lat"])                 ? $this->vars["lat"]                : null;
        $lon    = isset($this->vars["lon"])                 ? $this->vars["lon"]                : null;
        $places = isset($this->vars["_latlon_places"]);
        $photos = isset($this->vars["_latlon_photos"]);

        $entityDropdown = template::createPulldown("_latlon_entity", $entity,
                array("km" => "km", "miles" => "miles"));
        $valueInput = template::createInput("_latlon_distance", $value, 10);

        $templateParams=array(
            "conj"      => template::createConjunctionPulldown("_latlon_conj", $conj),
            "value"     => $valueInput,
            "entity"    => $entityDropdown,
            "places_checked" => $places ? "checked" : "",
            "photos_checked" => $photos ? "checked" : "",
            "lat"       => template::createInput("lat", $lat, 10),
            "lon"       => template::createInput("lon", $lon, 10)
        );
        return new block("searchTermMap", $templateParams);
    }
}
