#
# Zoph 0.3.3 -> 0.4 update
#
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
# You should have received a copy of the GNU General Public License
# along with Zoph; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


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

CREATE TABLE zoph_photo_ratings (
  user_id int NOT NULL,
  photo_id int NOT NULL,
  rating tinyint NOT NULL,
  PRIMARY KEY (user_id, photo_id)
);

insert into zoph_photo_ratings (user_id, photo_id, rating)
  select '1', photo_id, rating from zoph_photos where rating is not null;

alter table zoph_photos modify rating float (4,2) unsigned;
