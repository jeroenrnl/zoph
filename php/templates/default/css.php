<?php
/* This file is part of Zoph.
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

use template\colorScheme;
use template\template;
use conf\conf;
?>

@import "templates/default/reset.css";
@import "templates/default/leaflet.css";
/* Some of the styles have been based on http://www.alistapart.com/articles/taminglists/ */

/* Main CSS style, all elements inherit these settings */

body    {
    font-family: Arial, Verdana, sans-serif;
    font-size: medium;
    color: <?= colorScheme::getColor("text_color") ?>;
    background: <?= colorScheme::getColor("page_bg_color") ?>;
    width: <?= conf::get("interface.width"); ?>;
    margin: 8px auto; /* To center the page */
    line-height: 1.1;
}

/* Links */

a   {
    color: <?= colorScheme::getColor("link_color") ?>;
}

h1  {
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    text-align: left;
    width: 100%;
    clear: left;
    font-size: x-large;
    font-weight: bold;
    padding: 2px 10px 2px 10px;
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
    margin: -1px 0;
    z-index: 5;
}

/* Secondary title such as album title */
h2  {
    text-align: left;
    font-size: large;
    margin-top: 10px;
    margin-bottom: 10px;
    font-weight: bold;
}

h2.logon {
    margin-bottom: 30px;
}

/* Level 3 title */
h3  {
    text-align: center;
    font-size: medium;
    font-weight: bold;
}

/* Unordered list */
ul  {
    padding-left: 1em;
    margin: 0.5em 1em 1em 1em;
}

ul.thumbs {
    clear: both;
    list-style: none;
    padding-bottom: 80px;
}

ul.thumbs > li {
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    position: relative;
    display: block;
    text-align: center;
    width: <?= THUMB_SIZE + 40 ?>px;
    height:<?= THUMB_SIZE + 60 ?>px;
    float:left;
    margin: 5px;
    padding: 5px;

    border-radius: 10px;

    box-shadow: 5px 5px 10px rgba(0,0,0,0.5), 0 0 10px rgba(255,255,255,0.6) inset;
}

ul.thumbs > li dl.extradata {
    display: none;
}

ul.thumbs > li.thumb_circle {
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    box-shadow: 5px 5px 10px rgba(0,0,0,0.5), 0 0 10px rgba(0,0,0,0.2) inset;
}

div.details {
    display: block;
    position: absolute;
    float: left;

    padding: 20px;
    background: rgba(255,255,255,0.9);

    border-radius: 10px;
    box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
    z-index: 10;
}

dl dt {
    font-weight: bold;
    width: 15%;
    float: left;
    clear: left;
    min-height: 1.5em;
    text-align: right;
    margin: 2px 5px;
}

dl dd {
    width: 75%;
    float: left;
    clear: right;
    margin: 2px 5px;
}

div.details > h3 {
    margin: -20px -20px 20px -20px;
    padding: 5px 20px;
    border-radius: 10px 10px 0 0;
    width: 100%;
    background: <?= colorScheme::getColor("tab_bg_color") ?>;
    color: <?= colorScheme::getColor("tab_font_color") ?>;
    font-size: large;
    text-align: left;
}

div.details > dl {
    display: block;
    width: 100%
}

div.details > dl > dt {
    float: left;
    clear: left;
    margin: 1px 5px;
}

div.details > dl > dd {
    float: left;
    clear: right;
    margin: 1px 5px;
}

ul.thumbs > li div.coverphoto {
    width: 100%;
    height:<?= THUMB_SIZE + 20 ?>px;
    clear: right;
}

ul.thumbs > li div.name {
    position: absolute;
    width: 90%;
    max-height: 40px;
    bottom: 0;
    margin: 0 2px 2px 2px;
    clear: both;
}

ul.thumbs li img {
    display: block;
    margin: 10px auto;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

ul.list {
    clear: both;
    list-style: none;
}

ul.list > li:nth-child(2n) {
    background-color: <?= colorScheme::getColor("title_bg_color") ?>;
}

ul.tree {
    clear: both;
    list-style: none;
}

ul.tree ul {
    list-style: none;
}


li.collapsed > ul.tree,
div.collapsed > div.timedetail,
div.collapsed > div.ratingdetail {
    display: none;
}

div.toggle {
    width: 16px;
    height: 16px;
    float: left;
    margin: 0 0 0 -25px;
}

.collapsed > div.toggle {
    background-image: url("<?= template::getImage("icons/1rightarrow.png") ?>");
}

.expanded > div.toggle {
    background-image: url("<?= template::getImage("icons/1downarrow.png") ?>");
}

ul.thumbs a {
    text-align: center;
    text-decoration: none;
}

ul.admin {
    list-style: none;
}

ul.admin li {
    width: 110px;
    height: 110px;
    display: block;
    margin: 3px;
    float: left;
    clear: none;
    text-align: center;
}

ul.admin a {
    text-decoration: none;
    padding: 5px;
    width: 100px;
    height: 100px;
    display: block;
}

ul.admin a:hover {
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    border-radius: 5px;
}

ul.admin img {
    margin-top: 10px;
}

/* Form properties */
form    {
    width: 100%;
}

form.viewsettings {
    clear: both;
    width: auto;
}

form.viewsettings select {
    margin-right: 15px;
}


form.viewsettings select#parent_place_id {
    float: right;
    clear: none;
    margin-right: 0;
    width: 10em;
}

