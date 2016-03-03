#
# Zoph 0.9.1 -> 0.9.2 update
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
