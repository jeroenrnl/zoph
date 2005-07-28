#
# Zoph 0.2.1 -> 0.3 update
# 20 Sep 2002
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
#
# increase size of name and path photo fields
#
alter table photos modify name varchar(128);
alter table photos modify path varchar(255);

#
# create timestamp field
#
alter table photos add timestamp timestamp(14);

#
# create a language pref field
#
alter table prefs add language char(2);

#
# create a field for the number of days used by the view recent links
#
alter table prefs add recent_photo_days smallint not null default 7;

#
# create a import permission field
#
alter table users add import char(1) not null default '0';
