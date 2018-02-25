Zoph Installation
=================

Requirements
------------

See the [requirements](REQUIREMENTS.md) document.

Creating the database
---------------------

### Create a database and import the tables ###

```
$ mysql -u root -p -e "CREATE DATABASE zoph CHARACTER SET utf8 COLLATE utf8_general_ci"
$ mysql -u root -p zoph < sql/zoph.sql
```

### Create users for zoph ###

I created two users: ```zoph_rw``` is used by the application and ```zoph_admin``` is used when I work directly in mysql so I don't
have to use root.

```
$ mysql -u root -p
mysql> grant select, insert, update, delete on zoph.* to zoph_rw@localhost identified by 'PASSWORD';
mysql> grant all on zoph.* to zoph_admin identified by 'PASSWORD';
```

Create zoph.ini
---------------
In Zoph 0.8.2 and later, you need to create a zoph.ini file, usually in 
/etc. zoph.ini is where you define database settings. A simple example:

```
[zoph]
db_host = "localhost"
db_name = "zoph"
db_user = "zoph_rw"
db_pass = "pass"
db_prefix = "zoph_"

php_location = /var/www/html/zoph
```

An example zoph.ini file, called zoph.ini.example is included in the cli directory.
See the man page for zoph.ini(5) or the [documentation](docs/) for more details

Install the templates
---------------------

### Pick a location to put Zoph ###

Create a zoph/ directory off the doc root of your web server, or create a Virtual Host with a new doc root.

```
$ mkdir /var/www/html/zoph
```

### Copy the templates ###
```
$ cp -r php/* /var/www/html/zoph/
```
### Set accessrights ###

For better security, you probably want to set accessrights on your Zoph files. (You may want to do this after testing whether Zoph works, in that case you know what caused it when it seizes working after this change)

First, you need to figure out which user Apache is running under. Usually this is apache for both user and group. To determine this, check httpd.conf or use

```
ps -ef | grep httpd
```

You should probably make all files owned by the user apache and the group apache. You can do than with

```
chown -R apache:apache /var/www/html/zoph 
```
You can either make them only readable by this user/group (more security): *440*, readable by all users: *444*, or readable and writable by all users: *666*. The last case means that you don't need root access to edit config.inc.php or to make changes to the other php files (such as upgrades to a new version). Keep in mind that giving write access to the .php files effectively gives control over Zoph. If you have other users on your system, you should choose the first option. Also, your mysql password is in `/etc/zoph.ini`, so if you've users on your system that are not allowed to know it, you should protect it against reading as well. The directories should have execute rights: *550* for max security or *777* for access for all users.

To do this, first go to the directory directly above your Zoph directory, in this example /var/www/html

```
cd /var/www/html
chmod [dir] zoph
cd zoph
find -type f | xargs chmod [file]
find -type d | xargs chmod [dir]
```
replace [dir] with the accesspattern you've chosen for directories above and replace [file] with the one for files.

> :exclamation: Warning :exclamation:
> Double check whether you are using the correct directory and if you have typed it correctly, if you would 
> accidently type `/[space]var/www/html/zoph` or something, you would change all files on your entire system to 
> apache/apache as owner - not good).


### Access rights for your photos ###
In many cases you can simply leave the access rights on you photo directories on default.
However, if you use both the CLI and the webinterface to access your photos, you may want to change to a more advanced way of managing accessrights, using the [setgid](https://en.wikipedia.org/wiki/Setgid#setgid_on_directories]) feature in Linux and most other POSIX Operating Systems.

* Create a new Unix group (in example "photo")
````
groupadd photo
````
* Add all users that use the CLI and/or are allowed to modify the photos on disk to this group (in this example, the user is called 'jeroen')
````
useradd -g photo jeroen
````
* Additionally, the apache user is added to this group, on my system, this user is called 'apache', but 'www-data' is also often used.
````
useradd -g photo apache
````
* Change the ownership of the photo directory to your user and the group photo
````
chown jeroen:photo /data/images
````
* Set the permissions on this directory as you wish, for example *775* (full rights for user and group, read rights for other) or *770* (full rights for user and group, no access for others).
````
chmod 775 /data/images
````
* Now set 'setgid' on the dir, this causes new files and directories to be created with the group 'photo'.
````
chmod g+s /data/images
````


Configure the PHP templates
---------------------------

Some configuration options can be set in `php/config.inc.php file`. Usually you will not have to change anything there. Most configuration can be done from the web interface of Zoph. For more information, see the [Configuration documentation](CONFIGURATION.md).

Install the CLI scripts
-----------------------

### Check the path to PHP ###

The CLI script points to `/usr/bin/php`.  If your PHP installation is in a different place, edit the first line of the script.

### Copy cli/zoph to /bin ###
Or some other directory in your `PATH`.

### Install the man page ###
Man pages for zoph and `zoph.ini` are in the `cli`/ directory. Copy these to the `man1` and `man5` directoies in your manpath, `/usr/local/man/man1` and `/usr/local/man/man5` for example.

Test it
-------
Try hitting http://localhost/zoph/logon.php.  You should be presented with the logon screen.

You can log in with admin / admin. It is recommended to change this.

If you get a 404 error...
make sure the zoph/ folder and templates can be seen by the web server.

If you see a bunch of code...
make sure Apache is configured to handle PHP (see the [requirements file](REQUIREMENTS.md) file)

If you see a MySQL access denied error...
make sure the `db_user` you specified in `zoph.ini` actually has access to the database.  If your database is not on localhost, you will need to grant permissions to `zoph_rw@hostname` for that host.
