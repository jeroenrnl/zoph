#
# Zoph 0.3 -> 0.3.1 update
# 30 Sep 2002
#

#
# create a pref for an always edit mode
#
alter table prefs add auto_edit char(1) not null default '0';

#
# create a pref for whether to display camera info
#
alter table prefs add camera_info char(1) not null default '1';

#
# create a field to define a lightbox for a user
#
alter table users add lightbox_id int;

#
# These tables were accidentally included in zoph.sql 0.3
#
drop table contact_types;
drop table email_addresses;
drop table links;
drop table people_email_addresses;
drop table people_phone_numbers;
drop table people_places;
drop table phone_numbers;
drop table photo_links;
drop table places_phone_numbers;
drop table related_photos;