/* Form to add a page to a pageset */
form.addpage {
    width: auto;
    text-align: right;
}
form.addpage input[type="submit"]   {
    display: inline;
    margin: 15px;
    vertical-align: middle;
}
form.addpage select {
    display: inline;
    vertical-align: middle;
}
form.addpage label {
    vertical-align: middle;
    float: none;
    display: inline;
}

form.grouppermissions,
form.editgroup {
    display: block;
    clear: both;
    float: left;
}

/*
 For form validation
 */
input:invalid {
    background: rgba(255,0,0,0.1);
}

div.generate input {
    display: block;
    float: left;
}

div.generate input[type=button] {
    width: 75px;
    font-size: medium;
    float: left;
    height: 18px;
    clear: none;
}

ul.autocompdropdown {
    position: relative;
    margin: 0;
    padding: 0;
    background: <?= colorScheme::getColor("page_bg_color") ?>;
    width: 300px;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    max-height: 15em;
    overflow: auto;
    z-index: 500;
}

ul.autocompdropdown ul {
    margin: 0 0 0 1em;
    padding-left: 0;
}

ul.autocompdropdown li {
    list-style: none;
    padding: 0 10px 0 10px;
}

input[type=text].autocompinput {
    background: white url("<?= template::getImage("down2.gif") ?>");
    background-repeat: no-repeat;
    background-position: 99% center;
    margin-right: 6px;

}

ul.autocompdropdown li:hover,
ul.autocompdropdown li#selected {
    background: <?= colorScheme::getColor("tab_bg_color") ?>;
}

/* Menubar */

nav ul {
    margin: 0;
    padding: 0;
}

nav ul li {
    list-style: none;
    display: inline;
}

nav.menu {
    background: <?= colorScheme::getColor("page_bg_color") ?>;
}

nav.menu ul {
    overflow: hidden;
    padding: 7px 10px 3px;
    margin-bottom: -1px;
}

nav.menu ul li  {
    padding: 2px 4px 4px 4px;
    margin: 1px;
    text-align: center;
    background: <?= colorScheme::getColor("tab_bg_color") ?>;
    color: <?= colorScheme::getColor("tab_font_color") ?>;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    border-bottom: none;
    border-radius: 2px 2px 0 0;
    font-size: large;
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
}

nav.menu ul li:hover {
    top: -1px;
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    padding: 5px;
    font-weight: bold;
}

nav.menu ul li.selected {
    background: <?= colorScheme::getColor("selected_tab_bg_color") ?>;
    color: <?= colorScheme::getColor("selected_tab_font_color") ?>;
}

/* since the A element does not inherit font colors from it's parents, we set it
 * explicetly here. Also underlining is removed from links in menu, unless it is hovered */

nav.menu ul li a {
    color: <?= colorScheme::getColor("tab_font_color") ?>;
    text-decoration: none;
}

nav.menu ul li.selected > a {
    color: <?= colorScheme::getColor("selected_tab_font_color") ?>;
}

nav.menu ul li a:hover {
    color: <?= colorScheme::getColor("text_color") ?>;
}

/* The breadcrumb line at the top of the page */

nav.breadcrumbs {
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    color: <?= colorScheme::getColor("text_color") ?>;
    font-size: small;
    padding: 3px 10px;
    clear: left;
    width: 100%;
    border-radius: 5px 5px 0 0;
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
    z-index: 5;
}

ul.breadcrumbs li {
    color: <?= colorScheme::getColor("text_color") ?>;
    background: transparent;
}

ul.breadcrumbs li:before {
    content: "\0020 \0020 \0020 \00BB \0020";
}

ul.breadcrumbs li:first-child:before {
    content: " ";
}

ul.breadcrumbs.firstdots li:first-child:before {
    content: "... \00BB \0020 ";
}

ul.breadcrumbs li a {
    padding: 0 1em;
    color: <?= colorScheme::getColor("text_color") ?>;
}

/* Main page */
.main, .page, div.map, div#selection  {
    background: <?= colorScheme::getColor("table_bg_color") ?>;
    font-size: medium;
    width: 100%;
    border-spacing: 0px;
    padding: 10px;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    border-radius: 0 0 5px 5px;
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
    clear: both;
    overflow: hidden;
}

