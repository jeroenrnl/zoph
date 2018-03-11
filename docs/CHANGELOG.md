# Zoph Changelog #
## Zoph 0.9.9 ##
### t.b.d. ###

#### Refactor ####

* Changing `and` and `or` into `&&` and `||` for readability and consistency
* Changed capitalization of exception classes for consistency
* Several fixes in namespace, capitalization and documentation

#### bugs ####
* [issue#111](https://github.com/jeroenrnl/zoph/issues/111): Not possible to edit album or category

## Zoph 0.9.8 ##
### 02 march 2018 ###

I have moved all Zoph's documentation to Github. About 12 years ago, Zoph's documentation was hand-crafted HTML that was an ordeal to keep updated. I moved it to Wikibooks so I could update the docs through their webinterface. Nowadays, Github offers 'markdown' which is a text format that is both readable in plain text and can be rendered to a more pleasing look on the website. I was already keeping some documentation in this format and it caused a lot of extra work, because I was essentially maintaining two sets of documentation. So, as of this release, all documentation is back in one place: [Zoph's repository on Github](http://github.com/jeroenrnl/zoph/)

Furthermore, quite a few bugfixes have been been made. Most of them related to the rewrite of the search page in the previous version.

#### Bugs ####
* [issue#102](https://github.com/jeroenrnl/zoph/issues/102): Error instead of thumbnail for empty circles
* [issue#103](https://github.com/jeroenrnl/zoph/issues/103): each() is deprecated as of PHP 7.2
* [issue#104](https://github.com/jeroenrnl/zoph/issues/104): Search results for text-based 'LIKE' searches are reversed
* [issue#105](https://github.com/jeroenrnl/zoph/issues/105), [issue#106](https://github.com/jeroenrnl/zoph/issues/106), [issue#108](https://github.com/jeroenrnl/zoph/issues/108): Several issues with the search results page
* [issue#107](https://github.com/jeroenrnl/zoph/issues/107): ">", ">=", "<" and "<=" are pointless for text searches
* [issue#109](https://github.com/jeroenrnl/zoph/issues/109): Autocomplete dropdown sometimes hidden behind map
* [issue#110](https://github.com/jeroenrnl/zoph/issues/110): 'show all EXIF' button doesn't work

#### Refactor ####
* Removed unused functions in util.inc.php
* Removing ancient scripts that are either redundant or no longer working
* [issue#100](https://github.com/jeroenrnl/zoph/issues/100): Deprecate ssl.force, url.http and url.https
As of **Zoph 0.9.9** these settings will be removed. Zoph warns you if you are using them as of **Zoph 0.9.8**. If you feel your Zoph installation can not do without these settings, please comment in this issue.
* [issue#110](https://github.com/jeroenrnl/zoph/issues/110): Changed deprecated `read_exif_data()` to `exif_read_data()`

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
### 23 jun 2012 ###

Zoph 0.9 is a stable release. It's equal to v0.9pre2, except for an updated Italian translation.

### Translations ###
* Updated Italian translation, by Francesco Ciattaglia

There are no known bugs in this version.

## Zoph 0.9pre2 ##
### 20 feb 2012 ###

Zoph 0.9pre2 is the second release candidate for Zoph 0.9. Zoph is now completely feature-frozen for the 0.9 release, only bugfixes will be made.

### Bugs ###
* Bug#3471099: Map not displaying when looking at photo in edit mode
* Bug#3471100: On some pages, title contains PHP warning

## Zoph 0.9pre1 ##
### 26 nov 2011 ###

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
### 9 Sept 2011 ###

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
* zoph CLI: Added -D as shorthand for `--path`

### Bugs ###
* Bug#3312029: `MAGIC_FILE` cannot be empty
* Fixed an issue that caused the 'search' button for geocoding on the edit location page to be misplaced.
* Fixed a typo that caused the 'track' screen to no longer work

### Translations ###
* Updated translations
* Added some previously forgotten translations

### Refactoring ###
Zoph has started it's life in the era of PHP3, while the current version of PHP is version 5.3. In between a lot has been changed in PHP. I have started to adopt PHP5-style programming some time ago for new development. I have now also started to refactor the other code to a new coding style. Currently, Zoph still has ''a lot'' of global functions and I am slowly moving almost all of them to static methods.
* Made several changes to function names to accommodate new coding style
* Refactored `photo->update_relations()` to merge with the similar `photo->updateRelations()` that the new import introduced.
* Moved `get_root_...()` functions into static functions.
* Refactor of `zoph_table` object (now called `zophTable`)
* Renamed function `photo->get_image_href()` to `photo->getURL()`
* Made some changes to the `delete()` methods so PHP strict standards are followed.

### Other ###
* Inline documentation improvements
* Improved expand/collapse Javascript robustness 
* Some eyecandy (esp expand/collapse)
* Changed the date and time field to type 'date' and type 'time', which are new types for HTML5. Tested in Chromium.
* Removed deprecated IMAGE_SERVICE setting. IMAGE_SERVICE is now always on.
* Renamed image_service.php to image.php 
* Improved loading speed of the 'tracks' page by using a different, better cachable SQL query

## Zoph 0.8.3 ##
### April 3, 2011 ###

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
### November 20, 2010 ###

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
### October 20, 2010 ###

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
### October 20, 2010 ###

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
### July 15, 2010 ###

Zoph 0.8.1.2 is a bugfix release that fixes a few bugs in Zoph 0.8.1.1.

### Bugs ###
* A few cases of duplicate encoding, causing HTML code to appear instead of being interpreted by the browser
* A bug that caused markers not to work correctly
* A bug that caused Zoph to loose timezone information when using the 'assign timezone to children' functionality. 

## Zoph 0.8.0.4 ##
### July 15, 2010 ###

Zoph 0.8.0.4 is a bugfix release that fixes a few bugs in Zoph 0.8.0.3.

### Bugs ###
* A few cases of duplicate encoding, causing HTML code to appear instead of being interpreted by the browser

## Zoph 0.8.1.1 ##
### July 1, 2010 ###

Zoph 0.8.1.1 is a security release that fixes a number of Cross Site Scripting (XSS) issues of which most were found by [VUPEN Security](http://www.vupen.com). I would like to thank VUPEN for reporting these bugs.

Zoph 0.8.1.1 does not fix any other bugs.

### Bugs ###
* Several XSS scripting issues found by VUPEN Security
* Several XSS scripting issues found during fixing of the above bugs

## Zoph 0.8.0.3 ##
### July 1, 2010 ###

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
### 3 Jan 2010 ###

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
### 1 Nov 2009 ###

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
### 23 Sept 2009 ###

Security fixes for 0.7 and 0.8.

### Bugs ###
* Fixes a security bug that caused a user to be able to execute admin-only pages.

## Zoph 0.8 ##
### 9 Sept 2009 ###

Final 0.8 release. Only small changes compared to 0.8pre3:

### Bugs ###
* Fixed a bug that caused users of PHP 5.1.x get an error about non-existant DateTime class.

### Documentation ###
* Added a few long-existing but overlooked and therefore not documented configuration settings
* Added a troubleshooting section ("Solving Problems")

## Zoph 0.8pre3 ##
### 28 August 2009 ###

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

## Zoph 0.7.0.7 ##
### 24 Aug 2009 ###

Zoph 0.7.0.7 is an update of the stable 0.7 branch and fixes a cross site scripting security bug.

### Bugs ###
* Fix for a cross site scripting bug that found during development of Zoph v0.8

## Zoph 0.8pre2 ##
### 8 July 2009 ###

This is the second pre-release for 0.8, it fixes the bugs discovered since v0.8pre1, including the security bug.
### Bugs ###
* Bug#2813464: Date link on photo page links to the wrong year
* Bug#2813467: '+' links to expand date/time, ratings and tree view do not work anymore after a Googlemaps update
* Fix for a cross site scripting bug that was reported by "y3nh4ck3r".
* Fix for a bug that caused manually entered dates with webimport not to be used

## Zoph 0.7.0.6 ##
### 2 July 2009 ###

Zoph 0.7.0.6 is an update of the stable 0.7 branch and fixes a cross site scripting security bug.

### Bugs ###
* Fix for a cross site scripting bug that was reported by "y3nh4ck3r".

## Zoph 0.8pre1 ##
### 27 June 2009 ###

Zoph 0.8pre1 is a prerelease (release candidate) for Zoph 0.8. It fixes a number of bugs from 0.7.5. 

### Bugs ###
* Fix for a bug that would give an error (or not execute without an error, depending on the situation) when an album is added to a photo. Bug found and fixed by Pekka Kutinlati.
* Bug#2687577: Download link does not work in some cases
* Bug#2720782: edit does not work after using back and forward buttons
* Bug#2720807: Layout glitch on slideshow
* Fixed two small issues in saved searches
* Bug#2718812: Cannot assign someone as a father/mother/spouse when person does not yet appear on a photo.
* Bug#2724768: Error in timezone code
* Bug#2750454: Fatal error: Call to undefined function `get_photographer_search_array()` in person.inc.php
* Bug#2775190: Dropdown menu with people is not sorted by name.
* Bug#2718814: Not possible to unset a relationship between persons.
* Fixed a bug where the average rating would become 0 when the last rating for that photo was removed
* Bug#2794052: Syntax error in timezone.inc.php when using PHP4
* Bug#2803133: Making a category/album or place it's own parent causes out of memory error.
* Bug#2804335: Division by zero error when importing JPG with zeroes in some of the EXIF fields.
* Fix for a bug where the map on the photo page did not show the location of the photo if it is set on the location and not on the photo itself.
* Fix for a bug where the map on the photo page did not show if the user is not using the 'auto-edit' feature.
* Fix for a bug that caused a javascript error when title or address of a place contained quotes.


### Translations ###
* German, Canadian English, Danish, Dutch, Italian and Swedish Chef have been updated.
* Added Finnish translation by Pekka Kutinlati.
* Removed some empty translations from outdated translations


### Other ###
* Removed `zoph-0.3.3.postgress.diff` from the contrib dir. It was too outdated to serve any purpose.

## Zoph 0.7.5 ##
### 14 March 2009 ###

Zoph 0.7.5 is the last "feature release" before v0.8. This version introduces a few new features that will be present in the next "stable" version, 0.8. This release fixes a number of bugs from the earlier 0.7.x releases.

### Bugs ###
* Bug#2465009, wrong counters for rating
* Fixed a bug where a translated version of Zoph would not make a breadcrumb for search results.
* Fixed: Timezone calculations are using local timezone instead of configured `CAMERA_TZ`.
* Bug#2671365 Can not leave comments
* Fixed a bug in `zophImport.pl` where `--update` could in some cases move a photo to a wrong location.

### Features ###
* Added a feature where an admin user can check out the ratings a certain user has given, adds a graph similar to the one on the reports page to the user's page.
* Admins can now see who has rated what per photo
* Admins can delete ratings
* IP address and date/time are now stored when rating
* An admin can control wheter a user can rate photos or not.
* Req#2126915: Allow a user to rate the same photo multiple times, but only once per IP addres, you can use this for the `DEFAULT_USER` or a user account that is shared among multiple people. 
* Improved error handling on erronous time or date. (timezone calculations)
* Move all MySQL calls into `database.inc.php`, making adjusting to other db's easier, partly resolves Req#2464455
* Req#1480136: Save search results
* A list of all comments by a user is now shown in user profile.

### Known issues ###
* The translations have not yet been updated
* Not all documentation is up to date

## Zoph 0.7.4 ##
### 22 December 2008 ###

Zoph 0.7.4 is a "feature release", that introduces a few new features that will be present in the next "stable" version, 0.8. This release fixes a number of bugs from the earlier 0.7.x releases and specifically deals with some performance issues.

### Bugs ###
* Bug#2044965: Assign timezone to all children only assigns timezone to direct children.
* Bug#2044967: Better error handling for guess timezone functionality
* Bug#1820234: Zoph shows places, categories and people for restricted users.
* Bug#2059210: Overal bad performance: long loading times, autocomplete boxes taking forever, etc. This fix gives a giant improvement on zoph performance.
  * Simplified several SQL queries
  * Changed SQL queries so unused rows are now longer requested from the db
  * Changed SQL queries so records are no longer sorted when it is not needed
  * Changed autocomplete code so it was no longer necessary to load both autocomplete and legacy dropdowns (major improvement on loading the seachpage!)
* Bug#2125858: table headers on user page swapped
* Bug#2097894: Layout failure in bulk edit page when using MSIE
* Bug#1706366: People slots feature is incompatible with autocomplete Also adds autocomplete support to several pages that did not have it before (only bulk edit page does not have autocomplete support yet)
* Bug#2274989: When changing user, password is overwritten.
* Bug#2275005: Photographers not in people list. People list not showing all people for admin users. 
* Bug#2373633: Counter on zoph.php wrong for non-admin users
* Bug#2373609: Tree view shows all albums/categories/places
* Bug#2315870: Layout glitch when using non-standard size thumbnails.
* Bug#2438062: Zoph does not pick a different coverphoto for people if the assigned one is not visible for the user.
### Features ###
* Req#2097906: Add "next" and "prev" links to edit photo page, when not using auto-edit feature
* Req#1467095: Group access rights

### Translation ###
* Fixed some errors in translations (mostly extra spaces)
* Updated Canadian English, Dutch and German translations

### Various ###
* Removed 'smart_pulldown' code that was not used in most of the cases anyway, especially since autocomplete was added.
* Created a script to automatically migrate from user-rights to group-rights. To be used with 0.7.3 to 0.7.4 or 0.7 to 0.8 migrations.

## Zoph 0.7.3 ##
### 24 July 2008 ###

Zoph 0.7.3 is a "feature release", that introduces a few new features that will be present in the next "stable" version, 0.8. It also fixes some bugs from 0.7.1 and 0.7.2 Finally, it includes the bugfixes from 0.7.0.5. This includes the security update.

### Bugs ###
* Bug#1985434: a-z index for people doesn't work anymore.
* Bug#2006151: one of the '+' buttons on the search page does not work
* Bug#1987338: ZIP downloading feature does not work with PHP4
* Bug#2006154: Case insensitive search for description field doesn't work
* Bug#1985432: two different meanings for 'home'
* Bug#1986847: wrong charset for French translation
* Bug#1983556: It is not possible to unset many attributes once they have been set. Fix by Charles Brunet.
* Bug#2015802: SQL error when inserting a new place
* Bug#2012300: Missing linefeed on places page.
* Bug#2015312: Wrong layout for 'work' field on person page.
* Bug#2015346: Home location does not display the title
* Bug#2015340: Deleting a person does not delete all references
* Bug#2015348: Deleting a place does not remove all references to it
* Bug#2021272: Crash when changing the parent of the root album
* Bug#2022777: [person] tag is missing from pages feature
* Bug#2021272: Crash when changing the parent of the root album

### Features ###
* Req#1505552: Mapping support. You can now use maps to show the location of your photos, using the mapstraction api. There is support for Google, Yahoo and Openstreetmap maps.
* Req#1586463: Time zone support. You can store information about the timezone where a photo was taken and have Zoph automatically compute the correct time for you.
* Req#2006156: Increase length of title field for albums and categories
* Req#2021275 Expand all button for tree view

### Translations ###
Translations for Dutch, French, German and Polish  have been updated

## Zoph 0.7.0.5 ##
### 20 July 2008 ###

Zoph 0.7.0.5 is a security fix that repairs several SQL injections. Although most are not exploitable or only exploitable by an admin user, I recommend upgrading to 0.7.0.5. This release also includes a number of extra 'safety nets' that will make exploiting any future SQL injections a lot harder.

It also fixes a number of bugs in the 0.7 release:
* Bug#1813293: import is not compatible with PHP < 5
* Bug#2006151: one of the '+' buttons on the search page does not work
* Bug#2012300: Missing linefeed on places page.
* Bug#2015312: Wrong layout for 'work' field on person page.
* Bug#2015346: Home location does not display the title
* Bug#2021272: Crash when changing the parent of the root album

## Zoph 0.7.2.1 ##
### 3 June 2008 ###

Zoph 0.7.2.1 is a bugfix release for Zoph 0.7.2 it fixes the following issues:
* Bug#1981910: Some files in the distribution for 0.7.2 are not the latest version
* Bug#1820229: Some thumbs not displayed when user has no right to see them.
* Bug#1813293: web import is not compatible with PHP < 5

## Zoph 0.7.2 ##
### 1 June 2008 ###

Zoph 0.7.2 is a "feature release", that introduces a few new features that will be present in the next "stable" version, 0.8. It also fixes some bugs from 0.7.1. Finally, it includes the bugfixes from 0.7.0.3 and 0.7.0.4.

### Bugs ###
* Bug#1819755: User that cannot see all albums does not always see all the albums he *is* allowed to see.
* Bug#1820225: Restricted user can see the list of people.
* Bug#1820229: User does not see all thumbs if he has not the right to see the manually assigned thumb.
* Sometimes not all albums were shown and sortorder was not always correct.

### Features ###
* Zophcode: Possibility to add markup and smileys to comments. Smileys were taken from PHPBB. (they are under GPL)
* Patch#1923522 and Patch#1923525 Default language now configurable and logon screen translated. Thanks to Francisco Javier Félix for providing these patches.
* Req#1928328: Use an alternating colour scheme to make it easier to keep the overview on the list of people. Thanks to Francisco Javier Félix for providing this.
* Added Licence and some extra security to selection.inc.php (although there was no security isssue with this file, in case there will be one discovered in the future, it will be harder to exploit).
* Moved the functionality from `pager.inc.php` to `util.inc.php`, so it is easier to re-use.
* Added an admin page where administrator can manage settings. Replaced 'users' in the main menu with 'admin'.
* Req#1506959: Zoph Pages feature that allows customization of the first page of an album/category/person/place.

### Translations ###
* Spanish was updated by Francisco Javier Félix
* Canadian English, German and Dutch were updated

## Zoph 0.7.0.4 ##
### 26 May 2008 ###

This is a bugfix release that fixes a few bugs in the 0.7 release.
* Bug#1923507: pleasewait.gif missing
* Bug#1926107 SQL error because of dashed line in zoph.sql
* Bug#1923955: photo x of y is not correctly translated 
* Bug#1928150: tree view shows a "+" even though the branch is already open
* Bug#1928671: Notify mail doesn't work
* Perl chokes when the .zophrc file ends with a negative assignment (" = 0"), adding "1;" to make sure it always ends "positively".
* Bug#1964408 Garbled layout on prefs page.
Very small new feature: the photo is now shown when asking for confirmation of deletion

## Zoph 0.7.0.3 ##
### 15 March 2008 ###

This is a bugfix release that fixes a few bugs in the 0.7 release.

* Bug#1856587: CSS fixes for MSIE rendering problems
* Bug#1859100: `zophImport.pl` moves files to wrong dir when path is specified in filename
* Bug#1840352: Ratings and Favourites do not always work correctly.

## Zoph 0.7.1 ##
### 21 Oct 2007 ###

Zoph 0.7.1 is a "feature release", that introduces a few new features that will be present in the next "stable" version, 0.8. It also includes the bugfixes from 0.7.0.1 and 0.7.0.2.

* It is now possible to define the position of the watermark. 
* Req#1713938: Zoph can now be configured to move an imported image instead of copying it. This saves you from having to clean up later. Default is to move the photo.
* Req#1504375 You can now download a set (album, category, search result, ..) of photos in a ZIP file. The size of the ZIP file and the number of photos are configurable.
* Req#1500560:  For albums and categories, you can now set the desired sort order through preferences. (newest/oldest photo, first/last change, lowest/highest/avg rating, name, sortname). Sortname is a new field that you can use to sort on.
* Req#1742672 Albums/Categories/Places now also have a thumbnail when the album itself does not have any photos, it picks a photo from one of it's subalbums/c/p
* Info table now displays total size of photos in the most appropriate unit (KiB, MiB, GiB) instead of always in MiB


## Zoph 0.7.0.2 ##
### 25 July 2007 ###
* Bug#1756660: Admin can not see details of places
* Admin can not see details of people
* Bug#1755325: Not possible to unset a coverphoto
* Bug#1598437 A user can now only put photos into an album he has write permission to.
* Bug#1760100: SQL script for new installations doesn't work.
* Italian translation is now up to date

## Zoph 0.7.0.1 ##
### 14 July 2007 ###
* Fix for a (non-exploitable) SQL injection error.

## Zoph 0.7 ##
### 1 July 2007 ###

### Bugfixes ###
* Bug#1745803: Layout problem on annotate photo page
* Bug#1745795: Autocompletion navigation with keyboard did not handle "enter" right
* Fixed a bug that caused auto thumbnail not to when user was not logged in as admin
* Fixed a bug where a non-admin user would get the same thumbnail for ALL categories, regardless of whether this photo would actually be in that category.
* Bug#1742676: Thumbnails show unexpected behaviour with insufficent rights.
* Bug#1742674: An autocomplete field now advances to next field when "enter" is pressed.

### Cleanup and various ###
* Made several (small) changes to Dutch, German, Canadian English, French, Norwegian and Swedish Chef.
* Updated Turkish and Danish


## Zoph 0.7pre2 ##
### 24 June 2007 ###

### Bugfixes ###
* Bug#1738931 View selection does not work for people
* Capitalization error in `places.php`, `albums.php`, `categories.php` that caused translations not to work
* Bug#1738592 Pressing enter in autocomplete field did not work
* Bug#1738307: In some cases `zophImport.pl` would try to connect to the database before the db connection was made.
* Fixed a layout-issue where in some cases the photo description would end up on an odd place on the page.

### Cleanup and various ###
* All languages have been updated. All duplicate and unused strings have been removed from the translation files. Dutch, German, Canadian English, French, Norwegian and even Swedish Chef (Bork! Bork! Bork!) are completely up to date now. Danish, Italian and Turkish are almost up to date.

## Zoph 0.7pre1 ##
### 02 June 2007 ###

### New Features ###
* Req#722617: read/display/handle more/full exif data
* Req#1260584: Javascript-based autocompletion for select-boxes.
* Req#1478748 Now possible to search albums/categories/photographers/people by text instead of selecting from list.
* Req#1491208: In albums/categories/places each link now shows the number of photos in that album and the number of photos in the album and the ones below it.  
* In albums and categories you now see the number of photos in the current album, as well as the number of photos in the current album and all albums below it (which was the only one shown up until now) - just like places has had since the previous version of Zoph
* Req#1506959 (partly): Specify a coverphoto for albums, categories, people and places
* Req#1511961: There are now 3 views for albums/categories/people/places: list (the "old" view), tree and thumbnail.
* Automatically pick a coverphoto in thumbnail view for a/c/p/p when none has been picked.
* Req#1709390: zophImport.pl: You can now set the defaults for dateddirs, copy, hierarchical and verbose through the .zophrc file. Thanks to Peter Farr for the patch.
* Patch#1647439: zophImport.pl can now resolve symlinks before importing. Thanks to Peter Farr for the patch.

### Bugfixes ###
* Bug#1564548, Bug#1725811: Bugs with slideshows showing an error
* Bug#1568418: Pager links do not work in bulk edit page when no search criteria are used.
* Bug#1571227: Webimport of ZIP files not working
* Bug#1571577: Cannot login with `DEBUG` set
* Bug#1571682: extra '/' in URL after logon
* Bug#1574205: No "return" from edit page
* Bug#1574206: Removing crumbs when on edit page does not correctily return
* in some cases the second page of a search would change ">=" or "<=" into "=".
* urls for places could not be longer than 32 chars.
* Fix for a bug that made search behave incorrectly when text-search for a person did not return any people.
* Bugfix for layout problem - sometimes the main window on the people page was not large enough to display all
* Bug#1713946 Missing localized strings
* Bug#1592560 Import fails when "path" field is empty
* Bug#1598437 Import does not check if user can write to the selected album.
* Patch#1713924: EXIF date/time priority, patch by Antoine Delvaux.

### Cleanup and various ###
* Lots of cleanout of HTML and CSS code. Now all unnecessary tables have been replaced by semantic HTML/CSS combinations.
* Removed duplicate spaces in translation files.
* Cleanout and getting rid of lots of (but not yet all) PHP warning messages.
* Updated info page with new mailadress for Zoph
* Changed "view" to "display" on the people page for consistancy reasons and to remove a translation problem (the word "view" is also used on the photo page, and has a different meaning there)
* Dutch, German and French translation updated
* changed some SQL syntax for speedup

## Zoph 0.6 ##
### 21 September 2006 ###

* Removed mailaddress of original Dutch translator on his request
* Fixed: Rating links on reports page not working in translated Zoph version.
* Updated Danish language file
* Fixed: issues with LIKE searches (Bug#1541763)
* Improved error handling in imports
* Fixed an issue with imports not working on Windows systems (Bug#1527333)
* Fixed: slideshow not working on search results (Bug#1562419)

## Zoph 0.6pre2 ##
### 13 July 2006 ###

* Updated translations: Dutch, English, German, Danish and Canadian English should be completely up to date now. 
* Fixed a layout glitch in the edit screen for places
* Fixed missing translations in relation and selection features.
* Fixed some incompatibilities with PHP4
* Fixed an issue that caused guest users to be unable to logon.
* Fixed an issue with trying to logon after a session timeout
* Fixed an issue with search not working for translated Zoph versions
* Fixed some issues in the SQL installation script, thanks to Ed P. for the patch.
* Added partial Turkish translation, thanks to Mufit Eribol
* Fixed and issue with auto-edit mode where you would not return to the correct photo after making a change.
* Updated man pages for zophImport.pl and zophExport.pl
* In the userlist, changed "view" to "display" for consistancy reasons and to remove a translation problem (the word "view" is also used on the photo page, and has a different meaning there)

## Zoph 0.6pre1 ##
### 4 June 2006 ###

### New features ###
* It is now possible to leave comments with photos
* You can select a photo to do certain actions with that selection.
* You can now create links between photos. (Req#778845 (partly), Req#828750) (for now, this is the only feature that makes use of "selections")
* Using external links to Zoph will now go to the login page and then to the requested URL. (Req#1443574)
* Image service is now on by default
* Possibility to overide sort order of photos in album (Req#665237)
* Possibility to overide sort order of photos in category (similar to Req#665237)
* Possibility to call albums and categories by name in URL instead of id. (Req#778024)
* Made a small change to the menu: when hovering a menu-option, the layout changes to emulate a "tab"-like display (let me know if you like this!)
* It is now longer required to be in the image dir to import a photo. (Req#853091)
* ZophImport.pl and zophExport.pl now use and external file to store the configuration (like the Debian version of Zoph).
* Quick navigation through locations. (Req#1417305)
* The search page now has a "no children" checkbox next to albums, categories and places. (Req#1416195)
* Add URL to places, so a link to -for example- a map can be made. (Req#1466069)

### Bugfixes ###
* Include URL to Zoph in e-mails (Req#655957)
* Tranlation fixes in `define_annotated_photo.php`, `edit_person.inc.php` and `edit_place.inc.php`
* `zoph_table.inc.php`: small layout fix in debug code
* Fixed: a string would not be correctly translated if it starts with a "special character".
* Fixed a few html encoding issues. (Bug#1467146 and some not reported bugs)
* Button text not correct when php.ini setting is short_open_tag = Off (Bug#1459175)
* Ratings being truncated (Bug#1466551)
* Fixed a bug where logging in without SSL would redirect you to the wrong page.
* Next/prev buttons lost after editting/deleteing a photo when using 'auto-edit' mode. (Bug#1467143, Bug#1463947)
* CSS style is not applied when mid prefix is changed in config.inc.php (Bug#1466068)
* Added missing space in photo.inc.php
* Specifying the `DEFAULT_TABLE_WIDTH` as a percentage doesn't work (Bug#1446202)
* HTML tag missing for all pages.
* MySQL >4.1 conversion doesn't work with default user feature. (Bug#1500325)
* Object syntax in `comment.inc.php not` compatible with PHP4.(Bug#1500582)

### Cleanup and various ###
* Updated Danish, Italian, Dutch, German and Canadian English language files
* Cleanup of all language files (removed no longer used strings)
* Removed `zoph_update-0.4pre1.sql`
* In photo.php, the actionlinks are now built using an array. To make life a bit easier for people using the auto-edit feature, the edit page now displays more links.
* Cleaned out the code of the search page: Removed lots of messy and redundant code and added whitespace for readability. Functionality should be unchanged.
* Fixed code layout in `util.inc.php`
* Updated HTML for the edit page of places to use semantic HTML and not tables.

          

## Zoph 0.5.1 ##
### 12 March 2006 ###

* Updated Richard Heyes mailclass to newest version. Should partly solve Req#655957
* Fixed: Quotes and apostrophes do not display correctly (Bug#1443235)
* Fixed: Places are sorted by id instead of alphabetically. (Bug#1443427)
* Fixed: Loosing context after editing (Bug#1333428)
* Fixed: Clicking on the thumbnail of a randomly chosen photo would pick a new random photo instead of showing a larger version of the thumb (Bug#1443927)
* Fixed: field with double quotes are truncated (Bug#1443235)
* Fixed: photo.php: the `_rows`, `_cols` etc. fields are added to the url, instead of replaced, whenever they are changed. (did not cause any functionality issues)
* Fixed: error at the end of a slideshow (Bug#1446200)
* Removed extra space in `create_text_input`
* Fixed installation SQL file: some missing changes needed for Zoph 0.5, (Bug#1447727)
* Resolved duplicate subject header in mail sent from Zoph
* Translation fixes in German translation, thanks to Ulrich Wiederhold
* Added missing translation to Dutch and Canadian English and updated zoph_strings.txt
* Fixed: search page does not show results when using a translated Zoph version (Bug#1448346)

## Zoph 0.5 ##
### 1 March 2006 ###

* v0.5 is equal to v0.5-pre4

## Zoph 0.5 pre4 ##
### 18 February 2006 ###

* Solved a bug that caused an error on the bulk edit page if you would add some people to a photo and consequently made another edit (Bug#1422741)
* Fixed an issue where the pager links on the bulk edit page would cause errors after an edit has been made.
* Additional anti-SQL injection code in the search page.
* When updating user permissions with a high number of albums, a "URL too long" error occurred. (Bug#1434235)
* Fixed a bug that caused some albums permissions not to be properly updated when making a change.

## Zoph 0.5-pre3 ##
### 30 January 2006 ###

* Solved a typo in upgrade documentations
* Solved a bug that caused an Admin user not to be able to browse people 
* zophImport.pl: `--verbose` combined with `--path` would not correctly tell where the file was copied.
* zophImport.pl: now exits with a non-0 status code when something goes wrong
* updated man-pages for `zophImport.pl` and `zophExport.pl` (thanks to Edelhard Becker)
* Solved a bug that caused the bulk-edit page not to work when called from search-results (Bug#1415457)
* Added brackets to some queries to make the search page react better on "not in" queries.
* Fixed a bug that caused some changes made on the bulk-edit page to be ignored.  
* Added an extra Update button to the bulk edit paged (Req#1416184)
* Made a change to the db lookup for the place dropdown that dramatically increases the performance of the bulk edit page.

## Zoph 0.5-pre2 ##
### 24 January 2006 ###

* Logging on with non-admin user in Zoph-0.5pre1 does not work (Bug#1413557)
* Rating links do not work in v0.5pre1 (Bug#1413244)

## Zoph 0.5-pre1 ##
### 21 January 2006 ###

* Changed typos in `logon.php` and `credits.html`
* Fixed php errors when user is not logged in (bug#1325547)
* Added compatibility with MySQL=>4.1, and code to automatically convert passwords from MySQL pre-4.1 to 4.1 and later format.
* Many updates to HTML and CSS, most to improve HTML semantics. (Less tables used for layout).
* Resolved some inconsistencies in config.inc.php (some defines used quotes and some not)
* `zoph_table.inc.php` now gives some more debug info when `DEBUG` is on.
* Locations are now hierarchical. The necessary database updates for this are done by the SQL update script; an unsopported script is included in the contrib dir that will try to change your locations to a real hierarchical list. Use at your own risk!
* Dated_dirs can now be made hierarchical (instead of a directory called `2006.01.20` you will have a directory-tree `2006/01/20`). Thanks to Oliver Seidel (Req#656472)
* Immediate editting of color schemes and possibility to copy them (Req#715104)
* Dated dirs in webimporter (Req#739557)
* Imported tar and zip files can be removed automatically (Req#739267)
* Change of error message in import.php to ease translation.
* People without "browse people" rights can now no longer see people's names. (Req#749503)
* Use the file date and time if there is no date in exif header. (Req#752404)
* Option to open the fullsize image in a new window. (Req#1252457)
* Watermarking for high quality images. (Req#1250028)
* Forced SSL login, thanks to Aaron Parecki. (Patch#1253265)
* Forced SSL usage
* `zophImport.pl`: Now fails when album/location/category/person does not exist. (Can be turned off by setting `$ignoreerror`). Partly solves Debian bug #284539.
* `zophImport.pl`: A friendly error is now displayed when a photo is added to an album/cat/person it is already in. (partly solves Debian bug #284539)
* Changed default permissions in `config.inc.php` as requested in Debian bug#326649
* `zophImport.pl`: Added `--copy` and `--verbose` options. Solves Debian bug#211312 and partly #218491.
* Major improvements to the search page. Thanks to Roy Bonser. (Req#685269 and Patch#1395052).
* Fixed some possible SQL-injection issues.
* Adding multiple people to a photo at once, thanks to Neil McBride. (Patch#1406959)
* Fixed Date Field set inconsistently when using files with no EXIF info. (Bug#1402492)
* Updated Canadian English, German and Dutch translations.

## Zoph 0.4 ##
### 4 September 2005 ###

* Removed "float" in CSS breadcrumb definition, this was a workaround for a very small layout issue in Firefox, but caused some ugly behaviour in Konqueror and Safari.
* Fixed incorrect 'Next' URL after editing photos. (bug#1252455)
* Moved edit button to right side in `edit_photo.php`
* Updated Dutch, English, Canadian English and German translation
* `zoph_strings.txt` (translation skeleton file) was updated for 0.4
* The "root category" on the categories page is now translated
* Fixed a layout issue when pressing pause during a slideshow
* "Up" button now takes you to the last page you were looking at, instead of the first (bug#1259152)
* Added a warning to check for maximum file size when uploading fails (bug#739546)
* Added Polish translation (thanks Krzysztof Kajkowski)
* Swedish translation was updated by Johan Linder
* Increased `DEFAULT_WIDTH` to 600, for layout reasons

## Zoph 0.4pre2 ##
### 1 August 2005 ###

* Changed layout to use CSS (thanks Jeroen Roos)
* Added Traditional Chinese translation (thanks Mat Lee)
* Fixed translation of update and submit buttons
* Added a "Contrib" directory in which some user-contributed tools are distributed.
* Contrib: Diff to use Postgres as database (for zoph 0.3.3) (thanks Chris Beauchamp)
* Contrib: ZophEdit Python script to edit photo metadata in a zoph database (thanks Nils Decker)
* Contrib: ZophClean Perl script to find and solve differences between database and files on disk.
* Fixed a bug where only Admin users could rate photos and add photos to a lightbox album (pat#1179920) (thanks Jason Taylor)
* Added a check to prevent album names, category names, location, people names, user names and color schemes to have empty names (bug#846417)
* Added a fix for `zophImport.pl`, it failed in looking up people that have a name with multiple spaces (pat#830236) (thanks Hans Verbrugge)
* Contrib: Added a script to add movies to Zoph (pat#1176317) (thanks Giles Morant)
* Fixed bug: a deleted album could still be a lightbox album (bug#1193347)
* Fixed an url-encoding bug in relation to breadcrumbs (bug#1194722)
* Fixed a problem with deleting a photo: returning to the photos after the delete was inconsistent when auto-edit is on or off. (bug#772403)
* Added an error message when file cannot be unzipped (#1193351)
* Changed the licence from BSD to GPL.
* Changed default width in `config.inc.php` to be slidely wider to solve a layout glitch

## Zoph 0.4pre1 ##
Never released

* Created a validator class to allow different types of authentication
* Added a function to `validator.inc.php` to allow htpasswd authentication (req#656449) (thanks Asheesh Laroia)
* Added $host param to `zophImport.pl` (bug#656438)
* Fixed it's vs its grammar (bug#656444)
* Changed `<?=` to `<?php echo` for `short_open_tag = Off` compatibility (bug#670542)
* Changed logout tab in `header.inc.php` to show "logon" for default users (req#656448)
* Added `DB_PREFIX` in `config.inc.php` and updated sql to use (req#656450)
* Fixed `DEFAULT_ORDERING` bug in `photos.php` and `photo_search.inc.php` (bug#667484)
* Fixed bug with date ordering failing to imply time ordering in `photo_search.inc.php` (pat#675164) (thanks Ian Kerr)
* Fixed bug in which `PHPSESSID` failed to be passed in image links when cookies were disabled (bug#663523)
* Fixed a bug in which `update_query_string()` in `util.inc.php` failed to overwrite new parameters (bug#678491)
* Updated exif flash handling in exif.inc.php (bug#671023)
* Included an udpated language package with new German, Dutch and Canadian English translations
* Added image rotation (req#666979)
* Fixed a problem with double escaping (bug#656435)
* Fixed a problem with slideshows with IE on Mac (bug#667480)
* Fixed a bug where the last modified date and the date a photo was taken were mixed up in the calendar view (bug#667486)
* Added a "default destination path", so the import no longer fails when the path is not specified (bug#670855)
* Added an extra space on the categories page (bug#741736). (thanks Mark Cooper)
* Added languages Swedish (thanks Mikael Magnusson), Afrikaans (thanks Neels Jordaan), Hebrew (thanks "Prince01"), Portuguese (thanks Joaquim Azevedo), Danish (thanks Jesper Skytte) languages
* Fixed a typo in "Swedish Chef" translation
* Added support for PNG and GIF in the webimported (thanks Patrick Lam)
* Added validation using `PHP_AUTH_USER`/`PW` using `php_validate()` (thanks Samuel Keim)
* Upgraded mail classes
* Added email notification
* Added registration of last login time and IP address per user
* Added annotated photo emails (thanks Nixon P. Childs)
* Added ratings by multiple users
* Improved navigation by adding up & return links
* Fixed a problem with next button in some specific cases (bug#782519) (thanks Curtis Rawls)
* Added bulk editting mode that can change any photo page into a "power edit" page (req#667478)
* Fixed a problem with photo editting (bug#782600) (thanks Curtis Rawls)
* Fixed offset bug in slideshow
* Fixed a bug with the pager on search results page.
* Fixed a bug where some photos where counted twice (or more) on the reports page (pat#675172) (thanks Ian Kerr)

## Zoph 0.3.3 ##
### 13 Dec 2002 ###

* Fixed a bug in `zophImport.pl` in which creating a thumbnail (or midsize) could fail when the original image was smaller than the thumbnail size (thanks Tetsuji Kyan)
* Removed the +profile option to `convert()` in `zophImport.pl` since this was caused problems on some user's systems (a problem with expansion of the * ?)
* Fixed a bug in `slideshow.php` which caused an error to be displayed when a slideshow was completed
* Added a pref to allow descriptions to be displayed under thumbnails
* Fixed a minor pager bug in photos.php
* Updated `person.inc`, `person.php` and `photo_search.php` so that the person and photos pages accept "person=LastName,FirstName" in the url instead of just person_ids
* Added a missing call to `getvar("type")` in `image_service.php` (thanks Ian Kerr)
* Added photo counts to "photos of", "photos by" and "photo at" links in `person.php` and `place.php`
* Added "photos of" and "photos by" links to `people.php`, "photos at" links to `places.php`
* Split `WEB_IMPORT` config into `CLIENT_WEB_IMPORT` and `SERVER_WEB_IMPORT`
* Updated `import.php` to handle uploads of zip and tar archives
* Fixed a bug in `import.php` which caused server imports to fail when no destination path was set
* Replaced `<?` with `<?php` so that `short_open_tag` need not be enabled in `php.ini`
* Fixed a typo in mail.php which caused html mail to have broken images
* Created zophExport.pl to create static html galleries of photos
* Added a man page for zophImport.pl (thanks Mark Cooper)
* Updated the tutorial renamed it as the manual
* Added an updated language pack with Norwegian and Spanish translations (thanks Haavard Leonardo Lund and Alvaro González Crespo)

## Zoph 0.3.2 ##
### 17 Oct 2002 ###

* Fixed a bug in edit_photo.inc.php in which the "show additional attributes" link did not work if register_globals was disabled
* Updated photos.php so that the first and last pages are always shown in the pager (thanks Christian Hoenig)
* Added a "delete" link to the edit photo page in edit_photo.inc.php
* Fixed a bug in photo.php where the auto edit pref was ignored when using the search page
* Added four new color schemes
* Fixed spelling of aperture and metering in dropdown in util.inc.php (thanks Francesco Ciattaglia)
* Added missing translation code to `categories.inc.php`, `albums.php`, `zoph.php`
* Added `DEFAULT_SHOW_ALL` config parameter for `people.php` and `places.php`
* Added missing footer include from `info.php` and `reports.php`
* Replaced calls to `include_once` with calls to `require_once`
* Added path to field pulldown in `search.php`
* Add `or die` checks to `zophImport.pl` after file manipulations commands
* Replaced `rename()` with calls to `copy()` + `unlink()` in `zophImport.pl` as rename fails when moving accross filesystems
* Altered table structure in `person.php`
* Added new language pack with new Italian translation (thanks Francesco Ciattaglia)

## Zoph 0.3.1 ##
### 30 Sep 2002 ###

* Fixed a bug in `zophImport.pl` in which `thumb_extension` was applied even when `mixed_thumbnails` was set
* Updated `zophImport.pl` so that a path need not be passed when doing `--update --thumbnails`
* Fixed `user.inc.php` so that the "Offset 1 is invalid for MySQL result index" warning is not displayed when a non admin views a photo (this bug was only present in the Zoph 0.3 download for 2 or so hours on Sep 26)
* Fixed state field size label in `edit_place.inc.php`
* Updated French language module
* Removed extra tables in `zoph.sql` included by accident in 0.3
* Updated `image_service.php` to enable use of cached images (thanks Alan Shutko)
* Fixed a bug in `user.inc.php` in which, if `register_globals` is disabled, revoking an album would cause all albums to be revoked for that user
* Fixed `photos.php` so that an odd pager size no longer results in fractional page numbers
* Fixed `zoph.php` so that the minimum random photo rating is used in the randomly chosen photo link
* Modified `get_link()` in `place.inc.php` so that a city link can also be displayed
* Updated `album_permissions.inc.php` so that revoking permissions on an album will also revoke permissions on descendant albums
* Added a pref to bring up the edit screen whenever a photo is clicked
* Added a pref to control whether the camera (exif) info is displayed
* Added a lightbox feature to hold favorite photos

## Zoph 0.3 ##
### 25 September 2002 ###

* Update `zophImport.pl` to look up photos by path as well as name when updating (thanks Francisco J. Montilla)
* Fixed spelling of "Metering Mode" in `photo.inc.php` (thanks Francisco J. Montilla)
* Updated `zophImport.pl` to generate jpeg thumbnails for all image types if desired
* Updated `photo.inc.php` and `image_service.php` to handle the new thumbnails
* Fixed the urlencoding of image names/paths in `photo.inc.php` and `util.inc.php` (thanks Francisco J. Montilla)
* Increased size of name and path fields in photos table
* Created a timestamp field in the photos table
* Added recent photos taken/modified links (thanks David Moulton for the idea)
* Fixed a change password bug in `password.php`
* Added a (view all) photos tab to the header
* Created `variables.inc.php` for PHP 4.2.x compatibility (thanks David Baldwin)
* Modified `calendar.inc.php` to handle pre 1970 dates (thanks David Baldwin)
* Zoph is now internationalized (thanks Eric Seigne for the code and French translation)
* Added a web based importer (initial code from Jan Miczaika)
* Added ability to order results
* Other minor fixes and improvements
* Updated documentation

## Zoph 0.2.1 ##
### 21 June 2002 ###

* Added default, auto logged in user feature (disabled by default)
* Fixed spelling of "aperture" in `zophImport.pl` (thanks Donald Gover)
* Fixed greedy split match in `zophImport.pl` (thanks Donald Gover)
* Quoted image name passed to `jhead` in `zophImport.pl`
* Wrapped image name in `urlencode()` in `get_image_href` in `photo.inc.php`
* Fixed remove photo links (thanks Matthew MacIntyre)
* Added view all options to people and places templates
* Added check for null in color scheme loading in `prefs.inc.php`
* Fixed templates to display album and category descriptions, if present
* Increased size of album and category description fields
* Added `focus_dist`, `ccd_width` and `comment` photo fields
* Increased size of `focal_length` photo field
* Increased size of state field in places table
* Added missing not null constraint to `detailed_people` field in `users` table

## Zoph 0.2 ##
### 24 April 2002 ###

* Initial public release
* Rewrite of Zoph 0.1 

## Zoph 0.1 ##
completed on 14 Sep 2000, never released
