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

-- MySQL dump 8.22
--
-- Host: localhost    Database: zoph
---------------------------------------------------------
-- Server version	3.23.54

--
-- Table structure for table 'zoph_album_permissions'
--

CREATE TABLE zoph_album_permissions (
  user_id int(11) NOT NULL default '0',
  album_id int(11) NOT NULL default '0',
  access_level tinyint(4) NOT NULL default '0',
  watermark_level tinyint(4) NOT NULL default '0',
  writable char(1) NOT NULL default '0',
  changedate timestamp(14) NOT NULL,
  PRIMARY KEY  (user_id,album_id),
  KEY ap_access_level (access_level)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_album_permissions'
--



--
-- Table structure for table 'zoph_albums'
--

CREATE TABLE zoph_albums (
  album_id int(11) NOT NULL auto_increment,
  parent_album_id int(11) NOT NULL default '0',
  album varchar(32) NOT NULL default '',
  album_description varchar(255) default NULL,
  sortorder varchar(32) default NULL,
  PRIMARY KEY  (album_id),
  KEY album_parent_id (parent_album_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_albums'
--


INSERT INTO zoph_albums VALUES (1,0,'Album Root',NULL, NULL);

--
-- Table structure for table 'zoph_categories'
--

CREATE TABLE zoph_categories (
  category_id int(11) NOT NULL auto_increment,
  parent_category_id int(11) NOT NULL default '0',
  category varchar(32) NOT NULL default '',
  category_description varchar(255) default NULL,
  sortorder varchar(32) default NULL;
  PRIMARY KEY  (category_id),
  KEY cat_parent_id (parent_category_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_categories'
--


INSERT INTO zoph_categories VALUES (1,0,'Category Root',NULL);

--
-- Table structure for table 'zoph_color_schemes'
--

CREATE TABLE zoph_color_schemes (
  color_scheme_id int(11) NOT NULL auto_increment,
  name varchar(64) NOT NULL default '',
  page_bg_color varchar(6) default NULL,
  text_color varchar(6) default NULL,
  link_color varchar(6) default NULL,
  vlink_color varchar(6) default NULL,
  table_bg_color varchar(6) default NULL,
  table_border_color varchar(6) default NULL,
  breadcrumb_bg_color varchar(6) default NULL,
  title_bg_color varchar(6) default NULL,
  tab_bg_color varchar(6) default NULL,
  tab_font_color varchar(6) default NULL,
  selected_tab_bg_color varchar(6) default NULL,
  selected_tab_font_color varchar(6) default NULL,
  title_font_color varchar(6) default NULL,
  PRIMARY KEY  (color_scheme_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_color_schemes'
--


INSERT INTO zoph_color_schemes VALUES (1,'default','ffffff','000000','111111','444444','ffffff','000000','ffffff','f0f0f0','000000','ffffff','c0c0c0','000000','000000');
INSERT INTO zoph_color_schemes VALUES (2,'blugram','909090','000000','111111','333333','eef0f0','000000','cce0e0','dde0cc','ccd0bb','000000','bbd0d0','000000','000000');
INSERT INTO zoph_color_schemes VALUES (3,'dow','444444','000000','000055','000033','cccccc','000000','aaaaaa','2222aa','2222aa','ffffff','cccccc','000000','ffffff');
INSERT INTO zoph_color_schemes VALUES (4,'hoenig','FFEFD6','5C1F00','330000','330000','FFFBF5','000000','FFF7EB','FFE7C2','FFE7C2','5C1F00','FFD799','000000','993300');
INSERT INTO zoph_color_schemes VALUES (5,'forest','336633','000000','000000','000000','99CC99','000000','669966','663300','663300','E0E0E0','996633','FFFFFF','99CC99');
INSERT INTO zoph_color_schemes VALUES (6,'black','000000','FFFFFF','FFFFFF','FFFFFF','000000','FFFFFF','000000','666666','666666','FFFFFF','999999','FFFFFF','FFFFFF');
INSERT INTO zoph_color_schemes VALUES (7,'beach','646D7E','000000','000000','000000','F9EEE2','000000','9AADC7','C6DEFF','617C58','D0D0D0','8BB381','000000','646D7E');

--
-- Table structure for table 'zoph_comments'
--

CREATE TABLE zoph_comments (
  comment_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  comment_date datetime default NULL,
  subject varchar(255) default NULL,
  comment blob,
  timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  ipaddr varchar(16) default '',
  PRIMARY KEY  (comment_id)
) TYPE=MyISAM;

--
-- Table structure for table 'zoph_people'
--

CREATE TABLE zoph_people (
  person_id int(11) NOT NULL auto_increment,
  first_name varchar(32) default NULL,
  last_name varchar(32) default NULL,
  middle_name varchar(32) default NULL,
  called varchar(16) default NULL,
  gender char(1) default NULL,
  dob date default NULL,
  dod date default NULL,
  home_id int(11) default NULL,
  work_id int(11) default NULL,
  father_id int(11) default NULL,
  mother_id int(11) default NULL,
  spouse_id int(11) default NULL,
  notes varchar(255) default NULL,
  email varchar(64) default NULL,
  PRIMARY KEY  (person_id),
  KEY person_last_name (last_name(10)),
  KEY person_first_name (first_name(10))
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_people'
--


INSERT INTO zoph_people VALUES (1,'Unknown','Person',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

--
-- Table structure for table 'zoph_photo_albums'
--

CREATE TABLE zoph_photo_albums (
  photo_id int(11) NOT NULL default '0',
  album_id int(11) NOT NULL default '0',
  PRIMARY KEY  (photo_id,album_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_photo_albums'
--



--
-- Table structure for table 'zoph_photo_categories'
--

CREATE TABLE zoph_photo_categories (
  photo_id int(11) NOT NULL default '0',
  category_id int(11) NOT NULL default '0',
  PRIMARY KEY  (photo_id,category_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_photo_categories'
--

--
-- Table structure for table 'zoph_photo_comments'
--

CREATE TABLE zoph_photo_comments (
  photo_id int(11) NOT NULL default '0',
  comment_id int(11) NOT NULL default '0',
  PRIMARY KEY  (photo_id,comment_id)
) TYPE=MyISAM;



--
-- Table structure for table 'zoph_photo_people'
--

CREATE TABLE zoph_photo_people (
  photo_id int(11) NOT NULL default '0',
  person_id int(11) NOT NULL default '0',
  position int(11) default NULL,
  PRIMARY KEY  (photo_id,person_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_photo_people'
--



--
-- Table structure for table 'zoph_photos'
--

CREATE TABLE zoph_photos (
  photo_id int(11) NOT NULL auto_increment,
  name varchar(128) default NULL,
  path varchar(255) default NULL,
  width int(11) default NULL,
  height int(11) default NULL,
  size int(11) default NULL,
  title varchar(64) default NULL,
  photographer_id int(11) default NULL,
  location_id int(11) default NULL,
  view varchar(64) default NULL,
  rating float(4,2) unsigned default NULL,
  description blob,
  date varchar(10) default NULL,
  time varchar(8) default NULL,
  camera_make varchar(32) default NULL,
  camera_model varchar(32) default NULL,
  flash_used char(1) default NULL,
  focal_length varchar(64) default NULL,
  exposure varchar(64) default NULL,
  compression varchar(64) default NULL,
  aperture varchar(16) default NULL,
  level tinyint(4) NOT NULL default '1',
  iso_equiv varchar(8) default NULL,
  metering_mode varchar(16) default NULL,
  focus_dist varchar(16) default NULL,
  ccd_width varchar(16) default NULL,
  comment varchar(128) default NULL,
  timestamp timestamp(14) NOT NULL,
  PRIMARY KEY  (photo_id),
  KEY photo_photog_id (photographer_id),
  KEY photo_loc_id (location_id),
  KEY photo_rating (rating),
  KEY photo_level (level)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_photos'
--



--
-- Table structure for table 'zoph_places'
--

CREATE TABLE zoph_places (
  place_id int(11) NOT NULL auto_increment,
  parent_place_id int(11) NOT NULL,
  contact_type int(11) NOT NULL default '0',
  title varchar(64) NOT NULL default '',
  address varchar(64) default NULL,
  address2 varchar(64) default NULL,
  city varchar(32) default NULL,
  state varchar(32) default NULL,
  zip varchar(10) default NULL,
  country varchar(32) default NULL,
  notes varchar(255) default NULL,
  PRIMARY KEY  (place_id),
  KEY place_city (city(10)),
  KEY place_title (title(10))
) TYPE=MyISAM;

INSERT INTO zoph_places VALUES (0,0,0,"World",NULL,NULL,NULL,NULL,NULL,NULL,NULL);

--
-- Dumping data for table 'zoph_places'
--



--
-- Table structure for table 'zoph_prefs'
--

CREATE TABLE zoph_prefs (
  user_id int(11) NOT NULL default '0',
  show_breadcrumbs char(1) NOT NULL default '1',
  num_breadcrumbs smallint(5) unsigned NOT NULL default '8',
  num_rows tinyint(3) unsigned NOT NULL default '3',
  num_cols tinyint(3) unsigned NOT NULL default '4',
  max_pager_size tinyint(3) unsigned NOT NULL default '10',
  random_photo_min_rating tinyint(3) unsigned NOT NULL default '0',
  reports_top_n smallint(5) unsigned NOT NULL default '5',
  color_scheme_id int(11) NOT NULL default '1',
  slideshow_time smallint(6) NOT NULL default '5',
  language char(2) default NULL,
  recent_photo_days smallint(6) NOT NULL default '7',
  auto_edit char(1) NOT NULL default '0',
  camera_info char(1) NOT NULL default '1',
  desc_thumbnails char(1) NOT NULL default '0',
  fullsize_new_win char(1) NOT NULL default '0',
  people_slots tinyint(3) NOT NULL default 1,
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_prefs'
--


INSERT INTO zoph_prefs VALUES (1,'1',8,3,4,10,0,5,1,5,NULL,7,'0','1','0','0','1');

--
-- Table structure for table 'zoph_photo_ratings'
--

CREATE TABLE zoph_photo_ratings (
  user_id int(11) NOT NULL default '0',
  photo_id int(11) NOT NULL default '0',
  rating tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (user_id,photo_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_photo_ratings'
--



--
-- Table structure for table 'zoph_users'
--

CREATE TABLE zoph_users (
  user_id int(11) NOT NULL auto_increment,
  person_id int(11) NOT NULL default '0',
  user_class char(1) NOT NULL default '1',
  user_name varchar(16) NOT NULL default '',
  password varchar(64) default NULL,
  browse_people char(1) NOT NULL default '0',
  browse_places char(1) NOT NULL default '0',
  detailed_people char(1) NOT NULL default '0',
  detailed_places char(1) NOT NULL default '0',
  import char(1) NOT NULL default '0',
  leave_comments char(1) NOT NULL default '0',
  lightbox_id int(11) default NULL,
  lastnotify datetime default NULL,
  lastlogin datetime default NULL,
  lastip varchar(16) default NULL,
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'zoph_users'
--


INSERT INTO zoph_users VALUES (1,1,'0','admin',password('admin'),'1','1','1','1','1',NULL,NULL,NULL,NULL);

