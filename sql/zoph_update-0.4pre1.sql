#
# Zoph 0.3.3 -> 0.4pre1 update
#

#
# rename tables using a zoph_ prefix to avoid naming conflicts.
# comment this out if you don't want/need this.
# the DB_PREFIX in config.inc.php and $db_prefix in zophImport
# and zophExport should be set appropriately.
#
rename table album_permissions to zoph_album_permissions;
rename table albums to zoph_albums;
rename table categories to zoph_categories;
rename table color_schemes to zoph_color_schemes;
rename table people to zoph_people;
rename table photo_albums to zoph_photo_albums;
rename table photo_categories to zoph_photo_categories;
rename table photo_people to zoph_photo_people;
rename table photos to zoph_photos;
rename table places to zoph_places;
rename table prefs to zoph_prefs;
rename table users to zoph_users;

alter table zoph_album_permissions add column changedate timestamp null;

alter table zoph_people add column email varchar(64) null;

alter table zoph_users add column lastnotify datetime null;
alter table zoph_users add column lastlogin datetime null;
alter table zoph_users add column lastip varchar(16) null;
