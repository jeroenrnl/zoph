#
# Zoph 0.4 -> 0.5 update
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


# This will change the length of the password field to 64 bytes.
# As of MySQL 4.1, the encrypted password size was too big for the old 32
# bytes. Zoph will take care of the conversion from the old to the new
# encryption method. When a user logs in for the first time since upgrading
# to MySQL 4.1, Zoph updates the password field.

ALTER TABLE zoph_users CHANGE password password varchar(64); 

# Changes for hierarchical locations

alter table zoph_places add parent_place_id int(11) NOT NULL after place_id;

insert into zoph_places select NULL, parent_place_id, contact_type, title, address, address2, city, state, zip, country, notes from zoph_places where place_id = 1;

update zoph_photos set location_id=last_insert_id() where location_id=1;

update zoph_places set parent_place_id=1;

# World may be a bad choice when you work for NASA ;-)
update zoph_places set
parent_place_id=0,
contact_type=0,
title="World",
address=NULL,
address2=NULL,
city=NULL,
state=NULL,
zip=NULL,
country=NULL,
notes=NULL
where place_id=1;

# Option for opening the full size image in a new window

alter table zoph_prefs add fullsize_new_win char(1) NOT NULL default '0';
alter table zoph_prefs add people_slots tinyint(3) NOT NULL default 1;

# Watermarking
alter table zoph_album_permissions add watermark_level tinyint(4) NOT NULL default '0' after access_level;
