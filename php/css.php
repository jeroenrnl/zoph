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

header("Content-Type: text/css");
if(isset($_GET['logged_on'])) {
    require_once("log.inc.php");
    require_once("config.inc.php");
    require_once("zoph_table.inc.php");
    require_once("user.inc.php");
    echo "/* This is the default CSS, the user is not logged on */";
} else {
    require_once("include.inc.php");
    echo "/* This is the customized CSS, user is logged on */";
}

?>

/* Some of the styles have been based on http://www.alistapart.com/articles/taminglists/ */

/* Main CSS style, all elements inherit these settings */

body    {
    font-family: Arial, Verdana, sans-serif;
    font-size: medium;
    color: <?php echo $TEXT_COLOR ?>;
    background: <?php echo $PAGE_BG_COLOR ?>;
    width: <?php echo DEFAULT_TABLE_WIDTH ?>;
    border: none;
    margin-left: auto; /* To center the page */
    margin-right: auto;
    padding: 0px;
    border-collapse: collapse;
    }

/* Links */

a   {
    color: <?php echo $LINK_COLOR ?>;
    background: transparent;
    }

/* Images that are links */

a IMG   {
    border: none;
    }

h1  {
    background: <?php echo $TITLE_BG_COLOR ?>;
    color: <?php echo $TITLE_FONT_COLOR ?>;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>; 
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
    }

ul.thumbs li {
    background: <?php echo $TITLE_BG_COLOR ?>;
    color: <?php echo $TITLE_FONT_COLOR ?>;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>; 
    border-radius: 10px;
    position: relative;
    display: block;
    text-align: center;
    width: <?php echo THUMB_SIZE + 40 ?>px;
    height:<?php echo THUMB_SIZE + 40 ?>px;
    float:left;
    margin: 5px;
    padding: 5px;
    -moz-border-radius: 10px;
    }

ul.thumbs li p {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: <?php echo THUMB_SIZE ?>px;
    }

ul.thumbs li img {
    display: block;
    margin-left: auto;
    margin-right: auto;
}

ul.thumbs li div {
    left: 5%;
    position: absolute;
    bottom: 0px;
    text-align: center;
    width: 90%;
    }

ul.list {
    clear: both;
    list-style: none;
    }

ul.list li.alternate {
    background-color: <?php echo $TITLE_BG_COLOR ?>;
    }

ul.tree {
    clear: both;
    list-style: none;
    }

ul.tree ul {
    list-style: none;
    margin-top: 0;
    margin-bottom: 0;
    }

ul.tree ul ul {
    display: none;
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
    background: <?php echo $BREADCRUMB_BG_COLOR ?>;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
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

ul.autocompdropdown {
    position: relative;
    background: <?php echo $PAGE_BG_COLOR ?>;
    width: 300px;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>; 
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

input.autocompinput {
    background: white url('images/down2.gif');
    background-repeat: no-repeat;
    background-position: center right;

    }

ul.autocompdropdown li:hover,
ul.autocompdropdown li#selected {
    background: <?php echo $TAB_BG_COLOR ?>;
}

/* Menubar */

ul.menu {
    background: <?php echo $PAGE_BG_COLOR ?>;
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
    background: <?php echo $TAB_BG_COLOR ?>;
    color: <?php echo $TAB_FONT_COLOR ?>;
    font-size: small;
    }

ul.menu li:hover {
    position: relative;
    background: <?php echo $BREADCRUMB_BG_COLOR ?>;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
    border-bottom: none;
    padding: 2px 0 2px 0;
    }

ul.menu li.selected {
    background: <?php echo $SELECTED_TAB_BG_COLOR ?>;
    color: <?php echo $SELECTED_TAB_FONT_COLOR ?>;
    }
                                
/* since the A element does not inherit font colors from it's parents, we set it explicetly here. Also underlining is removed from links in menu, unless it is hovered */

ul.menu li a { 
    color: <?php echo $TAB_FONT_COLOR ?>; 
    text-decoration: none; 
    }
    
ul.menu li > a:hover { text-decoration: underline; }
ul.menu li.selected > a { color: <?php echo $SELECTED_TAB_FONT_COLOR ?>; }

/* The breadcrumb line at the top of the page */

div.breadcrumb {
    background: <?php echo $BREADCRUMB_BG_COLOR ?>;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
    color: <?php echo $TEXT_COLOR ?>;
    font-size: small;
    float: left;
    margin: 0;
    padding: 2px 10px 2px 10px;
    clear: left;
    width: 100%;
    } 

div.breadcrumb ul {
    margin: 0;
    padding: 0;
    }
    
div.breadcrumb li {
    margin-left: 1px;
    padding-left: 2px;
    padding-right: 8px;
    border: none;
    list-style: none;
    display: inline;
    }

div.breadcrumb li:before {
    content: "\0020 \0020 \0020 \00BB \0020";
    }
    
div.breadcrumb li.first:before {
    content: " ";
    }

div.breadcrumb li.firstdots:before {
    content: "... \00BB \0020 ";
    }

/* Main page */
.main, .info, .letter, .page, div.map, div#selection, .warning   {
    background: <?php echo $TABLE_BG_COLOR ?>;
    font-size: medium;
    width: 100%;
    border-spacing: 0px;
    padding: 10px;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
    clear: both;
    overflow: hidden;
    }