.main > div.map {
    left: -1px;
}

div#selection + div.main {
    margin-top: -10px;
}

div.map {
    border-radius: 5px;
    margin-top: 5px;
    height: 450px;
}

div.map small {
    display: block;
    /* used in infoBubble */
    font-size: x-small;
}

div#selection {
    z-index: 6;
}

.leaflet-control-layers-base label {
    text-align: left;
}

div.minimap {
    float: right;
    right: 10px;
    width: 50%;
    height: 300px;
}

/* explanation of the config item on the config page */
div.main#config .desc {
    clear: both;
    width: 50%;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    border: 1px solid <?= colorScheme::getColor("title_font_color") ?>;
    margin-left: 11.5em;
    margin-bottom: 15px;
    padding: 0.5em;
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
}

div.main#config div.confGroup > .desc {
    color: <?= colorScheme::getColor("text_color") ?>;
    background: none;
    margin-left: 0;
    width: 98%;
}

div.main#config h2 {
    position: relative;
    left: -11px;
    clear: both;
    display: block;
    padding: 5px 10px;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    color: <?= colorScheme::getColor("text_color") ?>;
    width: 100%;
}

div.main#config input[type="checkbox"] {
    margin: 7px;
}

div.main#config input.reset {
    float: right;
}

div.main#config input.reset + span {
    margin-top: 2px;
    height: 1em;
    overflow: hidden;
    display: block;
    float: right;
    transition: opacity 700ms ease-out 200ms;
    opacity: 0;
}

div.main#config input.reset:hover + span,
div.main#config input.reset:checked + span {
    opacity: 1;
}

div.geocode {
    position: absolute;
    top: -10px;
    right: 0px;
    width: 160px;
}

div#geocoderesults {
    width: 100%;
    color: #666666;
    text-align: center;
    font-size: small;
}

div.geocode input[type="button"] {
    float: none;
    clear: none;
    height: 20px;
    width: 140px;
    margin: 10px;
}

input.geo_disabled {
    background: #aaaaaa !important;
}

input.leftright {
    width: 60px !important;
    height: 20px;
}

form#ratingform {
    margin-top: 5px;
}

select#rating + input[type="submit"] {
    height: 22px;
    clear: none;
    top: 4px;
    float: left;
    width: 40px;
    font-size: medium;
}

div.timedetails,
div.ratingdetails {
    margin-left: 25px;
}

div.timedetail,
div.ratingdetail {
    margin-top: 5px;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    background: white;
}

div.timedetail dt,
div.timedetail dd {
    width: 40%;
}

div.timedetail h3 {
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    font-size: large;
}

table.ratingdetail td, table.ratingdetail th {
    text-align: left;
    font-size: small;
    padding: 2px;
}

table.ratingdetail th {
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
}

table.tracks {
    border-collapse: collapse;
    width: 100%;
}

table.tracks th, table.tracks td {
    padding: 2px 5px;
    text-align: left;
}

table.tracks tr:nth-child(odd) {
    background: <?= colorScheme::getColor("title_bg_color") ?>;
}

p.main {
    padding: 4px;
}

/* The short introduction on zoph.php */
.intro  {
    padding: 5px;
    text-align: left;
}

div.intro {
    margin-left: 15px;
    float: left;
    clear: right;
}

div.intro ul {
    margin: 5px;
    padding-bottom: 0px;
}


p.intro {
    clear: both;
    margin-top: 2px;
    margin-bottom: 2px;
}

/* ratings and reports are used on the reports page */

div.ratings   {
    margin-left: auto; /* To center the page */
    margin-right: auto;
    padding: 10px 5px 20px 5px;
    width: 50%;
}

.graph > table {
    width: 100%;
    border-collapse: collapse;
}

.graph > table tr td:first-child {
    width: 20%;
    text-align: right;
    padding-right: 15px;
    border-right: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
}

.graph.bar div.bar   {
    float: left;
    width: 100%;
    height: 100%;
}


/* This is the bar that shows the number of photos for each rating */
.graph.bar div.fill   {
    float: left;
    background: <?= colorScheme::getColor("selected_tab_bg_color") ?>;
    border-radius: 0 3px 3px 0;

    box-shadow: 3px 3px 3px rgba(0,0,0,0.6);
    z-index: 3;

}

.graph.bar div.count   {
    font-size: small;
    position: relative;
    float: left;
    left: 20px;
    top: 2px;
    margin: 0 -10px 0 -10px;
}

table.reports {
    width: 50%;
    float: left;
    padding: 10px 5px 20px 5px;
}

table.pages, table.pagesets {
    width: 100%;
}

div.smileys {
    border: 1px solid black;
    clear: right;
}

