<?php
/**
 * Via this class Zoph can read configurations from the database
 * the configurations themselves are stored in confItem objects
 *
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
 *
 * @package Zoph
 * @author Jeroen Roos
 */

require_once("database.inc.php");
require_once("user.inc.php");

/**
 * conf is the main object for access to Zoph's configuration
 * in the database
 */
class conf {

    /**
     * @var array Groups are one or more configuration objects that
     *            belong together;
     */
    private static $groups=array();
    
    /** @var bool whether or not the configuration has been loaded from the db */
    private static $loaded=false;

    /**
     * Read configuration from database
     */
    public static function loadFromDB() {
        self::getDefault();
        $sql="SELECT conf_id, value FROM " . DB_PREFIX . "conf";

        $result=query($sql, "Cannot load configuration from database");

        while($row= fetch_row($result)) {
            $key=$row[0];
            $value=$row[1];
            try {
                $item=conf::getItemByName($key);
            } catch (ConfigurationException $e) {
                /* An unknown item will automatically be deleted from the
                   database, so we can remove items without leaving a mess */
                echo $e->getMessage();
                $sql="DELETE FROM " . DB_PREFIX . "conf WHERE " .
                    "conf_id='" . escape_string($key) . "';";
                    echo $sql;
                query($sql);
            }

            try {
                $item->setValue($value);
            } catch (ConfigurationException $e) {
                /* An illegal value is automatically set to the default */
                echo $e->getMessage();
            }
        }
        self::$loaded=true;
        
    }

    /**
     * Read configuration from submitted form
     * @param array $_GET or $_POST variables
     * @todo: bug - when submit contains both GET and POST, only GET is loaded in $vars
     *        POST is needed, so in case there are GET vars, this will not work!
     */
    public static function loadFromRequestVars(array $vars) {
        self::getDefault();
        foreach($vars as $key=>$value) {
            if(substr($key,0,1) == "_") { continue; }
            $key=str_replace("_", ".", $key);
            try {
                $item=conf::getItemByName($key);
                $item->setValue($value);
                $item->update();
            } catch(ConfigurationException $e) { 
                log::msg("Configuration cannot be updated: " . $e->getMessage(), log::ERROR, log::CONFIG);
            }
        }
        self::$loaded=true;
    }


    /**
     * Get a configuration item by name
     * @param string Name of item to return
     * @return confItem Configuration item
     * @throws ConfigurationException
     */
    public static function getItemByName($name) {
        $name_arr=explode(".", $name);
        $group=array_shift($name_arr);
        if(isset(self::$groups[$group]) && isset(self::$groups[$group][$name])) {
            return self::$groups[$group][$name];
        } else {
            throw new ConfigurationException("Unknown configuration item " . $name);
        }
    }

    /**
     * Get the value of a configuration item
     * @param string Name of item to return
     * @return string Value of parameter
     */
    public static function get($key) {
        if(!self::$loaded) {
            self::loadFromDB();
        }
        $item=conf::getItemByName($key);
        return $item->getValue();
            
    }

    /**
     * Get all configuration items (in groups)
     * @return array Array of group objects
     */
    public static function getAll() {
        if(!self::$loaded) {
            self::loadFromDB();
        }
        return self::$groups;
    }

    /**
     * Create a new confGroup and add it to the list
     * @param string name
     * @param string description
     */
    public static function addGroup($name, $desc = "") {
        $group = new confGroup();

        $group->setName($name);
        $group->setDesc($desc);


        self::$groups[$name]=$group;
        return $group;
    }

