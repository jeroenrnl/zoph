#
# Zoph 0.9 -> 0.10 update
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
# Between stable releases 0.9 and 0.10 several feature releases are planned
# If you upgrade to 0.9.x, be prepared to comment the changes for 0.9.1 once
# you upgrade to 0.9.2 or 0.10.
#

#
# Changes for 0.9.1
#
CREATE TABLE zoph_conf ( 
	conf_id char(64) NOT NULL, 
	value varchar(128) default NULL, 
	timestamp timestamp,
	PRIMARY KEY (conf_id)
	) ENGINE=MyISAM;

ALTER TABLE zoph_prefs DROP COLUMN desc_thumbnails;