div.smileys div {
    float: left;
    width: 70px;
    height: 20px;
}

div.smileys span {
    font-size: 8pt;
    vertical-align: middle;
}

div.smileys img {
    float: left;
    margin: 2px;
    margin-right: 4px;
}

table#search td {
    vertical-align: top;
}

table#search input[type="checkbox"] {
    float: none;
}

form.search input[type="checkbox"] {
    margin: 0 0 0 5px;
}

form.search input[type="checkbox"] + label,
table#search input[type="checkbox"] + label {
    float: none;
    display: inline;
    position: relative;
    font-size: x-small;
    font-weight: normal;
    margin: 3px;
    top: 3px;
    width: auto;
    clear: none;
    line-height: 1em;
}

div.searchTerm {
    display: flex;
    align-items: top;
    clear: both;
}


div.searchTerm > div {
    margin: 3px;
    font-size: 120%;
}

div.searchTerm > div.searchIncrement,
div.searchTerm > div.searchConj {
    width: 5em;
}

div.searchTerm > div.searchOp,
div.searchTerm > div.searchLabel {
    width: 12em;
}

div.searchTerm > div.searchValue {
    width: auto;
}

div.searchTerm > div.searchLabel:first-child {
    margin-left: calc( 22em + 21px);
}

div.searchLabel,
span.searchValueText,
span.searchOpText {
    line-height: 3em;
}

div.searchTerm select,
div.searchTerm input {
    margin: 5px;
}

div.searchTerm input[type="checkbox"] {
    float: none;
}

div.searchLatLon {
    line-height: 1.5em; !important
}

span.photocount {
    font-size: x-small;
}

/* Links that appear on the right hand side of the title bar or page */

ul.actionlink {
    display: block;
    margin: 1px;
    text-align: right;
    vertical-align: top;
    font-size: 70%;
    float: right;
    font-weight: normal;
    clear: right;
}

ul.letter {
    text-align: center;
    border: 1px solid black;
    float: none;
    padding: 1em;
    font-size: small;
}

ul.actionlink li:before {
    content: ' | ';
    color: <?= colorScheme::getColor("title_font_color") ?>;
}

ul.actionlink li:first-child:before {
    content: ' [ ';
    color: <?= colorScheme::getColor("title_font_color") ?>;
    }

ul.actionlink li:last-child:after {
    content: ' ] ';
    color: <?= colorScheme::getColor("title_font_color") ?>;
}

ul.actionlink li {
    display: inline;
}

ul.actionlink a {
    text-decoration: none;
}

ul.actionlink a:hover {
    text-decoration: underline;
}

/* Text next to 'remove' tickbox */
.remove {
    text-align: left;
    font-size: small;
    vertical-align: top;
}

/* The letter that is currently active */
.letter .selected   {
    font-weight: bold;
}

/* Description of an album, category, etc. */
.description    {
    font-style: italic;
    font-size: medium;
    }

/* Description of a photo */
.photodesc  {
    border:  4px solid <?= colorScheme::getColor("title_bg_color") ?>;
    font-size: small;
    background: <?= colorScheme::getColor("table_bg_color") ?>;
    clear: both;
}
/* The description of a photo in thumbnail view */
.thumbdesc  {
    font-size: small;
}

/* Rotate links above a photo */
.rotate {
    font-size: small;
    text-align: center;
}

.rotate select {
    float: none;
}

/* Color scheme */

div.colordef {
    margin-left: 1em;
    float: left;
    width: 10em;
}

div.color {
    float: left;
    width: 60px;
}

/* Tables for the permissions */

table.permissions   {
    background: <?= colorScheme::getColor("table_bg_color") ?>;
    width: 90%;
    margin-left: auto; /* To center the page */
    margin-right: auto;
    border-collapse: collapse;
    font-size: medium;
    }

table.permissions td, table.permissions th  {
    background: <?= colorScheme::getColor("table_bg_color") ?>;
    font-size: medium;
    text-align: left;
}

table.permissions col {
    text-align: center;
}

table.permissions > col.col1 {
    padding-left: 15px;
    text-align: left;
    width: 5%;
}

table.permissions > col.col2 { width: 55%; text-align: left; }
table.permissions > col.col3 { width: 20%; text-align: center; }
table.permissions > col.col4 { width: 20%; text-align: center;}

table.permissions td.permremove {
    padding-top: 3px;
    padding-bottom: 0px;
    font-size: x-small;
    text-align: left;
    vertical-align: bottom;
}

table.permissions select,
table.permissions input {
    float: none;
}

nav.photohdr ul {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
}

nav.photohdr ul li {
    width: 50px;
}
nav.photohdr ul li:empty {
    background: transparent;
}

div.photodata {
    text-align: center;
    width: 100%;
}

span.md5 {
    display: none;
}

