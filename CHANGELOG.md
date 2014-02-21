# Zoph Changelog #

## Zoph 0.9.1 ##
### 21 Feb 2014 ###
Zoph 0.9.1 is the first feature release for Zoph 0.9, it shows a preview of some of the new features for Zoph 0.10. Most important change is the move of most configuration items from config.inc.php into the Web GUI.

####Features####

* issue#28 Configuration through webinterface 
* Removed display desc under thumbnail feature 
* Removed MIXED_THUMBNAILS and THUMB_EXTENSION settings 
* removed DEFAULT_SHOW_ALL setting 
* Removed LANG_DIR configuration item 
* Changed the looks of <input> fields a bit 
* Removed alternative password validators 
* Removed checks for PHP 5.1 
* Adding CLI support for configuration 
* issue#7 Added a favicon 
* issue#18 Added "return" link on bulk edit page 
* Added a script to migrate config to new db-based system 
* issue#8 Made template selectible from webinterface 
* Removed MAX_CRUMBS 

####Bugs####

* Simplified CLI code & fixed bug in --autoadd
* issue#34 Rows and columns swapped on photos page
* issue#36 Webimporter does not import description
* issue#37 Can not add position on map using the mouse
* Fixed a bug that caused EXIF information in some (rare) cases to report the aperture wrong.
* Strict standards warning 
* issue#45 Pagebreak inside HTML tags causes browser to render incorrectly
* issue#45 Added selectArray cache to zophTable
* issue#48 Repair photo ratings during import
* issue#50 Geonames project has changed URL and requires username
* issue#51 Fixed depth in tree display when autocorrect is off
* issue#39 Added support for session.upload_progress as APC replacement (PHP 5.4 compatibility)
* issue#38 CLI tries to lookup previous argument's value when looking up photographer

####Improvements####

I have made quite a few improvements on the "inside" of Zoph. I have refactored many parts of Zoph
to create cleaner, less duplicated and more robust code. I have introduced UnitTests (resulting in 
about 20% of Zoph's sourcecode now tested fully automatic for bugs). As a help to that, I am now 
using Sonar to automatically run these tests and also analyse Zoph code for other problems.

* * issue#29 First step in creating unittests for Zoph 
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
* issue#40 Change documentation to Markdown        
* Modified some queries to improve performance 

## Zoph 0.9.0.1 ##
### 18 oct 2012 ###

Zoph 0.9.0.1 is the first maintenance release for Zoph 0.9. It adds compatibility with MySQL 5.4.4 and later and PHP 5.4 support. Several bugs were fixed.


#### Bugs ####

* issue#1  Changed TYPE=MyISAM to ENGINE=MyISAM for MySQL > 5.4.4 compatibility
* issue#1  Fixed: PHP Notice: Array to string conversion
* issue#2  Changed timestamp(14) into timestamp
* issue#3  Removed pass-by-reference for PHP 5.4 compatibility
* issue#6  Missing French translation
* issue#30 Remove warning about undefined variables
* issue#31 Fixed several errors in geotagging code
* issue#33 Fixed: no error message when rotate fails
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
