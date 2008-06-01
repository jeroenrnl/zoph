#!/usr/bin/perl -w

#
# zophExport.pl
# v0.7.2
# Jason Geiger & Jeroen Roos - 2002-2008
# This file is part of Zoph.
#
# Zoph is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Zoph is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Zoph; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
#
# zophExport.pl can be used to create static html for your photos in Zoph.
# This script, like zophImport.pl, is an ugly hack in many ways.
# But it mostly works.
#
# Example: zophExport.pl --dir ~/Christmas --format album --category Snow
#
# See the exporter section of the tutorial for more info.
#

use strict;
use Getopt::Long;
use DBI;
use File::Copy;

$| = 1;

die "Error: \$HOME/.zophrc not found"
  if !-e $ENV{HOME}."/.zophrc";

require $ENV{HOME}."/.zophrc" if -r $ENV{HOME}."/.zophrc";
my $db_prefix = $::db_prefix;
my $image_dir = $::image_dir;

my $version = '0.7.2';

my $export_dir; # where to export to
my $format;     # one of zoph, album, bins

my $language_file; # one of the files shipped with zoph
my %lang_hash;     # a place to store the translations

my $show_footer = 1; # for the zoph format

my $album_dirs  = 1;     # create dirs for albums
my $subalbums  = 1;      # get subalbums for albums
my $subcategories  = 1;  # get subcats for cats

my $encoding = 'UTF-8';  # default encoding

# what to display
my $show_date = 1;
my $show_title = 1;
my $show_view = 1;
my $show_people = 1;
my $show_photographer = 1;
my $show_location = 1;
my $show_description = 1;

my $show_exif = 1;
my @exif_array =
    ("camera_make", "camera_model", "flash_used", "focal_length",
    "exposure", "aperture", "iso_equiv", "metering_mode");

# these aren't used yet
my $show_categories = 0;
my $show_albums = 0;

# prefix, size and extension used only with zoph format
my $midPrefix = 'mid';
my $thumbPrefix = 'thumb';
my $midSize = 480;
my $thumbSize = 120;
# if mixed_thumbnails was set to false in zophImport.pl, set this
my $thumb_extension = ".jpg";

my %fieldHash; # --field name=value constraints

my %export_hash; # for saving album_id -> path info

my @albums;
my @categories;
my @people;

my $sql;

if ($#ARGV < 0) {
    printUsage();
    exit(0);
}

my $dbh = DBI->connect("DBI:mysql:$::db_name:$::db_host", $::db_user, $::db_pass);
$::db_name = '';
$::db_user = '';
$::db_pass = '';
$::db_host = '';
$::db_prefix = '';
$::image_dir = '';

GetOptions(
    'help' => sub { printUsage(); exit(0); },
    'dir=s' => \$export_dir,
    'format=s' => \$format,
    'albumdirs!' => \$album_dirs,
    'subalbums!' => \$subalbums,
    'subcategories!' => \$subcategories,
    'album|albums=s' => \@albums,
    'category|categories=s' => \@categories,
    'person|people=s' => \@people,
    'photographer=s' => sub {
        my ($n, $v) = @_;
        if ($v = lookupPersonId($v)) { $fieldHash{'photographer_id'} = $v; }
    },
    'location=s' => sub {
        my ($n, $v) = @_;
        if ($v = lookupPlaceId($v)) { $fieldHash{'location_id'} = $v; }
    },
    'field=s' => \%fieldHash,
    'sql=s' => \$sql,
    'lang=s' => \$language_file,
    'showdate!' => \$show_date,
    'showtitle!' => \$show_title,
    'showview!' => \$show_view,
    'showpeople!' => \$show_people,
    'showphotographer!' => \$show_photographer,
    'showlocation!' => \$show_location,
    'showdescription!' => \$show_description,
    'showexif!' => \$show_exif,
    'showfooter!' => \$show_footer
) or die "Error parsing options";

unless ($export_dir) {
    print "Error: Please specify an export directory.\n";
    exit(1);
}

