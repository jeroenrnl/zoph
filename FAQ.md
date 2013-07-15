Zoph FAQ
========
02-06-2007
----------

Documentation
=============

Up to date documentation can be found on http://en.wikibooks.org/wiki/zoph

Troubleshooting
=================

MySQL not installed
-------------------
After logging in I get the following error: 

 Fatal error: Call to undefined function: mysql_pconnect(). 

What's wrong? 

You may need to install the php-mysql module (rpms and debs are
available).

session.autostart
-----------------
I can log in but when I try to view any other page I get: 
 
 Fatal error:
 The script tried to execute a method or access a property of an incomplete
 object. Please ensure that the class definition user of the object you are
 rying to operate on was loaded _before_ the session was started in
 /var/www/zoph/auth.inc.php on line 64".

This can happen when a session is automatically started at the
beginning of a request. You can fix this by disabling
session.auto_start in your php.ini or by inserting a call to
session_write_close() before the call to session_start() in
auth.inc.php.

GD library missing
------------------
I'm trying to use the importer from the web but I get this error: 

 Fatal error: Call to undefined function: imagecreatefromjpeg()

To use the importer you need the GD 2 library for image creation
support in PHP. See the REQUIREMENTS doc for more info.

Moving photos on disk
---------------------
I moved my photos around after I loaded them and now I see broken images.
How can I fix them?

If you move images to a different directory you'll start seeing broken
images in Zoph unless you also update the 'path' field in the
database.

If you edit a photo, at the bottom of the page you'll see a 'show
additional attributes' link. That will let you edit the path for a
photo.

If you're moving a bunch of photos, you may want to just create a list
of their names as you are relocating them and then change all the
paths at once from within MySQL:

   mysql> update photos set path = 'new_path' where name in
   ('photo1.jpg', 'photo2.jpg');

Cookies
-------
Can I use Zoph without having to enable cookies?

Zoph will work without cookies but you have to enable
session.use_trans_sid in your php.ini file so that url rewriting will
work. Starting with PHP 4.2.0 this parameter is disabled by default.

Missing translations
--------------------
Why do I see some English phrases when I'm using a translation: 
 [vo] that have been categorized

Some language files are missing a few translations. Many, but not all,
are shown in italics and preceded by [vo]. To fix this simply open the
correct language file in the lang/ directory and add a transltions of
the missing string (the English string should already be present in
the file). If you make an additions please send me an email
(zoph@zoph.org). 

Why do I see a bunch of code when I try to access Zoph?
-------------------------------------------------------
First, check to make sure that you have an AddType line for php files
in your httpd.conf file. This is described in the INSTALL document.

Customization
=============
Change width of Zoph display
----------------------------
Can I get Zoph to take up my whole browser window rather than that little
rectangle?

Try setting *Screen width* in the configuration screen (*admin* -> *configuration*) to "100%".

Can I customize the name/title used in the interface?
-----------------------------------------------------
Change *Title* in the configuration screen (*admin* -> *configuration*)
This is what appears on the logon page, on the home page, and in the title of every
page.

Changing text
-------------
I don't like your welcome screen, your instructions on the import page,
or your use of English in general.

You could edit the templates so that Zoph says just what you want. A
better alternative is perhaps to create your own custom translation.
Create a file in the lang/ directory that maps English to English and
tweak whatever phrases you want. For example:

 Welcome %s. %s currently contains=Go away %s. %s isn't for you.

Miscellaneous
=============
How do you pronounce Zoph?
--------------------------

I say Zoph with an O like in photos, some say Zoph like software 
("Zophtware"), but you can pronounce it however you like.

What license is Zoph released under?
------------------------------------

Zoph used to be licensed under the modified BSD license. As of version 0.4
this has been changed to the GPL license. We have done our best to make sure
all the code in Zoph could be changed to this license. If you feel your
copyright has been violated with this change, please contact us a.s.a.p.
Some included files have their own license because the license doen not
allow us to change it to GPL.