/* Page links */
div.pagelink {
    text-align: center;
    width: 60%;
}

ul.pager    {
    display: block;
    font-size: medium;
    font-weight: normal;
    text-align: center;
    left: auto;
    right: auto;
}

ul.pager li {
    display: inline-block;
}

ul.pagegroup {
    display: inline-block;
    margin: 5px;
    float: left;
    overflow: hidden;
}

nav.photohdr ul li,
ul.pagegroup li {
    display: block;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    border-radius: 5px;
    float: left;
    margin: 3px;
}

nav.photohdr ul li a,
ul.pagegroup li a {
    display: block;
    padding: 6px 12px;
    text-decoration: none;
    text-align: center;
    vertical-align: middle;
}

ul.pagegroup li:hover {
    background: <?= colorScheme::getColor("selected_tab_bg_color") ?>;
    color: <?= colorScheme::getColor("selected_tab_font_color") ?>;
}

ul.pagegroup li.current {
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    color: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    font-weight: bold;
}

/* up and down arrows for sort order */
.up, .down  {
    display: block; /* needed to make the arrows exactly connect */
}

/* Form on top of each photopage to determine sortorder, asc or desc and
   number of photos displayes */

form.viewsettings input,
form.viewsettings select {
    float: none;
}

div#sortorder {
    float: left;
    margin-bottom: 15px;
}

div#updown {
    float: left;
    margin: 5px 0px 15px 0px;
}

div#rowscols {
    float: right;
    margin-bottom: 15px;
}

ul.ancestors {
    font-size: x-large;
    font-weight: bold;
    list-style: none;
    padding-left: 0;
    margin: 1em 0;
}

ul.ancestors li:first-child::before {
    content: none;
}

ul.ancestors li::before {
    content: ">";
    margin: 0 0.5em;
    color: <?= colorScheme::getColor("title_font_color") ?>;
}

ul.ancestors li {
    display: inline;
    margin: 0;
    list-style: none inside none;
}

ul.photolinks {
    font-size: x-large;
    font-weight: bold;
    list-style: none;
    padding: 30px;
    display: flex;
    justify-content: space-around;
    margin: 10px 0;
}

ul.photolinks li {
    display: block;
    width: 250px;
    list-style: outside none none;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    border-radius: 10px;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    box-shadow: 5px 5px 10px rgba(0,0,0,0.5), 0 0 10px rgba(0,0,0,0.2) inset;
    margin: 0;
}

ul.photolinks li img {
    padding: 10px;
    vertical-align: middle;
}

ul.photolinks li a {
    text-decoration: none;
    padding: 5px;
    width: 100%;
    height: 100%;
    display: block;
}

ul.photolinks li:hover {
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
}

ul.photolinks span.photocount {
    padding: 10px;
    font-size: xx-large;
}

img.<?= THUMB_PREFIX ?> {
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
}

div.thumbnail img.<?= THUMB_PREFIX ?>:hover {
    margin-top: -2px;
    margin-left: -2px;
    box-shadow: 7px 7px 7px rgba(0,0,0,0.4);
}

img.<?= MID_PREFIX ?> {
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
    margin: 10px auto;
    clear: both;
    text-align: center;
    display: block;
}

img.busy,
img.waiting {
    margin-left: auto;
    margin-right: auto;
    clear: both;
    text-align: center;
    display: block;
}


div#rotate {
    margin-left: auto;
    margin-right: auto;
    margin-top: 5px;
    margin-bottom: 15px;
    text-align: center;
    clear: right;
}

/* Links to persons under a photo */
div#personlink  {
    text-align: center;
    font-size: small;
    width: 100%;
    margin: 0 0 15px 0;
}

/* Text next to an input field, suggesting what to put there, such as "64 chars max" */
.inputhint  {
    font-size: small;
    padding: 8px;
    font-weight: normal;
    float: left;
    opacity: 0.7;
}

div.inputhint {
    margin: -2px 0 15px 180px;
    clear: left;
    float: left;
}

div.formtext {
    padding: 15px;
}

/* Checkbox on the annotate photo page */

.checkbox   {
    text-align: right;
}

div.editchoice  {
    position: relative;
    z-index: 5;
    vertical-align: top;
    clear: none;
    font-size: small;
    margin: 10px;
    margin-right: -10em;
    float: left;
    width: 10em;
}

/* Thumbnail photo */
div.thumbnail   {
    text-align: center;
    vertical-align: top;
    width: <?= THUMB_SIZE ?>px;
    height: <?= THUMB_SIZE ?>px;
    float:left;
    margin: 2px;
    padding: 5px;
}

div.comment {
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    background: transparent;
    font-size: small;
    min-height: 5em;
    padding-bottom: 5px;
    margin-bottom: 5px;
}

