#REQUIREMENTS#

Zoph is being developped on Linux, but it should be able to run on any OS that can run Apache, MySQL and PHP. Users have reported succesful installations on MacOSX, several BSD flavours and even Windows. 

Zoph requires the following:
* Apache 2.2 or 2.4
* PHP 5.5 or 5.6
* MySQL 5.6
* ImageMagick 6.9

Other versions may work as well, see below for more details. How to install these applications and get them to work together is depending on your OS and distribution. Check the documentation of the application and/or your distribution for details.

##Apache##
* Current versions of Zoph are developped on Apache 2.4.x

##PHP##
Current versions of Zoph are developped on PHP 5.5 and 5.6
* PHP 5.4 and older are no longer supported

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

max_execution_time
This is the time Zoph is allowed by PHP to run. Depending on the speed of your webserver, Zoph could spend quite a lot of time resizing an image. 30 seconds may not be enough, especially if you have a camera with a lot of megapixels.

###memory_limit###
This is the amount of memory PHP allows Zoph to use. Especially if you have large images, the default (8 or 16 Megabyte) may not be enough. If you have sufficient memory in your server, setting it to 128M is perfectly safe.

If you are using the web importer you may need to increase the max_execution_time, upload_max_filesize, post_max_size and max_input_time in php.ini defined in php.ini. See MAX_UPLOAD on the Configuration page.

If you are using the watermarking feature, you probably need to increase the memory_limit setting. See WATERMARKING on the Configuration page.

The e-mail photo feature may require increasing the memory_limit setting. See EMAIL_PHOTOS on the Configuration page.

##MySQL##
* Current versions are developped with MySQL 5.6
* MySQL 5.0 to 5.5 may still work but are no longer supported.

##ImageMagick##
* Current Zoph versions have been tested against ImageMagick 6.9.x

##Browser##
In order to be able to use Zoph, you will need a browser.
* Zoph is being developped and thoroughly tested with a recent Firefox build
* Zoph should work with all recent browser versions
    * Please report a bug if it doesn't.
* Older versions usually work, but layout may not be 100% ok.
* Some features require Javascript support
    * Most of Zoph should work when Javascript is turned off in the browser, but this is decreasing, Javascript is required for more and more functions!
