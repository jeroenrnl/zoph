# Zoph 0.9.8 Readme #
http://www.zoph.org

## Introduction ##

**Zoph** (**Z**oph **O**rganizes **Ph**otos) is a web based digital image presentation and management system. In other words, a photo album. It is built with PHP and MySQL.

Many people store their photos in the digital equivalent of a shoe box: lots of directories with names like 'Holiday 2008', 'January 2005' or even 'Photos034'. Like shoe boxes, this is a great way to put your photos away, but not such a great way to find them back or even look at them. Zoph can help you to store your photos and keep them organized.

While most photo album projects are primarily targeted at showing your photos to others, Zoph is primarily targeted at keeping your photos organized for yourself, giving you granular control over what you'd like to show to others, on a per-album or even a per-photo basis.

If you just want to generate a gallery of thumbnails from a bunch of images, you may want to try one of the other numerous photo album projects. But if you want to also store additional information about your photos, search them, or control access to them, take a look at Zoph.

## Installation ##

Read the the [Requirements](docs/REQUIREMENTS.md), [Installation guide](docs/INSTALL.md) docs. In order to customize your Zoph installation, read the [Configuration guide](docs/CONFIGURATION). If you are upgrading from a previous version, read the [Upgrade Instructions](docs/UPGRADE.md) document.

For full documentation, see the [docs](docs/) directory.

## Copying ##

Zoph is free software.  It is released under the GPL license. Please read the [license](COPYING) file for more details

## Feedback ##
   
Please report issues via https://github.com/jeroenrnl/zoph/issues

## Thanks ##

Zoph makes use of the following packages, for which I thank their authors for making available:

* **HTML Mime Mail class** by Richard Heyes http://www.phpguru.org/mime.mail.html

* **PHP Calendar class** by David Wilkinson http://www.cascade.org.uk/software/php/calendar/index.php

* **Rycks Translation Project** by Eric Seigne (website no longer available)   

* **Leaflet** an open-source JavaScript library for mobile-friendly interactive maps http://leafletjs.com

For a list of individuals who have contributed fixes, improvements or translations, click on the 'about' tab within Zoph.

## Troubleshooting ##

### GD library missing ###

I'm trying to use the importer from the web but I get this error: 

    Fatal error: Call to undefined function: imagecreatefromjpeg()

To use the importer you need the GD 2 library for image creation
support in PHP. See the [REQUIREMENTS](docs/REQUIREMENTS.md) doc for more info.

### Moving photos on disk ###
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

````
mysql> update photos set path = 'new_path' where name in ('photo1.jpg', 'photo2.jpg');

````

Why do I see some English phrases when I'm using a translation:

    [vo] that have been categorized

Some language files are missing a few translations. Many, but not all,
are shown in italics and preceded by [vo]. To fix this simply open the
correct language file in the lang/ directory and add a transltions of
the missing string (the English string should already be present in
the file). Please share your changes, through an issue or fork + pull 
request.

### Change width of Zoph display ###

Can I get Zoph to take up my whole browser window rather than that little
rectangle?

Try setting *Screen width* in the configuration screen (*admin* -> *configuration*) to "100%".

### Can I customize the name/title used in the interface? ###

Change *Title* in the configuration screen (*admin* -> *configuration*)
This is what appears on the logon page, on the home page, and in the title of every
page.

### Changing text ###
I don't like your welcome screen, your instructions on the import page, or your use of English in general.

You could edit the templates so that Zoph says just what you want. A better alternative is perhaps to create your own custom translation.  Create a file in the lang/ directory that maps English to English and tweak whatever phrases you want. For example:

 Welcome %s. %s currently contains=Go away %s. %s isn't for you.

## Miscellaneous ##

### How do you pronounce Zoph? ###

I say Zoph with an O like in photos, some say Zoph like software 
("Zophtware"), but you can pronounce it however you like.

### What license is Zoph released under? ###

Zoph used to be licensed under the modified BSD license. As of version 0.4 this has been changed to the GPL license. We have done our best to make sure all the code in Zoph could be changed to this license. If you feel your copyright has been violated with this change, please contact us a.s.a.p.  Some included files have their own license because the license doen not allow us to change it to GPL.