    /**
     * Returns the default configuration
     * This is used to define all configurable items in Zoph
     */
    private static function getDefault() {

        /************************** INTERFACE **************************/
        $interface = self::addGroup("interface", "Zoph interface settings");

        $int_title = new confItemString();
        $int_title->setName("interface.title");
        $int_title->setLabel("Title");
        $int_title->setDesc("The title for the application. This is what appears on the home page and in the browser's title bar.");
        $int_title->setDefault("Zoph");
        $int_title->setRegex("^.*$");
        $interface[]=$int_title;

        $int_width = new confItemString(); 
        $int_width->setName("interface.width");
        $int_width->setLabel("Screen width");
        $int_width->setDesc("A number in pixels (\"px\") or percent (\"%\"), the latter is a percentage of the user's browser window width.");
        $int_width->setDefault("600px");
        $int_width->setRegex("^[0-9]+(px|%)$");
        $interface[]=$int_width;

        $int_css = new confItemString(); 
        $int_css->setName("interface.css");
        $int_css->setLabel("Style Sheet");
        $int_css->setDesc("The CSS file Zoph uses");
        $int_css->setDefault("css.php");
        $int_css->setRegex("^[A-Za-z0-9_\.]+$");
        $interface[]=$int_css;

        $int_share = new confItemBool();
        $int_share->setName("interface.share");
        $int_share->setLabel("Sharing");
        $int_share->setDesc("Sometimes, you may wish to share an image in Zoph without creating a user account for those who will be watching them. For example, in order to post a link to an image on a forum or website. When this option is enabled, you will see a 'share' tab next to a photo, where you will find a few ways to share a photo, such as a url and a HTML &lt;img&gt; tag. With this special url, it is possible to open a photo without logging in to Zoph. You can determine per user whether or not this user will see the tab and therefore the urls.");
        $int_share->setDefault(false);
        $interface[]=$int_share;

        $int_salt_full = new confItemSalt();
        $int_salt_full->setName("interface.share.salt.full");
        $int_salt_full->setLabel("Salt for sharing full size images");
        $int_salt_full->setDesc("When using the sharing feature, Zoph uses a hash to identify a photo. Because you do not want people who have access to you full size photos (via Zoph or otherwise) to be able to generate these hashes, you should give Zoph a secret salt so only authorized users of your Zoph installation can generate them. The salt for full size images (this one) must be different from the salt of mid size images (below), because this allows Zoph to distinguish between them. If a link to your Zoph installation is being abused (for example because someone whom you mailed a link has published it on a forum), you can modify the salt to make all hash-based links to your Zoph invalid.");
        $int_salt_full->setDefault("Change this");
        $int_salt_full->setRequired();
        $interface[]=$int_salt_full;

        $int_salt_mid = new confItemSalt();
        $int_salt_mid->setName("interface.share.salt.mid");
        $int_salt_mid->setLabel("Salt for sharing mid size images");
        $int_salt_mid->setDesc("The salt for mid size images (this one) must be different from the salt of mid full images (above), because this allows Zoph to distinguish between them. If a link to your Zoph installation is being abused (for example because someone whom you mailed a link has published it on a forum), you can modify the salt to make all hash-based links to your Zoph invalid.");
        $int_salt_mid->setDefault("Modify this");
        $int_salt_mid->setRequired();
        $interface[]=$int_salt_mid;

        $int_autoc = new confItemBool();
        $int_autoc->setName("interface.autocomplete");
        $int_autoc->setLabel("Autocomplete");
        $int_autoc->setDesc("Use autocompletion for selection of albums, categories, places and people instead of standard HTML selectboxes. Can be individually switched off from user preferences.");
        $int_autoc->setDefault(true);
        $interface[]=$int_autoc;

        $int_lang = new confItemSelect();
        $int_lang->setName("interface.language");
        $int_lang->setLabel("Default language");
        $int_lang->setDesc("Set the language used when neither the user or the browser specifies a preference");
        $langs=language::getAll();
        foreach ($langs as $iso=>$lang) {
            $int_lang->addOption($iso, $lang->name);
        }
        $int_lang->setDefault("en");
        $interface[]=$int_lang;
        
        $users=user::getAll();
        
        $int_user_default = new confItemSelect();
        $int_user_default->setName("interface.user.default");
        $int_user_default->setLabel("Default user");
        $int_user_default->setDesc("Automatically log on as this user when not logged on. Can be used to give people access without a username and password. This user should be a non-admin user and should not have any change permissions.");
        $int_user_default->addOption(0, "Disabled");
        foreach ($users as $usr) {
            if(!$usr->is_admin()) {
                $int_user_default->addOption($usr->getId(), $usr->getName());
            }
        }
        $int_user_default->setDefault(0);
        $interface[]=$int_user_default;

        $int_user_cli = new confItemSelect();
        $int_user_cli->setName("interface.user.cli");
        $int_user_cli->setLabel("CLI user");
        $int_user_cli->setDesc("This is the Zoph user that is used when using the CLI interface when interacting with Zoph. You should set it to the user_id of a valid Zoph user. This user must be an admin user. You can also set it to \"autodetect\", which means Zoph will lookup the name of the Unix user starting the CLI client and tries to find that user's name in the Zoph database.");
        $int_user_cli->addOption(0, "Autodetect");
        foreach ($users as $usr) {
            if($usr->is_admin()) {
                $int_user_cli->addOption($usr->getId(), $usr->getName());
            }
        }
        $int_user_cli->setDefault(0);
        $interface[]=$int_user_cli;


        /************************** SSL **************************/
        $ssl = self::addGroup("ssl", "SSL");

        $ssl_force = new confItemSelect();
        $ssl_force->setName("ssl.force");
        $ssl_force->setLabel("Force SSL");
        $ssl_force->setDesc("Force users to use https when using Zoph. When connecting to Zoph using http, the user will automatically be redirected to the same URL, but with https. If choosing \"login only\", the user will be redirected back to http after logging in. If your https-site is hosted on a different URL, you will need to define the correct url below.");
        $ssl_force->addOption("never", "Never");
        $ssl_force->addOption("always", "Always");
        $ssl_force->addOption("login", "Login only");
        $ssl_force->setDefault("never");
        $ssl[]=$ssl_force;

        /************************** URL **************************/
        $url = self::addGroup("url", "URLs");

        $url_http = new confItemString();
        $url_http->setName("url.http");
        $url_http->setLabel("Zoph's URL");
        $url_http->setDesc("Override autodetection of Zoph's URL, for example if you use a domainname to access Zoph but get redirected to a different URL.");
        $url_http->setDefault("");
        $url_http->setRegex("(^$|^https?:\/\/[^\s\/$.?#].[^\s]*$)"); // Stolen from http://mathiasbynens.be/demo/url-regex, @stephenhay
        $url[]=$url_http;

        $url_https = new confItemString();
        $url_https->setName("url.https");
        $url_https->setLabel("Zoph's Secure URL");
        $url_https->setDesc("Override autodetection of Zoph's Secure URL (https).");
        $url_https->setDefault("");
        $url_https->setRegex("(^$|^https:\/\/[^\s\/$.?#].[^\s]*$)"); // Stolen from http://mathiasbynens.be/demo/url-regex, @stephenhay
        $url[]=$url_https;

        /************************** PATH **************************/
        $path = self::addGroup("path", "File and directory locations");
        

        $path_images = new confItemString();
        $path_images->setName("path.images");
        $path_images->setLabel("Images directory");
        $path_images->setDesc("Location of the images on the filesystem. Absolute path, thus starting with a /");
        $path_images->setDefault("/data/images");
        $path_images->setRegex("^\/[A-Za-z0-9_\.\/]+$");
        $path_images->setTitle("Alphanumeric characters (A-Z, a-z and 0-9), forward slash (/), dot (.), and underscore (_). Must start with a /");
        $path_images->setRequired();
        $path[]=$path_images;

        $path_upload = new confItemString();
        $path_upload->setName("path.upload");
        $path_upload->setLabel("Upload dir");
        $path_upload->setDesc("Directory where uploaded files are stored and from where files are imported in Zoph. This is a directory under the images directorty (above). For example, if the images directory is set to /data/images and this is set to upload, photos will be uploaded to /data/images/upload.");
        $path_upload->setDefault("upload");
        $path_upload->setRegex("^[A-Za-z0-9_]+[A-Za-z0-9_\.\/]*$");
        $path_upload->setTitle("Alphanumeric characters (A-Z, a-z and 0-9), forward slash (/), dot (.), and underscore (_). Can not start with a dot or a slash");
        $path[]=$path_upload;
        
        $path_magic = new confItemString();
        $path_magic->setName("path.magic");
        $path_magic->setLabel("Magic file");
        $path_magic->setDesc("Zoph needs a MIME Magic file to be able to determine the filetype of an uploaded file. This is an important security measure, since it prevents users from uploading files other than images and archives. If left empty, PHP will use the built-in Magic file, if for some reason this does not work, you can specify the location of the MIME magic file. Where this file is located, depends on your distribution, /usr/share/misc/magic.mgc, /usr/share/misc/file/magic.mgc, /usr/share/file/magic are often used.");
        $path_magic->setDefault("");
        $path_magic->setRegex("^(\/[A-Za-z0-9_\.\/]+|)$");
        $path_magic->setTitle("Alphanumeric characters (A-Z, a-z and 0-9), forward slash (/), dot (.), and underscore (_). Must start with a /. Can be empty for PHP builtin magix file.");
        $path[]=$path_magic;

        /************************** MAPS **************************/
        $maps = self::addGroup("maps", "Mapping support");

        $maps_provider = new confItemSelect();
        $maps_provider->setName("maps.provider");
        $maps_provider->setDesc("Enable or disable mapping support and choose the mapping provider");
        $maps_provider->setLabel("Mapping provider");
        $maps_provider->addOption("", "Disabled");
        $maps_provider->addOption("googlev3", "Google Maps v3");
        $maps_provider->addOption("yahoo", "Yahoo maps");
        $maps_provider->addOption("cloudmade", "Cloudmade (OpenStreetMap)");
        $maps_provider->addOption("openlayers", "OpenLayers (OpenStreetMap)");
        $maps_provider->setDefault("");
        $maps[]=$maps_provider;

        $maps_geocode = new confItemSelect();
        $maps_geocode->setName("maps.geocode");
        $maps_geocode->setLabel("Geocode provider");
        $maps_geocode->setDesc("With geocoding you can lookup the location of a place from it's name. Here you can select the provider. Currently the only one available is 'geonames'");
        $maps_geocode->addOption("", "Disabled");
        $maps_geocode->addOption("geonames", "GeoNames");
        $maps_geocode->setDefault("");
        $maps[]=$maps_geocode;

        $maps_key_cloudmade = new confItemString();
        $maps_key_cloudmade->setName("maps.key.cloudmade");
        $maps_key_cloudmade->setLabel("Cloudmade Key");
        $maps_key_cloudmade->setDesc("API key for Cloudmade Maps. Only needed if using \"Cloudmade\" as provider. You can use Zoph's key (which is the default), but please do not use this key for any other applications..");
        $maps_key_cloudmade->setRegex("(^$|[a-z0-9]{32})"); 
        $maps_key_cloudmade->setDefault("f3b46b04edd64ea79066b7e6921205df");
        $maps[]=$maps_key_cloudmade;


        
        /************************** IMPORT **************************/
        $import = self::addGroup("import", "Importing and uploading photos");

        $import_enable = new confItemBool();
        $import_enable->setName("import.enable");
        $import_enable->setLabel("Import through webinterface");
        $import_enable->setDesc("Use this option to enable or disable importing using the webbrowser. With this option enabled, an admin user, or a user with import rights, can import files placed in the import directory (below) into Zoph. If you want users to be able to upload as well, you need to enable uploading as well.");
        $import_enable->setDefault(false);
        $import[]=$import_enable;

        $import_upload = new confItemBool();
        $import_upload->setName("import.upload");
        $import_upload->setLabel("Upload through webinterface");
        $import_upload->setDesc("Use this option to enable or disable uploading files. With this option enabled, an admin user, or a user with import rights, can upload files to the server running Zoph, they will be placed in the import directory (below). This option requires \"import through web interface\" (above) enabled.");
        $import_upload->setDefault(false);
        $import[]=$import_upload;


        $import_maxupload = new confItemString(); 
        $import_maxupload->setName("import.maxupload");
        $import_maxupload->setLabel("Maximum filesize");
        $import_maxupload->setDesc("Maximum size of uploaded file in bytes. You might also need to change upload_max_filesize, post_max_size and possibly max_execution_time and max_input_time in php.ini.");
        $import_maxupload->setRegex("^[0-9]+$");
        $import_maxupload->setDefault("10000000");
        $import[]=$import_maxupload;
        
        $import_parallel = new confItemString(); 
        $import_parallel->setName("import.parallel");
        $import_parallel->setLabel("Resize parallel");
        $import_parallel->setDesc("Photos will be resized to thumbnail and midsize images during import, this setting determines how many resize actions run in parallel. Can be set to any number. Don't change this, unless you have a fast server with multiple CPU's or cores.");
        $import_parallel->setRegex("^[0-9]+$");
        $import_parallel->setDefault("1");
        $import[]=$import_parallel;

        $import_rotate = new confItemBool();
        $import_rotate->setName("import.rotate");
        $import_rotate->setLabel("Rotate images");
        $import_rotate->setDesc("Automatically rotate imported images, requires jhead");
        $import_rotate->setDefault(false);
        $import[]=$import_rotate;

        $import_resize = new confItemSelect();
        $import_resize->setName("import.resize");
        $import_resize->setLabel("Resize method");
        $import_resize->setDesc("Determines how to resize an image during import. Resize can be about 3 times faster than resample, but the resized image has a lower quality.");
        $import_resize->addOption("resize", "Resize (lower quality / low CPU / fast)");
        $import_resize->addOption("resample", "Resample (high quality / high CPU / slow)");
        $import_resize->setDefault("resample");
        $import[]=$import_resize;

        $import_dated = new confItemBool();
        $import_dated->setName("import.dated");
        $import_dated->setLabel("Dated dirs");
        $import_dated->setDesc("Automatically place photos in dated dirs (\"2012.10.16/\") during import");
        $import_dated->setDefault(false);
        $import[]=$import_dated;

        $import_dated_hier = new confItemBool();
        $import_dated_hier->setName("import.dated.hier");
        $import_dated_hier->setLabel("Hierarchical dated dirs");
        $import_dated_hier->setDesc("Automatically place photos in a dated directory tree (\"2012/10/16/\") during import. Ignored unless \"Dated dirs\" is also enabled");
        $import_dated_hier->setDefault(false);
        $import[]=$import_dated_hier;

        /************************** WATERMARK **************************/
        $wm = self::addGroup("watermark", "Watermarking");

        $wm_enable = new confItemBool();
        $wm_enable->setName("watermark.enable");
        $wm_enable->setLabel("Enable Watermarking");
        $wm_enable->setDesc("Watermarking can display a (copyright) watermark over your full-size images. Watermarking only works the watermark file below is set to an existing GIF image. Please note that enabling this function uses a rather large amount of memory on the webserver. PHP by default allows a script to use a maximum of 8MB memory. You should probably increase this by changing memory_limit in php.ini. A rough estimation of how much memory it will use is 6 times the number of megapixels in your camera. For example, if you have a 5 megapixel camera, change the line in php.ini to memory_limit=30M");
        $wm_enable->setDefault(false);
        $wm[]=$wm_enable;

        /** @todo: should allow .png too */
        $wm_file = new confItemString();
        $wm_file->setName("watermark.file");
        $wm_file->setLabel("Watermark file");
        $wm_file->setDesc("If watermarking is used, this should be set to the name of the file that will be used as the watermark. It should be a GIF file, for best results, use contrasting colours and transparency. In the Contrib directory, 3 example files are included. The filename is relative to the image directory, defined above.");
        $wm_file->setDefault("");
        $wm_file->setRegex("(^$|^[A-Za-z0-9_]+[A-Za-z0-9_\.\/]*\.gif$)");
        $wm_file->setTitle("Alphanumeric characters (A-Z, a-z and 0-9), forward slash (/), dot (.), and underscore (_). Can not start with a dot or a slash");
        $wm[]=$wm_file;

        $wm_pos_x = new confItemSelect();
        $wm_pos_x->setName("watermark.pos.x");
        $wm_pos_x->setLabel("Horizontal position");
        $wm_pos_x->setDesc("Define where the watermark will be placed horizontally.");
        $wm_pos_x->addOptions(array(
            "left" => "Left",
            "center" => "Center",
            "right" => "Right"
        ));
        $wm_pos_x->setDefault("center");
        $wm[]=$wm_pos_x;

        $wm_pos_y = new confItemSelect();
        $wm_pos_y->setName("watermark.pos.y");
        $wm_pos_y->setLabel("Horizontal position");
        $wm_pos_y->setDesc("Define where the watermark will be placed vertically.");
        $wm_pos_y->addOptions(array(
            "top" => "Top",
            "center" => "Center",
            "bottom" => "Bottom"
        ));
        $wm_pos_y->setDefault("center");
        $wm[]=$wm_pos_y;
        
        $wm_trans = new confItemString();
        $wm_trans->setName("watermark.transparency");
        $wm_trans->setLabel("Watermark transparency");
        $wm_trans->setDesc("Define the transparency of a watermark. 0: fully tranparent (invisible, don't use this, it's pointless and eats up a lot of resources, better turn off the watermark feature altogether) to 100: no transparency.");
        $wm_trans->setDefault("50");
        $wm_trans->setRegex("^(100|[0-9]{1,2})$");
        $wm[]=$wm_trans;


        /*********************** ROTATIONS ************************/

        $rt = self::addGroup("rotate", "Rotation");

        $rt_enable = new confItemBool();
        $rt_enable->setName("rotate.enable");
        $rt_enable->setLabel("Rotation");
        $rt_enable->setDesc("Allow users (admins or with write access) to rotate images");
        $rt_enable->setDefault(false);
        $rt[]=$rt_enable;

        $rt_command = new confItemSelect();
        $rt_command->setName("rotate.command");
        $rt_command->setLabel("Rotate command");
        $rt_command->setDesc("Determine which command is used to rotate the image. This command must be available on your system. Convert is a lossy rotate function, which means it will lower the image quality of your photo. JPEGtran, on the other hand, only works on JPEG images, but is lossless.");
        $rt_command->addOptions(array(
            "convert" => "convert",
            "jpegtran" => "jpegtran"
        ));
        $rt_command->setDefault("convert");
        $rt[]=$rt_command;
        
        $rt_backup = new confItemBool();
        $rt_backup->setName("rotate.backup");
        $rt_backup->setLabel("Backup");
        $rt_backup->setDesc("Keep a backup image when rotating an image.");
        $rt_backup->setDefault(true);
        $rt[]=$rt_backup;

        $rt_backup_prefix = new confItemString();
        $rt_backup_prefix->setName("rotate.backup.prefix");
        $rt_backup_prefix->setLabel("Backup prefix");
        $rt_backup_prefix->setDesc("Prepend backup file for rotation backups with this.");
        $rt_backup_prefix->setDefault("orig_");
        $rt_backup_prefix->setRegex("^[a-zA-Z0-9_\-]+$");
        $rt_backup_prefix->setRequired();
        $rt[]=$rt_backup_prefix;




        

        /*********************** FEATURES *************************/

        $ft = self::addGroup("feature", "Features");

        $ft_download = new confItemBool();
        $ft_download->setName("feature.download");
        $ft_download->setLabel("Downloading");
        $ft_download->setDesc("With this feature you can use download a set of photos (Albums, Categories, Places, People or a search result) in one or more ZIP files. Important! The photos in the ZIP file will NOT be watermarked. You must also grant each non-admin user you want to give these rights permission by changing \"can download zipfiles\" in the user's profile.");
        $ft_download->setDefault(false);
        $ft[]=$ft_download;

        $ft_comments = new confItemBool(); 
        $ft_comments->setName("feature.comments");
        $ft_comments->setLabel("Comments");
        $ft_comments->setDesc("Enable comments. Before a user can actually leave comments, you should also give the user these rights through the edit user screen.");
        $ft_comments->setDefault(false);
        $ft[]=$ft_comments;

        $ft_mail = new confItemBool(); 
        $ft_mail->setName("feature.mail");
        $ft_mail->setLabel("Mail photos");
        $ft_mail->setDesc("You can enable or disable the \"mail this photo feature\" using this option. Since Zoph needs to convert the photo into Base64 encoding for mail, it requires quite a large amount of memory if you try to send full size images and you may need to adjust memory_limit in php.ini, you should give it at least about 4 times the size of your largest image.");
        $ft_mail->setDefault(false);
        $ft[]=$ft_mail;

        $ft_mail_bcc = new confItemString();
        $ft_mail_bcc->setName("feature.mail.bcc");
        $ft_mail_bcc->setLabel("BCC address");
        $ft_mail_bcc->setDesc("Automatically Blind Carbon Copy this mailaddress when a mail from Zoph is sent");
        $ft_mail_bcc->setDefault("");
        $ft_mail_bcc->setRegex("^([0-9a-zA-Z_\-%\.]+@([0-9a-zA-Z\-]+\.)+[a-zA-Z]{2,10})?$"); // not sure how long the "new" TLD's are going to be, 
                                                                                             // 10 should be enough for most, feel free to report 
                                                                                             // a bug if your TLD is longer.
        $ft[]=$ft_mail_bcc;
        
        $ft_annotate = new confItemBool(); 
        $ft_annotate->setName("feature.annotate");
        $ft_annotate->setLabel("Annotate photos");
        $ft_annotate->setDesc("A user can use the annotate photo function to e-mail a photo with a textual annotation. Can only be used in combination with the \"Mail photos\" feature above.");
        $ft_annotate->setDefault(false);
        $ft[]=$ft_annotate;

        $ft_rating = new confItemBool(); 
        $ft_rating->setName("feature.rating");
        $ft_rating->setLabel("Photo rating");
        $ft_rating->setDesc("Allow users to rate photos. Before a non-admin user can actually rate, you should also give the user these rights through the edit user screen.");
        $ft_rating->setDefault(true);
        $ft[]=$ft_rating;


        /************************** DATE **************************/
        $date = self::addGroup("date", "Date and time");

        $date_tz = new confItemSelect();
        $date_tz->setName("date.tz");
        $date_tz->setLabel("Timezone");
        $date_tz->setDesc("This setting determines the timezone to which your camera is set. Leave empty if you do not want to use this feature and always set your camera to the local timezone");

        $date_tz->addOptions(TimeZone::getTzArray());
        $date_tz->setDefault("");

        $date[]=$date_tz;
        
        $date_guesstz = new confItemBool();
        $date_guesstz->setName("date.guesstz");
        $date_guesstz->setLabel("Guess timezone");
        $date_guesstz->setDesc("If you have defined the precise location of a place (using the mapping feature), Zoph can 'guess' the timezone based on this location. It uses the Geonames project for this. This will, however, send information to their webserver, do not enable this feature if you're not comfortable with that.");
        $date_guesstz->setDefault(false);
        $date[]=$date_guesstz;

    }
}

