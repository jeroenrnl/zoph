# Zoph Changelog #

##Zoph 0.9.2##
###1 apr 2016###

Zoph 0.9.2 is the new stable release. I have decided to drop the separation between 'stable' and 'unstable' or 'feature' releases. This means that it is recommended for everyone to upgrade to this release.

###Features###
* Issue #44 : Added 'circles': a way to group people in Zoph. This is especially handy if you have a large amount of people in your Zoph, and the 'person' page is becoming confusing or cluttered.
* Issue #46 A circle and it's members can be surpressed in the overview page, so you can, for example, hide people that you added only for a small set of photos.
* Issue #20 Zoph has switched to the PDO classes for database access. This ensures compatibility with PHP in the future, because the old mysql libs will be dropped soon.
* Issue #32 It is now possible to set more properties of a photo, including map zoom from the web import.
* Issue #60 The link text for "next" and "previous" as well as page numbers has been increased in size for better usability esp. on mobile devices
* Added a script for fixing filename case (by Jason Taylor [@JiCit] )
* Access Google maps via https (Jason Taylor [@JiCiT])

###Bugs###
* [http://github.com/jeroenrnl/zoph/issues/49 Issue #49] Zoph now supports MySQL strict mode
* [http://github.com/jeroenrnl/zoph/issues/55 Issue #55] Autocomplete not working for people
* [http://github.com/jeroenrnl/zoph/issues/58 Issue #58] Sort order for albums and categories can not be changed
* CLI: Fixed an issue where Zoph would try to import to the current directory when double spaces were present in CLI
* Better handling of file not found problems during import
* Fixed two bugs that caused maps not to display
* Fixed an issue where breadcrumbs wouldn't be removed correctly in some cases
* Changed erronous extension of Exception class
* Fixed slow login times for non-admin users
* Improved performance on people page
* Fixed: zoom buttons are missing from Google Maps
* Remove duplicate files from import (if you would specify the same file twice on CLI import, you would get an error, this is now filtered out)
* Fixed an issue where the person pulldown on the add user page appeared to be empty
* Remove a user from a group when a the user is deleted
* Fixed a warning about unknown variable on places page
* Allow apostropes in place names when creating map markers (Jason Taylor [@JiCiT])

###Refactor###
* A complete new query builder has been created
* Many more parts of Zoph can be (and are being) tested automatically now, this should improve overall quality and reduce bugs
* Many parts of Zoph have been cleaned up to modernize code to the current state of PHP - dropping PHP 5.3 and 5.4 compatibility
* Dropped MSIE6/7 compatibility
* Added documentation to many parts of Zoph's source code
* Many changes to readability of source code, such as more consistent use of whitespace
* Added some more debugging possibilities to easier troubleshoot in case of problems
* Changed logging so less logging is displayed when set to log::NONE
* Changed all self:: references into static:: references
* Added function scope to many methods
* Started using namespaces to better organize the classes
* Updated version numbers in REQUIREMENTS readme. 
* Issue #8 (partial) Changed several parts of Zoph to use templates 
* Added improvements to templating system
* Modified query for photo access rights to a view for performance reasons
* Changed logging so SQL query log to file can be done without displaying 
* Performance improvement on place page
* Added a posibility to debug queries including parameters

## Zoph 0.9.1 ##
### 21 Feb 2014 ###
Zoph 0.9.1 is the first feature release for Zoph 0.9, it shows a preview of some of the new features for Zoph 0.10. Most important change is the move of most configuration items from config.inc.php into the Web GUI.

####Features####

* Issue #28 Configuration through webinterface 
* Removed display desc under thumbnail feature 
* Removed MIXED_THUMBNAILS and THUMB_EXTENSION settings 
* removed DEFAULT_SHOW_ALL setting 
* Removed LANG_DIR configuration item 
* Changed the looks of <input> fields a bit 
* Removed alternative password validators 
* Removed checks for PHP 5.1 
* Adding CLI support for configuration 
* Issue #7 Added a favicon 
* Issue #18 Added "return" link on bulk edit page 
* Added a script to migrate config to new db-based system 
* Issue #8 Made template selectible from webinterface 
* Removed MAX_CRUMBS 

####Bugs####

* Simplified CLI code & fixed bug in --autoadd
* Issue #34 Rows and columns swapped on photos page
* Issue #36 Webimporter does not import description
* Issue #37 Can not add position on map using the mouse
* Fixed a bug that caused EXIF information in some (rare) cases to report the aperture wrong.
* Strict standards warning 
* Issue #45 Pagebreak inside HTML tags causes browser to render incorrectly
* Issue #45 Added selectArray cache to zophTable
* Issue #48 Repair photo ratings during import
* Issue #50 Geonames project has changed URL and requires username
* Issue #51 Fixed depth in tree display when autocorrect is off
* Issue #39 Added support for session.upload_progress as APC replacement (PHP 5.4 compatibility)
* Issue #38 CLI tries to lookup previous argument's value when looking up photographer

####Improvements####

I have made quite a few improvements on the "inside" of Zoph. I have refactored many parts of Zoph
to create cleaner, less duplicated and more robust code. I have introduced UnitTests (resulting in 
about 20% of Zoph's sourcecode now tested fully automatic for bugs). As a help to that, I am now 
using Sonar to automatically run these tests and also analyse Zoph code for other problems.

* * Issue #29 First step in creating unittests for Zoph 
* Sonar Support 
* Refactor of PHP part of Mapping implementation 
* Move timezone-related global functions into class 
* TimeZone object improvements 
* Small change in way template is called on photo page (Full page templates are now "templates" and partial pages are "blocks") 
* Refactor of htmlMimeMail.php 
* Refactor of Mail_mimePart 
* Refactor annotate photo, watermark photo, image.php 
* Removed several global variables  
* Finished refactor of MIME classes 
* Refactor album, category, place, person, photo 
* Refactor: getEditArray() + unittests 
* Further refactor of photo, album, person, place, category  
* Refactor: move ratings out of photo object  
* Refactor: moved relations from photo object to new photoRelations object 
* Refactor: photo object 
* Got rid of adding session_id to URL 
* Modified internal database references to static 
* Removed brackets from require and include statements 
* Replaceed a die() with exception 
* Changed self-references in objects to use self:: 
* Removed unused class smtp 
* Made autoload a little more robust 
* Changes to autoload so it works in unittests too. 
* Removed unused RFC822 class 
* Changed line-endings in mailMimePart.inc.php to unix-style 
* Removed various unused variables 
* Removed duplicate templates 
* Removed unused $user from createPulldown() calls. 
* Issue #40 Change documentation to Markdown        
* Modified some queries to improve performance 

## Zoph 0.9.0.1 ##
### 18 oct 2012 ###

Zoph 0.9.0.1 is the first maintenance release for Zoph 0.9. It adds compatibility with MySQL 5.4.4 and later and PHP 5.4 support. Several bugs were fixed.


#### Bugs ####

* Issue #1  Changed TYPE=MyISAM to ENGINE=MyISAM for MySQL > 5.4.4 compatibility
* Issue #1  Fixed: PHP Notice: Array to string conversion
* Issue #2  Changed timestamp(14) into timestamp
* Issue #3  Removed pass-by-reference for PHP 5.4 compatibility
* Issue #6  Missing French translation
* Issue #30 Remove warning about undefined variables
* Issue #31 Fixed several errors in geotagging code
* Issue #33 Fixed: no error message when rotate fails
             Fixed a small layout issue on the prefs page

## Zoph 0.9 ##
### 23 jun 2012 ###

Zoph 0.9 is a stable release. It's equal to v0.9pre2, except for an updated Italian translation.

#### Translations ####
Updated Italian translation, by Francesco Ciattaglia

There are no known bugs in this version.

## Zoph 0.9pre2 ##
### 20 Feb 2012 ###

Zoph 0.9pre2 is the second release candidate for Zoph 0.9. Zoph is now completely feature-frozen for the 0.9 release, only bugfixes will be made.

#### Bugs ####

* Bug#3471099: Map not displaying when looking at photo in edit mode
* Bug#3471100: On some pages, title contains PHP warning

## Zoph 0.9pre1 ##
### 26 Nov 2011 ###

Zoph 0.9pre1 is the first release candidate for Zoph 0.9. Zoph is now completely feature-frozen for the 0.9 release, only bugfixes will be made.

#### Bugs ###

* Bug#3420574: When using --autoadd, zoph CLI import sometimes tries to create new locations or photographers even though they already exist in the database.
* Bug#3427517: Share this photo feature does not work
* Bug#3427518: Not possible to remove and album or category from a photo
* Bug#3433687: Not possible to remove album or category from photo (bulk)
* Bug#3431130: Share this photo doesn't show links in photo edit mode
* Bug#3433810: Popup for albums, categories, people and places doesn't always disappear when moving mouse away.
* Removed a warning that in some cases caused images not to be displayed.

#### Translations ####

* Added a few missing strings, reported by Pekka Kutinlahti.
* Updated Italian translation, by Francesco Ciattaglia
* Updated Dutch, German, Canadian English and Finnish

#### Other ####
* Got rid of a lot of PHP warnings
* Got rid of a lot of PHP strict messages
* Cut down on the number of global variables
* Removed support for magic_quotes
* Removed (last traces of) PHP4 support
* Bug#3435181: Variable inside quotes
* Updated wikibooks documentation

Older changes can be found in http://en.wikibooks.org/wiki/Zoph/Changelog/Archive and http://en.wikibooks.org/wiki/Zoph/Changelog/0.8-0.9
