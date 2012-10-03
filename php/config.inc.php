<?php
/*
 * This file is part of Zoph.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

    define('VERSION', '0.9');

    // DB_HOST, DB_NAME, DB_USER, DB_PASS and DB_PREFIX have been moved to
    // zoph.ini. The location can be set by the next config item:
    
    define('INI_FILE', "/etc/zoph.ini");

    // Define how Zoph looks by choosing a stylesheet and iconset.
    // Deprecated: Modify through admin -> config in web interface.
    //define('CSS_SHEET', 'css.php');
    define('ICONSET', 'default');

    define('IMAGE_DIR', "/data/images/");

    // authentication method used from auth.inc.php
    // (this needs to be the name of a function in validator.inc.php)
    $VALIDATOR = 'default_validate';
    //$VALIDATOR = 'htpasswd_validate';
    //$VALIDATOR = 'php_validate';

    // Set this to 1 if you want to have your users (logon) over SSL.
    // Make sure you set ZOPH_URL and ZOPH_SECURE_URL as well.
    define('FORCE_SSL_LOGIN', 0);
    define('FORCE_SSL', 0);

    // Deprecated: Modify through admin -> config in web interface.
    //define('ZOPH_TITLE', "Zoph");

    define('LANG_DIR', "lang"); // where language files are stored

    # Use this language when neither user or browser specify a language
    define('DEFAULT_LANG', 'en');

    define('EMAIL_PHOTOS', 1); // enable email photo feature

    // sent all emails also to this address
    // set to '' if not needed
    define('BCC_ADDRESS', '');

    // URL used in Notification EMail and for https logons
    // set to '' if not needed, example:
    // define('ZOPH_URL', 'http://myserver.com/zoph');
    // define('ZOPH_SECURE_URL', 'https://myserver.com/zoph');
    define('ZOPH_URL', '');
    define('ZOPH_SECURE_URL', '');

    // If set to 0 Zoph will not use any Javascript
    // Of course this might mean some functionality is not available
    define('JAVASCRIPT', 1);

    // Enable the autocompletion feature
    // This needs JAVASCRIPT to be on
    define('AUTOCOMPLETE', 1);

    // Enable Mapping and determine hich mapping provider to use
    // currently supported: 'google', 'googlev3', 'yahoo', 'cloudmade' or '' to disable
    // This needs JAVASCRIPT to be on
    // Deprecated: Modify through admin -> config in web interface.
    // define('MAPS', '');

    // Enable geocoding and specify provider. Currently only supported is
    // 'geonames'.
    define('GEOCODE', '');
    // API key for Google Maps, only needed if MAPS = 'google'
    define('GOOGLE_KEY', 'Get yours at http://code.google.com/apis/maps');
    
    // API key fore Cloudmade Maps, only needed if MAPS = 'cloudmade'
    // This is Zoph's API key. Please do not use for other applications
    define('CLOUDMADE_KEY', 'f3b46b04edd64ea79066b7e6921205df');

    // The timezone your camera is set to
    // Leave empty if you always set your camera to local time
    // Deprecated: Modify through admin -> config in web interface.
    // define('CAMERA_TZ','');
    // Define how Zoph displays date and time
    // See http://www.php.net/manual/en/function.date.php for explanation.
    define('DATE_FORMAT', 'd-m-Y');
    define('TIME_FORMAT', 'H:i:s T');

    // if you set your camera to local time you may want to use this
    //define('TIME_FORMAT', 'H:i:s');

    // If you want to enable guessing the timezone based on the current
    // coordinated, set this to 1, this will however mean, that Zoph sends
    // information to the Geonames project. http://www.geonames.org/
    define('GUESS_TZ', 0);
 
    // The SHARE feature allows you to publish a link to a photo that people 
    // can see without logging in to Zoph
    define('SHARE', 0);
    // To prevent people from generating hashes for the share functions,
    // you should provide Zoph with a unique salt, for both fullsize images and
    // midsize images. Both MUST be different. Longer is better. It should be
    // 10 characters at the very least. This is the only place where you need them,
    // so you don't need to remember them. You should keep them secret.
    define('SHARE_SALT_FULL', 'Set this to a long secret string');
    define('SHARE_SALT_MID', 'Set this to a long, different secret string');

    
    // allow annotation of photos for emailing
    define('ANNOTATE_PHOTOS', 1);
    define('ANNOTATE_TEMP_DIR', '/tmp');
    define('ANNOTATE_TEMP_PREFIX', 'zoph');

    // Enable watermarking
    // Image service must be enabled to make this work!
    define('WATERMARKING', 0);
    // Watermark must be a GIF image, transparancy is honoured!
    // the filename is relative to the image root (IMAGE_DIR)
    define('WATERMARK', 'watermark.gif');

    // Position of the watermark: left, centre or right
    define('WM_POSX', 'center');
    // Position of the watermark: top, centre or bottom
    define('WM_POSY', 'center');
    // Transparency of the watermark overlay
    // 0 = fully transparent (invisible) to 100 = no transparency
    define('WM_TRANS', 50);


    // If set to 1, users can leave comments with photos
    define('ALLOW_COMMENTS', 0);

    // Enable downloading of a set of photos in a ZIP file.
    // Warning: the downloaded photos are NOT watermarked.
    define('DOWNLOAD', 0);

    // Import related options:

    // Enable (1) or disable (0) importing through the webinterface.
    define('IMPORT', 1);
    // Enable (1) or disable (0) uploading photos.
    define('UPLOAD', 0);

    // Maximum filesize to be uploaded, in bytes:
    // Make sure you also change "upload_max_filesize" "post_max_size"
    // and possibly "max_execution_time" and "max_input_time" in php.ini
    define('MAX_UPLOAD', 10000000);

    // Directory where uploads are placed until they are completely processed
    // this is a directory under IMAGE_DIR
    define('IMPORT_DIR', 'upload');

    // Number of files to resize at the same time
    // on a fast server with multiple CPU's or cores, you could increase this
    define('IMPORT_PARALLEL', 1);

    // Automatically rotate imported images
    // requires "jhead"
    define('IMPORT_AUTOROTATE', 0);

    // How to resize an image during  import
    // 'resample': high quality / high CPU / slow [default]
    // 'resize': lower quality / low CPU / fast
    // resize can be about 3 times faster, but the resized image has a
    // lower quality.
    define('IMPORT_RESIZE', 'resample');

    // Zoph needs a MIME Magic file to be able to determine the filetype of an 
    // uploaded file. This is an important security measure, since it prevents 
    // users from uploading files other than images and archives.
    // 
    // If left empty, PHP will use the built-in Magic file, if for some reason
    // this does not work, you can specify the location of the MIME magic file
    // Where this file is located, depends on your distribution, 
    // /usr/share/misc/magic.mgc, /usr/share/misc/file/magic.mgc, 
    // /usr/share/file/magic are often used.
    //
    define('MAGIC_FILE', '');

        
    // commands to use to expand uploaded archives.  set to 0 to disable.
    // Set to a valid command to enable, NOT "1"

    define('UNZIP_CMD', 0);
    //define('UNZIP_CMD', 'unzip');
    define('UNTAR_CMD', 0);
    //define('UNTAR_CMD', 'tar xvf');
    define('UNGZ_CMD', 0);
    //define('UNGZ_CMD', 'gunzip');
    define('UNBZ_CMD', 0);
    //define('UNBZ_CMD', 'bunzip2');

    // Use dated dirs with web import 
    define('USE_DATED_DIRS', 0);
    // Use hierarchical dated dirs like 2005/12/21
    // This parameter is ignored when USE_DATED_DIRS is not set
    define('HIER_DATED_DIRS', 0);

    // let users rate photos
    define('ALLOW_RATINGS', 1);

    define('MAX_CRUMBS', 100);

    // max days for photos taken/modified X days ago pulldown
    define('MAX_DAYS_PAST', 30);

    // the maximum number of characters of a description to display
    // under a thumbnail (see also desc_thumbnail pref).  Set this
    // to 0 to disable this feature (and override a user's pref).
    define('MAX_THUMB_DESC', 40);

    define('THUMB_SIZE', 120);
    define('MID_SIZE', 480);

    define('THUMB_PREFIX', 'thumb');
    define('MID_PREFIX', 'mid');

    // beginning with Zoph 0.3, all thumbnails may be jpegs.
    // however, if you wish to avoid regenerating thumbnails for
    // other types (gif, tiff, etc.), you can set this var to 1.
    define('MIXED_THUMBNAILS', 1);

    // the extension used for thumbnails.  this should correspond
    // to what is found in zophImport.pl.  this is ignored (for some
    // image types - jpg, gif, tif) if MIXED_THUMBNAILS is set.
    define('THUMB_EXTENSION', "jpg");

    // allow images to be rotated
    define('ALLOW_ROTATIONS', 1);
    define('ROTATE_CMD', 'convert');
    //define('ROTATE_CMD', 'jpegtran');
    // set to 1 to backup the original before it is rotated
    define('BACKUP_ORIGINAL', 1);
    // copy the original to a file with this prefix
    define('BACKUP_PREFIX', 'orig_');

    // width of the main table
    // May be in pixels ("px") or percent ("%").
    // As of v0.6, the entity is required.
    define('DEFAULT_TABLE_WIDTH', "600px");
    // define('DEFAULT_TABLE_WIDTH', "90%");

    // set to the id of a non admin user or to 0 to disable
    // note that this is a user_id, not a person_id
    define('DEFAULT_USER', 0);

    // This is the user_id of the user that is used when using the CLI
    // this user *must* be an admin user.
    // If set to 0, zoph will try to find a Zoph user by the name of the
    // currently logged on (unix) user.
    define('CLI_USER', 0);

    // if this is non-zero the people and places pages will default to
    // "show all" instead of "a".
    define('DEFAULT_SHOW_ALL', 1);

    // these two are for the importer
    // Make sure there are no quotes around these numbers!
    define('FILE_MODE', 0644);
    define('DIR_MODE', 0755);

    // LOG_ALWAYS and LOG_SEVERITY can have the following values:
    // log::DEBUG, log::NOTIFY, log::WARN, log::ERROR, log::FATAL, log::NONE

    // Always show fatal errors
    define('LOG_ALWAYS', log::FATAL);

    // Use the next options to show errors on a specific subject
    // You can use the following subjects:
    // log::VARS, log::LANG, log::LOGIN, log::REDIRECT,
    // log::DB, log::SQL, log::XML, log:IMG, log::GENERAL, log::ALL

    // Combine several subjects with | and |~
    // For example to see SQL and LANG errors, log::SQL | log::LANG
    // to see all errors except redirect log::ALL | ~log::REDIRECT
    // all erros except SQL and LANG: log::ALL | ~(log::SQL |log::LANG)
    define('LOG_SEVERITY', log::NONE);
    define('LOG_SUBJECT', log::NONE);

    // default photo results ordering
    $DEFAULT_ORDER = "date";
    // default direction of ordering
    $DEFAULT_DIRECTION = "asc";

    // the following values are defaults that can be overriden
    // by a user's preferences
    $SHOW_BREADCRUMBS = 1;
    $MAX_CRUMBS_TO_SHOW = 8;
    $DEFAULT_ROWS = 3;
    $DEFAULT_COLS = 4;
    $MAX_PAGER_SIZE = 10;
    $RANDOM_PHOTO_MIN_RATING = 5;
    $TOP_N = 5;
    $SLIDESHOW_TIME = 5; // seconds

    $PAGE_BG_COLOR = "#ffffff";
    $TEXT_COLOR = "#000000";
    $LINK_COLOR = "#111111";
    $VLINK_COLOR = "#444444";
    $TABLE_BG_COLOR = "#ffffff";
    $TABLE_BORDER_COLOR = "#000000";
    $BREADCRUMB_BG_COLOR = "#ffffff";
    $TITLE_BG_COLOR = "#f0f0f0";
    $TITLE_FONT_COLOR = "#000000";
    $TAB_BG_COLOR = "#000000";
    $TAB_FONT_COLOR = "#ffffff";
    $SELECTED_TAB_BG_COLOR = "#c0c0c0";
    $SELECTED_TAB_FONT_COLOR = "#000000";

?>
