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

    define('VERSION', '0.5pre1');

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'zoph');
    define('DB_USER', 'zoph_rw');
    define('DB_PASS', 'pass');
    define('DB_PREFIX', 'zoph_'); // prefix for tables, '' for none

    // Temporary addition until CSS sheet can be chosen from config page
    define('CSS_SHEET', 'css.php');

    define('USE_IMAGE_SERVICE', 0);
    define('IMAGE_DIR', "/data/images/");
    define('WEB_IMAGE_DIR', "/images/"); // from webserver doc root

    // authentication method used from auth.inc.php
    // (this needs to be the name of a function in validator.inc.php)
    $VALIDATOR = 'default_validate';
    //$VALIDATOR = 'htpasswd_validate';
    //$VALIDATOR = 'php_validate';

    define('ZOPH_TITLE', "Zoph");

    define('LANG_DIR', "lang"); // where language files are stored

    define('EMAIL_PHOTOS', 1); // enable email photo feature

    // sent all emails also to this address
    // set to '' if not needed
    define('BCC_ADDRESS', '');

    // URL used in Notification EMail
    // set to '' if not needed
    define('ZOPH_URL', '');

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

    // web import of photos
    define('CLIENT_WEB_IMPORT', 1);
    define('SERVER_WEB_IMPORT', 0);

    // commands to use to expand uploaded archives.  set to 0 to disable.
    define('UNZIP_CMD', 0);
    //define('UNZIP_CMD', 'unzip');
    define('UNTAR_CMD', 0);
    //define('UNTAR_CMD', 'tar xvf');

    // directory to use to temporarily extract uploaded archives
    define('EXTRACT_DIR', '/tmp');

    // Remove zip or tar file after successful import
    define('REMOVE_ARCHIVE', 0);

    // destination path params for importing
    // "date(format)" will be expanded to today's date
    define('DEFAULT_DESTINATION_PATH', 'uploads/date(Y.m.d)');

    // Use dated dirs with web import 
    define('USE_DATED_DIRS', 0);
    // Use hierarchical dated dirs like 2005/12/21
    // This parameter is ignored when USE_DATED_DIRS is not set
    define('HIER_DATED_DIRS', 0);

    define('SHOW_DESTINATION_PATH', 0); // show for non admin users

    // let users rate photos
    define('ALLOW_RATINGS', 1);

    define('MAX_CRUMBS', 100);

    // max days for photos taken/modified X days ago pulldown
    define('MAX_DAYS_PAST', 30);

    // the maximum number of characters of a description to display
    // under a thumbnail (see also desc_thumbnail pref).  Set this
    // to 0 to disable this feature (and override a user's pref).
    define('MAX_THUMB_DESC', 40);

    // support for optgroups in many browsers seems incomplete or buggy
    define('GROUPED_PULLDOWN_SIZE', 9999);
    define('MAX_PULLDOWN_SIZE', 400);

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
    define('DEFAULT_TABLE_WIDTH', 600);
    //define('DEFAULT_TABLE_WIDTH', "100%");

    // set to the id of a non admin user or to 0 to disable
    // note that this is a user_id, not a person_id
    define('DEFAULT_USER', 0);

    // if this is non-zero the people and places pages will default to
    // "show all" instead of "a".
    define('DEFAULT_SHOW_ALL', 1);

    // these two are for the importer
    define('IMPORT_UMASK', 0);
    define('DIR_MODE', 0777);

    define('DEBUG', 0);

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
