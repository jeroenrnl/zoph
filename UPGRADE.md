Zoph 0.8 to 0.9
===============

* If you want to upgrade from a version older then 0.8, first follow the instructions to upgrade to 0.8. It is not necessary to install older versions first, you can just install the current version and follow the upgrade instructions below.

* If you want to upgrade from 0.8.4, you just need to copy the files, no database changes are needed
* You can also follow these instructions to go to 0.9preX
* You can also follow these instruction if you are on one of the maintenance releases of 0.8 (0.8.0.x)

* If you are on one of the feature releases for 0.8 (0.8.x), except 0.8.4, you will need to edit zoph_update-0.9.sql to comment any changes you have already made on your system.

Copy the contents of the php directory, including all subdirs, into your webroot and copy the lang directory into the webroot as well. You should make a backup copy of config.inc.php to prevent overwriting it.

     cp config.inc.php config.local.php
     cp -a php/* /var/www/html/zoph
     cp -a lang /var/www/html/zoph

Copy cli/zoph into /bin (or another directory in your $PATH):
     cp cli/zoph /bin

Copy zoph.1.gz into your man 1 directory (usually /usr/share/man/man1) and zoph.ini.5.gz into man 5 (usually /usr/share/man/man5):
     cp cli/zoph.1.gz /usr/share/man/man1
     cp cli/zoph.ini.5.gz /usr/share/man/man5

Database changes
================

Don't forget to edit the sql script if you are running on 0.8.x.

Zoph 0.9 requires a manual upgrade to the database, this is described in http://en.wikibooks.org/wiki/Zoph/Upgrading/Changing_your_database_to_UTF-8. If you are on 0.8.1 or later, you should already have made this change.

Execute zoph-update-0.9.sql:
     mysql -u zoph_admin -p zoph < sql/zoph_update-0.9.sql

Change zoph into zophutf8 if you are working on the temporary database.

Changes this script makes:

* Remove the people_slots setting from the user preferences table (0.8.4)
* Add a hash to the photos table (0.8.4)
* Add a setting to control whether or not the user is allowed to use the sharing feature in the users table (0.8.4)
* Added tables and preferences for geotagging support (0.8.3).
* Make the language field in the prefs table longer so languages like en-ca can be stored (0.8.1)

Configuration updates
=====================

In Zoph 0.8.2, .zophrc and a part of config.inc.php were replaced by zoph.ini. You can use zoph.ini.example in the cli dir as an example. (see http://en.wikibooks.org/wiki/Zoph/Configuration) for details):

New options
-----------

* LOG_ALWAYS 
    Control how much debug information is showed for all subjects. (0.8.1)
* LOG SEVERITY 
    Configure how much debug information is showed, for the subjects defined in LOG_SUBJECT (0.8.1)
* LOG_SUBJECT 
    Configure on which subject you would like to see logging. 0.8.1)
* CLI_USER 
    User id that the CLI client uses to connect to Zoph. Must be admin. Change this into '0' to let Zoph lookup the user from the Unix user that is running Zoph. (0.8.2)
* IMPORT
    Enable ('1') or disable ('0') webimport (0.8.2)
* UPLOAD
    Enable ('1') or disable ('0') uploading photos through the browser (0.8.2)
* IMPORT_DIR
    Directory, relative to IMAGE_DIR, that will store uploaded photos until they have been imported in Zoph. (0.8.2)
* IMPORT_PARALLEL
    Number of photos to resize concurrently. (0.8.2)
* MAGIC_FILE
    MIME Magic file. Zoph needs this to determine the file type of an imported file. (0.8.2)
* FILE_MODE
    File permissions for files imported in Zoph. (0.8.2)
* UNGZ_CMD
    Command to be used to decompress .gz files. (0.8.2)
* UNBZ_CMD
    Command to be used to decompress .bzip files. (0.8.2)
* SHARE 
    Enable the possibility to share a photo by using a URL that can be used without logging in to Zoph. Once enabled, you can determine per user whether or not this user is allowed to see these URLs. (0.8.4)
* SHARE_SALT_FULL 
    When using the SHARE feature, Zoph uses a hash to identify a photo. Because you do not want people who have access to you full size photos (via Zoph or otherwise) to be able to generate these hashes, you should give Zoph a secret salt so only authorized users of your Zoph installation can generate them. This one is used for fullsize photos (0.8.4)
* SHARE_SALT_FULL 
    When using the SHARE feature, Zoph uses a hash to identify a photo. Because you do not want people who have access to you full size photos (via Zoph or otherwise) to be able to generate these hashes, you should give Zoph a secret salt so only authorized users of your Zoph installation can generate them. This one is used for midsize photos (0.8.4)

Removed options
---------------

The following configuration options no longer exist, you should remove them from you config.inc.php:

* DB_HOST 
    Moved to zoph.ini (0.8.2)
* DB_NAME 
    Moved to zoph.ini (0.8.2)
* DB_USER 
    Moved to zoph.ini (0.8.2)
* DB_PASS 
    Moved to zoph.ini (0.8.2)
* CLIENT_WEB_IMPORT 
    Replaced by UPLOAD (0.8.2)
* SERVER_WEB_IMPORT 
    Replaced by IMPORT (0.8.2)
* DEFAULT_DESTINATION_PATH 
    Due to introduction of IMPORT_DIR no longer necessary (0.8.2)
* SHOW_DESTINATION_PATH 
    Due to introduction of IMPORT_DIR no longer necessary (0.8.2)
* REMOVE_ARCHIVE 
    As of Zoph 0.8.2, Zoph always removes an archive after a successful decompress (0.8.2)
* IMPORT_MOVE 
    Due to introduction of IMPORT_DIR, Zoph always moves files (0.8.2)
* IMPORT_UMASK 
    Replaced by FILE_MODE (0.8.2)
* USE_IMAGE_SERVICE 
    The Image Service is now always on. If you were previously using define('USE_IMAGE_SERVICE', 0), you should move your images out of your webroot, and update IMAGE_DIR accordingly. (0.8.4)
* WEB_IMAGE_DIR 
    This was only needed when USE_IMAGE_SERVICE was enabled. (0.8.4)
* MAX_PEOPLE_SLOTS 
    The people slots feature, that allowed multiple 'add people' dropdowns on the edit photo and bulk edit photo pages has been replaced by a Javascript that automatically adds a new dropdown whenever a new person is added, allowing a virtually unlimited amount of people to be added in one edit. (0.8.4)

For upgrade instruction for older releases, please see http://en.wikibooks.org/wiki/Zoph/Upgrading/Archive
