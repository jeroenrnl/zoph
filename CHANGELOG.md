# Zoph Changelog #

##Zoph 0.9.6##
###14 apr 2017###
Zoph 0.9.5 coincided with a significant change in MySQL, that caused a lot of bugs in Zoph and other open source projects. MySQL changed the way they process queries to handle them much more strictly. What makes things worse, is that MariaDB did not make this change, so at first I could not reproduce the issue. Because of the amount of work, I have decided to postpone the development that was planned for 0.9.6 and make this a bugfix-only release. In this release, I have included a few bugfixes by Pontus Fröding which is really great, thanks Pontus!


###Bugs###
* [issue#86](https://github.com/jeroenrnl/zoph/issues/86) Fixed an omission in the upgrade instructions for 0.9.5
* [issue#87](https://github.com/jeroenrnl/zoph/issues/87) error about class not found on add or edit
* [issue#88](https://github.com/jeroenrnl/zoph/issues/88) Changes for MySQL 5.7 compatibility
  * Give timestamp a default value
  * Add field needed for MySQL 5.7 compatibility with SELECT DISTNCT .. ORDER BY
  * Adding "ORDER BY" fields to autocover query
  * More changes for MySQL 5.7 compatibility
  * Updated SQL scripts
  * Removed unused field from the database

* [issue#91](https://github.com/jeroenrnl/zoph/issues/91) Changed PHPUnit classes to namespaced class naming
* Fixed an issue in a UnitTest that caused a failed test commit
* [Pull Request#94](https://github.com/jeroenrnl/zoph/pull/94) Add namespace to template showJSwarning in edit_person (by Pontus Fröding)
* [Pull Request#95](https://github.com/jeroenrnl/zoph/pull/95) Add template namespace on two more places. (by Pontus Fröding)
* [issue#92](https://github.com/jeroenrnl/zoph/issues/92) Fixed database connection to utf-8
* [issue#93](https://github.com/jeroenrnl/zoph/issues/93) [Pull Request#95](https://github.com/jeroenrnl/zoph/pull/95) Fix for "Class pager not found" when using pagesets (by Pontus Fröding)

###Refactor###
* Some modifications to backtrace printing, for easier debugging
* Moved album view into template
* [issue#89](https://github.com/jeroenrnl/zoph/issues/89) Changed look of next and previous buttons on photo page and increased size of actionlinks
* Small style change

##Zoph 0.9.5##
###4 feb 2017###

Zoph 0.9.5 is the new stable release. It is recommended for everyone to upgrade to this release

###Features###
* [Issue#68](https://github.com/jeroenrnl/zoph/issues/68) Changed from Mapstraction to Leaflet as mapping abstraction - with GoogleMaps, OpenStreetMap and MapBox (OpenStreetMap) support
 The code for this was based on code provided by Jason (@JiCiT)
* [Issue#80](https://github.com/jeroenrnl/zoph/issues/80) You can now edit permissions from the album screen, without the need to go to the group edit.
* [Issue#82](https://github.com/jeroenrnl/zoph/issues/82) Zoph now gives a proper error message if a photo can not be found

###Bugs###
* Fixed a bug where in some cases it was possible for an admin to unintentionally delete albums

###Refactor###
* Lots of internal changes to move to an MVC-architecture
* Several more parts of Zoph moved into templates
* Added more unittests - to automatically test Zoph


##Zoph 0.9.4##
###18 Sept 2016###

Zoph 0.9.4 is the new stable release. It is recommended for everyone to upgrade to this release
###Features###
* Geocoding: Zoph now also searches Wikipedia
* [Issue#67](https://github.com/jeroenrnl/zoph/issues/67) Changed the colour scheme definition to use a nice interface to select the colour
* [Issue#23](https://github.com/jeroenrnl/zoph/issues/23) An admin user can now define default prefences for new users
* [Issue#24](https://github.com/jeroenrnl/zoph/issues/24) Added an option to automatically propagate permissions to newly created albums
* [Issue#78](https://github.com/jeroenrnl/zoph/issues/78) Removed Yahoo, Cloudmade mapping as they no longer offer their services to the public
* [Issue#78](https://github.com/jeroenrnl/zoph/issues/78) Removed Openlayers mapping, as Zophs implementation was buggy and did not work anymore.
* [Issue#47](https://github.com/jeroenrnl/zoph/issues/47) Photos can now be deleted from disk (moved to a trash dir)
* [Issue#67](https://github.com/jeroenrnl/zoph/issues/67) Added some new colour schemes

###Bugs###
* Fixed an issue with album pulldown when editing group access rights
* Fixed an issue where the circles page would sometimes report $title not found
* Fixed an issue with changing views on circle page
* Fixed an issue that caused errors in Firefox when using the configuration page
* fixed collapsable details for time and rating
* [Issue#78](https://github.com/jeroenrnl/zoph/issues/78) Fixed a case where an admin user was sometimes not allowed to see a person or a place

###Other improvements###
* [Issue#77](https://github.com/jeroenrnl/zoph/issues/77) Lots of fixes in the German translation by Thomas Weiland (@HonkXL)
* Moved group display to template
* Moved group delete (confirm) into template
* Moved group edit to a template
* [Issue#79](https://github.com/jeroenrnl/zoph/issues/79) Modify recursive creation of directories, so Zoph can function in an open_basedir enverironment.
* [Issue#66](https://github.com/jeroenrnl/zoph/issues/66) Cleanup of CSS
* Some modernization of the looks of Zoph
* [Issue#85](https://github.com/jeroenrnl/zoph/issues/85) Modified import process to show clearer error message
* [Issue#66](https://github.com/jeroenrnl/zoph/issues/66) Added a reset CSS
* [Issue#81](https://github.com/jeroenrnl/zoph/issues/81) Documentation updates
* Some fixes for UnitTests
* Additional tests
* Refactor of group_permissions class into permissions class
* Refactor prefs class
* Moved preferences page to template
* Modified prefs template to use labels instead of definition lists

##Zoph 0.9.3##
###10 jun 2016###

Zoph 0.9.3 is the new stable release. It is recommended for everyone to upgrade to this release

###Features###
* [Issue #72](https://github.com/jeroenrnl/zoph/issues/72) Zoph now has a new logon screen.
The logon screen has background photos. Two of them are already included in Zoph. You can place your own backgrounds in ```templates/default/images/backgrounds```. Or, you can (on the config screen) define an album from which the images will be used as background images. Zoph will display a random image as background.
* [Issue #76](https://github.com/jeroenrnl/zoph/issues/76) The logon screen now gives a message about the username and/or password being wrong instead of just returning to the same screen
* [Issue #75](https://github.com/jeroenrnl/zoph/issues/75) Zoph now uses PHP's password hashing algorithm instead of MySQL's.
This includes a random 'salt' added to each password. This will make it much, much harder to decrypt your passwords, if your database would ever fall into the wrong hands. The old hashes will be updated with the new ones as soon the the user logs in. Zoph will continue to support the old password hashes at least until v0.9.5.
* [Issue #26](https://github.com/jeroenrnl/zoph/issues/26) It is now possible to define the cookie expirement time. In previous versions of Zoph, a user would be logged out when closing the browser. Is now possible to extend the time to 1 hour, 4 hours, 8 hours, 1 day, 1 week or 1 month. This means a user will not need to re-login for that period of time, even when the browser is closed in the mean time. This can be very convenient, but it could mean that a user leaves Zoph logged in on a public PC. Therefore, the default is still 'session', which means a user will be logged out when closing the browser.
* "new" pages now show up in breadcrumbs
* It is now possible to give a user "can see all photos" access rights. This means you can give a user access to all photos, without giving him/her admin rights and without having to update user rights whenever an album is added.
* [Issue #22](https://github.com/jeroenrnl/zoph/issues/22) It is now possible to allow a user to create albums, categories, people, circles and places. The user automatically has access rights to place photos in the albums, categories, people, circles and places he or she has created.
* [Issue #21](https://github.com/jeroenrnl/zoph/issues/21) It is now possible to allow a user to delete photos. The user will have to have "write" access to at least one album a photo is in.
* Remove the rather ugly trailing space on the links on zoph.php

###Bugs###
* [Issue #73](https://github.com/jeroenrnl/zoph/issues/73) Fixed sharing feature
* [Issue #74](https://github.com/jeroenrnl/zoph/issues/74) Fixed Canadian English, Dutch and German translation files

###Other improvements###
* Added a way to disable a setting on the configuration page depending on the state of another configuration item. (This was created because the photo album as a logon background relies on the sharing feature to be enabled).
* Moved user page to template
* Moved form into a separate class
* Some cleanup of the places and categories pages
* Refactor HTML for actionlinks
* Modified createTestData script to only require password once
* Rearranged order of unittests
* Added translations for German, Canadian English and Dutch

##Zoph 0.9.2##
###1 apr 2016###

Zoph 0.9.2 is the new stable release. I have decided to drop the separation between 'stable' and 'unstable' or 'feature' releases. This means that it is recommended for everyone to upgrade to this release.

###Features###
* [Issue #44](https://github.com/jeroenrnl/zoph/issues/44) : Added 'circles': a way to group people in Zoph. This is especially handy if you have a large amount of people in your Zoph, and the 'person' page is becoming confusing or cluttered.
* [Issue #46](https://github.com/jeroenrnl/zoph/issues/46) A circle and it's members can be surpressed in the overview page, so you can, for example, hide people that you added only for a small set of photos.
* [Issue #20](https://github.com/jeroenrnl/zoph/issues/20) Zoph has switched to the PDO classes for database access. This ensures compatibility with PHP in the future, because the old mysql libs will be dropped soon.
* [Issue #32](https://github.com/jeroenrnl/zoph/issues/32) It is now possible to set more properties of a photo, including map zoom from the web import.
* [Issue #60](https://github.com/jeroenrnl/zoph/issues/60) The link text for "next" and "previous" as well as page numbers has been increased in size for better usability esp. on mobile devices
* Added a script for fixing filename case (by Jason Taylor [@JiCit] )
* Access Google maps via https (Jason Taylor [@JiCiT])
* As of this version, the language files are in the php dir, and no longer need to be copied or moved separately

###Bugs###
* [Issue #49](https://github.com/jeroenrnl/zoph/issues/49) Zoph now supports MySQL strict mode
* [Issue #55](https://github.com/jeroenrnl/zoph/issues/55) Autocomplete not working for people
* [Issue #58](https://github.com/jeroenrnl/zoph/issues/58) Sort order for albums and categories can not be changed
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
* [Issue #8](https://github.com/jeroenrnl/zoph/issues/8) (partial) Changed several parts of Zoph to use templates 
* Added improvements to templating system
* Modified query for photo access rights to a view for performance reasons
* Changed logging so SQL query log to file can be done without displaying 
* Performance improvement on place page
* Added a posibility to debug queries including parameters

## Zoph 0.9.1 ##
### 21 Feb 2014 ###
Zoph 0.9.1 is the first feature release for Zoph 0.9, it shows a preview of some of the new features for Zoph 0.10. Most important change is the move of most configuration items from config.inc.php into the Web GUI.

####Features####

* [Issue #28](https://github.com/jeroenrnl/zoph/issues/28) Configuration through webinterface 
* Removed display desc under thumbnail feature 
* Removed MIXED_THUMBNAILS and THUMB_EXTENSION settings 
* removed DEFAULT_SHOW_ALL setting 
* Removed LANG_DIR configuration item 
* Changed the looks of <input> fields a bit 
* Removed alternative password validators 
* Removed checks for PHP 5.1 
* Adding CLI support for configuration 
* [Issue #7](https://github.com/jeroenrnl/zoph/issues/7) Added a favicon 
* [Issue #18](https://github.com/jeroenrnl/zoph/issues/18) Added "return" link on bulk edit page 
* Added a script to migrate config to new db-based system 
* [Issue #8](https://github.com/jeroenrnl/zoph/issues/8) Made template selectible from webinterface 
* Removed MAX_CRUMBS 

####Bugs####

* Simplified CLI code & fixed bug in --autoadd
* [Issue #34](https://github.com/jeroenrnl/zoph/issues/34) Rows and columns swapped on photos page
* [Issue #36](https://github.com/jeroenrnl/zoph/issues/36) Webimporter does not import description
* [Issue #37](https://github.com/jeroenrnl/zoph/issues/37) Can not add position on map using the mouse
* Fixed a bug that caused EXIF information in some (rare) cases to report the aperture wrong.
* Strict standards warning 
* [Issue #45](https://github.com/jeroenrnl/zoph/issues/45) Pagebreak inside HTML tags causes browser to render incorrectly
* [Issue #45](https://github.com/jeroenrnl/zoph/issues/45) Added selectArray cache to zophTable
* [Issue #48](https://github.com/jeroenrnl/zoph/issues/48) Repair photo ratings during import
* [Issue #50](https://github.com/jeroenrnl/zoph/issues/50) Geonames project has changed URL and requires username
* [Issue #51](https://github.com/jeroenrnl/zoph/issues/51) Fixed depth in tree display when autocorrect is off
* [Issue #39](https://github.com/jeroenrnl/zoph/issues/39) Added support for session.upload_progress as APC replacement (PHP 5.4 compatibility)
* [Issue #38](https://github.com/jeroenrnl/zoph/issues/38) CLI tries to lookup previous argument's value when looking up photographer

####Improvements####

I have made quite a few improvements on the "inside" of Zoph. I have refactored many parts of Zoph
to create cleaner, less duplicated and more robust code. I have introduced UnitTests (resulting in 
about 20% of Zoph's sourcecode now tested fully automatic for bugs). As a help to that, I am now 
using Sonar to automatically run these tests and also analyse Zoph code for other problems.

* [Issue #29](https://github.com/jeroenrnl/zoph/issues/29) First step in creating unittests for Zoph 
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
* [Issue #40](https://github.com/jeroenrnl/zoph/issues/40) Change documentation to Markdown        
* Modified some queries to improve performance 

## Zoph 0.9.0.1 ##
### 18 oct 2012 ###

Zoph 0.9.0.1 is the first maintenance release for Zoph 0.9. It adds compatibility with MySQL 5.4.4 and later and PHP 5.4 support. Several bugs were fixed.


#### Bugs ####

* [Issue #1](https://github.com/jeroenrnl/zoph/issues/1)  Changed TYPE=MyISAM to ENGINE=MyISAM for MySQL > 5.4.4 compatibility
* [Issue #1](https://github.com/jeroenrnl/zoph/issues/1)  Fixed: PHP Notice: Array to string conversion
* [Issue #2](https://github.com/jeroenrnl/zoph/issues/2)  Changed timestamp(14) into timestamp
* [Issue #3](https://github.com/jeroenrnl/zoph/issues/3)  Removed pass-by-reference for PHP 5.4 compatibility
* [Issue #6](https://github.com/jeroenrnl/zoph/issues/6)  Missing French translation
* [Issue #30](https://github.com/jeroenrnl/zoph/issues/30) Remove warning about undefined variables
* [Issue #31](https://github.com/jeroenrnl/zoph/issues/31) Fixed several errors in geotagging code
* [Issue #33](https://github.com/jeroenrnl/zoph/issues/33) Fixed: no error message when rotate fails
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
