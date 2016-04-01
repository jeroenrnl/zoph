#
# Zoph 0.9 -> 0.9.1 update
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
# Changes for 0.9.1
#
CREATE TABLE zoph_conf ( 
	conf_id char(64) NOT NULL, 
	value varchar(128) default NULL, 
	timestamp timestamp, 
	PRIMARY kEY (conf_id)
	) ENGINE=MyISAM;

ALTER TABLE zoph_prefs DROP COLUMN desc_thumbnails;

ALTER TABLE zoph_photos DROP COLUMN rating;

CREATE INDEX photo_id ON zoph_photo_ratings(photo_id);

CREATE VIEW zoph_view_photo_avg_rating AS 
	SELECT p.photo_id, avg(pr.rating) AS rating FROM zoph_photos AS p 
	LEFT JOIN zoph_photo_ratings AS pr ON p.photo_id = pr.photo_id 
	GROUP BY p.photo_id;
