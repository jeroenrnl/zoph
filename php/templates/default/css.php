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
?>

/* Some of the styles have been based on http://www.alistapart.com/articles/taminglists/ */

/* Main CSS style, all elements inherit these settings */

body    {
    font-family: Arial, Verdana, sans-serif;
    font-size: medium;
    color: <?php echo color_scheme::getColor("text_color") ?>;
    background: <?php echo color_scheme::getColor("page_bg_color") ?>;
    width: <?php echo conf::get("interface.width"); ?>;
    border: none;
    margin-left: auto; /* To center the page */
    margin-right: auto;
    padding: 0px;
    border-collapse: collapse;
}

/* Links */

a   {
    color: <?php echo color_scheme::getColor("link_color") ?>;
    background: transparent;
}

/* Images that are links */

a IMG   {
    border: none;
}

h1  {
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    text-align: left;
    width: 100%;
    clear: left;
    font-size: x-large;
    font-weight: bold;
    display: block;
    padding: 2px 10px 2px 10px;
    margin: 0;
}

/* Secondary title such as album title */
h2  {
    text-align: left;
    font-size: large;
    margin: 0;
    margin-top: 10px;
    margin-bottom: 10px;
}

h2.logon {
    margin-bottom: 30px;
}

/* Level 3 title */
h3  {
    text-align: center;
    font-size: medium;
    font-weight: bold;
    margin: 0px;
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
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    //border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    position: relative;
    display: block;
    text-align: center;
    width: <?php echo THUMB_SIZE + 40 ?>px;
    height:<?php echo THUMB_SIZE + 60 ?>px;
    float:left;
    margin: 5px;
    padding: 5px;

    border-radius: 10px;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;

    box-shadow: 5px 5px 10px rgba(0,0,0,0.5), 0 0 10px rgba(255,255,255,0.6) inset;
    -moz-box-shadow: 5px 5px 10px rgba(0,0,0,0.5), 0 0 10px rgba(255,255,255,0.6) inset;
}

ul.thumbs > li dl.extradata {
    display: none;
}

ul.thumbs > li.thumb_circle {
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    box-shadow: 5px 5px 10px rgba(0,0,0,0.5), 0 0 10px rgba(0,0,0,0.2) inset;
}

div.details {
    display: block;
    position: absolute;
    float: left;

    padding: 20px;
    background: rgba(255,255,255,0.9);

    border-radius: 10px;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -o-border-radius: 10px;
    -ms-border-radius: 10px;

    box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
    -moz-box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
    -webkit-box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
    -o-box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
    -ms-box-shadow: 5px 5px 10px rgba(0,0,0,0.3);
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
    margin: -20px -20px 0 -20px;
    padding: 5px 20px;
    border-radius: 10px 10px 0 0;
    -moz-border-radius: 10px 10px 0 0;
    -webkit-border-radius: 10px 10px 0 0;
    width: 100%;
    background: <?php echo color_scheme::getColor("tab_bg_color") ?>;
    color: <?php echo color_scheme::getColor("tab_font_color") ?>;
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
    height:<?php echo THUMB_SIZE + 20 ?>px;
    margin: 0;
    padding: 0;
    clear: right;
}

ul.thumbs > li div.name {
    position: absolute;
    width: 90%;
    max-height: 40px;
    bottom: 0;
    margin: 0 2px 2px 2px;
    padding: 0;
    clear: both;
}