div.comment h3 {
    width: 100%;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    border-bottom: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    text-align: left;
}

div.commentinfo {
    border-bottom: 1px dashed <?= colorScheme::getColor("table_border_color") ?>;
    width: 100%;
    font-size: x-small;
    font-style: italic;
    margin: 0 0 0.5em 0;
}

span.searchinfo {
    font-size: x-small;
}

br  {
    clear: both;
}

br.noclear {
    clear: none;
}

/* The random thumnail on the first page */
#random.thumbnail   {
    width: <?= THUMB_SIZE+10 ?>px;
    vertical-align: middle;
    padding-top: 10px;
    padding-left: 0px;
    float: left;
}

/* Person / place in the list of persons / places */
.person, .place, .showattr {
    text-align: left;
    font-size: medium;
    clear: left;
    display: block;
}

/* hr */
.wide   {
    width: 90%
}

dl.display {
    margin-top: 0px;
    margin-bottom: 30px;
}

label,
dl.display > dt,
dl.display > dd {
    float: left;
    font-size: medium;
    padding-left: 4px;
    padding-right: 4px;
    min-height: 1.3em;
    margin: 4px 0px;
    position: relative;
}

dl.display > dd {
    width: 55%;
    margin: 5px;
}

form.user label,
form.prefs label,
dl.display > dt {
    clear: left;
    width: 40%;
    font-weight: bold;
    text-align: right;
}

dl.allexif {
    display: none;
    width: 100%;
}

dl.allexif dl {
    width: 100%
}

dl.groups dt {
    margin-top: 0;
    margin-bottom: 10px;
    width: 20%;
}

dl.groups dd {
    margin-top: 2px;
    width: 40%;
    font-size: small;
}

label {
    text-align: right;
    vertical-align: top;
    font-weight: bold;
    margin-left: 1em;
    margin-bottom: 10px;
    width: 10em;
    display: block;
    float: left;
    clear: left;
}

/* This is to get the labels in a nice column, even if there's a checkbox
next to it, like on the Annotate photo page */

input[type="checkbox"] + label {
    clear: none;
    width: 9em;
}

fieldset  {
    border-radius: 5px;
    margin-bottom: 5px;
    clear: right;
    overflow: hidden;
    display: block;
    float: left;
}

legend {
    border-radius: 2px;
}

fieldset.editphotos,
fieldset.map,
fieldset.geotag  {
    width: 100%;
    margin: 10px 0 20px 0;
    padding: 10px 0;
    border: 1px solid <?= colorScheme::getColor("page_bg_color") ?>;
}

fieldset.members {
    clear: both;
    margin: 20px 20px 20px 12em;
    padding: 1em;
    width: 15em;
    border: 1px solid <?= colorScheme::getColor("page_bg_color") ?>;
}


/* The map is inside a table on the search page */
table fieldset.map {
    width: 95%;
}

fieldset.map {
    position: relative;
}

fieldset.editphotos legend,
fieldset.map legend,
fieldset.geotag legend,
fieldset.members legend {
    clear: both;
    display: block;
    margin-left: 2em;
    padding-right: 2em;
    padding-left: 2em;
    font-weight: bold;
    border: 1px solid <?= colorScheme::getColor("page_bg_color") ?>;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
}

fieldset.members legend {
    padding: 0.3em 1em;
    margin-left: 0.5em;

}

fieldset.editphotos div.thumbnail {
    vertical-align: top;
    clear: none;
    font-size: small;
    margin-left: -10em;
    float: right;
}

fieldset.editphotos-fields {
    clear: none;
    width: 80%;
    padding-top: 10px;
}

/* These are the lists on the bulk edit page, such as the list of albums + the remove checkbox. */

fieldset.checkboxlist legend {
    display: none;
}

input[type="button"],
input[type="submit"],
input[type="reset"] {
    border: none;
    border-radius: 5px;
    background: <?= colorScheme::getColor("tab_bg_color") ?>;
    color: <?= colorScheme::getColor("tab_font_color") ?>;
    font-size: x-large;
    font-weight: bold;
    width: 200px;
    height: 30px;
    float: right;
    clear: both;
}

input[type="button"]:hover,
input[type="submit"]:hover,
input[type="reset"]:hover {
    background: <?= colorScheme::getColor("selected_tab_bg_color") ?>;
    color: <?= colorScheme::getColor("selected_tab_font_color") ?>;
}

input[disabled] {
    background: #aaaaaa;
}

div#rowscols input[type="submit"] {
    display: inline;
    margin: 0 15px 0 15px;
    width: 60px;
    height: 20px;
    font-size: medium;
}

input[type="submit"].updatebutton {
    clear: right;
    margin-right: 15px;
    margin-left: auto;
    display: block;
}

input[type="submit"].increment {
    width: auto;
    height: auto;
}

