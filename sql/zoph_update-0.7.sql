#
# Zoph 0.6 -> 0.7 update
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

alter table zoph_prefs add allexif char(1) default "0" after camera_info;
alter table zoph_prefs add autocomp_albums char(1) default "1" after allexif;
alter table zoph_prefs add autocomp_categories char(1) default "1" after autocomp_albums;
alter table zoph_prefs add autocomp_photographer char(1) default "1" after autocomp_categories;
alter table zoph_prefs add autocomp_people char(1) default "1" after autocomp_photographer;
alter table zoph_prefs add autocomp_places char(1) default "1" after autocomp_albums;

alter table zoph_albums add coverphoto int(11) default NULL after album_description;    