ul.thumbs li img {
    display: block;
    margin: 10px auto;
    -moz-box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

ul.list {
    clear: both;
    list-style: none;
}

ul.list > li:nth-child(2n) {
    background-color: <?php echo color_scheme::getColor("title_bg_color") ?>;
}

ul.tree {
    clear: both;
    list-style: none;
}

ul.tree ul {
    list-style: none;
    margin: 0;
}


li.collapsed > ul.tree,
div.collapsed > div.timedetail,
div.collapsed > div.ratingdetail {
    display: none;
}

.collapsed > div.toggle {
    width: 16px;
    height: 16px;
    float: left;
    margin: 0 0 0 -25px;
    background-image: url("<?php echo template::getImage("icons/1rightarrow.png") ?>");
}

.expanded > div.toggle {
    width: 16px;
    height: 16px;
    float: left;
    margin: 0 0 0 -25px;
    background-image: url("<?php echo template::getImage("icons/1downarrow.png") ?>");
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
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
}

/* Form properties */
form    {
    margin: 0 0 0 0;
    width: 100%;
}

form.viewsettings {
    clear: both;
    width: auto;
    margin: 0;
    padding: 0;
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

form#ratingform input, form#ratingform select {
    margin: 0;
    float: left;
}

form#ratingform select {
    margin-top: 4px;
    margin-right: 5px;
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
    margin: 0;
    float: left;
}

ul.autocompdropdown {
    position: relative;
    background: <?php echo color_scheme::getColor("page_bg_color") ?>;
    width: 300px;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    max-height: 15em;
    overflow: auto;
    margin: 0;
    padding: 0;
    z-index: 5;
}

ul.autocompdropdown ul {
    margin: 0 0 0 1em;
    padding-left: 0em;
}

ul.autocompdropdown li {
    list-style: none;
    margin: 0;
    padding: 0 10px 0 10px;
}

input[type=text].autocompinput {
    background: white url("<?php echo template::getImage("down2.gif") ?>");
    background-repeat: no-repeat;
    background-position: 99% center;
    margin-right: 6px;

}

ul.autocompdropdown li:hover,
ul.autocompdropdown li#selected {
    background: <?php echo color_scheme::getColor("tab_bg_color") ?>;
}

/* Menubar */

ul.menu {
    background: <?php echo color_scheme::getColor("page_bg_color") ?>;
    margin-left: 4px;
    padding: 0 0 0 10px;
    display: inline;
}

ul.menu li  {
    padding: 1px;
    padding-top: 3px;
    margin: 1px;
    border: none;
    text-align: center;
    list-style: none;
    display: inline;
    background: <?php echo color_scheme::getColor("tab_bg_color") ?>;
    color: <?php echo color_scheme::getColor("tab_font_color") ?>;
    font-size: small;
}

ul.menu li:hover {
    position: relative;
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    border-bottom: none;
    padding: 2px 0 2px 0;
}

ul.menu li.selected {
    background: <?php echo color_scheme::getColor("selected_tab_bg_color") ?>;
    color: <?php echo color_scheme::getColor("selected_tab_font_color") ?>;
}

/* since the A element does not inherit font colors from it's parents, we set it
 * explicetly here. Also underlining is removed from links in menu, unless it is hovered */

ul.menu li a {
    color: <?php echo color_scheme::getColor("tab_font_color") ?>;
    text-decoration: none;
}

ul.menu li > a:hover { text-decoration: underline; }
ul.menu li.selected > a { color: <?php echo color_scheme::getColor("selected_tab_font_color") ?>; }

/* The breadcrumb line at the top of the page */

div.breadcrumbs {
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    color: <?php echo color_scheme::getColor("text_color") ?>;
    font-size: small;
    float: left;
    margin: 0;
    padding: 2px 10px 2px 10px;
    clear: left;
    width: 100%;
}

ul.breadcrumbs {
    margin: 0;
    padding: 0;
}

ul.breadcrumbs li {
    margin-left: 1px;
    padding-left: 2px;
    padding-right: 8px;
    border: none;
    list-style: none;
    display: inline;
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

/* Main page */
.main, .page, div.map, div#selection  {
    background: <?php echo color_scheme::getColor("table_bg_color") ?>;
    font-size: medium;
    width: 100%;
    border-spacing: 0px;
    padding: 10px;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    clear: both;
    overflow: hidden;
}


.main > div.map {
    left: -1px;
    padding: 0;
}

div.map {
    height: 450px;
}

