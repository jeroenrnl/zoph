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

alter table zoph_places add parent_place_id int(11) NOT NULL after place_id
