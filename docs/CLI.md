# The `zoph` CLI tool #

`zoph` is the commandline interface (CLI) of Zoph 0.8.2 and later. You can use the CLI to import photos in Zoph and make (bulk) changes to photos already in Zoph. 

## Multiple Zoph installations ##

### `--instance` ###
You can have multiple Zoph installations on one system. For example a Zoph installation for yourself and one for a family member or friend, or if you are a Zoph developper, a *production* and a *development* version. The webinterface can determine which installation your are using by the URL you are using. The command line interface does not have an URL thus it needs a different way to find out which instance of Zoph is used.

**Aliases:** `-i`

**Default:** First instance in zoph.ini

**Options:** Instance defined in zoph.ini

**Example:** zoph --instance production photo.jpg

## Commands ##
You can only supply one "command" type option to Zoph, if you supply more, Zoph will take the last one.

### `--import` ###
The list of photos given will be imported in Zoph

**Aliases:** `-I`

**Default:** --import is the default command, it doesn't need to be given.

**Options:** 

**Example:** zoph --import photo.jpg

### `--update` ###
Zoph will try to find the given list of photos in the database and apply the options to those photos. You can either give a list of filenames or a list of id's, see [--useIds](#--useIds).

**Aliases:** `-u`

**Default:** `--import` is the default command

**Example:** `zoph --update photo.jpg`

### `--new` ###
Create albums, categories, places and people from CLI

**Aliases:** `-N`

**Default:** 

