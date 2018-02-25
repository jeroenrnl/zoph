# Zoph Changelog #
## Zoph 0.9.7 ##
### 19 jan 2018 ###
I have had a very busy year and little time to spend on Zoph, but last december, I finally found time to finish what I had originally planned for 0.9.6: a complete rewrite of the search screen and the search engine. Most of the code in that part of Zoph was over 10 years old and had become quite messy over the years. The search engine is really the core of Zoph: if you open an album in Zoph, under the hood, Zoph really executes a search for all the photos in that album. This makes this code really important and I've made sure to cover all this by automated tests (UnitTests) before making any changes.


* [issue#83](https://github.com/jeroenrnl/zoph/issues/83) Complete rewrite of the search page and the core functions of Zoph, including modernization of several other part of Zoph.
* [issue#90](https://github.com/jeroenrnl/zoph/issues/90) Error displayed when adding a new place
* [issue#99](https://github.com/jeroenrnl/zoph/issues/99) Geolocation doesn't work when using https
* Documentation updates - not all files were correctly displayed using Github's Markdown interpreter

## Zoph 0.9.6 ##
### 14 apr 2017 ###
Zoph 0.9.5 coincided with a significant change in MySQL, that caused a lot of bugs in Zoph and other open source projects. MySQL changed the way they process queries to handle them much more strictly. What makes things worse, is that MariaDB did not make this change, so at first I could not reproduce the issue. Because of the amount of work, I have decided to postpone the development that was planned for 0.9.6 and make this a bugfix-only release. In this release, I have included a few bugfixes by Pontus Fröding which is really great, thanks Pontus!


### Bugs ###
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
* Fixed an issue in a UnitTest that caused a failed test
* [Pull Request#94](https://github.com/jeroenrnl/zoph/pull/94) Add namespace to template showJSwarning in edit_person (by Pontus Fröding)
* [Pull Request#95](https://github.com/jeroenrnl/zoph/pull/95) Add template namespace on two more places. (by Pontus Fröding)
* [issue#92](https://github.com/jeroenrnl/zoph/issues/92) Fixed database connection to utf-8
* [issue#93](https://github.com/jeroenrnl/zoph/issues/93) [Pull Request#95](https://github.com/jeroenrnl/zoph/pull/95) Fix for "Class pager not found" when using pagesets (by Pontus Fröding)

### Refactor ###
* Some modifications to backtrace printing, for easier debugging
* Moved album view into template
* [issue#89](https://github.com/jeroenrnl/zoph/issues/89) Changed look of next and previous buttons on photo page and increased size of actionlinks
* Small style change

## Zoph 0.9.5 ##
### 4 feb 2017 ###

Zoph 0.9.5 is the new stable release. It is recommended for everyone to upgrade to this release

### Features ###
* [Issue#68](https://github.com/jeroenrnl/zoph/issues/68) Changed from Mapstraction to Leaflet as mapping abstraction - with GoogleMaps, OpenStreetMap and MapBox (OpenStreetMap) support
 The code for this was based on code provided by Jason (@JiCiT)
* [Issue#80](https://github.com/jeroenrnl/zoph/issues/80) You can now edit permissions from the album screen, without the need to go to the group edit.
* [Issue#82](https://github.com/jeroenrnl/zoph/issues/82) Zoph now gives a proper error message if a photo can not be found

### Bugs ###
* Fixed a bug where in some cases it was possible for an admin to unintentionally delete albums

### Refactor ###
* Lots of internal changes to move to an MVC-architecture
* Several more parts of Zoph moved into templates
* Added more unittests - to automatically test Zoph


## Zoph 0.9.4 ##
### 18 Sept 2016 ###

Zoph 0.9.4 is the new stable release. It is recommended for everyone to upgrade to this release
### Features ###
* Geocoding: Zoph now also searches Wikipedia
* [Issue#67](https://github.com/jeroenrnl/zoph/issues/67) Changed the colour scheme definition to use a nice interface to select the colour
* [Issue#23](https://github.com/jeroenrnl/zoph/issues/23) An admin user can now define default prefences for new users
* [Issue#24](https://github.com/jeroenrnl/zoph/issues/24) Added an option to automatically propagate permissions to newly created albums
* [Issue#78](https://github.com/jeroenrnl/zoph/issues/78) Removed Yahoo, Cloudmade mapping as they no longer offer their services to the public
* [Issue#78](https://github.com/jeroenrnl/zoph/issues/78) Removed Openlayers mapping, as Zophs implementation was buggy and did not work anymore.
* [Issue#47](https://github.com/jeroenrnl/zoph/issues/47) Photos can now be deleted from disk (moved to a trash dir)
* [Issue#67](https://github.com/jeroenrnl/zoph/issues/67) Added some new colour schemes

### Bugs ###
* Fixed an issue with album pulldown when editing group access rights
* Fixed an issue where the circles page would sometimes report $title not found
* Fixed an issue with changing views on circle page
* Fixed an issue that caused errors in Firefox when using the configuration page
* fixed collapsable details for time and rating
* [Issue#78](https://github.com/jeroenrnl/zoph/issues/78) Fixed a case where an admin user was sometimes not allowed to see a person or a place

### Other improvements ###
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

## Zoph 0.9.3 ##
### 10 jun 2016 ###

Zoph 0.9.3 is the new stable release. It is recommended for everyone to upgrade to this release

### Features ###
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

### Bugs ###
* [Issue #73](https://github.com/jeroenrnl/zoph/issues/73) Fixed sharing feature
* [Issue #74](https://github.com/jeroenrnl/zoph/issues/74) Fixed Canadian English, Dutch and German translation files

### Other improvements ###
* Added a way to disable a setting on the configuration page depending on the state of another configuration item. (This was created because the photo album as a logon background relies on the sharing feature to be enabled).
* Moved user page to template
* Moved form into a separate class
* Some cleanup of the places and categories pages
* Refactor HTML for actionlinks
* Modified createTestData script to only require password once
* Rearranged order of unittests
* Added translations for German, Canadian English and Dutch

## Zoph 0.9.2 ##
### 1 apr 2016 ###

Zoph 0.9.2 is the new stable release. I have decided to drop the separation between 'stable' and 'unstable' or 'feature' releases. This means that it is recommended for everyone to upgrade to this release.

### Features ###
* [Issue #44](https://github.com/jeroenrnl/zoph/issues/44) : Added 'circles': a way to group people in Zoph. This is especially handy if you have a large amount of people in your Zoph, and the 'person' page is becoming confusing or cluttered.
* [Issue #46](https://github.com/jeroenrnl/zoph/issues/46) A circle and it's members can be surpressed in the overview page, so you can, for example, hide people that you added only for a small set of photos.
* [Issue #20](https://github.com/jeroenrnl/zoph/issues/20) Zoph has switched to the PDO classes for database access. This ensures compatibility with PHP in the future, because the old mysql libs will be dropped soon.
* [Issue #32](https://github.com/jeroenrnl/zoph/issues/32) It is now possible to set more properties of a photo, including map zoom from the web import.
* [Issue #60](https://github.com/jeroenrnl/zoph/issues/60) The link text for "next" and "previous" as well as page numbers has been increased in size for better usability esp. on mobile devices
* Added a script for fixing filename case (by Jason Taylor [@JiCit] )
* Access Google maps via https (Jason Taylor [@JiCiT])
* As of this version, the language files are in the php dir, and no longer need to be copied or moved separately

### Bugs ###
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

### Refactor ###
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

#### Features ####

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

#### Bugs ####

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

#### Improvements ####

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

## Zoph 0.9 ##
23 jun 2012

Zoph 0.9 is a stable release. It's equal to v0.9pre2, except for an updated Italian translation.

### Translations ###
* Updated Italian translation, by Francesco Ciattaglia

There are no known bugs in this version.

## Zoph 0.9pre2 ##
20 feb 2012

Zoph 0.9pre2 is the second release candidate for Zoph 0.9. Zoph is now completely feature-frozen for the 0.9 release, only bugfixes will be made.

### Bugs ###
* Bug#3471099: Map not displaying when looking at photo in edit mode
* Bug#3471100: On some pages, title contains PHP warning

## Zoph 0.9pre1 ##
26 nov 2011

Zoph 0.9pre1 is the first release candidate for Zoph 0.9. Zoph is now completely feature-frozen for the 0.9 release, only bugfixes will be made.

### Bugs ###
* Bug#3420574: When using --autoadd, zoph CLI import sometimes tries to create new locations or photographers even though they already exist in the database.
* Bug#3427517: Share this photo feature does not work
* Bug#3427518: Not possible to remove and album or category from a photo
* Bug#3433687: Not possible to remove album or category from photo (bulk)
* Bug#3431130: Share this photo doesn't show links in photo edit mode
* Bug#3433810: Popup for albums, categories, people and places doesn't always disappear when moving mouse away.
* Removed a warning that in some cases caused images not to be displayed.

### Translations ###
* Added a few missing strings, reported by Pekka Kutinlahti.
* Updated Italian translation, by Francesco Ciattaglia
* Updated Dutch, German, Canadian English and Finnish

### Other ###
* Got rid of a lot of PHP warnings
* Got rid of a lot of PHP strict messages
* Cut down on the number of global variables
* Removed support for magic_quotes
* Removed (last traces of) PHP4 support
* Bug#3435181: Variable inside quotes
* Updated wikibooks documentation

## Zoph 0.8.4 ##
9 Sept 2011

Zoph 0.8.4 is the final pre-release for Zoph 0.9.

This version adds several feature improvements. More features have been added the new CLI import, which was introduced in v0.8.2. The 'bulk edit' page has been improved, both in features as in loading speed (100x faster in some cases!). The 'tree view' and 'thumb view' overview pages have been improved. Several coding style modernisation changes have been made.

### Features ###
* Req#1985439: Adding albums, categories, places and people via the CLI
* Req#1985439: Automatically adding albums, categories, places and people via the CLI
* Req#3042674: Recursive import of directories
* Req#1985439: Setting album, category, person, photographer, path from import dir.
* Req#1756507: photocount in tree view.
* Req#1491208: Show more info in thumbnail overview
* REQ#2813979: Added date & time fields to bulk edit page
* Added autocomplete support to bulk edit page
* Changed the photo edit page to automatically add new dropdowns to albums, categories and people.
* Removed 'people_slots' functionality
* Changed add people on bulk photo edit page to use multiple dropdowns
* Add multiple albums, categories, persons on both single and bulk  photo edit. 
* Req#2871210: Added 'share photo' feature.
* Zoph now stores a hash of a photo in the database
* zoph CLI: Added -D as shorthand for --path
### Bugs ###
* Bug#3312029: MAGIC_FILE cannot be empty
* Fixed an issue that caused the 'search' button for geocoding on the edit location page to be misplaced.
* Fixed a typo that caused the 'track' screen to no longer work
### Translations ###
* Updated translations
* Added some previously forgotten translations
### Refactoring ###
Zoph has started it's life in the era of PHP3, while the current version of PHP is version 5.3. In between a lot has been changed in PHP. I have started to adopt PHP5-style programming some time ago for new development. I have now also started to refactor the other code to a new coding style. Currently, Zoph still has ''a lot'' of global functions and I am slowly moving almost all of them to static methods.
* Made several changes to function names to accommodate new coding style
* Refactored photo->update_relations() to merge with the similar photo->updateRelations() that the new import introduced.
* Moved get_root_...() functions into static functions.
* Refactor of zoph_table object (now called zophTable)
* Renamed function photo->get_image_href() to photo->getURL()
* Made some changes to the delete() methods so PHP strict standards are followed.

### Other ###
* Inline documentation improvements
* Improved expand/collapse Javascript robustness 
* Some eyecandy (esp expand/collapse)
* Changed the date and time field to type 'date' and type 'time', which are new types for HTML5. Tested in Chromium.
* Removed deprecated IMAGE_SERVICE setting. IMAGE_SERVICE is now always on.
* Renamed image_service.php to image.php 
* Improved loading speed of the 'tracks' page by using a different, better cachable SQL query

## Zoph 0.8.3 ##
April 3, 2011

Zoph 0.8.3 is a pre-release for Zoph 0.9.

This version adds several feature improvements, mostly related to mapping. The most important addition is the support for geotagging. This version also fixes several bugs.

Zoph 0.8.3 is beta release, I tested it as well as possible on my system, but it should not be considered a "stable" version. I would, however, very much appreciate if people could test and give feedback on this release and the updated documentation, in this way I can make sure that the stable (v0.9) version will be as bug-free as possible.
### Features ###
* Geotagging support
* Req#2974014: Search for location
* Geocoding: finding lat/lon location from city, county.
* Req#2974016: Additional mapping resources
* Req#3077944: When adding a new place, or editting a place with no location (lat/lon) set, zoph will zoom the map to the parent location.  If a photo is editted, and the photo has no lat/lon, but it's location does, the map is zoomed to the location's lat/lon.

### Bugs ###
* Getting rid of a NOTICE regarding unset `DB_PREFIX` constant
* Several small changes to decrease the number of NOTICE messages.
* In photo edit mode, moved maps to bottom of page, to fix a bug with Openlayers maps
* Better error handling when `UPLOAD_DIR` does not exist.
* Zoph.ini: Added quotes around values, PHP fails if they contain special characters. As suggested by scantron.
* Bug#3237112: Rating counts are incorrect with new import
* Bug#3237012: There is no "next" link on the bulk edit page, although a "previous" link is present.

### Other ###
* Switched from Mapstraction 1.x to Mapstraction 2.0.15
* Namespacing in mapping Javascript.
* Some changes in templating system
* Bug#3104632: Various changes for PHP 5.3 compatibility
* Refactor of zophcode, tag, smiley and replace objects to new coding style, including added PHPdoc comments.
* Added a copyright note to Openlayers maps
* Refactor of the admin class & move admin page to a template.
* Getting rid of some warning messages

### Translations ###
* Dutch and Canadian English have been updated and are completely up to date

## Zoph 0.8.2.1 ##
November 20, 2010

Zoph 0.8.2.1 is a bugfix release for Zoph 0.8.2.

Many changes were made in Zoph 0.8.2 and with so many changed lines of code, a few bugs is almost inevitable. This release fixes all known bugs in v0.8.2.

### Bugs ###
* Bug#3064940: HTML in dropdown menus. (This bug was previously fixed in Zoph 0.8.0.5, but the fix was not correctly ported to the development branch)
* Bug#3094182: New CLI does not store location and photographer
* Bug#3094198: New CLI does not always look up location name correctly.
* Bug#3094201: New CLI does not exit when it encounters an error (album, category, ... not found)
* Bug#3102078: Webimport of archives fails with no error
* Bug#3102080: New CLI `--update` can not set location and photographer
* Bug#3102148: New CLI `--field` gives an error
* Fix for an issue that caused javascript errors when an apostroph would appear in a title of a place.
* Bug#3108196: Translation not working in Zoph 0.8.2

## Zoph 0.8.2 ##
October 20, 2010

Zoph 0.8.2 is the second pre-release for Zoph 0.9.

Zoph 0.8.2 features a completely rewritten import system. The webinterface has been modernized. Error handling and user-friendliness have been improved. The CLI interface prior to v0.8.2 was written in Perl, because the rest of Zoph was written in PHP, a lot of duplicate work needed to be done whenever something needed to be changed in the import system. As of this version, the CLI interface has been rewritten in PHP as well.

Zoph 0.8.2 is beta release, I tested it as well as possible on my system, but it should not be considered a "stable" version. I would, however, very much appreciate if people could test and give feedback on this release and the updated documentation, in this way I can make sure that the stable (v0.9) version will be as bug-free as possible.

### Features ###
* New webimport
* New CLI-import

### Bugs ###
* Bugfixes from v0.8.0.5 have been included in this release.

### Other changes ###
* Configuration of database connection has been moved from `config.inc.php` (webinterface) and `.zophrc` (CLI interface) to `/etc/zoph.ini`, for both the webinterface and the CLI interface.
* `bin` and `man` directories in release tarball have been combined into the `cli` directory
* HTML documentation (`docs` directory) is no longer included in the release. Maintaining this documentation cost a lot of time. The scripts I wrote to convert the Wikibooks documentation into offline documentation could not handle images and the documentation I wrote for the new webimport contains a lot of pictures. 

## Zoph 0.8.0.5 ##
October 20, 2010

Zoph 0.8.0.5 is a bugfix release that fixes a few bugs in Zoph 0.8.0.4

### Bugs ###
* Bug#3049203: Rating links on search page do not work.
* Bug#3054562: HTML in rating dropdown on search page
* Bug#3054566: Search for albums/categories/places/people/photographers is broken after 0.8.0.2 update.
* Bug#3066174: Rotation not working in auto edit mode
* Bug#3064937: SQL error when inserting a place with no timezone.
* Bug#3064940: HTML in dropdown menu's.
* Bug#3072586: Latitude is misspelled as "lattitude"

## Zoph 0.8.1.2 ##
July 15, 2010

Zoph 0.8.1.2 is a bugfix release that fixes a few bugs in Zoph 0.8.1.1.

### Bugs ###
* A few cases of duplicate encoding, causing HTML code to appear instead of being interpreted by the browser
* A bug that caused markers not to work correctly
* A bug that caused Zoph to loose timezone information when using the 'assign timezone to children' functionality. 

## Zoph 0.8.0.4 ##
July 15, 2010

Zoph 0.8.0.4 is a bugfix release that fixes a few bugs in Zoph 0.8.0.3.

### Bugs ###
* A few cases of duplicate encoding, causing HTML code to appear instead of being interpreted by the browser

## Zoph 0.8.1.1 ##
July 1, 2010

Zoph 0.8.1.1 is a security release that fixes a number of Cross Site Scripting (XSS) issues of which most were found by [VUPEN Security](http://www.vupen.com). I would like to thank VUPEN for reporting these bugs.

Zoph 0.8.1.1 does not fix any other bugs.

### Bugs ###
* Several XSS scripting issues found by VUPEN Security
* Several XSS scripting issues found during fixing of the above bugs

## Zoph 0.8.0.3 ##
July 1, 2010

Zoph 0.8.0.3 is a security release that fixes a number of Cross Site Scripting (XSS) issues of which most were found by [VUPEN Security](http://www.vupen.com). I would like to thank VUPEN for reporting these bugs.

This release also fixes all the bugs found since the 0.8.0.2 release.

### Bugs ###
* Several XSS scripting issues found by VUPEN Security
* Several XSS scripting issues found during fixing of the above bugs
* Bug#2901852: Fatal error when a photo without a photographer is displayed on the map
* Bug#2902011: zophImport.pl cannot find people with no last name.
* Bug#2925030: Last modified time is not displayed correctly
* Bug#2925498: NULL entries in the database change to 0.000 after rotating an image causing fake map entries to appear. Fix by Jason Taylor.
* Bug#2925508: Thumbnail covers actionlinks on people page. Fix by Jason Taylor.
* Bug#2925506: Count of places is wrong. Fix by Jason Taylor.
* Bug#2982051: editting photo does not work when using "auto edit".
* Bug#3002691: Next/prev links lost after update.


## Zoph 0.8.1 ##
3 Jan 2010

Zoph 0.8.1 is the first feature release for v0.9. This release introduces a new logging system, that should allow users and developers to control more granular which debugging messages Zoph displays. The other major change is that Zoph is now completely UTF-8 based, this should fix issues users had with international characters. This last change requires some manual changes to the MySQL database.

Zoph 0.8.1 is beta release, I tested it as well as possible on my system, but especially the UTF-8 conversion is very dependent on specific situations on your system; therefore it should not be considered a "stable" version. I would, however, very much appreciate if people could test and give feedback on this release and the upgrade documentation, in this way I can make sure that the stable (v0.9) version will be as bug-free as possible.

### Features ###
* New logging/debugging system

### Bugs ###
* Bug#1985449: Zoph should be UTF-8
* Bug#2901852: Fatal error when a photo without a photographer is displayed on the map
* Bug#2902011: zophImport.pl cannot find people with no last name.
* Bug#2925030: Last modified time is not displayed correctly
* All the bugfixes from Zoph 0.8.0.1 and 0.8.0.2

## Zoph 0.8.0.2 ##
1 Nov 2009

Zoph 0.8.0.2 is a bugfix release for Zoph 0.8.

### Bugs ###
* Bug#2876282: Not possible to create new pages.
* Bug#2873171: fatal error when autocomplete is switched off.
* Bug#2873171: Javascript error in MSIE when trying to change the parent place using the autocomplete dropdown.
* Bug#2873171: Timezone autocomplete does not work in MSIE
* Bug#2881212: Not possible to unset timezone.
* Bug#2889934: No icons in admin menu when using MSIE8
* Bug#2888263: Unintuative working of bulk edit page could lead to dataloss
* Bug#2890387: Saved search does not remember the "include sub-albums/categories/places" checkbox and the state of the "AND/OR" dropdown.

### Translations ###
* Added a Russion translation created by Sergey Chursin and Alexandr Bondarev

### Various ###
* Changed deprecated mysql_escape_string() into new mysql_real_escape_string().

## Zoph 0.7.0.8 and Zoph 0.8.0.1 ##
23 Sept 2009

Security fixes for 0.7 and 0.8.

### Bugs ###
* Fixes a security bug that caused a user to be able to execute admin-only pages.

## Zoph 0.8 ##
9 Sept 2009

Final 0.8 release. Only small changes compared to 0.8pre3:

### Bugs ###
* Fixed a bug that caused users of PHP 5.1.x get an error about non-existant DateTime class.

### Documentation ###
* Added a few long-existing but overlooked and therefore not documented configuration settings
* Added a troubleshooting section ("Solving Problems")

## Zoph 0.8pre3 ##
28 August 2009

This is the third pre-release for 0.8, it fixes the bugs discovered since v0.8pre2, including the security bug. It also updates several translations.

### Bugs ###
* Bug#2841196: PHP error when logging in as non-admin user
* zophImport.pl: Perl error due to missing quote and indentation fixes
* Bug#2841296: Not possible to download 4.2GB ZIP files
* Bug#2841357: Save search fails without an error in some cases
* Bug#2841373: Saved search does not always work correctly when saving a photo collection that was not the result of a search action.
* Fix for a cross site scripting bug (the same as the 0.7.0.7 release)
* Bug#2845750: zophImport.pl fails when `--path` contains multiple dirs

### Translations ###
* Dutch, Danish, French, Italian, Norwegian Bokmål and Swedish chef have been updated and are fully up to date.

### Documentation ###
* Various updates
* Removing very old changelog and upgrade instructions. They can still be read in the online (wikibooks) version.
* Adding long existing but until now not documented options `DEFAULT_ORDER` and `DEFAULT_DIRECTION`
* Completely rewritten requirements page

