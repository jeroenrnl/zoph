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
# focal length was not long enough for some cameras
#
alter table photos modify focal_length varchar(64);

#
# let these descriptions be larger too
#
alter table albums modify album_description varchar(255);
alter table categories modify category_description varchar(255);

#
# a couple new photo fields
#
alter table photos add focus_dist varchar(16);
alter table photos add ccd_width varchar(16);
alter table photos add comment varchar(128);

#
# forgot to make this not null
#
alter table users modify detailed_people char(1) not null default '0';

#
# change state from char(2) to varchar(32) so it can be more versatile
#
alter table places modify state varchar(32);