unless ($format and ($format eq "zoph" or $format eq "album" or
    $format eq "bins")) {

    print "Error: Please specify an export format (zoph|album|bins).\n";
    exit(1);
}

print "zophExport.pl $version\n";

# do it
export();

$dbh->disconnect();


######################################################################

#
# Usage.
#
sub printUsage {
    print
        "zophExport.pl $version\n" .
        "Usage: zophExport.pl --dir <dir> --format zoph|album|bins [OPTIONS]\n" .
        "OPTIONS:\n" .
        "	--album ALBUM\n" .
        "	--category CATEGORY\n" .
        "	--photographer \"FIRST_NAME LAST_NAME\"\n" .
        "	--people \"FIRST_NAME LAST_NAME, FIRST_NAME LAST_NAME\"\n" .
        "	--location PLACE_TITLE\n" .
        "	--field NAME=VALUE\n" .
        "	--sql SQL\n" .
        "	--lang <file>\n" .
        "	--[no]albumdirs\n" .
        "	--[no]subalbums\n" .
        "	--[no]subcategories\n" .
        "	--[no]showdate\n" .
        "	--[no]showtitle\n" .
        "	--[no]showview\n" .
        "	--[no]showpeople\n" .
        "	--[no]showphotographer\n" .
        "	--[no]showlocation\n" .
        "	--[no]showdescription\n" .
        "	--[no]showexif\n" .
        "	--[no]showfooter\n";
}

#
# Do the export.
#
sub export {

    if ($language_file) {
        if (open LANG, $language_file) {
            print "Reading in language file\n";
            # don't really need most strings but get them all
            while (<LANG>) {
                $encoding = $1 if /"charset"=>"([^"]*)/;
                next if /^#/;
                my ($en, $trans) = split '=';
                $lang_hash{$en} = $trans;
            }
            close LANG;
        }
        else {
            warn "Could not open language file: $language_file\n";
        }
        print "Using $encoding encoding\n";
    }

    $export_dir =~ s/\/$//;

    if (not -d $export_dir) {
        mkdir($export_dir, 0755)
            or die "Could not make directory: $export_dir\n";
    }

    if ($format eq "bins") {
        # make an album.xml for the top dir
        (my $title = $export_dir) =~ s/.*\///;
        $title =~ s/_/ /g;

        $export_dir .= ".tmp";

        # copy files to a temp directory.  bins creates its own copies.
        if (not -d $export_dir) {
            mkdir($export_dir, 0755)
                or die "Could not make directory: $export_dir\n";
        }

        makeAlbumXml($export_dir, $title, "-");

    }

    if (not $sql) {
        $sql = generateSql();
    }
    #print "$sql\n";

    my $sth = $dbh->prepare($sql);
    $sth->execute() or die "Error: Could not execute sql: $sql\n";

    my $photo_hash;
    my $count = 0;
    while ($photo_hash = $sth->fetchrow_hashref()) {

        if ($format eq "zoph") {
            simpleExport($photo_hash);
        }
        elsif ($format eq "album") {
            albumExport($photo_hash);
        }
        elsif ($format eq "bins") {
            binsExport($photo_hash);
        }

        print ".";
        $count++;
    }

    if ($format eq "zoph") {
        createIndexFiles($export_dir);
    }

    print "\nExported $count photos.\n";

    if ($count == 0) { return; }

    if ($format eq "album") {
        print "Now run album.  For example:\n";
        print "album -index index -medium \"480x360\" \"$export_dir\"\n";
    }
    elsif ($format eq "bins") {
        print "Now run bins.  For example:\n";
        (my $orig_dir = $export_dir) =~ s/\.tmp$//;
        print "bins \"$export_dir\" \"$orig_dir\"\n";
        print "When bins finishes, you may delete $export_dir.\n";
    }
}