div.map small {
    display: block;
    /* used in infoBubble */
    font-size: x-small;
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
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    border: 1px solid <?php echo color_scheme::getColor("title_font_color") ?>;
    margin-left: 11.5em;
    margin-bottom: 15px;
    padding: 0.5em;
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
}

div.main#config div.confGroup > .desc {
    color: <?php echo color_scheme::getColor("text_color") ?>;
    background: none;
    margin-left: 0;
    width: 100%;
    border: none;
}

div.main#config h2 {
    position: relative;
    left: -11px;
    clear: both;
    display: block;
    padding: 5px 10px;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    color: <?php echo color_scheme::getColor("text_color") ?>;
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

    -webkit-transition: opacity 700ms ease-out 200ms;
    -moz-transition: opacity 700ms ease-out 200ms;
    -o-transition: opacity 700ms ease-out 200ms;
    -ms-transition: opacity 700ms ease-out 200ms;
    transition: opacity 700ms ease-out 200ms;

    opacity: 0;
}

div.main#config input.reset:hover + span,
div.main#config input.reset:checked + span {
    opacity: 1;
}

.olControlAttribution {
    bottom: 1em !important;
    display: block;
    left: 10px;
}

div.geocode {
    position: absolute;
    top: -10px;
    right: 0px;
    width: 150px;
}

div#geocoderesults {
    width: 144px;
    margin: 0;
    color: #666666;
    text-align: center;
    font-size: small;
}

div.geocode input[type="button"] {
    width: 140px;
    margin: 2px;
    float: left;
}

input.geo_disabled {
    background: #aaaaaa !important;
}

input.leftright {
    width: 68px !important;
    height: 20px;
}


div.timedetails, div.ratingdetails {
    width: 100%;
    margin-left: 24px;
}

div.timedetail, div.ratingdetail {
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    background: white;
    width: 100%;
}

div.timedetail dd {
    width: 40%;
}

div.timedetail dt {
    width: 40%;
}

div.timedetail h3 {
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    font-size: large;
}

table.ratingdetail {
    border-collapse: collapse;
    width: 100%;
}

table.ratingdetail td, table.ratingdetail th {
    text-align: left;
    font-size: small;
    padding: 2px;
}

table.ratingdetail th {
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
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
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
}

p.main, {
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
    border-right: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
}

.graph.bar div.bar   {
    float: left;
    width: 100%;
    height: 100%;
}


/* This is the bar that shows the number of photos for each rating */
.graph.bar div.fill   {
    float: left;
    border: none;
    background: <?php echo color_scheme::getColor("selected_tab_bg_color") ?>;
    border-radius: 0 3px 3px 0;

    box-shadow: 3px 3px 3px rgba(0,0,0,0.6);
    -moz-box-shadow: 3px 3px 3px rgba(0,0,0,0.6);
    -o-box-shadow: 3px 3px 3px rgba(0,0,0,0.6);
    -webkit-box-shadow: 3px 3px 3px rgba(0,0,0,0.6);
    -ms-box-shadow: 3px 3px 3px rgba(0,0,0,0.6);
    z-index: 3;

}

.graph.bar div.count   {
    font-size: small;
    position: relative;
    float: left;
    left: 20px;
    top: 2px;
    margin: 0 -10px 0 -10px;
    padding: 0;
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

table#search input[type="checkbox"] + label {
    float: none;
    display: inline;
    position: relative;
    font-size: x-small;
    font-weight: normal;
    margin: 3px;
    padding: 0;
    top: 3px;
    width: auto;
    clear: none;
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
    font-size: x-small;
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
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
}

ul.actionlink li:first-child:before {
    content: ' [ ';
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    }

