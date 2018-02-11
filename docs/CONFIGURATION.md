# CONFIGURATION #

## Database connection ##
Access to the database needs to be configured through `/etc/zoph.ini`.
The `zoph.ini` files tells Zoph where it can find the database and it tells Zoph's CLI scripts where it can find your Zoph installation. Normally, `zoph.ini` will be placed in `/etc`. If you have no write access in `/etc` or have another reason to not put this file there, you should change the `INI_FILE` setting in `config.inc.php` and the 'zoph' CLI utility.

**Never, _ever_, place it in the same directory as the Zoph PHP files. This will enable _everyone_ to download it and read your passwords.**

An example `zoph.ini` file called **`zoph.ini.example`** is included in the `cli` dir of the Zoph tarball.

### Contents of `zoph.ini` ###
`zoph.ini` consists of one or more *sections*. A section starts with the name of the section between square brackets.
`[zoph]`
You should create a section for each Zoph installation on your system. The section name is a descriptive name that you can choose yourself. Each section must contain the following settings:

`db_host`	
The hostname of the system that is running your MySQL server, usually "`localhost`".

`db_name`
The name of the database. If you have followed the installation instructions closely, this will be `zoph`, but of course you are free to use any other name.

`db_user`
The user to connect to your Zoph database. If you have followed the installation instructions closely, this will be `zoph_rw`, but of course you are free to use any other name.

`db_pass` 
Password to connect to the database. This is what you have set while creating users for Zoph in MySQL.

`db_prefix`
Zoph can prefix all MySQL table names with a prefix string. This is especially useful for people who only have a single database to use and want to use multiple applications on, for example, a shared hosting environment. By default, this is "`zoph_`".

`php_location`
With the `php_location` setting, you define where the PHP-files for your Zoph installation are located. This is necessary for the Zoph CLI scripts to locate the rest of your Zoph installation.

All values that contain non-alphanumeric characters must be enclosed in double quotes. It won't hurt to use quotes even if the values are purely alphanumeric.

#### Examples ####
##### Single installation ####
Most Zoph users will have only one Zoph installation on their system. This is how a `zoph.ini` for a single installation looks:

````
[zoph]
db_host = "localhost"
db_name = "zoph"
db_user = "zoph_rw"
db_pass = "pass"
db_prefix = "zoph_"
php_location = "/var/www/html/zoph"
````
##### Multiple installations ####
You can have multiple Zoph installations on one system. For example, one for yourself and one for a family member or friend; or, if you are a Zoph developper, a development and a productions environment. If you have more than one Zoph installation, simply create a section *per installation*. For example:

````
[production]
db_host = "localhost"
db_name = "zoph"
db_user = "zoph_rw"
db_pass = "pass"
db_prefix = "zoph_"
php_location = "/var/www/html/zoph"

[development]
db_host = "localhost"
db_name = "zophdev"
db_user = "zoph_rw"
db_pass = "pass"
db_prefix = "zoph_"
php_location = "/var/www/html/zophdev"
````

The webinterface of Zoph will be able to determine which settings it should use with the `php_location` setting. The CLI scripts need the `--instance` parameter to determine that. If you omit the `--instance` parameter, it will use the first one in `zoph.ini`.

## Web GUI ##
Most of Zoph can be configured from the Web GUI. Log in as a user with admin rights. If you haven't created a user for yourself, you can login with the user `admin`. Go to "admin" in the top menu and then choose "config". The configuration items should be self-explanatory.

When you first get started with Zoph, you should at least change the following:

### Images path ###
**Images directory** under **paths**. This is the directory where your photos are stored. It should be an _absolute path_ (that is: referenced from the root) and it should not be in your webroot. See the [installation documentation](INSTALLATION.md) for how to set the correct access rights for this directory.