input[type="submit"].bigbutton {
    width: 200px !important;
}

textarea {
    margin: 2px;
    width: 70%;
    }

textarea.email {
    margin-top: 0;
    margin-bottom: 15px;
    margin-left: 5%;
    margin-right: 5%;
    width: 90%;
}

textarea.desc {
    display: block;
    float: left;
    width: 300px;
    }


input, select {
    margin: 2px;
}

input[type="checkbox"] {
    margin: -1px 4px 0;
    float: left;
}

input[type="checkbox"].remove {
    clear: left;
}

div#logon input[type="text"],
div#logon input[type="password"],
div#passwordchange input[type="text"],
div#passwordchange input[type="password"]   {
    margin-bottom: 10px;
    margin-left: 5px;
}

select,
input[type="text"],
input[type="time"],
input[type="date"],
input[type="number"],
input[type="password"],
textarea {
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    padding: 2px;
    border-radius: 2px;
    margin: 2px;
    background: white;
    float: left;
}

select:disabled {
    background: #ddd;
    border: 1px solid #bbb;
}

span.unmetRequirements {
    color: red;
}

span.confDeprecated {
    color: red;
    font-weight: bold;
}

form.geotag select,
form.import select,
form.geotag input,
form.import input {
    float: left;
}

form.import fieldset {
    border-radius: 0;
    padding: 0;
    margin: 0;
}

/* There is text to the left of this select box, floating
   caused the text and the select to be in the wrong order
   so, a small workaround to stop this: */
form.geotag fieldset.checkboxlist select {
    float: none;
}

table#users,
table.credits {
    width: 100%;
}

table.credits th {
    text-align: right;
    padding: 2px;
}

table#users td,
table.credits td {
    padding: 2px;
    vertical-align: top;
}

table#zophinfo {
    width: 60%;
    margin-left: auto;
    margin-right: auto;
}

table#zophinfo th {
    width: 80%;
}

table#zophinfo td {
    width: 20%;
}

div#relation {
    margin-left: auto;
    margin-right: auto;
    width: 50%;
}

div#rotate select,
div#rotate input[type="submit"] {
    font-weight: normal;
    float: none;
    height: 20px;
    width: 40px;
    font-size: medium;
    margin-left: auto;
    margin-right: auto;
}

div.page-preview {
    border: 1px solid black;
    width: 80%;
    max-height: 600px;
    min-width: 600px;
    overflow: scroll;
    background: <?= colorScheme::getColor("table_bg_color") ?>;
    font-size: medium;
    border-spacing: 0;
    padding: 10px;
    border: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    clear: both;
}

div.page h1, div.page-preview h1 {
    position: relative;
    left: -10px;
    width: 100%;
    border-left: 0;
    border-right: 0;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    font-size: large;
    text-align: center;

}

div.page h2, div.page-preview h2 {
    text-align: center;
    border-bottom: 1px solid black;
}

div.page h3, div.page-preview h3 {
    text-align: left;
}

div.page div.background {
    margin: -10px;
    padding: 10px;
    width: 100%;
}

/* Styles for the import page */
html.iframe_upload {
    width: 100%;
    clear: both;
}

html.iframe_upload body {
    background: transparent;
    width: 100%;
    clear: both;
}

html.iframe_upload input {
    margin: 5px;
}


div.import_thumbs,
div.import_uploads,
div.import,
div.import_details {
    float:      left;
    border-radius: 5px;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    margin: 10px;
    width: 95%;
}

div.import_details,
div.import_thumbs {
    display: none;
}

div.import_thumbs div.thumbnail {
    height: <?= THUMB_SIZE + 30 ?>px;
}

div.import_thumbs img {
    clear: both;
    display: block;
    margin: auto;
}

div.import textarea {
    width: 60%;
}

iframe.upload {
    width: 100%;
    margin-bottom: 3px;
    height: 100px;
}

div.uploadprogress {
    display: none;
    float: right;
    width: 350px;
}

div.import_details,
div.import {
    min-height: 150px;
}

div.import_details div {
    padding: 1em;
}

