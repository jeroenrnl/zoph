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

if ( $_GET['logged_on']=="no" ) {
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

body	{
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

a	{
	color: <?php echo $LINK_COLOR ?>;
	background: transparent;
	}

/* Images that are links */

a IMG	{
	border: none;
	}

h1	{
	background: <?php echo $TITLE_BG_COLOR ?>;
	color: <?php echo $TITLE_FONT_COLOR ?>;
	border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>; 
	text-align: left;
	width: 100%;
	clear: left;
	font-size: x-large;
	font-weight: bold;
	display: block;
	padding: 2px 16px 2px 4px;
	margin: 0;
	}

/* Secondary title such as album title */
h2	{
	text-align: left;
	font-size: large;
	margin: 0;
	margin-bottom: 10px;
	}

h2.logon {
	margin-bottom: 30px;
	}

/* Level 3 title */
h3	{
	text-align: center;
	font-size: medium;
	font-weight: bold;
	margin: 0px;
	}

/* Unordered list */
ul	{
	padding-left: 1em;
	margin: 0.5em 1em 1em 1em;
	}

/* Form properties */
form	{
	margin: 0 0 0 0;
	width: 100%;
	}

/* Takes care of the border around the page */

	
/* Menubar */

ul.menu	{
	background: <?php echo $PAGE_BG_COLOR ?>;
	margin-left: 4px;
	padding: 0;
	display: inline;
	width: 100%;
	}

ul.menu li 	{
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
	padding: 2px;
	padding-right: 18px;
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
.main, .info, .letter	{
	background: <?php echo $TABLE_BG_COLOR ?>;
	font-size: medium;
	width: 100%;
	border-spacing: 0px;
	padding: 10px;
	border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
	clear: both;
	}

p.main, p.info {
	padding: 4px;
	}

table.info > col { width: 50%; }

/* The short introduction on zoph.php */
.intro	{
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
	

p.intro	{
	clear: both;
	margin-top: 2px;
	margin-bottom: 2px;
	}
	
/* ratings and reports are used on the reports page */

table.ratings	{
	margin-left: auto; /* To center the page */
	margin-right: auto;
	padding: 10px 5px 20px 5px;
	}

table.reports {
	width: 50%;
	float: left;
	padding: 10px 5px 20px 5px;
}
	
/* This is the bar that shows the number of photos for each rating */
div.ratings   {
	float: left;
	background: <?php echo $BREADCRUMB_BG_COLOR ?>;
	}

table.places {
	width: 100%;
	}

table.places td	{
	padding-left: 5px;
	padding-right: 5px;
	}

/* Links that appear on the right hand side of the title bar or page */

span.actionlink {
	margin: 1px;
	text-align: right;
	vertical-align: top;
	font-size: x-small;
	float: right;
	font-weight: normal;
	}
	
.actionlink:before, div.letter:before {
	content: "[ ";
	}

.actionlink:after, div.letter:after {
	content: " ]";
	}

/* Text next to 'remove' tickbox */
.remove	{
	text-align: left;
	font-size: small;
	vertical-align: top;
	}


/* Letter in an alphabetic select list */
.letter	{
	text-align: center;
	font-size: small;
	}

/* The letter that is currently active */
.letter .selected	{
	font-weight: bold;
	}

/* Description of an album, category, etc. */
.description	{
	font-style: italic;
	font-size: medium;
	}	
	
/* Description of a photo */
.photodesc	{
	border:  4px solid <?php echo $TITLE_BG_COLOR ?>;
	font-size: small;
	background: <?php echo $TABLE_BG_COLOR ?>;
	}
/* The description of a photo in thumbnail view */
.thumbdesc	{
	font-size: small;
	}
	
/* Rotate links above a photo */
.rotate	{
	font-size: small;
	text-align: center;
	}



/* Tables for the color scheme */

table.colors {
	margin-left: auto; /* To center the page */
	margin-right: auto;
	}	

table.colors > td, table.colors > th	{
	padding-left: 10px;
	padding-right: 10px;
	}

/* Tables for the permissions */

table#permissions	{
	background: <?php echo $TABLE_BG_COLOR ?>;
	width: 90%;
	margin-left: auto; /* To center the page */
	margin-right: auto;
	border-collapse: collapse;
	font-size: medium;
	}	

table#permissions td, table#permissions th	{
	background: <?php echo $TABLE_BG_COLOR ?>;
	font-size: medium;
	}

table#permissions col {
	text-align: center; 
	padding: 0px;
	}

table#permissions > col.col1 { 
	padding-left: 15px; 
	text-align: left; 
	width: 5%;
	}
	
table#permissions > col.col2 { width: 55%; text-align: left; }
table#permissions > col.col3 { width: 20%; text-align: center; }
table#permissions > col.col4 { width: 20%; text-align: center;}

table#permissions td.permremove	{
	padding-top: 3px;
	padding-bottom: 0px;
	font-size: x-small;
	text-align: left;
	vertical-align: bottom;
	}

/* Previous and next links above a photo */
div#prev, div#next, div#pagelink, div#photohdr	{
	margin-bottom: 2px;
	margin-top: 30px;
	font-size: small;
	float: left;
	}