#
# Generates a sql statement to look up photos.
#
sub generateSql {
    my $from = $db_prefix . "photos as ph";
    my $where = "";
    my $field;
    foreach $field (keys %fieldHash) {
        my $value = $dbh->quote($fieldHash{$field});
        if ($where) { $where .= " and "; }
        my $op = "=";
        if ($field eq "rating") {
            $op = ">=";
        }
        if ($field eq "description") {
            $op = "like";
            $value =~ s/'(.*)'/'%$1%'/;
        }
        $where .= "ph.$field $op $value";
    }

    #@albums = split(/\s*,\s*/, join(',', @albums)); I have albums with commas
    if (@albums) {
        $from .= ", " . $db_prefix . "photo_albums as pa";
        if ($where) { $where .= " and "; }

        my $prev = 0;
        my @album_ids = grep($_ ne $prev && ($prev = $_),
            sort map(getAlbumIds($_), @albums));
        $where .= "ph.photo_id = pa.photo_id and pa.album_id in (" .
            join(',', @album_ids) . ")";
    }

    @categories = split(/\s*,\s*/, join(',', @categories));
    if (@categories) {
        $from .= ", " . $db_prefix . "photo_categories as pc";
        if ($where) { $where .= " and "; }

        my $prev = 0;
        my @category_ids = grep($_ ne $prev && ($prev = $_),
            sort map(getCategoryIds($_), @categories));

        $where .= "ph.photo_id = pc.photo_id and pc.category_id in (" .
            join(',', sort @category_ids) . ")";
    }

    @people = split(/\s*,\s*/, join(',', @people));
    if (@people) {
        $from .= ", " . $db_prefix . "photo_people as pp";
        if ($where) { $where .= " and "; }

        my $prev = 0;
        my @person_ids = grep($_ ne $prev && ($prev = $_),
            sort map(lookupPersonId($_), @people));

        $where .= "ph.photo_id = pp.photo_id and pp.person_id in (" .
            join(',', sort @person_ids) . ")";
    }

    if ($where) { $where = "where $where"; }
    my $sql = "select ph.* from $from $where order by ph.date asc, ph.time asc";

    return $sql;
}

#
# Gets the path for where to put a particular photo.
# Creates new directories if needed.
#
sub getExportPath {
    my ($photo_id) = @_;

    # if --noalbumDirs was passed, don't make album directories
    if (not $album_dirs) {
        return $export_dir;
    }

    my $export_path = $export_dir;

    my @album_ids = getRelatedIds($photo_id, "album_id", "photo_albums");
    if (@album_ids) {
        my $album_id = $album_ids[0]; # take first (not always desireable)

        if (defined($export_hash{$album_id})) {
            $export_path = $export_hash{$album_id};
        }
        else {
            my @ancestors = getAncestorNames($album_id, "album", "albums");

            foreach my $ancestor (@ancestors) {
                $export_path .= '/' . $ancestor;
                
                if (not -d $export_path) {
                    mkdir($export_path, 0755)
                        or die "Could not make directory: $export_path\n";

                    (my $msg = $export_path) =~ s/\Q$export_dir\E\/?//;
                    $msg =~ s/\// > /g;
                    print "\n$msg\n";

                    if ($format eq "bins") {
                        # create an album.xml file

                        # this will fail if there are two albums with
                        # the same name.  This is often empty anyway.
                        my $description = getName("'$ancestor'", "album",
                            "album_description", "albums", 1);
                        if (not $description) { $description = " "; }

                        makeAlbumXml($export_path, $ancestor, $description);
                    }
                }
            }

            $export_hash{$album_id} = $export_path;
        }
    }

    return $export_path;
}

#
# Looks up a person_id from a FirstName LastName.
#
sub lookupPersonId {
    my ($person) = @_;

    $person = lc($person);

    my ($first, $last) = split / +/, $person;
    my $query =
        "select person_id from " . $db_prefix . "people where " .
        "lower(first_name) = " .  $dbh->quote($first) . " and " .
        "lower(last_name) = " .  $dbh->quote($last);

    my @row_array = $dbh->selectrow_array($query);

    if (@row_array) {
        return $row_array[0];
    }

    print "Person not found: $person\n";
    return 0;
}