### Sharing Salt ###
**Salt for sharing full size images** and **Salt for sharing mid size images** under **Sharing**. You should set these salts to unique values. You can do so by clicking the generate buttons. Even though you will not need these unless you enable **Sharing**, it is a good idea to make sure you have a unique salt set. (and Zoph will refuse to save your configuration if you don't).

### Enable import and upload ###
**Import through webinterface** and **Upload through webinterface** under **Import**. Unless you plan to use the CLI import exclusively, you should enable import through the web interface here.

### Interface title ###
**Title** under **Interface settings**. You probably want to change the name Zoph will show on the login page and in the title bar.

## `config.inc.php` ##
There are a few configuration settings that can only be changed in `config.inc.php`. Most users will never need to change anything here. 

### `LOG_ALWAYS` ###
**Description:**: This option controls how much debug information is showed. Zoph will show you the severity you configure and everything worse than that. So if you configure `log::ERROR`, you will see `ERROR` and `FATAL` messages and if you configure `log::DEBUG`, you will see all messages.

**Default:** `log::FATAL`

**Options:** See [Log Severity](#log-severity) below

**Example:** `define('LOG_ALWAYS', log::ERROR);`


### `LOG_SUBJECT` ###
**Description:** This option, together with [`LOG_SEVERITY`](#log_severity) enables you to have granular control over which messages are displayed. With `LOG_SUBJECT` you configure on which subject you would like to see logging.

**Default:** `log::NONE`

**Options:** See [Log Subjects](#log-subjects) below

**Example:**
Display all messages which indicate an error or a fatal error, regarding the translation of Zoph or images:

````php
define('LOG_SEVERITY', log::ERROR);
define('LOG_SUBJECT', log::LANG | log::IMG);
````
Display all messages, except debug-level messages, except those regarding SQL queries:

````php
define('LOG_SEVERITY', log::NOTIFY);
define('LOG_SUBJECT', log::ALL | ~log::SQL);
````

Display all messages, except those regarding redirects or the database connection:

````php
define('LOG_SEVERITY', log::DEBUG);
define('LOG_SUBJECT', log::ALL ~(log::REDIRECT | log::DB));
````

### `LOG_SEVERITY` ###
**Description:**
This option, together with [`LOG_SUBJECT`](#log_subject) enables you to have granular control over which messages are displayed. With `LOG_SEVERITY` you configure how much debug information is showed. The difference with [`LOG_ALWAYS`](#log_always) is, that the messages are only shown for the subject you have configured in [`LOG_SUBJECT`](#log_subject). Zoph will show you the severity you configure and everything worse than that. So if you configure `log::ERROR`, you will see `ERROR` and `FATAL` messages and if you configure `log::DEBUG`, you will see all messages.

**Default:** `log::NONE`

**Options:** See [Log Severity](#log-severity) below

**Example:** `define('LOG_SEVERITY', log::NOTIFY);`

### Log Severity ###
Severity      | Meanint
--------------|---------------------
log::DEBUG    |	Debugging messages, Zoph gives information about what it's doing.
log::NOTIFY   |	Notification about something that is happening which is influencing Zoph's program flow
log::WARN     |	Warning about something that is happening
log::ERROR    | Error condition, something has gone wrong, but Zoph can recover
log::FATAL    |	Fatal error, something has gone wrong and Zoph needs to stop execution of the current script.
log::NONE     | Do not display any messages

### Log Subjects ###
Subject       | Type of messages in this subject
--------------|---------------------
log::ALL      | All messages
log::VARS     | Messages regarding setting of variables
log::LANG     | Messages regarding the translation of Zoph
log::LOGIN    | Messages regarding the Login procedure
log::REDIRECT | Messages regarding redirection
log::DB       | Messages regarding the database connection
log::SQ       | Messages regarding SQL Queries
log::XML      | Messages regarding XML creation
log::IMG      | Messages regarding image creation
log::IMPORT   | Messages regarding the import functions
log::GENERAL  | Other messages
log::NONE     | No messages.

## Resized image generation ##
Zoph automatically creates thumbnails and medium-sized ('mid') images during import. To influence this process, you can edit the parameters below. It is not recommended to change these, especially not after you have imported some photos. In the near future there will be an option to change this in the webinterface.

### `THUMB_SIZE``` ###
**Description:**
Maximum width or height of thumbnails

**Default:**
`120`
**Options:**
Maximum width/height in pixels.

**Example:**
`define('THUMB_SIZE', 120);`

### `MID_SIZE``` ###
**Description:**
Maximum width or height of midsized images

**Default:**
`480`
**Options:**
Maximum width/height in pixels.

**Example:**
`define('MID_SIZE', 480);`

### `THUMB_PREFIX``` ###
**Description:**
Prefix for filenames of thumbnails

**Default:**
`thumb`
**Options:**
**Do not** make this string empty!

**Example:**
`define('THUMB_PREFIX', 'thumb');`

### `MID_PREFIX``` ###
**Description:**
Prefix for filenames of thumbnails

**Default:**
`mid`
**Options:**
**Do not** make this string empty!

**Example:**
`define('MID_PREFIX', 'mid');`