div#prev	{ 
	width: 20%;
	float: left; 
	text-align: left 
	}
	
div#next	{
	width: 20%;
	float: right;
	text-align: right;
	clear: right;
	}


/* Page links */
div#pagelink,
div#photohdr {
	text-align: center;
	width: 60%;
	}
	
#currentpage	{
	color: red;
	font-weight: bold;
	}

/* up and down arrows for sort order */
.up, .down	{
	margin: 0px;
	padding: 0px;
	display: block; /* needed to make the arrows exactly connect */
	}

/* Form on top of each photopage to determine sortorder, asc or desc and
   number of photos displayes */


div#sortorder {
	float: left;
	margin-bottom: 15px;
	}

div#updown {
	float: left;
	margin: 5px 30px 15px 30px;
	}

div#rowscols {
	float: right;
	clear: right;
	margin-bottom: 15px;
	}

img.<?php echo MID_PREFIX ?> {
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
div#personlink	{
	text-align: center;
	font-size: small;
	width: 100%;
	margin: 0 0 15px 0;
	}

/* Text next to an input field, suggesting what to put there, such as "64 chars max" */
.inputhint	{
	font-size: small;
	padding-left: 4px;
	padding-right: 4px;
	}

span.inputhint	{
	padding-left: 30px;
	text-align: right;
	}
/* Checkbox on the annotate photo page */

.checkbox	{
	text-align: right;
	}

.fieldtitle-centered	{
	text-align: center;
	vertical-align: top;
	font-weight: bold;
	}

div.editchoice	{
	vertical-align: top;
	clear: none;
	font-size: small;
	margin: 10px;
	margin-right: -10em;
	float: left;
	width: 10em;
	}

/* Thumbnail photo */
div.thumbnail	{
	text-align: center;
	vertical-align: top;
	width: <?php echo THUMB_SIZE ?>px;
	float:left;
	margin: 2px;
	padding: 5px;
	}
	
div.comment	{
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

div.commentinfo	{
	border-bottom: 1px dashed <?PHP echo $TABLE_BORDER_COLOR ?>;
	width: 100%;
	font-size: x-small;
	font-style: italic;
	margin: 0 0 0.5em 0;
	padding: 0;
	}

br	{
	clear: both;
	}

br.noclear {
	clear: none;
	}

/* The random thumnail on the first page */
#random.thumbnail	{
	width: <?php echo THUMB_SIZE+10 ?>px;
	vertical-align: middle;
	margin: 0;
	padding-top: 10px;
	padding-left: 0px;
	float: left;
	}

/* Mid size photo */
.photo	{
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
.wide 	{
	width: 90%
	}

.field, .fieldtitle, .fieldtitle-centered, label	{ 
	font-size: medium; 
	padding-left: 4px;
	padding-right: 4px;
	}

.fieldtitle, label	{
	text-align: right;
	vertical-align: top;
	font-weight: bold;
	}

label	{
	margin-left: 1em;
	margin-bottom: 10px;
	width: 10em;
	display: block;
	float: left;
	clear: none;
	}

/* This is to get the labels in a nice column, even if there's a checkbox
next to it, like on the Annotate photo page */

input[type="checkbox"] + label {
	width: 9em;
	}

fieldset  {
	margin: 0;
	border: none;
	padding: 0;
	margin-bottom: 5px;
	clear: right;
	}

fieldset.editphotos  {
	margin-top: 10px;
	margin-bottom: 5px;
	padding-bottom: 20px;
	border: 1px solid <?php echo $PAGE_BG_COLOR ?>;
	}

fieldset.editphotos legend {
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

div#logon input[type="text"],
div#logon input[type="password"],
div#passwordchange input[type="text"],
div#passwordchange input[type="password"]	{
	margin-bottom: 10px;
	margin-left: 5px;
	}

input, select {
	margin: 2px;
	}

table#users,
table#credits, 
table#user, 
table#slideshow, 
table#place,
table#photo,
table#editperson,
table#credits {
	width: 100%;
	}

table.editphotos {
	width: 350px;
	}

table#users td, 
table#credits td, 
table#user td,
table#slideshow td,
table#place td,
table#photo td, 
table#editperson td,
table.editphotos td {
	margin: 2px;
	}

table#zophinfo {
	width: 60%;
	margin-left: auto;
	margin-right: auto;
	}

table#zophinfo td.fieldtitle {
	width: 80%;
	}

table#zophinfo td.field {
	width: 20%;
	}
	
div#rowscols select,
div#rowscols input,
div#rotate select,
div#rotate input,
table#photo select,
table#photo input {
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

/* This is a temporary style to workaround this bug: http://bugzilla.mozilla.org/show_bug.cgi?id=241317 */
.bigbutton { width: 200px !important; }

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

/* Styles to control alignment - not really semantically correct - should change in the future */
.right	{ text-align: right; }
.center	{ text-align: center; }

/* Error message */
.error	{
	text-align: center;
	}
	
/* The copyright statement at the bottom of the page */
.version	{
	text-align: center;
	font-size: small;
	margin-bottom: 2px;
	}