#
# Looks up a place_id from the title.
#
sub lookupPlaceId {
    my ($place) = @_;

    $place = lc($place);

    my $query =
        "select place_id from " . $db_prefix . "places where " .
        "lower(title) = " .  $dbh->quote($place);

    my @row_array = $dbh->selectrow_array($query);

    if (@row_array) {
        return $row_array[0];
    }

    print "Place not found: $place\n";
    return 0;
}

#
# Looks up an album_id from an album name.
#
sub lookupAlbumId {
    my ($album) = @_;
    return getId($album, "album", "albums");
}

#
# Gets an array of an album's id plus the ids of its descendants.
#
sub getAlbumIds {
    my ($album) = @_;

    my $album_id = getId($album, "album", "albums");
    my @album_ids = ();
    push @album_ids, $album_id;

    if ($subalbums) {
        push @album_ids, getChildrenIds($album_id, "album", "albums");
    }

    return @album_ids;
}

#
# Looks up a category_id from a category name.
#
sub lookupCategoryId {
    my ($cat) = @_;
    return getId($cat, "category", "categories");
}

#
# Gets an array of a category's id plus the ids of its descendants.
#
sub getCategoryIds {
    my ($category) = @_;

    my $category_id = getId($category, "category", "categories");
    my @category_ids = ();
    push @category_ids, $category_id;

    if ($subcategories) {
        push @category_ids, getChildrenIds($category_id, "category", "categories");
    }

    return @category_ids;
}

#
# Looks up an id in a table.
#
sub getId {
    my ($value, $field, $table) = @_;

    my $sql =
        "select $field" . "_id from " . $db_prefix . "$table " .
        "where lower($field) = " .  $dbh->quote(lc($value));

    my $sth = $dbh->prepare($sql);
    $sth->execute() or die "Error: Could not execute sql: $sql\n";

    my $id = $sth->fetchrow_array();

    if (not $id) {
        print "Error: $field not found: $value\n";
    }

    return $id;
}

#
# Gets the value of some field in some table using some constraint.
# Mostly for getting a name using an id.
#
sub getName {
    my ($id, $field, $name, $table, $suppress) = @_;

    my $sql = "select $name from " . $db_prefix . "$table where $field = $id";
    #print "$sql\n";

    my $sth = $dbh->prepare($sql);
    $sth->execute() or die "Error: Could not execute sql: $sql\n";

    my $val = $sth->fetchrow_array();

    if (not $val and not $suppress) {
        print "Error: $field not found: $id\n";
    }

    return $val;
}

#
# Gets an array of ids using some constraint.
# Used with the photo_album, photo_category and photo_people tables.
#
sub getRelatedIds {
    my ($id, $field, $table) = @_;

    my $sql = "select $field from " . $db_prefix . "$table where photo_id = $id";

    my $sth = $dbh->prepare($sql);
    $sth->execute() or die "Error: Could not execute sql: $sql\n";

    my @vals = $sth->fetchrow_array();

    return @vals;
}

#
# Recursively get children ids (for albums or categories).
#
sub getChildrenIds {
    my ($id, $field, $table) = @_;

    my $sql =
        "select $field" . "_id from " . $db_prefix . "$table " .
        "where parent_" . $field . "_id = " .  $dbh->quote($id);

    my $sth = $dbh->prepare($sql);
    $sth->execute() or die "Error: Could not execute sql: $sql\n";

    my @children_ids = ();
    while (my $child_id = $sth->fetchrow_array()) {
        push @children_ids, $child_id;
        push @children_ids, getChildrenIds($child_id, $field, $table);
    }

    return @children_ids;
}

#
# Recursively get children ids (for albums or categories).
#
sub getAncestorNames {
    my ($id, $field, $table) = @_;

    my $sql =
        "select parent_$field" . "_id, $field from " . $db_prefix . "$table " .
        "where $field" . "_id = " .  $dbh->quote($id);

    my $sth = $dbh->prepare($sql);
    $sth->execute() or die "Error: Could not execute sql: $sql\n";

    my @ancestors = ();
    if (my ($parent_id, $name) = $sth->fetchrow_array()) {
        if ($parent_id > 0) {
            push @ancestors, getAncestorNames($parent_id, $field, $table);
            push @ancestors, $name;
        }
    }

    return @ancestors;
}

