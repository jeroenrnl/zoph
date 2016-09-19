Zoph 0.9.3 to 0.9.4
===================
* If you want to upgrade from an older version, first follow the instructions to upgrade to 0.9.3. It is not necessary to install older versions first, you can just install the current version and follow the upgrade instructions below.

Copy files
----------
Copy the contents of the php directory, including all subdirs, into your webroot.

    cp -a php/* /var/www/html/zoph

Database changes
----------------
* Execute zoph-update-0.9.4.sql:

    mysql -u zoph_admin -p zoph < sql/zoph_update-0.9.4.sql

Changes this script makes:

* Add a field that stores whether or not new subalbums should be automatically granted permission
* Add new colour schemes

Zoph 0.9.2 to 0.9.3
===================
* If you want to upgrade from an older version, first follow the instructions to upgrade to 0.9.2. It is not necessary to install older versions first, you can just install the current version and follow the upgrade instructions below.

Copy files
----------

Copy the contents of the php directory, including all subdirs, into your webroot.

cp -a php/* /var/www/html/zoph

Database changes
----------------
* Execute zoph-update-0.9.3.sql:

    mysql -u zoph_admin -p zoph < sql/zoph_update-0.9.3.sql

Changes this script makes:

* Resize the password field to allow store bigger hashes
* Add fields to the user table to allow for new access rights
* Add 'created by' fields to the albums, categories, places, people and circles tables

Zoph 0.9.1 to 0.9.2
===================
* If you want to upgrade from an older version, first follow the instructions to upgrade to 0.9.1. It is not necessary to install older versions first, you can just install the current version and follow the upgrade instructions below.

Copy files
----------
Copy the contents of the php directory, including all subdirs, into your webroot. 

     cp -a php/* /var/www/html/zoph

Database changes
----------------
* Execute zoph-update-0.9.2.sql:

    mysql -u zoph_admin -p zoph < sql/zoph_update-0.9.2.sql

Changes this script makes:

* Add previously missing 'random' sortorder to preferences
* Resize Last IP address field so IPv6 addresses can be stored
* Database changes for 'circles' feature
* Create a VIEW on the database to speed up queries for non-admin users
