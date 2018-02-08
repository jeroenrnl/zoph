#REQUIREMENTS#

Zoph is being developed on Linux, but it should be able to run on any OS that can run Apache, MySQL and PHP. Users have reported succesful installations on MacOSX, several BSD flavours and even Windows. 

Zoph requires the following:
* Apache 2.2 or 2.4
* PHP 7.1 or 7.2
* MariaDB 10.1 MySQL 5.6 or 5.7
* ImageMagick 6.9

Other versions may work as well, see below for more details. How to install these applications and get them to work together is depending on your OS and distribution. Check the documentation of the application and/or your distribution for details.

##Apache##
* Current versions of Zoph are developed on Apache 2.4.x

##PHP##
Current versions of Zoph are developed on PHP 7.2
* PHP 5.5 and older are no longer supported
* PHP 5.6 and 7.0 should still work, but it is recommended to update to 7.1 or 7.2

###Required features###
The following features (extensions) to PHP are required for Zoph. Not all distributions automatically install all of them.
* session
* pcre
* gd2
* exif
* xml
* pear (if you want to use the e-mail features)
* FileInfo

##php.ini settings##

Settings you may need to change in php.ini:
###max_input_time###
This is the time Zoph is allowed by PHP to spend waiting for the file to be uploaded. Depending on the size of your files and the speed of your server's connection, 30 seconds (the default) is usually enough to process single images, if you are uploading zip or tar files, you may want to increase this to 60 or 120 seconds.

###max_execution_time###
This is the time Zoph is allowed by PHP to run. Depending on the speed of your webserver, Zoph could spend quite a lot of time resizing an image. 30 seconds may not be enough, especially if you have a camera with a lot of megapixels.

###memory_limit###
This is the amount of memory PHP allows Zoph to use. Especially if you have large images, the default (8 or 16 Megabyte) may not be enough. If you have sufficient memory in your server, setting it to 128M is perfectly safe.
* If you are using the web importer you may need to increase the `max_execution_time`, `upload_max_filesize`, `post_max_size` and `max_input_time`  defined in php.ini.
* If you are using the watermarking feature, you probably need to increase the `memory_limit` setting. Please note that enabling this function uses a rather large amount of memory on the webserver. PHP by default allows a script to use a maximum of 8MB memory. You should probably increase this by changing `memory_limit` in php.ini. A rough estimation of how much memory it will use is 6 times the number of megapixels in your camera. For example, if you have a 5 megapixel camera, change the line in php.ini to `memory_limit=30M`
* The e-mail photo feature may require increasing the `memory_limit` setting. Since Zoph needs to convert the photo into Base64 encoding for mail, it requires quite a large amount of memory if you try to send full size images and you may need to adjust `memory_limit` in php.ini, you should give it at least about 4 times the size of your largest image.

##MySQL##
* Current versions are developed with MariaDB 10.x
* MySQL or MariaDB 5.6 or 5.7 should also work
* MySQL 5.0 to 5.5 may still work but are no longer supported.

##ImageMagick##
* Current Zoph versions have been tested against ImageMagick 6.9.x

##Browser##
In order to be able to use Zoph, you will need a browser.
* Zoph is being developed and thoroughly tested with a recent Firefox build
* Zoph should work with all recent browser versions
    * Please report a bug if it doesn't.
* Older versions usually work, but layout may not be 100% ok.
* Some features require Javascript support
    * Most of Zoph should work when Javascript is turned off in the browser, but this is decreasing, Javascript is required for more and more functions!