#
# Gets the names of the people in a photo.
#
sub getPeopleInPhoto {
    my ($photo_id) = @_;

    my $people = "";
    my @person_ids = getRelatedIds($photo_id, "person_id", "photo_people");
    foreach my $person_id (@person_ids) {
        my $person = getName($person_id, "person_id", "first_name", "people");

        if ($person) {
            if ($people) { $people .= ", "; }
            $people .= $person;
        }
    }

    return $people;
}

#
# Exports in a format suitable for the album program.
#
sub albumExport {
    my ($photo_hash) = @_;

    my $photo_id = $photo_hash->{'photo_id'};
    my $name = $photo_hash->{'name'};
    my $path = $photo_hash->{'path'};

    my $export_path = getExportPath($photo_id);

    unless (copy("$image_dir/$path/$name", "$export_path/$name")) {
        warn "Could not copy file: $image_dir/$path/$name\n";
        return;
    }

    open CAPTIONS, ">>$export_path/captions.txt"
        or die "Could not open captions file\n";

    my $title = $photo_hash->{'title'};
    if (not $title) { $title = $name; }

    my $captions = "";

    if ($show_people) {
        my $people = getPeopleInPhoto($photo_id);
        if ($people) {
            $captions .= "$people<p>";
        }
    }

    if ($show_date) {
        my $date = $photo_hash->{'date'};
        my $time = $photo_hash->{'time'};

        my $datestr = "";
        if ($date) { $datestr .= $date; }
        if ($time) { $datestr .= " $time"; }

        if ($datestr) {
            $captions .= $datestr;
        }
    }

    if ($show_view and $photo_hash->{'view'}) {
        $captions .= "<br>" . $photo_hash->{'view'};
    }
    if ($show_location and $photo_hash->{'location_id'}) {
        my $place_id = $photo_hash->{'location_id'};
        my $location = getName($place_id, "place_id", "title", "places");
        $captions .= "<br>location: $location";
    }
    if ($show_photographer and $photo_hash->{'photographer_id'}) {
        my $person_id = $photo_hash->{'photographer_id'};
        my $person = getName($person_id, "person_id", "concat(concat(first_name, \" \"), last_name)", "people");
        $captions .= "<br>photographer: $person";
    }
    if ($show_description and $photo_hash->{'description'}) {
        my $description = $photo_hash->{'description'};
        $description =~ s/(\r|\n)//g;
        $captions .= "<p>$description";
    }
    if ($show_exif) {
        $captions .= "<p>";
        foreach my $field (@exif_array) {
            my $value = $photo_hash->{$field};
            if ($value) {
                (my $field_name = $field) =~ s/_/ /g;
                $captions .= "$field_name: $value<br>";
            }
        }
    }

    print CAPTIONS "$name\t$title\t$captions\n";
    close CAPTIONS;
}