ul.actionlink li:last-child:after {
    content: ' ] ';
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
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

img.actionlink {
    float: right;
    position: relative;
    top: 3px;
    border: none;
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
    border:  4px solid <?php echo color_scheme::getColor("title_bg_color") ?>;
    font-size: small;
    background: <?php echo color_scheme::getColor("table_bg_color") ?>;
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
    background: <?php echo color_scheme::getColor("table_bg_color") ?>;
    width: 90%;
    margin-left: auto; /* To center the page */
    margin-right: auto;
    border-collapse: collapse;
    font-size: medium;
    }

table.permissions td, table.permissions th  {
    background: <?php echo color_scheme::getColor("table_bg_color") ?>;
    font-size: medium;
    text-align: left;
}

table.permissions col {
    text-align: center;
    padding: 0px;
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

/* Previous and next links above a photo */
div.prev, div.next, div.pagelink, div.photohdr  {
    margin-bottom: 2px;
    margin-top: 30px;
    font-size: small;
    float: left;
}

div.prev a, div.next a, div.pagelink a {
    text-decoration: none;
}

div.prev a:hover, div.next a:hover, div.pagelink a:hover {
    text-decoration: underline;
}

div.prev    {
    width: 20%;
    float: left;
    text-align: left
}

div.next    {
    width: 20%;
    float: right;
    text-align: right;
    clear: right;
}


/* Page links */
div.pagelink,
div.photohdr {
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

ul.pagegroup li {
    display: block;
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    border-radius: 5px;
    float: left;
    margin: 3px;
}

ul.pagegroup li a {
    display: block;
    padding: 6px 12px;
    text-decoration: none;
    text-align: center;
    vertical-align: middle;
}

ul.pagegroup li:hover {
    background: <?php echo color_scheme::getColor("selected_tab_bg_color") ?>;
    color: <?php echo color_scheme::getColor("selected_tab_font_color") ?>;
}

ul.pagegroup li.current {
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    color: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    font-weight: bold;
}

/* up and down arrows for sort order */
.up, .down  {
    margin: 0px;
    padding: 0px;
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

img.<?php echo MID_PREFIX ?> {
    margin-left: auto;
    margin-right: auto;
    clear: both;
    text-align: center;
    display: block;
    padding: 10px;
}

img.busy,
img.waiting {
    margin-left: auto;
    margin-right: auto;
    clear: both;
    text-align: center;
    display: block;
}

span.md5 {
    display: none;
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
    padding-left: 4px;
    padding-right: 4px;
    font-weight: normal;
}

span.inputhint  {
    padding-left: 30px;
    text-align: right;
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
    width: <?php echo THUMB_SIZE ?>px;
    height: <?php echo THUMB_SIZE ?>px;
    float:left;
    margin: 2px;
    padding: 5px;
}

div.comment {
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    background: transparent;
    font-size: small;
    min-height: 5em;
    padding-bottom: 5px;
    margin-bottom: 5px;
}

div.comment h3 {
    width: 100%;
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    border-bottom: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    text-align: left;
}

div.commentinfo {
    border-bottom: 1px dashed <?php echo color_scheme::getColor("table_border_color") ?>;
    width: 100%;
    font-size: x-small;
    font-style: italic;
    margin: 0 0 0.5em 0;
    padding: 0;
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
    width: <?php echo THUMB_SIZE+10 ?>px;
    vertical-align: middle;
    margin: 0;
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

dl.color_scheme,
dl.comment,
dl.page,
dl.pageset,
dl photo,
dl.users,
dl.track {
    margin-top: 0px;
    margin-bottom: 30px;
    /* Workaround for Firefox bug */
    border: 1px solid transparent;
}

label,
dl.color_scheme dt,
dl.comment dt,
dl.page dt,
dl.pageset dt,
dl.photo dt,
dl.users dt,
dl.track dt,
dl.color_scheme dd,
dl.comment dd,
dl.page dd,
dl.pageset dd,
dl.photo dd,
dl.users dd,
dl.track dd {
    font-size: medium;
    padding-left: 4px;
    padding-right: 4px;
    min-height: 1.3em;
    margin: 4px 0px;
    position: relative;
    top: -4px;
    border: none;
}

dl.color_scheme dd,
dl.comment dd,
dl.page dd,
dl.pageset dd,
dl.photo dd,
dl.users dd,
dl.track dd {
    float: left;
    width: 55%;
    margin: 5px;
}

form.prefs label,
dl.color_scheme dt,
dl.comment dt,
dl.page dt,
dl.pageset dt,
dl.photo dt,
dl.users dt,
dl.track dt {
    clear: left;
    float: left;
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

label, table.credits th {
    text-align: right;
    vertical-align: top;
    font-weight: bold;
    margin-left: 1em;
    margin-bottom: 10px;
    width: 10em;
    display: block;
    float: left;
    clear: none;
}

label {
    clear: left;
}

/* This is to get the labels in a nice column, even if there's a checkbox
next to it, like on the Annotate photo page */

input[type="checkbox"] + label {
    clear: none;
    width: 9em;
}

fieldset  {
    margin: 0;
    border: none;
    padding: 0;
    margin-bottom: 5px;
    clear: right;
    overflow: hidden;
    display: block;
    float: left;
}

fieldset.editphotos,
fieldset.map,
fieldset.geotag  {
    width: 100%;
    margin-top: 10px;
    margin-bottom: 5px;
    padding-bottom: 20px;
    border: 1px solid <?php echo color_scheme::getColor("page_bg_color") ?>;
}

fieldset.members {
    clear: both;
    margin: 20px 20px 20px 12em;
    width: 15em;
    border: 1px solid <?php echo color_scheme::getColor("page_bg_color") ?>;
}


// The map is inside a table on the search page
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
    left: 2em;
    padding-right: 2em;
    padding-left: 2em;
    font-weight: bold;
    border: 1px solid <?php echo color_scheme::getColor("page_bg_color") ?>;
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
}

fieldset.members legend {
    padding: 0.5em 4em;
    margin-bottom: 0.8em;
}


fieldset.editphotos div.thumbnail {
    vertical-align: top;
    clear: none;
    font-size: small;
    margin: 0px;
    margin-left: -10em;
    float: right;
}

fieldset.editphotos-fields {
    margin: 0 0 0 20px;

    clear: none;
    width: 90%;
    padding-top: 10px;
}

/* These are the lists on the bulk edit page, such as the list of albums + the remove checkbox. */

fieldset.checkboxlist legend {
    display: none;
}

input[type="button"],
input[type="submit"],
input[type="reset"] {
    border: 2px outset;
    background: <?php echo color_scheme::getColor("tab_bg_color") ?>;
    color: <?php echo color_scheme::getColor("tab_font_color") ?>;
    font-weight: bold;
    width: 100px;
    height: 25px;
    margin-top: 15px;
    margin-bottom: 15px;
    margin-left: auto;
    margin-right: 15px;
    display: block;
}

input[disabled] {
    background: #aaaaaa;
}

div#rowscols input[type="submit"] {
    display: inline;
    margin: 0px 15px 0px 15px;
}

input[type="submit"].updatebutton {
    clear: right;
    margin-right: 15px;
    margin-left: auto;
    display: block;
}

input[type="submit"].increment {
    margin: 0px;
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
    margin-top: 0px;
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
    position: relative;
    top: -6px;
}

input[type="checkbox"] {
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
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
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

form.geotag select,
form.import select,
form.geotag input,
form.import input {
    float: left;
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

table#users td,
table.credits td {
    margin: 2px;
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

div#rowscols select,
div#rowscols input,
div#rotate select,
div#rotate input {
    margin: 0;
}

div#relation {
    margin-left: auto;
    margin-right: auto;
    width: 50%;
}

div#rotate input[type="submit"] {
    margin-left: auto;
    margin-right: auto;
}

div.page-preview {
    border: 1px solid black;
    width: 80%;
    max-height: 600px;
    min-width: 600px;
    overflow: scroll;
    background: <?php echo color_scheme::getColor("table_bg_color") ?>;
    font-size: medium;
    border-spacing: 0px;
    padding: 10px;
    border: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    clear: both;
}

div.page h1, div.page-preview h1 {
    position: relative;
    left: -10px;
    margin: 0;
    width: 100%;
    border-left: 0px;
    border-right: 0px;
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
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
    border: none;
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
    -moz-border-radius: 5px;
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    border: none;
    margin: 10px;
    width: 95%;
}

div.import_details,
div.import_thumbs {
    display: none;
}

div.import_thumbs div.thumbnail {
    height: <?php echo THUMB_SIZE + 30 ?>px;
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
    border: none;
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
    -moz-border-radius-topleft: 5px;
    -moz-border-radius-topright: 5px;
    background: <?php echo color_scheme::getColor("breadcrumb_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    border-bottom: 1px solid <?php echo color_scheme::getColor("table_border_color") ?>;
    text-align: center;
    width: 100%;
    clear: left;
    font-size: large;
    font-weight: bold;
    display: block;
    padding: 3px 0;
    margin: 0;
}


.upload {
    width:      100%;
    height:     80px;
    border: none;
}

.progressbar {
    margin: auto;
    padding: 0;
    height: 20px;
    background: white;
    border: 1px solid black;
    -moz-border-radius: 5px;
    overflow: hidden;
    clear: both;
}

.progressfill {
    height: 16px;
    margin: 2px;
    padding: 0;
    max-width: 99%;
    -moz-border-radius: 4px;
    text-align: center;
    background: <?php echo color_scheme::getColor("page_bg_color") ?>;
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
    padding: 0;
    border: none;
    width: 230px;
}

fieldset.formhelper-multiple {
    width: 100%;
}

fieldset.formhelper-multiple > .actionlink {
    float: left;
    margin-left: 10px;
}

fieldset.multiple img.actionlink:last-child {
    /* hide the remove icon on last dropdown */
    display: none;
}

fieldset.import-extrafields {
    margin: 0;
    padding: 0;
    border: none;
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
    padding: 0;
    clear: left;
}

ul.tabs > li {
    position: relative;
    margin: 0;
    z-index: 5;
    display: block;
    float: right;
    height: 55px;
    width: 40px;
    clear: both;

    overflow: hiddden;

    -webkit-transition: width 700ms ease-out 200ms;
    -moz-transition: width 700ms ease-out 200ms;
    -o-transition: width 700ms ease-out 200ms;
    -ms-transition: width 700ms ease-out 200ms;
    transition: width 700ms ease-out 200ms;

}

ul.tabs div.tab {
    position: relative;
    background: <?php echo color_scheme::getColor("title_bg_color") ?>;
    color: <?php echo color_scheme::getColor("title_font_color") ?>;
    border: none;

    border-radius: 10px 0 0 10px;
    -moz-border-radius: 10px 0 0 10px;
    -webkit-border-radius: 10px 0 0 10px;
    -o-border-radius: 10px 0 0 10px;

    width: 30px;
    height: 25px;
    margin: 0 -3px 0 0;
    padding: 10px 6px 10px 10px;

    box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -moz-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -o-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -webkit-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -ms-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    z-index: 3;
}

ul.tabs div.contents > h1 {
    border: none;
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
    -moz-border-radius: 0 0 0 20px;
    -webkit-border-radius: 0 0 0 20px;
    -o-border-radius: 0 0 0 20px;
    -ms-border-radius: 0 0 0 20px;


    box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -moz-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -o-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -webkit-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    -ms-box-shadow: -3px 3px 3px rgba(0,0,0,0.6);
    width: 515px;

    border: none;
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
    background-image: url("<?php echo template::getImage("icons/link.png") ?>");
}

li.share li.html {
    background-image: url("<?php echo template::getImage("icons/html.png") ?>");
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
}

div.message img.icon {
    float: left;
    margin-right: 10px;
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

