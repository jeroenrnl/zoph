<?php
/**
 * This class defines the configuration options Zoph has and their default settings
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

/**
 * confDefault is the class that defines config options & their defaults
 * in the database
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class confDefault extends conf {

    protected static function getConfig() {
        $interface=static::getConfigInterface();
        static::getConfigInterfaceUser($interface);
        static::getConfigSSL();
        static::getConfigURL();
        static::getConfigPath();
        static::getConfigMaps();
        $import=static::getConfigImport();
        $import=static::getConfigImportFileMode($import);
        static::getConfigImportCLI($import);
        static::getConfigWatermark();
        static::getConfigRotate();
        static::getConfigShare();
        static::getConfigFeature();
        static::getConfigDate();
    }

    /**
     * Get config group for interface settings
     */
    private static function getConfigInterface() {
        $interface = conf::addGroup("interface", "Interface settings",
            "Settings that define how Zoph looks");

        $intTitle = new confItemString();
        $intTitle->setName("interface.title");
        $intTitle->setLabel("Title");
        $intTitle->setDesc("The title for the application. This is what appears " .
            "on the home page and in the browser's title bar.");
        $intTitle->setDefault("Zoph");
        $intTitle->setRegex("^.*$");
        $interface[]=$intTitle;

        $intWidth = new confItemString();
        $intWidth->setName("interface.width");
        $intWidth->setLabel("Screen width");
        $intWidth->setDesc("A number in pixels (\"px\") or percent (\"%\"), the latter " .
            "is a percentage of the user's browser window width.");
        $intWidth->setDefault("800px");
        $intWidth->setRegex("^[0-9]+(px|%)$");
        $interface[]=$intWidth;

        $intTpl = new confItemSelect();
        $intTpl->setName("interface.template");
        $intTpl->setLabel("Template");
        $intTpl->setDesc("The template Zoph uses");
        $intTpl->addOptions(template::getAll());
        $intTpl->setDefault("default");
        $interface[]=$intTpl;

        $intAutoc = new confItemBool();
        $intAutoc->setName("interface.autocomplete");
        $intAutoc->setLabel("Autocomplete");
        $intAutoc->setDesc("Use autocompletion for selection of albums, categories, " .
            "places and people instead of standard HTML selectboxes. Can be individually " .
            "switched off from user preferences.");
        $intAutoc->setDefault(true);
        $interface[]=$intAutoc;

        $intLang = new confItemSelect();
        $intLang->setName("interface.language");
        $intLang->setLabel("Default language");
        $intLang->setDesc("Set the language used when neither the user or the browser " .
            "specifies a preference");
        $langs=language::getAll();
        foreach ($langs as $iso => $lang) {
            $intLang->addOption($iso, $lang->name);
        }
        $intLang->setDefault("en");
        $interface[]=$intLang;

        $intMaxDays = new confItemNumber();
        $intMaxDays->setName("interface.max.days");
        $intMaxDays->setLabel("Maximum days");
        $intMaxDays->setDesc("The maximum days Zoph displays in a dropdown box for 'photos " .
            "changed / made in the past ... days' on the search screen");
        $intMaxDays->setDefault("30");
        $intMaxDays->setRegex("^[1-9][0-9]{0,2}$");
        $intMaxDays->setBounds(0, 365, 1);
        $interface[]=$intMaxDays;

        $intSortOrder = new confItemSelect();
        $intSortOrder->setName("interface.sort.order");
        $intSortOrder->setLabel("Default sort order");
        $intSortOrder->setDesc("Default sort order of photos");
        $intSortOrder->addOptions(photo::getFields());
        $intSortOrder->setDefault("date");
        $interface[]=$intSortOrder;

        $intSortDir = new confItemSelect();
        $intSortDir->setName("interface.sort.dir");
        $intSortDir->setLabel("Default sort direction");
        $intSortDir->setDesc("Default sort order of photos, ascending or descending");
        $intSortDir->addOption("asc", "Ascending");
        $intSortDir->addOption("desc", "Descending");
        $intSortDir->setDefault("asc");
        $interface[]=$intSortDir;

        $intLogonBgAlbum = new confItemSelect();
        $intLogonBgAlbum->setName("interface.logon.background.album");
        $intLogonBgAlbum->setLabel("Logon screen background album");
        $intLogonBgAlbum->setDesc("Select an album from which a random photo is chosen as a " .
            "background for the logon screen");
        $intLogonBgAlbum->addOptions(album::getSelectArray());
        $intLogonBgAlbum->setOptionsTranslate(false);
        $intLogonBgAlbum->setDefault(null);
        $intLogonBgAlbum->requiresEnabled(new confItemBool("share.enable"));

        $interface[]=$intLogonBgAlbum;

        $intCookieExpire = new confItemSelect();
        $intCookieExpire->setName("interface.cookie.expire");
        $intCookieExpire->setLabel("Cookie Expiry Time");
        $intCookieExpire->setDesc("Set the time after which a cookie will expire, that is, " .
            "when a user will need to re-login. \"session\" (default) means: until user " .
            "closes the browser");
        $intCookieExpire->addOptions(array(
            0       => "session",
            3600    => "1 hour",
            14400   => "4 hours",
            28800   => "8 hours",
            86400   => "1 day",
            604800  => "1 week",
            2592300 => "1 month"
        ));
        $intCookieExpire->setDefault(0);
        $interface[]=$intCookieExpire;


        return $interface;
    }

    /**
     * Get config group for interface.user settings
     */
    private static function getConfigInterfaceUser(confGroup $interface) {
        $users=user::getAll();

        $intUserDefault = new confItemSelect();
        $intUserDefault->setName("interface.user.default");
        $intUserDefault->setLabel("Default user");
        $intUserDefault->setDesc("Automatically log on as this user when not logged " .
            "on. Can be used to give people access without a username and password. " .
            "This user should be a non-admin user and should not have any change " .
            "permissions.");
        $intUserDefault->addOption(0, "Disabled");
        foreach ($users as $usr) {
            if (!$usr->isAdmin()) {
                $intUserDefault->addOption($usr->getId(), $usr->getName());
            }
        }
        $intUserDefault->setDefault(0);
        $interface[]=$intUserDefault;

        $intUserCli = new confItemSelect();
        $intUserCli->setName("interface.user.cli");
        $intUserCli->setLabel("CLI user");
        $intUserCli->setDesc("This is the Zoph user that is used when using the CLI " .
            "interface when interacting with Zoph. This user must be an admin user. " .
            "You can also set it to \"autodetect\", which means Zoph will lookup the " .
            "name of the Unix user starting the CLI client and tries to find that user's " .
            "name in the Zoph database.");
        $intUserCli->addOption(0, "Autodetect");
        foreach ($users as $usr) {
            if ($usr->isAdmin()) {
                $intUserCli->addOption($usr->getId(), $usr->getName());
            }
        }
        $intUserCli->setDefault(0);
        $interface[]=$intUserCli;
    }

    /**
     * Get config group for SSL settings
     */
    private static function getConfigSSL() {
        $ssl = conf::addGroup("ssl", "SSL", "Protect your site against eavesdropping by " .
            "using https. You will need to configure this in your webserver as well.");

        $sslForce = new confItemSelect();
        $sslForce->setName("ssl.force");
        $sslForce->setLabel("Force SSL");
        $sslForce->setDesc("Force users to use https when using Zoph. When connecting " .
            "to Zoph using http, the user will automatically be redirected to the same " .
            "URL, but with https. When choosing \"login only\", the user will be " .
            "redirected back to http after logging in. If your https-site is hosted on " .
            "a different URL, you will need to define the correct url below.");
        $sslForce->addOption("never", "Never");
        $sslForce->addOption("always", "Always");
        $sslForce->addOption("login", "Login only");
        $sslForce->setDefault("never");
        $ssl[]=$sslForce;
    }

    /**
     * Get config group for url settings
     */
    private static function getConfigURL() {
        $url = conf::addGroup("url", "URLs", "Define the URLs that are used to access " .
            "Zoph. Only configure this if Zoph cannot determine it automatically.");

        $urlHttp = new confItemString();
        $urlHttp->setName("url.http");
        $urlHttp->setLabel("Zoph's URL");
        $urlHttp->setDesc("Override autodetection of Zoph's URL, for example if you " .
            "use a domainname to access Zoph but get redirected to a different URL.");
        $urlHttp->setDefault("");
        // This regex was stolen from http://mathiasbynens.be/demo/url-regex, @stephenhay
        $urlHttp->setRegex("(^$|^https?:\/\/[^\s\/$.?#].[^\s]*$)");
        $url[]=$urlHttp;

        $urlHttps = new confItemString();
        $urlHttps->setName("url.https");
        $urlHttps->setLabel("Zoph's Secure URL");
        $urlHttps->setDesc("Override autodetection of Zoph's Secure URL (https).");
        $urlHttps->setDefault("");
        // This regex was stolen from http://mathiasbynens.be/demo/url-regex, @stephenhay
        $urlHttps->setRegex("(^$|^https:\/\/[^\s\/$.?#].[^\s]*$)");
        $url[]=$urlHttps;
    }

    /**
     * Get config group for Path settings
     */
    private static function getConfigPath() {
        $path = conf::addGroup("path", "Paths", "File and directory locations");


        $pathImages = new confItemString();
        $pathImages->setName("path.images");
        $pathImages->setLabel("Images directory");
        $pathImages->setDesc("Location of the images on the filesystem. Absolute path, " .
            " thus starting with a /");
        $pathImages->setDefault("/data/images");
        $pathImages->setRegex("^\/[A-Za-z0-9_.\/]+$");
        $pathImages->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), and underscore (_). Must start with a /");
        $pathImages->setRequired();
        $path[]=$pathImages;

        $pathUpload = new confItemString();
        $pathUpload->setName("path.upload");
        $pathUpload->setLabel("Upload dir");
        $pathUpload->setDesc("Directory where uploaded files are stored and from where " .
            "files are imported in Zoph. This is a directory under the images directory " .
            "(above). For example, if the images directory is set to /data/images and " .
            "this is set to upload, photos will be uploaded to /data/images/upload.");
        $pathUpload->setDefault("upload");
        $pathUpload->setRegex("^[A-Za-z0-9_]+[A-Za-z0-9_.\/]*$");
        $pathUpload->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), and underscore (_). Can not start with a dot or a slash");
        $path[]=$pathUpload;

        $pathTrash = new confItemString();
        $pathTrash->setName("path.trash");
        $pathTrash->setLabel("Trash dir");
        $pathTrash->setDesc("Directory where photos are moved when they are " .
            "deleted. If left blank, files will remain where they were. This is a directory " .
            "under the images directory (above). For example, if the images directory is set to " .
            "/data/images and this is set to trash, photos will be moved to /data/images/trash.");
        $pathTrash->setDefault("");
        $pathTrash->setRegex("^[A-Za-z0-9_]*[A-Za-z0-9_.\/]*$");
        $pathTrash->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), and underscore (_). Can not start with a dot or a slash");
        $path[]=$pathTrash;

        $pathMagic = new confItemString();
        $pathMagic->setName("path.magic");
        $pathMagic->setLabel("Magic file");
        $pathMagic->setDesc("Zoph needs a MIME Magic file to be able to determine the " .
            "filetype of an uploaded file. This is an important security measure, since " .
            "it prevents users from uploading files other than images and archives. If " .
            "left empty, PHP will use the built-in Magic file, if for some reason this " .
            "does not work, you can specify the location of the MIME magic file. Where " .
            "this file is located, depends on your distribution, " .
            "/usr/share/misc/magic.mgc, /usr/share/misc/file/magic.mgc, " .
            "/usr/share/file/magic are often used.");
        $pathMagic->setDefault("");
        $pathMagic->setRegex("^\/[A-Za-z0-9_.\/]+$");
        $pathMagic->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), and underscore (_). Must start with a /. Can be " .
            "empty for PHP builtin magic file.");
        $path[]=$pathMagic;

        $pathUnzip = new confItemString();
        $pathUnzip->setName("path.unzip");
        $pathUnzip->setLabel("Unzip command");
        $pathUnzip->setDesc("The command to use to unzip gzip files. Leave empty to " .
            "disable uploading .gz files. On most systems \"unzip\" will work.");
        $pathUnzip->setDefault("");
        $pathUnzip->setRegex("^([A-Za-z0-9_.\/ -]+|)$");
        $pathUnzip->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), underscore (_), dash (-) and space. Can be empty to disable");
        $path[]=$pathUnzip;

        $pathUntar = new confItemString();
        $pathUntar->setName("path.untar");
        $pathUntar->setLabel("Untar command");
        $pathUntar->setDesc("The command to use to untar tar files. Leave empty to disable " .
            "uploading .tar files. On most systems \"tar xvf\" will work.");
        $pathUntar->setDefault("");
        $pathUntar->setRegex("^([A-Za-z0-9_.\/ ]+|)$");
        $pathUntar->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), underscore (_), dash (-) and space. Can be empty to disable");
        $path[]=$pathUntar;

        $pathUngz = new confItemString();
        $pathUngz->setName("path.ungz");
        $pathUngz->setLabel("Ungzip command");
        $pathUngz->setDesc("The command to use to unzip gzip files. Leave empty to disable " .
            "uploading .gz files. On most systems \"gunzip\" will work.");
        $pathUngz->setDefault("");
        $pathUngz->setRegex("^([A-Za-z0-9_.\/ ]+|)$");
        $pathUngz->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), underscore (_), dash (-) and space. Can be empty to disable");
        $path[]=$pathUngz;

        $pathUnbz = new confItemString();
        $pathUnbz->setName("path.unbz");
        $pathUnbz->setLabel("Unbzip command");
        $pathUnbz->setDesc("The command to use to unzip bzip files. Leave empty to disable " .
            "uploading .bz files. On most systems \"bunzip2\" will work.");
        $pathUnbz->setDefault("");
        $pathUnbz->setRegex("^([A-Za-z0-9_.\/ ]+|)$");
        $pathUnbz->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward " .
            "slash (/), dot (.), underscore (_), dash (-) and space. Can be empty to disable");
        $path[]=$pathUnbz;
    }

    /**
     * Get config group for maps settings
     */
    private static function getConfigMaps() {
        $maps = conf::addGroup("maps", "Mapping support",
            "Add maps to Zoph using various different mapping providers.");

        $mapsProvider = new confItemSelect();
        $mapsProvider->setName("maps.provider");
        $mapsProvider->setDesc("Enable or disable mapping support and choose the " .
            "mapping provider");
        $mapsProvider->setLabel("Mapping provider");
        $mapsProvider->addOption("", "Disabled");
        $mapsProvider->addOption("googlev3", "Google Maps v3");
        $mapsProvider->addOption("mapbox", "Mapbox (OpenStreetMap)");
        $mapsProvider->addOption("osm", "OpenStreetMap");
        $mapsProvider->setDefault("");
        $maps[]=$mapsProvider;

        $mapsMapBoxAPIKey = new confItemString();
        $mapsMapBoxAPIKey->setName("maps.mapbox.apikey");
        $mapsMapBoxAPIKey->setDesc("API key to use to access MapBox. The default is Zoph's API key, please do not use it in other projects. If you are setting up a high-volume site, please consider requesting your own key");
        $mapsMapBoxAPIKey->setDefault("pk.eyJ1IjoiamVyb2Vucm5sIiwiYSI6ImNpdmh6dnlsazAwYWUydXBrbG50cHhlbmMifQ.0pSkJxO6ycD2Wg5GL4yYyw");
        $mapsMapBoxAPIKey->setRegex("^[0-9a-zA-Z\.]+$");
        $maps[]=$mapsMapBoxAPIKey;

        $mapsGeocode = new confItemSelect();
        $mapsGeocode->setName("maps.geocode");
        $mapsGeocode->setLabel("Geocode provider");
        $mapsGeocode->setDesc("With geocoding you can lookup the location of a " .
            "place from it's name. Here you can select the provider. Currently " .
            "the only one available is 'geonames'");
        $mapsGeocode->addOption("", "Disabled");
        $mapsGeocode->addOption("geonames", "GeoNames");
        $mapsGeocode->setDefault("");
        $maps[]=$mapsGeocode;
    }

    /**
     * Get config group for import settings
     */
    private static function getConfigImport() {
        $import = conf::addGroup("import", "Import", "Importing and uploading photos");

        $importEnable = new confItemBool();
        $importEnable->setName("import.enable");
        $importEnable->setLabel("Import through webinterface");
        $importEnable->setDesc("Use this option to enable or disable importing using " .
            "the webbrowser. With this option enabled, an admin user, or a user with " .
            "import rights, can import files placed in the import directory (below) " .
            "into Zoph. If you want users to be able to upload as well, you need to " .
            "enable uploading as well.");
        $importEnable->setDefault(false);
        $import[]=$importEnable;

        $importUpload = new confItemBool();
        $importUpload->setName("import.upload");
        $importUpload->setLabel("Upload through webinterface");
        $importUpload->setDesc("Use this option to enable or disable uploading files. " .
            "With this option enabled, an admin user, or a user with import rights, " .
            "can upload files to the server running Zoph, they will be placed in the " .
            "import directory (below). This option requires \"import through web " .
            "interface\" (above) enabled.");
        $importUpload->setDefault(false);
        $import[]=$importUpload;

        $importMaxupload = new confItemNumber();
        $importMaxupload->setName("import.maxupload");
        $importMaxupload->setLabel("Maximum filesize");
        $importMaxupload->setDesc("Maximum size of uploaded file in bytes. You might " .
            "also need to change upload_max_filesize, post_max_size and possibly" .
            "max_execution_time and max_input_time in php.ini.");
        $importMaxupload->setRegex("^[0-9]+$");
        $importMaxupload->setDefault("10000000");
        $importMaxupload->setBounds(0, 1000000000, 1); // max = 1GB
        $import[]=$importMaxupload;

        $importParallel = new confItemNumber();
        $importParallel->setName("import.parallel");
        $importParallel->setLabel("Resize parallel");
        $importParallel->setDesc("Photos will be resized to thumbnail and midsize " .
            "images during import, this setting determines how many resize actions run " .
            "in parallel. Can be set to any number. If you have a fast server with " .
            "multiple CPU's or cores, you can increase this for faster response on " .
            "the import page.");
        $importParallel->setRegex("^[0-9]+$");
        $importParallel->setBounds(1, 99, 1);
        $importParallel->setDefault("1");
        $import[]=$importParallel;

        $importRotate = new confItemBool();
        $importRotate->setName("import.rotate");
        $importRotate->setLabel("Rotate images");
        $importRotate->setDesc("Automatically rotate imported images, requires jhead");
        $importRotate->setDefault(false);
        $import[]=$importRotate;

        $importResize = new confItemSelect();
        $importResize->setName("import.resize");
        $importResize->setLabel("Resize method");
        $importResize->setDesc("Determines how to resize an image during import. " .
            "Resize can be about 3 times faster than resample, but the resized image " .
            "has a lower quality.");
        $importResize->addOption("resize", "Resize (lower quality / low CPU / fast)");
        $importResize->addOption("resample", "Resample (high quality / high CPU / slow)");
        $importResize->setDefault("resample");
        $import[]=$importResize;

        $importDated = new confItemBool();
        $importDated->setName("import.dated");
        $importDated->setLabel("Dated dirs");
        $importDated->setDesc("Automatically place photos in dated dirs " .
            "(\"2012.10.16/\") during import");
        $importDated->setDefault(false);
        $import[]=$importDated;

        $importDatedHier = new confItemBool();
        $importDatedHier->setName("import.dated.hier");
        $importDatedHier->setLabel("Hierarchical dated dirs");
        $importDatedHier->setDesc("Automatically place photos in a dated directory " .
            "tree (\"2012/10/16/\") during import. Ignored unless \"Dated dirs\" is " .
            "also enabled");
        $importDatedHier->setDefault(false);
        $import[]=$importDatedHier;
        return $import;
    }

    /**
     * Get config group for import file/dir mode settings
     */
    private static function getConfigImportFileMode(confGroup $import) {

        /**
         * @todo This requires octdec to be run before using it so use
         * octdec(conf::get("import.filemode")) or you will get "funny" results
         */
        $importFilemode = new confItemSelect();
        $importFilemode->setName("import.filemode");
        $importFilemode->setLabel("File mode");
        $importFilemode->setDesc("File mode for the files that are imported in Zoph. " .
            "Determines who can read or write the files. (RW: Read/Write, RO: Read Only)");
        $importFilemode->addOptions(array(
            "0644" => "RW for user, RO for others (0644)",
            "0664" => "RW for user/group, RO for others (0664)",
            "0666" => "RW for everyone (0666)",
            "0660" => "RW for user/group, not readable for others (0660)",
            "0640" => "RW for user, RO for group, not readable for others (0640)",
            "0600" => "RW for user, not readable for others (0600)"
        ));
        $importFilemode->setDefault("0644");
        $import[]=$importFilemode;

        /**
         * @todo This requires octdec to be run before using it so use
         * octdec(conf::get("import.dirmode")) or you will get "funny" results
         */
        $importDirmode = new confItemSelect();
        $importDirmode->setName("import.dirmode");
        $importDirmode->setLabel("dir mode");
        $importDirmode->setDesc("Mode for directories that are created by Zoph. " .
            "Determines who can read or write the files. (RW: Read/Write, RO: Read Only)");
        $importDirmode->addOptions(array(
            "0755" => "RW for user, RO for others (0755)",
            "0775" => "RW for user/group, RO for others (0775)",
            "0777" => "RW for everyone (0777)",
            "0770" => "RW for user/group, not readable for others (0770)",
            "0750" => "RW for user, RO for group, not readable for others (0750)",
            "0700" => "RW for user, not readable for others (0700)"
        ));
        $importDirmode->setDefault("0755");
        $import[]=$importDirmode;
        return $import;
    }

    /**
     * Get config group for import CLI settings
     */
    private static function getConfigImportCLI(confGroup $import) {
        $importCliVerbose=new confItemNumber();
        $importCliVerbose->setName("import.cli.verbose");
        $importCliVerbose->setLabel("CLI verbose");
        $importCliVerbose->setDesc("Set CLI verbosity, can be overriden with --verbose");
        $importCliVerbose->setDefault("0");
        $importCliVerbose->setBounds(1,99,1);
        $importCliVerbose->setInternal();
        $import[]=$importCliVerbose;

        $importCliThumbs=new confItemBool();
        $importCliThumbs->setName("import.cli.thumbs");
        $importCliThumbs->setLabel("CLI: generate thumbnails");
        $importCliThumbs->setDesc("Generate thumbnails when importing via CLI. Can be " .
            "overridden with --thumbs (-t) and --no-thumbs (-n).");
        $importCliThumbs->setDefault(true);
        $import[]=$importCliThumbs;

        $importCliExif=new confItemBool();
        $importCliExif->setName("import.cli.exif");
        $importCliExif->setLabel("CLI: read EXIF data");
        $importCliExif->setDesc("Read EXIF data when importing via CLI. The default " .
            "behaviour can be overridden with --exif and --no-exif.");
        $importCliExif->setDefault(true);
        $import[]=$importCliExif;

        $importCliSize=new confItemBool();
        $importCliSize->setName("import.cli.size");
        $importCliSize->setLabel("CLI: size of image");
        $importCliSize->setDesc("Update image dimensions in database when importing " .
            "via CLI. The default behaviour can be overridden with --size and --no-size.");
        $importCliSize->setDefault(true);
        $import[]=$importCliSize;

        $importCliHash=new confItemBool();
        $importCliHash->setName("import.cli.hash");
        $importCliHash->setLabel("CLI: calculate hash");
        $importCliHash->setDesc("Calculate a hash when importing or updating a photo " .
            "using the CLI. Can be overridden with --hash and --no-hash.");
        $importCliHash->setDefault(true);
        $import[]=$importCliHash;

        $importCliCopy=new confItemBool();
        $importCliCopy->setName("import.cli.copy");
        $importCliCopy->setDefault(false);
        $importCliCopy->setLabel("CLI: copy on import");
        $importCliCopy->setDesc("Make a copy of a photo that is imported using the " .
            "CLI. Can be overridden with --copy and --move.");
        $import[]=$importCliCopy;

        $importCliUseids=new confItemBool();
        $importCliUseids->setName("import.cli.useids");
        $importCliUseids->setLabel("CLI: Use Ids");
        $importCliUseids->setDesc("Use ids instead of filenames when referencing photos.");
        $importCliUseids->setDefault(false);
        $importCliUseids->setInternal();
        $import[]=$importCliUseids;

        $importCliAddAuto=new confItemBool();
        $importCliAddAuto->setName("import.cli.add.auto");
        $importCliAddAuto->setLabel("CLI: Auto add");
        $importCliAddAuto->setDesc("Add non-existent albums, categories, places and " .
            "people, when a parent is defined.");
        $importCliAddAuto->setDefault(false);
        $importCliAddAuto->setInternal();
        $import[]=$importCliAddAuto;

        $importCliAddAlways=new confItemBool();
        $importCliAddAlways->setName("import.cli.add.always");
        $importCliAddAlways->setLabel("CLI: Auto add always");
        $importCliAddAlways->setDesc("Add non-existent albums, categories, places " .
            "and people, regardsless of whether a parent is defined.");
        $importCliAddAlways->setDefault(false);
        $importCliAddAlways->setInternal();
        $import[]=$importCliAddAlways;

        $importCliRecursive=new confItemBool();
        $importCliRecursive->setName("import.cli.recursive");
        $importCliRecursive->setLabel("CLI: Recursive");
        $importCliRecursive->setDesc("Recursively import directories when importing " .
            "using the CLI.");
        $importCliRecursive->setDefault(false);
        $importCliRecursive->setInternal();
        $import[]=$importCliRecursive;
    }

    /**
     * Get config group for watermark settings
     */
    private static function getConfigWatermark() {
        $watermark = conf::addGroup("watermark", "Watermarking",
            "Watermarking can display a (copyright) watermark over your full-size images.");

        $watermarkEnable = new confItemBool();
        $watermarkEnable->setName("watermark.enable");
        $watermarkEnable->setLabel("Enable Watermarking");
        $watermarkEnable->setDesc("Watermarking only works if the watermark file below is set " .
            "to an existing GIF image. Please note that enabling this function uses a " .
            "rather large amount of memory on the webserver. PHP by default allows a " .
            "script to use a maximum of 8MB memory. You should probably increase this " .
            "by changing memory_limit in php.ini. A rough estimation of how much memory " .
            "it will use is 6 times the number of megapixels in your camera. For " .
            "example, if you have a 5 megapixel camera, change memory_limit in php.ini to 30M");
        $watermarkEnable->setDefault(false);
        $watermark[]=$watermarkEnable;

        /** @todo: should allow .png too */
        $watermarkFile = new confItemString();
        $watermarkFile->setName("watermark.file");
        $watermarkFile->setLabel("Watermark file");
        $watermarkFile->setDesc("If watermarking is used, this should be set to the name of the " .
            "file that will be used as the watermark. It should be a GIF file, for best " .
            "results, use contrasting colours and transparency. In the Contrib directory, " .
            "3 example files are included. The filename is relative to the image directory, " .
            "defined above.");
        $watermarkFile->setDefault("");
        $watermarkFile->setRegex("(^$|^[A-Za-z0-9_]+[A-Za-z0-9_.\/]*\.gif$)");
        $watermarkFile->setHint("Alphanumeric characters (A-Z, a-z and 0-9), forward slash (/), " .
            "dot (.), and underscore (_). Can not start with a dot or a slash");
        $watermark[]=$watermarkFile;

        $watermarkPosX = new confItemSelect();
        $watermarkPosX->setName("watermark.pos.x");
        $watermarkPosX->setLabel("Horizontal position");
        $watermarkPosX->setDesc("Define where the watermark will be placed horizontally.");
        $watermarkPosX->addOptions(array(
            "left" => "Left",
            "center" => "Center",
            "right" => "Right"
        ));
        $watermarkPosX->setDefault("center");
        $watermark[]=$watermarkPosX;

        $watermarkPosY = new confItemSelect();
        $watermarkPosY->setName("watermark.pos.y");
        $watermarkPosY->setLabel("Vertical position");
        $watermarkPosY->setDesc("Define where the watermark will be placed vertically.");
        $watermarkPosY->addOptions(array(
            "top" => "Top",
            "center" => "Center",
            "bottom" => "Bottom"
        ));
        $watermarkPosY->setDefault("center");
        $watermark[]=$watermarkPosY;

        $watermarkTrans = new confItemNumber();
        $watermarkTrans->setName("watermark.transparency");
        $watermarkTrans->setLabel("Watermark transparency");
        $watermarkTrans->setDesc("Define the transparency of a watermark. 0: fully " .
            "transparent (invisible, don't use this, it's pointless and eats " .
            "up a lot of resources, better turn off the watermark feature " .
            "altogether) to 100: no transparency.");
        $watermarkTrans->setDefault("50");
        $watermarkTrans->setRegex("^(100|[0-9]{1,2})$");
        $watermarkTrans->setBounds(0, 100, 1);
        $watermark[]=$watermarkTrans;
    }

    /**
     * Get config group for rotation settings
     */
    private static function getConfigRotate() {
        $rotate = conf::addGroup("rotate", "Rotation", "Rotate images");

        $rotateEnable = new confItemBool();
        $rotateEnable->setName("rotate.enable");
        $rotateEnable->setLabel("Rotation");
        $rotateEnable->setDesc("Allow users (admins or with write access) to rotate images");
        $rotateEnable->setDefault(false);
        $rotate[]=$rotateEnable;

        $rotateCommand = new confItemSelect();
        $rotateCommand->setName("rotate.command");
        $rotateCommand->setLabel("Rotate command");
        $rotateCommand->setDesc("Determine which command is used to rotate the image. " .
            "This command must be available on your system. Convert is a lossy " .
            "rotate function, which means it will lower the image quality of your " .
            "photo. JPEGtran, on the other hand, only works on JPEG images, but " .
            "is lossless.");
        $rotateCommand->addOptions(array(
            "convert" => "convert",
            "jpegtran" => "jpegtran"
        ));
        $rotateCommand->setDefault("convert");
        $rotate[]=$rotateCommand;

        $rotateBackup = new confItemBool();
        $rotateBackup->setName("rotate.backup");
        $rotateBackup->setLabel("Backup");
        $rotateBackup->setDesc("Keep a backup image when rotating an image.");
        $rotateBackup->setDefault(true);
        $rotate[]=$rotateBackup;

        $rotateBackupPrefix = new confItemString();
        $rotateBackupPrefix->setName("rotate.backup.prefix");
        $rotateBackupPrefix->setLabel("Backup prefix");
        $rotateBackupPrefix->setDesc("Prepend backup file for rotation backups with this.");
        $rotateBackupPrefix->setDefault("orig_");
        $rotateBackupPrefix->setRegex("^[a-zA-Z0-9_\-]+$");
        $rotateBackupPrefix->setRequired();
        $rotate[]=$rotateBackupPrefix;
    }

    /**
     * Get config group for share settings
     */
    private static function getConfigShare() {
        $share = conf::addGroup("share", "Sharing", "Sharing photos with non-logged on users");

        $shareEnable = new confItemBool();
        $shareEnable->setName("share.enable");
        $shareEnable->setLabel("Sharing");
        $shareEnable->setDesc("Sometimes, you may wish to share an image in Zoph " .
            "without creating a user account for those who will be watching them. " .
            "For example, in order to post a link to an image on a forum or website. " .
            "When this option is enabled, you will see a 'share' tab next to a photo, " .
            "where you will find a few ways to share a photo, such as a url and a " .
            "HTML &lt;img&gt; tag. With this special url, it is possible to open a " .
            "photo without logging in to Zoph. You can determine per user whether " .
            "or not this user will see the tab and therefore the urls.");
        $shareEnable->setDefault(false);
        $share[]=$shareEnable;

        $shareSaltFull = new confItemSalt();
        $shareSaltFull->setName("share.salt.full");
        $shareSaltFull->setLabel("Salt for sharing full size images");
        $shareSaltFull->setDesc("When using the sharing feature, Zoph uses a hash " .
            "to identify a photo. Because you do not want people who have access to " .
            "you full size photos (via Zoph or otherwise) to be able to generate " .
            "these hashes, you should give Zoph a secret salt so only authorized " .
            "users of your Zoph installation can generate them. The salt for full " .
            "size images (this one) must be different from the salt of mid size " .
            "images (below), because this allows Zoph to distinguish between them. " .
            "If a link to your Zoph installation is being abused (for example " .
            "because someone whom you mailed a link has published it on a forum), " .
            "you can modify the salt to make all hash-based links to your Zoph invalid.");
        $shareSaltFull->setDefault("Change this");
        $shareSaltFull->setRequired();
        $share[]=$shareSaltFull;

        $shareSaltMid = new confItemSalt();
        $shareSaltMid->setName("share.salt.mid");
        $shareSaltMid->setLabel("Salt for sharing mid size images");
        $shareSaltMid->setDesc("The salt for mid size images (this one) must be " .
            "different from the salt of full images (above), because this allows " .
            "Zoph to distinguish between them. If a link to your Zoph installation " .
            "is being abused (for example because someone whom you mailed a link " .
            "has published it on a forum), you can modify the salt to make all " .
            "hash-based links to your Zoph invalid.");
        $shareSaltMid->setDefault("Modify this");
        $shareSaltMid->setRequired();
        $share[]=$shareSaltMid;
    }

    /**
     * Get config group for feature settings
     */
    private static function getConfigFeature() {
        $feature = conf::addGroup("feature", "Features", "Various features");

        $featureDownload = new confItemBool();
        $featureDownload->setName("feature.download");
        $featureDownload->setLabel("Downloading");
        $featureDownload->setDesc("With this feature you can use download a set of " .
            "photos (Albums, Categories, Places, People or a search result) in " .
            "one or more ZIP files. Important! The photos in the ZIP file will " .
            "NOT be watermarked. You must also grant each non-admin user you " .
            "want to give these rights permission by changing \"can download " .
            "zipfiles\" in the user's profile.");
        $featureDownload->setDefault(false);
        $feature[]=$featureDownload;

        $featureComments = new confItemBool();
        $featureComments->setName("feature.comments");
        $featureComments->setLabel("Comments");
        $featureComments->setDesc("Enable comments. Before a user can actually leave " .
            "comments, you should also give the user these rights through the edit " .
            "user screen.");
        $featureComments->setDefault(false);
        $feature[]=$featureComments;

        $featureMail = new confItemBool();
        $featureMail->setName("feature.mail");
        $featureMail->setLabel("Mail photos");
        $featureMail->setDesc("You can enable or disable the \"mail this photo feature\" " .
            "using this option. Since Zoph needs to convert the photo into Base64 " .
            "encoding for mail, it requires quite a large amount of memory if you " .
            "try to send full size images and you may need to adjust memory_limit " .
            "in php.ini, you should give it at least about 4 times the size of your " .
            "largest image.");
        $featureMail->setDefault(false);
        $feature[]=$featureMail;

        $featureMailBcc = new confItemString();
        $featureMailBcc->setName("feature.mail.bcc");
        $featureMailBcc->setLabel("BCC address");
        $featureMailBcc->setDesc("Automatically Blind Carbon Copy this mailaddress when " .
            "a mail from Zoph is sent");
        $featureMailBcc->setDefault("");
        // not sure how long the "new" TLD's are going to be,
        // 10 should be enough for most, feel free to report
        // a bug if your TLD is longer.
        $featureMailBcc->setRegex("^([0-9a-zA-Z_\-%\.]+@([0-9a-zA-Z\-]+\.)+[a-zA-Z]{2,10})?$");
        $feature[]=$featureMailBcc;

        $featureAnnotate = new confItemBool();
        $featureAnnotate->setName("feature.annotate");
        $featureAnnotate->setLabel("Annotate photos");
        $featureAnnotate->setDesc("A user can use the annotate photo function to e-mail a " .
            "photo with a textual annotation. Can only be used in combination with the " .
            "\"Mail photos\" feature above.");
        $featureAnnotate->setDefault(false);
        $feature[]=$featureAnnotate;

        $featureRating = new confItemBool();
        $featureRating->setName("feature.rating");
        $featureRating->setLabel("Photo rating");
        $featureRating->setDesc("Allow users to rate photos. Before a non-admin user can " .
            "actually rate, you should also give the user these rights through the " .
            "edit user screen.");
        $featureRating->setDefault(true);
        $feature[]=$featureRating;
    }

    /**
     * Get config group for date settings
     */
    private static function getConfigDate() {
        $date = conf::addGroup("date", "Date and time", "Date and time related settings");

        $dateTz = new confItemSelect();
        $dateTz->setName("date.tz");
        $dateTz->setLabel("Timezone");
        $dateTz->setDesc("This setting determines the timezone to which your camera " .
            "is set. Leave empty if you do not want to use this feature and always set " .
            "your camera to the local timezone");

        $dateTz->addOptions(TimeZone::getTzArray());
        $dateTz->setDefault("");

        $date[]=$dateTz;

        $dateGuesstz = new confItemBool();
        $dateGuesstz->setName("date.guesstz");
        $dateGuesstz->setLabel("Guess timezone");
        $dateGuesstz->setDesc("If you have defined the precise location of a place " .
            "(using the mapping feature), Zoph can 'guess' the timezone based on this " .
            "location. It uses the Geonames project for this. This will, however, send " .
            "information to their webserver, do not enable this feature if you're not " .
            "comfortable with that.");
        $dateGuesstz->setDefault(false);
        $date[]=$dateGuesstz;

        $dateFormat = new confItemString();
        $dateFormat->setName("date.format");
        $dateFormat->setLabel("Date format");
        $dateFormat->setDesc("This determines how Zoph displays dates. You can use the " .
            "following characters: dDjlNSwzWFmMntLoYy (for explanation, see " .
            "http://php.net/manual/en/function.date.php) and /, space, -, (, ), :, \",\" and .");
        $dateFormat->setDefault("d-m-Y");
        $dateFormat->setRegex("^[dDjlNSwzWFmMntLoYy\/ \-():,.]+$");
        $dateFormat->setRequired();
        $date[]=$dateFormat;

        $dateTimeFormat = new confItemString();
        $dateTimeFormat->setName("date.timeformat");
        $dateTimeFormat->setLabel("Time format");
        $dateTimeFormat->setDesc("This determines how Zoph displays times. You can use the " .
            "following characters: aABgGhHisueIOPTZcrU (for explanation, see " .
            "http://php.net/manual/en/function.date.php) and /, space, -, (, ), :, \",\" and .");
        $dateTimeFormat->setDefault("H:i:s T");
        $dateTimeFormat->setRegex("^[aABgGhHisueIOPTZcrU\/ \-():,.]+$");
        $dateTimeFormat->setRequired();
        $date[]=$dateTimeFormat;

    }
}