#
# Exports in a format suitable for the BINS program.
#
sub binsExport {
    my ($photo_hash) = @_;

    my $photo_id = $photo_hash->{'photo_id'};
    my $name = $photo_hash->{'name'};
    my $path = $photo_hash->{'path'};

    my $export_path = getExportPath($photo_id);

    unless (copy("$image_dir/$path/$name", "$export_path/$name")) {
        warn "Could not copy file: $image_dir/$path/$name\n";
        return;
    }

    open IMAGE_XML, ">$export_path/$name.xml"
        or die "Could not open $name.xml file\n";

    print IMAGE_XML "<?xml version=\"1.0\" encoding=\"$encoding\"?><image>\n";
    print IMAGE_XML "   <description>\n";

    if ($show_description and $photo_hash->{'description'}) {
        my $description = encodeEntities($photo_hash->{'description'});
        print IMAGE_XML "      <field name=\"description\">\n";

        if ($show_view and $photo_hash->{'view'}) {
            print IMAGE_XML "<br/>view: " .
                encodeEntities($photo_hash->{'view'}) . "\n";
        }

        if ($show_photographer and $photo_hash->{'photographer_id'}) {
            my $person_id =
                encodeEntities($photo_hash->{'photographer_id'});
            my $person = getName($person_id, "person_id", "concat(concat(first_name, \" \"), last_name)", "people");
            print IMAGE_XML "<br/>photographer: " .
                encodeEntities($person) . "\n";
    }

        print IMAGE_XML "<p>\n" . encodeEntities($description) . "\n</p>\n";
        print IMAGE_XML "      </field>\n\n";

    }
    if ($show_location and $photo_hash->{'location_id'}) {
        my $place_id = $photo_hash->{'location_id'};
        my $location = getName($place_id, "place_id", "title", "places");
        print IMAGE_XML "      <field name=\"location\">\n";
        print IMAGE_XML encodeEntities($location) . "\n";
        print IMAGE_XML "      </field>\n\n";
    }
    if ($show_title and $photo_hash->{'title'}) {
        my $title = $photo_hash->{'title'};
        print IMAGE_XML "      <field name=\"title\">\n";
        print IMAGE_XML encodeEntities($title) . "\n";
        print IMAGE_XML "      </field>\n\n";
    }

    if ($show_people) {
        my $people = getPeopleInPhoto($photo_id);
        if ($people) {
            print IMAGE_XML "      <field name=\"people\">\n";
            print IMAGE_XML encodeEntities($people) . "\n";
            print IMAGE_XML "      </field>\n\n";
        }
    }

    print IMAGE_XML "  </description>\n  <bins>\n  </bins>\n  <exif>\n  </exif>\n</image>";
    close IMAGE_XML;
}