**Options:** Use `--album "new album"`, `--category "new category"`, `--person "new person"`, `--place "new location"`. The new object will be created directly under the root unless [--parent](#--parent) is specified. See [--person](#--person) for details on how Zoph determines what's the first and second name.

**Example:** `zoph --new --parent "Holidays" --album "Summer 2011"`

### `--version` ###
Show the current Zoph version.

**Aliases:** `-V`

**Default:** `--import` is the default command

**Options:** All other options will be ignored if `--version` is specified

**Example:** `zoph --version`

### `--help` ###
Display help.

**Aliases:** `-h`

**Default:** --import is the default command

**Options:** All other options will be ignored if `--help` is specfied

**Example:** zoph --help

## Organizers ##
Organizers is what Zoph is all about, these are the ways you can organize your photos by.

### `--album` ###
Specify one or multiple albums Zoph should add the given list of photos to. You can specify `--album` multiple times.

**Aliases:** `-a` `--albums`

**Options:** The name of an album or multiple, separated by commas. The album must pre-exist in the database.

**Example:** 
````
zoph --album "Summer, Holiday" photo.jpg
zoph -a "Summer" -a "Holiday" photo.jpg
````

### `--category` ###
Specify one or multiple categories Zoph should add the given list of photos to. You can specify `--category` multiple times.

**Aliases:** `-c` `--categories``

**Options:** The name of a category or multiple, separated by commas. The category must pre-exist in the database.

**Example:** 
````
zoph --category "sun, water" photo.jpg
zoph -c "sun" -c "water" photo.jpg
````

### `--person` ###
Specify one or multiple persons that appear on the photos specified. You can specify `--person` multiple times.

**Aliases:** `-p` `--persons` `--people`

**Options:** The name of a person or a list of persons separated by commas. The person must pre-exist in the database. When using [--new](#--new) to add new persons to the database, Zoph will try to determine which parts of the name are first, middle and last. If a name is a single word ("John"), Zoph assumes this is the first name. If a name is two words ("John Doe"), Zoph will assume this is the first and last name. If a name is 3 or more words, Zoph will assume the first word is the first name, the second is a middle name and all remaining words are the last name. If this does not give the correct results, you can choose to separate by colon (":") instead of space. Zoph will then set the part before the first colon to first name, then middle, then last and finally 'called'.

**Example:** 
````
zoph --person "Linus Torvalds, Mark Shuttleworth" photo.jpg
zoph -p "Linus Torvalds" -p "Mark Shuttleworth" photo.jpg
zoph --new --person "Linus Torvalds"
zoph --new --person "John Fitzgerald Kennedy"
zoph --new --person "Johnny B.::Goode"
zoph --new --person "John::Doe:Average Joe"
````

### `--location` ###
Specify the location where the photos specified were taken. You can specify `--location` only one time.

**Aliases:** `-l` `--place`

**Options:** The name of a place. The place must pre-exist in the database.

**Example:** 
````
zoph --location "Rotterdam" photo.jpg
zoph -l "Rotterdam" photo.jpg
````

### --photographer ###
Specify the photographer of the photos specified. You can specify `--photographer` only one time.

**Aliases:** `-P`

**Options:** The name of a person. The person must pre-exist in the database.

**Example:** 
````
zoph --photographer "Alan Cox" photo.jpg
zoph -P "Alan Cox" photo.jpg
````

### `--fields` ###

**Aliases:** `-f` `--field`
Specify fields that should be filled for the photos specified. You can specify `--field` multiple times.

**Options:** The following fields can be used: 
* date
* time
* camera_make
* camera_model
* flash_used
* focal_length
* exposure
* compression
* aperture
* iso_equiv
* metering_mode
* ccd_width
* focus_dist
* comment
* lat
* lon
* rating
* description
* level
* view
* title

**Example:** 
````
zoph --field "rating=10" photo.jpg
zoph -f "description=self portrait" photo.jpg
````

## Options ##
### `--thumbs` / `--no-thumbs`  ###
Specify whether thumbnails should be created.

**Aliases:** `-t` / `--nothumbs` `-n`

**Default:** When importing ([--import](#--import)): create thumbs. When updating ([--update](#--update)): do not create thumbs.

**Options:** Use these commands to overrule the defaults. If you want to recreate thumbs for already imported photos, use `--thumbs`. If you do not want to create thumbnails while importing, use `--no-thumbs`.

**Example:** 
````
zoph --import --no-thumbs photo.jpg
zoph --update -t photo.jpg
````

### `--exif` / `--no-exif`  ###
Specify whether EXIF date should be read.

**Aliases:** `--EXIF` / `--noexif` `--no-EXIF` `--noEXIF` 

**Default:** When importing ([--import](#--import)): read EXIF data. When updating ([--update](#--update)): do not read EXIF data.

**Options:** Use these commands to overrule the defaults. If you want to reread the EXIF date of already imported photos, use `--exif`. If you do not want to read EXIF data while importing, use `--no-exif`.

**Example:** 
````
zoph --import --no-exif photo.jpg
zoph --update --exif photo.jpg
````

### `--size` / `--no-size`  ###
Specify whether Zoph should update the dimensions of the photo stored in the database.

**Aliases:** *(none)* / --nosize

**Default:** When importing ([--import](#--import)): update database with dimensions of the image. When updating ([--update](#--update)): do not update the size information.

**Options:** Use these commands to overrule the defaults. If you want to update the information stored in the database when updating, use `--size`. If you do not want store size information while importing (although I see no real use for this), use `--no-size`.

**Example:** 
````
zoph --import --no-size photo.jpg
zoph --update --size photo.jpg
````

### `--useids`  ###
When updating photos it can be useful to be able to specify database ids instead of filenames.

**Aliases:** `--useIds` `--use-ids` `--useid` `--use-id`

**Default:** Filenames are used. Using `--useids` implies `--update`

**Options:** You can specify a list of ids instead of a list of filenames. You can either specify a single id or a range of ids. Keep in mind that the list of filenames or ids are the **last** options of the command and do not necessarily follow the `--useids` option.

**Example:** 
````
zoph --update --useids 2 5 11-20 56
zoph --update --useids --album "Summer" 15-60
````

### `--move` / `--copy` ###
When importing photos, you can either import a copy of the photo or move the photo into the Zoph imagedirectory.

**Default:** Files are moved.

**Options:** If the file imported is a symlink, in case of `--move`, a copy of the file the symlink points to is imported and the symlink is deleted. In case of `--copy`, the symlink is not deleted.

**Example:** 
````
zoph --move photo.jpg
zoph --copy photo.jpg
````
### `--dateddirs` / `--no-dateddirs` ###

With dated dirs, Zoph automatically creates directories based on the (EXIF-)date of a photo. For example a photo taken on March 15, 2010, will automatically be places in a directory called 2010.03.15

**Aliases:** `--datedDirs` `--dated` `-d` / `--no-datedDirs` `--nodateddirs` `--nodatedDirs`

**Default:** No dated dirs are used.

**Options:** 

**Example:** `zoph --dateddirs photo.jpg`

### `--hierarchical` / `--no-hierarchical` ###
Hierarchical dated dirs are similar to [--dateddirs](#--dated dirs), Zoph automatically creates directories based on the (EXIF-)date of a photo, the difference is that with hierarchical dated dirs, a separtate directory is create for year, month and day. For example a photo taken on March 15, 2010, will automatically be places in the directory tree `2010/03/15`.

**Aliases:** `-H` `--hier` / `--no-hierarchical` `--no-hier` `--nohierarchical` `--nohier`

**Default:** No hierarchical dated dirs are used.

**Example:** `zoph --hierarchical photo.jpg`

### `--hash` / `--no-hash` ###
As of v0.8.4 Zoph stores a hash of each photo in the database. This is currently only used for the 'share photo' feature. In the future other features will use this, as it will allow Zoph to detect whether a photo has been changed.

**Default:** Generate a hash or update the hash when `--update` is used.

**Options:** 

**Example:** `zoph --no-hash photo.jpg`

### `--parent` ###

**Default:** If you do not specify a parent, the new object will be placed directly under the root.
When adding new objects to the database using the [--new](#--new) option, you can determine where in the tree an album, category or place will be placed by specifying `--parent`.

**Options:** `--parent` **must precede** the actual album, category or place. The parent is only set for the next [--album](#--album), [--category](#--category) or [--place](#--place).

**Example:** 

Create a new album called 'summer 2011' under the root album:
````
zoph --new --album "Summer 2011"
````


Create new albums called 'Summer 2011' and 'Winter 2011' under the 'Holidays' album:
````
zoph --new --parent "Holidays" --album "Summer 2011, Winter 2011"
````

Create new albums called 'Summer 2011' and 'Winter 2011' under the 'Holidays' album and an album 'Trees' under the root album:
````
zoph --new --parent "Holidays" --album "Summer 2011, Winter 2011" --album "Trees"
````

Create new albums called 'Summer 2011' and 'Winter 2011' under the 'Holidays' album and an album "Trees" under the "Nature" album:
````
zoph --new --parent "Holidays" --album "Summer 2011, Winter 2011" --parent "Nature" --album "Trees"
````

Create a new album called 'Summer 2011' under the 'Holidays' album and a cateogory "Trees" under the "Nature" category:
````
zoph --new --parent "Holidays" --album "Summer 2011" --parent "Nature" --category "Trees"
````

### `--autoadd` ###

You can use [--new](--new) to add albums, categories, places and people from CLI, with autoadd you can add them in the same run as you are importing photos. Zoph will add any album, category, etc. you have specified, but does not exist. However, to protect you from every typo to be automatically added to the database, only items preceded with [--parent](#--parent) will be added, unless you specify [--addalways](#--addalways). Of course this only works for albums, categories and locations, and not for persons and photographers.

**Aliases:** `-A` `--auto-add`

**Example:**
````
zoph --autoadd --album "Summer 2011" IMG_1234.JPG
No parent album for "Summer 2011"
````
`zoph --autoadd --parent "Holidays" --album "Summer 2011" IMG_1234.JPG`

### `--addalways` ###

When using [--autoadd](#--autoadd), zoph protects you from every typo to be automatically added to the database by only adding albums, categories and location preceded with [--parent](--parent). To overrule this behaviour, use `--addalways`, which causes them to be added under the root album, category or location.

**Aliases:** `-w` `--add-always`

**Default:** Do not add albums, categories or locations unless a parent has been specified.

**Example:** `zoph --autoadd --addalways --album "Summer 2011" IMG_1234.JPG`

### `--recursive` ###

**Aliases:** `-r`
With `--recursive`, Zoph will recursively go through directories added to the file list and import photos found in those dirs as well.

**Default:** Zoph will error if you try to import a directory.

**Example:** 
Import image IMG_1234.JPG and any photos in the directory 'Photos', or any directory below that.
````
zoph -r IMG_1234.JPG Photos/
````

### `--dirpattern` ###
With `--dirpattern`, you can automatically assign albums, categories, people, photographer, location or path based on the directories the photos are in. You do this by specifying a pattern, based on which Zoph will use directory names to assign to correct organizer. This pattern consists of a list of letters, where each letter is a directory. This option makes no sense if you do not specify [--recursive](#--recursive) as well.

**Default:** No default.

**Options:** **a** (album), **c** (category), **l** (location), **p** (person), **P** (photographer) and **D** (path)

**Example:** `zoph -r --dirpattern "Paccc" *`
Import all files in the current directory **and** the directories below. For each path, assign the name of the first directory as photographer, the second as album, and the third, fourth and fifth as categories. For a more detailed example, see [Using dirpatterns](IMPORT-CLI.md#Using_dirpatterns) 

### `--path` ###
You may want to manually organize your photos in directories. You can use `--path` for that. The path is inserted between the image directory and (in case they are enabled) dated or hierarchical dated directories.

**Aliases:** `-D` 

**Default:** Photos are imported directly under the image dir.

**Options:** Valid path, relative to image dir.

**Example:** 
````
zoph --path "holiday" photo.jpg
zoph --path "travel/business" --dateddirs photo.jpg
````
