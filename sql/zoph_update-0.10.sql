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
	PRIMARY kEY (conf_id)
	) ENGINE=MyISAM;

ALTER TABLE zoph_prefs DROP COLUMN desc_thumbnails;

ALTER TABLE zoph_photos DROP COLUMN rating;

CREATE INDEX photo_id ON zoph_photo_ratings(photo_id);

CREATE VIEW zoph_view_photo_avg_rating AS 
	SELECT p.photo_id, avg(pr.rating) AS rating FROM zoph_photos AS p 
	LEFT JOIN zoph_photo_ratings AS pr ON p.photo_id = pr.photo_id 
	GROUP BY p.photo_id;

#
# Changes for 0.9.2
#
ALTER TABLE zoph_prefs MODIFY COLUMN child_sortorder enum('name', 'sortname', 'oldest', 'newest',
        'first', 'last', 'lowest', 'highest', 'average', 'random') default 'sortname' NOT NULL;

ALTER TABLE zoph_users MODIFY COLUMN lastip varchar(48);

CREATE TABLE zoph_circles (
        circle_id int(11) NOT NULL auto_increment,
        circle_name varchar(32) default NULL,
        description varchar(128) default NULL,
        coverphoto int(11) default NULL,
	hidden char(1) default '0',
        PRIMARY KEY  (circle_id)
) ENGINE=MyISAM;

CREATE TABLE zoph_circles_people (
        circle_id int(11) NOT NULL default '0',
        person_id int(11) NOT NULL default '0',
        changedate timestamp NOT NULL,
        PRIMARY KEY  (circle_id,person_id)
) ENGINE=MyISAM;

ALTER TABLE zoph_users ADD COLUMN see_hidden_circles char(1) NOT NULL default '0';