#
# Exports simple html files.
#
sub simpleExport {
    my ($photo_hash) = @_;

    my $photo_id = $photo_hash->{'photo_id'};
    my $name = $photo_hash->{'name'};
    my $path = $photo_hash->{'path'};

    my $export_path = getExportPath($photo_id);

    unless (copy("$image_dir/$path/$name", "$export_path/$name")) {
        warn "Could not copy file: $image_dir/$path/$name\n";
        return;
    }

    my $mid_name = $midPrefix . "_$name";
    if (not -f "$image_dir/$path/$midPrefix/$mid_name") {
        $mid_name =~ s/\.[^.]*$//;
        $mid_name .= ".$thumb_extension";
        if (not -f "$image_dir/$path/$midPrefix/$mid_name") {
            die "Could not find midsize image.  Check thumb_extension?\n";
        }
    }
    unless (copy("$image_dir/$path/$midPrefix/$mid_name", "$export_path/$mid_name")) {
        warn "Could not copy file: $image_dir/$path/$midPrefix/$mid_name\n";
        return;
    }

    my $thumb_name = $thumbPrefix . "_$name";
    if (not -f "$image_dir/$path/$thumbPrefix/$thumb_name") {
        $thumb_name =~ s/\.[^.]*$//;
        $thumb_name .= ".$thumb_extension";
        if (not -f "$image_dir/$path/$thumbPrefix/$thumb_name") {
            die "Could not find thumbnail image.  Check thumb_extension?\n";
        }
    }
    unless (copy("$image_dir/$path/$thumbPrefix/$thumb_name", "$export_path/$thumb_name")) {
        warn "Could not copy file: $image_dir/$path/$thumbPrefix/$thumb_name\n";
        return;
    }

    my $dimensions = $photo_hash->{'width'} . " x " . $photo_hash->{'height'};
    my $size = $photo_hash->{'size'} . " " . translate("bytes");

    my $header = createAlbumList($export_path);

    open IMAGE_FILE, ">$export_path/$name.html"
        or die "Could not open $name.html\n";

    print IMAGE_FILE <<"(HEADER)";
<html>
<head>
<title>$name</title>
</head>
<body>

<strong>
$header
</strong>
<hr>

<div align="center">
<a href="$name">$name</a> : $dimensions, $size
<br>
<a href="$name"><img src="$mid_name" border="0"></a>
<br>
(HEADER)

    my $write_header = 0;
    if (not -f "$export_path/index.html") {
        $write_header = 1;
    }

    open INDEX_FILE, ">>$export_path/index.html"
        or die "Could not open index.html\n";

    if ($write_header) {
        print INDEX_FILE <<"(HEADER)";
<html>
<head>
<title></title>
</head>
<body>

<strong>
$header
</strong>
<hr>

(HEADER)

        print INDEX_FILE createAlbumBullets($export_path);

    }

    print INDEX_FILE <<"(IMAGE)";
<p>
<table>
<tr>
  <td align="left" valign="top" width="$thumbSize">
    <a href="$name.html"><img align="left" border="0" src="$thumb_name"></a>
  </td>
  <td align="left" valign="top">
    <a href="$name.html">$name</a>

(IMAGE)

    if ($show_people) {
        my $people = getPeopleInPhoto($photo_id);
        if ($people) {
            print IMAGE_FILE "$people<p>\n";
        }
    }

    print IMAGE_FILE "<table cellspacing=\"4\">\n";

    if ($show_date) {
        my $date = $photo_hash->{'date'};
        my $time = $photo_hash->{'time'};

        my $datestr = "";
        if ($date) { $datestr .= $date; }
        if ($time) { $datestr .= " $time"; }

        if ($datestr) {
            print IMAGE_FILE createRow("date", "$datestr");
            print INDEX_FILE "<br>\n$datestr";
        }
    }

    if ($show_view and $photo_hash->{'view'}) {
        print IMAGE_FILE createRow("view", $photo_hash->{'view'});
    }
    if ($show_location and $photo_hash->{'location_id'}) {
        my $place_id = $photo_hash->{'location_id'};
        my $location = getName($place_id, "place_id", "title", "places");
        print IMAGE_FILE createRow("location", $location);
        print INDEX_FILE "<br>\n" . translate("location") . ": $location";
    }
    if ($show_photographer and $photo_hash->{'photographer_id'}) {
        my $person_id = $photo_hash->{'photographer_id'};
        my $person = getName($person_id, "person_id", "concat(concat(first_name, \" \"), last_name)", "people");
        print IMAGE_FILE createRow("photographer", $person);
        print INDEX_FILE "<br>\n" . translate("photographer") . ": $person";
    }

    if ($show_description and $photo_hash->{'description'}) {
        my $description = $photo_hash->{'description'};
        print IMAGE_FILE "<tr>\n  <td colspan=\"2\"><hr></td>\n</tr>\n";
        print IMAGE_FILE "<tr>\n  <td colspan=\"2\">\n$description\n  </td>\n</tr>\n";
        print INDEX_FILE "  </td>\n</tr>\n<tr>\n  <td colspan=\"2\">\n$description\n";
    }

    if ($show_exif) {
        print IMAGE_FILE "<tr>\n  <td colspan=\"2\"><hr></td>\n</tr>\n";
        foreach my $field (@exif_array) {
            my $value = $photo_hash->{$field};
            if ($value) {
                (my $field_name = $field) =~ s/_/ /g;
                print IMAGE_FILE createRow($field_name, $value);
            }
        }
    }

    print IMAGE_FILE "\n  </td>\n</tr>\n</table>\n";
    print IMAGE_FILE "\n</div>\n</body>\n</html>";
    close IMAGE_FILE;

    print INDEX_FILE "\n  </td>\n</tr>\n</table></p>\n";
    close INDEX_FILE;

}

