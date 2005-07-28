#
# Zoph 0.3 -> 0.3.1 update
# 30 Sep 2002
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
# create a pref for an always edit mode
#
alter table prefs add auto_edit char(1) not null default '0';

#
# create a pref for whether to display camera info
#
alter table prefs add camera_info char(1) not null default '1';

#
# create a field to define a lightbox for a user
#
alter table users add lightbox_id int;

#
# These tables were accidentally included in zoph.sql 0.3
#
drop table contact_types;
drop table email_addresses;
drop table links;
drop table people_email_addresses;
drop table people_phone_numbers;
drop table people_places;
drop table phone_numbers;
drop table photo_links;
drop table places_phone_numbers;
drop table related_photos;
