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
	}

/* Links */

a	{
	color: <?php echo $LINK_COLOR ?>;
	background: transparant;
	}

/* Images that are links */

a IMG	{
	border: none;
	}

/* Page title */
h1	{
	text-align: left;
	font-size: x-large;
	margin: 0px;
	font-weight: bold;
	}

/* Secondary title such as album title */
h2	{
	text-align: left;
	font-size: large;
	margin: 0px;
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
	}

/* The table that takes care of the border around the page */

table.page {
	background: <?php echo $TABLE_BORDER_COLOR ?>; 
	width: <?php echo DEFAULT_TABLE_WIDTH ?>px;
	border: none;
	margin-left: auto; /* To center the page */
	margin-right: auto;
	padding: 0px;
	border-collapse: collapse;
	}
	
table.page > td, table.page > th {
	margin: 0px;
	padding: 1px;
	}

/* Menubar */
.menu	{
	background: <?php echo $PAGE_BG_COLOR ?>;
	}

#menu ul	{
	margin-left: 4px;
	padding: 0;
	display: inline;
	}

#menu ul li 	{
	padding: 1px;
	padding-top: 3px;
	margin: -1px;
	text-align: center;
	list-style: none;
	display: inline;
        background: <?php echo $TAB_BG_COLOR ?>;
        color: <?php echo $TAB_FONT_COLOR ?>;
	font-size: small;
	}

#menu ul li.selected {
	background: <?php echo $SELECTED_TAB_BG_COLOR ?>;
	color: <?php echo $SELECTED_TAB_FONT_COLOR ?>;
	}
								
/* since the A element does not inherit font colors from it's parents, we set it explicetly here. Also underlining is removed from links in menu, unless it is hovered */

#menu ul li > a { 
	color: <?php echo $TAB_FONT_COLOR ?>; 
	text-decoration: none; 
	}
	
#menu ul li > a:hover { text-decoration: underline; }
#menu ul li.selected > a { color: <?php echo $SELECTED_TAB_FONT_COLOR ?>; }

/* The breadcrumb line at the top of the page */

#breadcrumb {
	background: <?php echo $BREADCRUMB_BG_COLOR ?>;
	border: 1px solid <?php echo $TABLE_BORDER_COLOR ?>;
	color: <?php echo $TEXT_COLOR ?>;
	font-size: small;
	}

#breadcrumb ul {
	margin: 0;
	padding: 0;
	float: left;
	border: none;
	} 

#breadcrumb ul li {
	margin-left: 1px;
	padding-left: 2px;
	border: none;
	list-style: none;
	display: inline;
	}

#breadcrumb ul li:before {
	content: "\0020 \0020 \0020 \00BB \0020";
	}
	
#breadcrumb ul li.first:before {
	content: " ";
	}

#breadcrumb ul li.firstdots:before {
	content: "... \00BB \0020 ";
	}

/* Main page */
.main, table.info	{
	background: <?php echo $TABLE_BG_COLOR ?>;
	font-size: medium;
	width: 100%;
	border-spacing: 0px;
	padding: 10px;
	}

table.main > td, table.main > th, table.info > td, table.info > th	{
	padding: 4px;
	}

table.info > col { width: 50%; }

/* Some pages have a table nested inside the "main" table */
table.content	{
	margin-left: auto; /* To center the page */
	margin-right: auto;
	}

/* ratings and reports are used on the reports page */

table.ratings	{
	margin-left: auto; /* To center the page */
	margin-right: auto;
	}

div.ratings   {
       float: left;
       background: <?php echo $BREADCRUMB_BG_COLOR ?>;
       }

table.reports {
	width: 100%;
	}

td.reports {
	  text-align: left;
	  vertical-align: top;
	  width: 50%;
	  }
			  
/* Main titlebar */
.titlebar	{
	background: <?php echo $TITLE_BG_COLOR ?>;
	color: <?php echo $TITLE_FONT_COLOR ?>;
	text-align: left;
	width: 100%;
	}


/* Links that appear on the right hand side of the title bar or page */

.actionlink {
	margin: 1px;
	text-align: right;
	vertical-align: top;
	font-size: x-small;
	display: block;
	float: right;
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

table.permissions	{
	background: <?php echo $TABLE_BG_COLOR ?>;
	width: 100%;
	margin-left: auto; /* To center the page */
	margin-right: auto;
	border-collapse: collapse;
	font-size: medium;
	}	

table.permissions > td, table.permissions > th	{
	background: <?php echo $TABLE_BG_COLOR ?>;
	font-size: medium;
	}

table.permissions > col {
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

td.permremove	{
	padding-top: 3px;
	padding-bottom: 0px;
	font-size: x-small;
	align: left;
	vertical-align: bottom;
	}

/* Previous and next links above a photo */
.prev, .next { font-size: small; }
.prev	{ text-align: left; }
.next	{ text-align: right; }
td.prev, td.next { width: 20%; }

/* Page links */
.pagelink	{
	font-size: small;
	text-align: center;
	}
	
.currentpage	{
	color: red;
	font-weight: bold;
	}

/* up and down arrows for sort order */
.up, .down	{
	margin: 0px;
	padding: 0px;
	display: block; /* needed to make the arrows exactly connect */
	}

/* Header showing name, size in pixels and size in bytes above a photo */
/* Links to persons under a photo */
.photohdr, .personlink	{
	text-align: center;
	font-size: small;
	}

.field, .fieldtitle, .fieldtitle-centered	{ 
	font-size: medium; 
	padding-left: 4px;
	padding-right: 4px;
	}

.fieldtitle	{
	text-align: right;
	vertical-align: top;
	font-weight: bold;
	}
	
/* Text next to an input field, suggesting what to put there, such as "64 chars max" */
.inputhint	{
	font-size: small;
	padding-left: 4px;
	padding-right: 4px;
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

.editchoice	{
	vertical-align: top;
	font-size: small;
	}
/* Thumbnail photo */
.thumbnail	{
	text-align: center;
	vertical-align: top;
	width: <?php echo THUMB_SIZE ?>px;
	}

/* The random thumnail on the first page */
#random.thumbnail	{
	vertical-align: middle; 
	padding: 10px;
	}

/* Mid size photo */
.photo	{
	text-align: center;
	}

/* Person / place in the list of persons / places */
.person, .place, .showattr {
	text-align: left;
	font-size: medium;
	}

/* hr */
.wide 	{
	width: 90%
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
	margin-left: auto;
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
.center { text-align: center; }

/* Error message */
.error	{
	text-align: center;
	}
	
/* The copyright statement at the bottom of the page */
.version	{
	text-align: center;
	font-size: small;
	}
