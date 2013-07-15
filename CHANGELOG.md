# Zoph Changelog #
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
