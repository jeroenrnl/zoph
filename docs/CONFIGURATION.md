# CONFIGURATION #

## Database connection ##
Access to the database needs to be configured through `/etc/zoph.ini`.
The `zoph.ini` files tells Zoph where it can find the database and it tells Zoph's CLI scripts where it can find your Zoph installation. Normally, `zoph.ini` will be placed in `/etc`. If you have no write access in `/etc` or have another reason to not put this file there, you should change the `INI_FILE` setting in `config.inc.php` and the 'zoph' CLI utility.

<aside class="warning">
Never, **ever**, place it in the same directory as the Zoph PHP files. This will enable **everyone** to download it and read your passwords.
</aside>

An example `zoph.ini` file called `**zoph.ini.example**` is included in the `cli` dir of the Zoph tarball.

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


