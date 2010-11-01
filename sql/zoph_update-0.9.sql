#
# Zoph 0.8 -> 0.9 update
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
# Between stable releases 0.8 and 0.9 several feature releases are planned
# If you upgrade to 0.8.x, be prepared to comment the changes for 0.8.1 once
# you upgrade to 0.8.2 or 0.9.
#

#
# Changes for 0.8.1
#

ALTER TABLE zoph_prefs MODIFY COLUMN language char(5) DEFAULT NULL;
#
# There are no db changes for 0.8.2
#

#
# Changes for 0.8.3
#
CREATE TABLE zoph_track (
  track_id int(11) NOT NULL auto_increment,
  name varchar(64) NOT NULL default 'track',
  PRIMARY KEY  (track_id)
) ENGINE=MyISAM;

CREATE TABLE zoph_point (
  point_id int(11) NOT NULL auto_increment,
  name varchar(64) default NULL,
  track_id int(11) default NULL,
  lat float(10,6) default NULL,
  lon float(10,6) default NULL,
  ele float(12,4) default NULL,
  speed float(12,4) default NULL,
  datetime datetime default NULL,
  PRIMARY KEY  (point_id)
) ENGINE=MyISAM;

ALTER TABLE zoph_users 
  ADD COLUMN browse_tracks char(1) NOT NULL DEFAULT 0 AFTER browse_places;  
