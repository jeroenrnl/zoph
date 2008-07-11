#
# Zoph 0.7 -> 0.8 update
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
# As of version 0.7.1 I am planning to release a few interim "feature" releases
# between two 'major' (0.7 and 0.8) releases, to make the 'time to market' for
# a new release shorter.
# If you upgrade to 0.7.1, be prepared to comment the changes for 0.7.1 once
# you upgrade to 0.7.2 or 0.8.
#

#
# Changes for 0.7.1
#

alter table zoph_users add column download char(1) NOT NULL DEFAULT '0' 
	after import;

alter table zoph_albums add column sortname char(32) 
	after album_description;
alter table zoph_categories add column sortname char(32)
	after category_description;
alter table zoph_prefs add column child_sortorder 
	enum('name', 'sortname', 'oldest', 'newest', 
		'first', 'last', 'lowest', 'highest', 'average') 
	default 'sortname' after autothumb;

#
# Changes for 0.7.2
#

CREATE TABLE zoph_pageset ( 
	pageset_id int(11) NOT NULL auto_increment, 
	title varchar(128), 
	show_orig enum('never', 'first', 'last', 'all') NOT NULL DEFAULT 'all',
	orig_pos enum('top', 'bottom') NOT NULL DEFAULT 'top',
	date datetime, 
	user int(11) , 
	timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	PRIMARY KEY  (pageset_id));

CREATE TABLE zoph_pages ( 
	page_id int(11) NOT NULL auto_increment, 
	title varchar(128), 
	text blob,
	date datetime,
	timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  
	PRIMARY KEY  (page_id));  

CREATE TABLE zoph_pages_pageset ( 
	pageset_id int(11) NOT NULL, 
	page_id int(11) NOT NULL, 
	page_order int(5) unsigned );

ALTER TABLE zoph_albums 
	ADD COLUMN pageset int(11) DEFAULT NULL after coverphoto;

ALTER TABLE zoph_categories
	ADD COLUMN pageset int(11) DEFAULT NULL after coverphoto;

ALTER TABLE zoph_places
	ADD COLUMN pageset int(11) DEFAULT NULL after coverphoto;

ALTER TABLE zoph_people
	ADD COLUMN pageset int(11) DEFAULT NULL after coverphoto;

#
# Changes for 0.7.3
#
ALTER TABLE zoph_places ADD COLUMN lat float(10,6);
ALTER TABLE zoph_places ADD COLUMN lon float(10,6);
ALTER TABLE zoph_places ADD COLUMN mapzoom tinyint unsigned;

ALTER TABLE zoph_photos ADD COLUMN lat float(10,6);
ALTER TABLE zoph_photos ADD COLUMN lon float(10,6);
ALTER TABLE zoph_photos ADD COLUMN mapzoom tinyint unsigned;

ALTER TABLE zoph_photos ADD COLUMN time_corr smallint NOT NULL default 0 after time;
ALTER TABLE zoph_places ADD COLUMN timezone varchar(50) default NULL;

# Change description from BLOB to TEXT
ALTER TABLE zoph_photos MODIFY COLUMN description TEXT;

# Make title for albums and categories longer
ALTER TABLE zoph_albums MODIFY COLUMN album varchar(64);
ALTER TABLE zoph_categories MODIFY COLUMN category varchar(64);
