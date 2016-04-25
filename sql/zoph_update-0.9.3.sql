#
# Zoph 0.9.2 -> 0.9.3 update
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

ALTER TABLE zoph_users CHANGE COLUMN password password varchar(255);

ALTER TABLE zoph_users ADD COLUMN view_all_photos CHAR(1) NOT NULL DEFAULT '0' AFTER class;
ALTER TABLE zoph_users ADD COLUMN delete_photos CHAR(1) NOT NULL DEFAULT '0' AFTER view_all_photos;
ALTER TABLE zoph_users ADD COLUMN edit_organizers CHAR(1) NOT NULL DEFAULT '0' AFTER browse_tracks;