#
# Makes an album.xml file for the BINS program.
#
sub makeAlbumXml {
    my ($path, $title, $description) = @_;

    if (open ALBUM_XML, ">$path/album.xml") {
        print ALBUM_XML <<"(XML)";
<?xml version="1.0" encoding="$encoding"?>
<album>
  <description>
    <field name="longdesc">
      $description
    </field>

    <field name="title">
      $title
    </field>
  </description>
  <bins>
  </bins>
</album>
(XML)
        close ALBUM_XML;
    }
    else {
        warn "Could not open file: $path/album.xml";
    }
}

#
# Encodes a few html entities (&,>,<).  This really should use the HTML perl
# module to do this properly.  Next time.
#
sub encodeEntities {
    my ($string) = @_;
    $string =~ s/&/&amp;/g;
    $string =~ s/>/&gt;/g;
    $string =~ s/</&lt;/g;
    return $string;
}

#
# Makes a row for a table.
#
sub createRow {
    my ($name, $value) = @_;

    $name = translate($name);
    return "<tr>\n  <td align=\"right\">$name</td>\n  <td>$value</td>\n</tr>\n";
}

#
# Makes the "Parent Album > Child Album" bar on top.
#
sub createAlbumList {
    my ($export_path, $extra) = @_;

    if (not $extra) { $extra = 0; }

    my $top_album = $export_dir;
    $top_album =~ s/\/$//;
    $top_album =~ s/.*\///;

    my $album_path = $export_path;
    $album_path =~ s|//|/|g;

    $album_path =~ s/.*(\Q$top_album\E.*)/$1/;
    @albums = split '/', $album_path;
    my $rel_path = '../' x (scalar @albums - 1 + $extra);

    my $header;
    foreach my $album (split '/', $album_path) {
        if ($header) { $header .= " &gt; "; }

        #map { s/([^a-zA-Z0-9_\-.])/uc sprintf("%%%02x",ord($1))/eg } @albums;
        #$href = join '/', @albums;

        $album =~ s/_/ /;

        if (not $rel_path) { $rel_path = "./"; }
        $header .= "<a href=\"$rel_path" . "index.html\">$album</a>";
        $rel_path  =~ s/^\.\.\///;
    }

    return $header;
}

#
# Creates a bulleted list of subalbums.
#
sub createAlbumBullets {
    my ($path) = @_;

    my $bullets = "";
    foreach my $album (getDirs($path)) {
        $album =~ s/.*\///;
        $bullets .= "<li><a href=\"$album/index.html\">$album</a></li>\n";
    }

    close PATH;

    if ($bullets) {
        $bullets = "<ul>\n$bullets</ul>\n<hr>\n";
    }

    return $bullets;
}

#
# Generates missing index files (for albums with no photos)
# and adds footer to existing index files.
#
sub createIndexFiles {
    my ($path) = @_;

    my $footer = "";
    if ($show_footer) {
        $footer = "<p>" . localtime;
    }

    if (not -f "$path/index.html") {

        open INDEX_FILE, ">$path/index.html"
            or die "Could not open index.html\n";

        my $header = createAlbumList($path);
        my $bullets = createAlbumBullets($path);

        print INDEX_FILE <<"(HEADER)";
<html>
<head>
<title></title>
</head>
<body>

<strong>
$header
</strong>
<hr>
$bullets
<p>
$footer
</body>
</html>

(HEADER)

        close INDEX_FILE;
    }
    else {
        open INDEX_FILE, ">>$path/index.html"
            or die "Could not open index.html\n";

        print INDEX_FILE "<p>$footer\n</body>\n</html>";
        close INDEX_FILE;
    }

    foreach my $album (getDirs($path)) {
        createIndexFiles($album);
    }
}

#
# Gets the subdirectories in a directory.
#
sub getDirs {
    my ($path) = @_;

    opendir PATH, $path or die "Could not open directory: $path\n";
    my @dirs = sort { -M $a <=> -M $b } grep !/\/\.+$/, grep -d,
        map "$path/$_", readdir PATH;
    close PATH;

    return @dirs;
}

#
# Translates a string.
#
sub translate {
    my ($string) = @_;

    if (%lang_hash and $lang_hash{$string}) {
        return $lang_hash{$string};
    }

    return $string;
}