div.import_thumbs h2,
div.import_uploads h2,
div.import h2,
div.import_details h2 {
    border-radius-top-left: 5px;
    border-radius-top-right: 5px;
    background: <?= colorScheme::getColor("breadcrumb_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    border-bottom: 1px solid <?= colorScheme::getColor("table_border_color") ?>;
    text-align: center;
    width: 100%;
    clear: left;
    font-size: large;
    font-weight: bold;
    display: block;
    padding: 3px 0;
}


.upload {
    width:      100%;
    height:     80px;
}

.progressbar {
    margin: auto;
    height: 20px;
    background: white;
    border: 1px solid black;
    border-radius: 5px;
    overflow: hidden;
    clear: both;
}

.progressfill {
    height: 16px;
    margin: 2px;
    max-width: 99%;
    border-radius: 4px;
    text-align: center;
    background: <?= colorScheme::getColor("page_bg_color") ?>;
    color: white;
    font-weight: bold;
    overflow: hidden;
}

span.filename {
    display: block;
    clear: left;
    font-size: 70%;
}

.fn_upload {
    font-size: 70%;
    margin: 2px;
    display: block;
    float: left;
    font-weight: bold;
    clear: both;
    font-size: 80%;
    margin-bottom: 0;
}

.sz_upload {
    margin: 2px;
    clear: right;
    display: block;
    font-size: 60%;
    float: right;
}

form.import {
    padding: 1.5em 0em 2em 0em;
}

form.import fieldset#import_checkboxes {
    display: none;
}

fieldset.multiple,
fieldset.formhelper-multiple {
    background: transparent;
    margin: 0 0 5px 0;
    padding: 5px;
    width: 230px;
}

fieldset.formhelper-multiple {
    width: 100%;
}

fieldset.formhelper-multiple > .actionlink {
    float: left;
    margin-left: 10px;
}

img.actionlink {
    float: right;
    position: relative;
    top: 3px;
}

fieldset.multiple img.actionlink:last-child {
    /* hide the remove icon on last dropdown */
    display: none;
}

fieldset.import-extrafields > select {
    font-size: 10px;
    float: left;
    width: 100px;
    margin: 2px 8px 10px 1em;
}

div.preview {
    border: 2px solid black;
    position: fixed;
    background: white;
    padding: 10px;
    margin: 10px;
    left: 0;
    top: 0;
}


/* tabs on the right side of the photo, for now only used for
   the sharing tab, but maybe some other features will be added later */

ul.tabs {
    list-style: none;
    float:right;
    margin: 0 -10px 0 -40px;
    width: 40px;
    height: 0;
    clear: left;
}

ul.tabs > li {
    position: relative;
    z-index: 5;
    display: block;
    float: right;
    height: 55px;
    width: 40px;
    clear: both;
    overflow: hiddden;
    transition: width 700ms ease-out 200ms;

}

ul.tabs div.tab {
    position: relative;
    background: <?= colorScheme::getColor("title_bg_color") ?>;
    color: <?= colorScheme::getColor("title_font_color") ?>;
    border-radius: 10px 0 0 10px;
    width: 30px;
    height: 25px;
    margin: 0 -3px 0 0;
    padding: 10px 6px 10px 10px;

    box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    z-index: 3;
}

ul.tabs div.contents > h1 {
    height: 25px;
    padding: 10px 20px;
}

ul.tabs div.contents {
    display: block;
    overflow: hidden;
    position: relative;

    top: -45px;
    left: 45px;
    border-radius: 0 0 0 20px;

    box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    width: 515px;

    background: rgba(255,255,255,0.9);
    z-index: 1;
}

ul.tabs li:hover {
    width: 550px;
}

li.share div.contents ul {
    list-style: none;
}

li.share div.contents > ul > li {
    overflow: hidden;
    background-repeat: no-repeat;
    padding-left: 25px;
}

li.share li.direct_link {
    background-image: url("<?= template::getImage("icons/link.png") ?>");
}

li.share li.html {
    background-image: url("<?= template::getImage("icons/html.png") ?>");
}

li.share input {
    border: 1px solid black;
    width: 30em;
}

li.share textarea {
    border: 1px solid black;
    width: 30em;
}

/* Styles for calendar */
.calendar {
    text-align: center;
    vertical-align: top;
    margin-left: auto; /* To center the page */
    margin-right: auto;
}

table.calendar .today,
table.calendar th {
    font-weight: bold;
}


.calendar .next,
.calendar .prev {
    font-size: x-small;
}

/* message */
.message  {
    text-align: center;
    margin: 10px 0px;
    padding:12px;
    clear: both;
    border-radius: 10px;
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
}

div.message img.icon {
    float: left;
    margin-right: 10px;
}

div.message div.messageText {
    float: left;
    width: 80%;
}
div.message div.messageText h1 {
    border: none;
    background: transparent;
    text-align: center;
    font-size: large;
    padding: none;
    box-shadow: none;
    margin: 0 0 1em 0;
    color: inherit;

}

.info {
    color: #00529B;
    background-color: #BDE5F8;
}

.success {
    color: #4F8A10;
    background-color: #DFF2BF;
}

.warning {
    color: #9F6000;
    background-color: #FEEFB3;
}

.error {
    color: #D8000C;
    background-color: #FFBABA;
}

/* The copyright statement at the bottom of the page */
.version    {
    text-align: center;
    font-size: small;
    margin-bottom: 2px;
}

/* vim: set syntax=css: */