div.warning {
    background: #ffffcc;
}

div.warning img.icon {
    float: left;
    margin-right: 10px;
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
    width: 300px;
    height: 300px;
    }

div.mapinfo {
    float: left;
}

span.showhide {
    font-size: small;
    text-align: right;
    }

div.timedetail, div.ratingdetail {
    display: none;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
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
    background: <?php echo $TITLE_BG_COLOR ?>;
    color: <?php echo $TITLE_FONT_COLOR ?>;
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
    background: <?php echo $TITLE_BG_COLOR ?>;
    color: <?php echo $TITLE_FONT_COLOR ?>;
}

p.main, p.info {
    padding: 4px;
    }

/* The short introduction on zoph.php */
.intro  {
    padding: 5px;
    text-align: left;
    }

div.intro {
    float: right;
    clear: right;
    width: <?php echo DEFAULT_TABLE_WIDTH-THUMB_SIZE-50 ?>px;
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

table.ratings   {
    margin-left: auto; /* To center the page */
    margin-right: auto;
    padding: 10px 5px 20px 5px;
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

/* This is the bar that shows the number of photos for each rating */
div.ratings   {
    float: left;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
    background: <?php echo $BREADCRUMB_BG_COLOR ?>;
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

/* Links that appear on the right hand side of the title bar or page */

span.actionlink, div.actionlink {
    margin: 1px;
    text-align: right;
    vertical-align: top;
    font-size: x-small;
    float: right;
    font-weight: normal;
    }

span.photocount {
    font-size: x-small;
    }

span.actionlink:before, div.letter:before {
    content: "[ ";
    }

span.actionlink:after, div.letter:after {
    content: " ]";
    }

/* New, semantic way to do actionlinks */

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

ul.actionlink li:before {
    content: ' | ';
    }

ul.actionlink li:first-child:before {
    content: ' [ '; 
    } 

ul.actionlink li:last-child:after {
    content: ' ] ';
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


/* Letter in an alphabetic select list */
.letter {
    text-align: center;
    font-size: small;
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
    border:  4px solid <?php echo $TITLE_BG_COLOR ?>;
    font-size: small;
    background: <?php echo $TABLE_BG_COLOR ?>;
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
    background: <?php echo $TABLE_BG_COLOR ?>;
    width: 90%;
    margin-left: auto; /* To center the page */
    margin-right: auto;
    border-collapse: collapse;
    font-size: medium;
    }   

table.permissions td, table.permissions th  {
    background: <?php echo $TABLE_BG_COLOR ?>;
    font-size: medium;
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
    
.currentpage    {
    color: red;
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

div#sortorder {
    float: left;
    margin-right: 15px;
    margin-bottom: 15px;
    }

div#updown {
    float: left;
    margin: 5px 30px 15px 30px;
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
    }

span.inputhint  {
    padding-left: 30px;
    text-align: right;
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
    border: 1px solid <?PHP echo $TABLE_BORDER_COLOR ?>;
    background: transparent;
    font-size: small;
    min-height: 5em;
    padding-bottom: 5px;
    margin-bottom: 5px;
    }

div.comment h3 {
    width: 100%;
    background: <?php echo $TITLE_BG_COLOR ?>;
    color: <?php echo $TITLE_FONT_COLOR ?>;
    border-bottom: 1px solid <?php echo $TABLE_BORDER_COLOR ?>; 
    text-align: left;
    }

div.commentinfo {
    border-bottom: 1px dashed <?PHP echo $TABLE_BORDER_COLOR ?>;
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

/* Mid size photo */
.photo  {
    text-align: center;
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

label, dt, dd    { 
    font-size: medium; 
    padding-left: 4px;
    padding-right: 4px;
    min-height: 1.3em;
    }

dd {
    float: left;
    width: 55%;
    margin: 5px;
    }

dt {
    clear: left;
    float: left;
    width: 40%;
    font-weight: bold;
    text-align: right;
    margin-top: 5px;
    margin-bottom: 5px;
    }

dl  {
    margin-top: 0px;
    margin-bottom: 30px;
    /* Workaround for Firefox bug */
    border: 1px solid transparent;
    }

dl#allexif {
    display: none;
    width: 100%;
    }

dl#allexif dl {
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
    }

fieldset.editphotos, fieldset.map  {
    margin-top: 10px;
    margin-bottom: 5px;
    padding-bottom: 20px;
    border: 1px solid <?php echo $PAGE_BG_COLOR ?>;
    }

fieldset.editphotos legend, fieldset.map legend {
    clear: both;
    display: block;
    left: 2em;
    padding-right: 2em;
    padding-left: 2em;
        font-weight: bold;
    border: 1px solid <?php echo $PAGE_BG_COLOR ?>;
    background: <?php echo $TITLE_BG_COLOR ?>;
    }

fieldset.editphotos div.thumbnail {
    vertical-align: top;
    clear: none;
    font-size: small;
    margin: 0px;
    margin-left: -10em;
    float: right;
    width: 10em;
    }

fieldset.editphotos-fields {
    margin: 0;
    clear: none;
    width: 90%;
    padding-top: 10px;
    }

/* These are the lists on the bulk edit page, such as the list of albums + the remove checkbox. This is a bit of a hack, needed to make opera and MSIE behave. */

fieldset.checkboxlist { 
    display: block;
    float: left;
    width: 60%;
    clear: right;
    }   

input[type="button"],
input[type="submit"],
input[type="reset"] {
    border: 2px outset;
    background: <?php echo $TAB_BG_COLOR ?>;
    color: <?php echo $TAB_FONT_COLOR ?>;
    font-weight: bold;
    width: 100px;
    height: 25px;
    margin-top: 15px;
    margin-bottom: 15px;
    margin-left: auto;
    margin-right: 15px;
    display: block;
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

input, select {
    margin: 2px;
    }

table#users,
table.credits {
    width: 100%;
    }

table#users td, 
table.credits td { 
    margin: 2px;
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
    background: <?php echo $TABLE_BG_COLOR ?>;
    font-size: medium;
    border-spacing: 0px;
    padding: 10px;
    border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
    clear: both;
    }

div.page h1, div.page-preview h1 {
    position: relative;
    left: -10px;
    margin: 0;
    width: 100%;
    border-left: 0px;
    border-right: 0px;
    background: <?php echo $TITLE_BG_COLOR ?>;
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
    background: <?php echo $TITLE_BG_COLOR ?>;
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
    background: <?php echo $BREADCRUMB_BG_COLOR ?>;
    color: <?php echo $TITLE_FONT_COLOR ?>;
    border-bottom: 1px solid <?php echo $TABLE_BORDER_COLOR ?>; 
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
    background: <?php echo $PAGE_BG_COLOR ?>;
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

fieldset.multiple {
    background: transparent;
    margin: 0;
    padding: 0;
    border: none;
    width: 220px;
}

fieldset.multiple img.actionlink:last-child {
    // hide the remove icon on last dropdown
    display: none;
}
    

/* Styles for calendar */
.calendar { 
    font-size: small; 
    text-align: center;
    vertical-align: top;
    margin-left: auto; /* To center the page */
    margin-right: auto;
    }

.calendarToday { font-weight: bold; }

.calendarHeader { font-weight: bold; }

.calendar .next, .calendar .prev { font-size: x-small; }

/* Error message */
.error  {
    text-align: center;
    }
    
/* The copyright statement at the bottom of the page */
.version    {
    text-align: center;
    font-size: small;
    margin-bottom: 2px;
    }
