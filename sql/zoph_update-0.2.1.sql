#
# focal length was not long enough for some cameras
#
alter table photos modify focal_length varchar(64);

#
# let these descriptions be larger too
#
alter table albums modify album_description varchar(255);
alter table categories modify category_description varchar(255);

#
# a couple new photo fields
#
alter table photos add focus_dist varchar(16);
alter table photos add ccd_width varchar(16);
alter table photos add comment varchar(128);

#
# forgot to make this not null
#
alter table users modify detailed_people char(1) not null default '0';

#
# change state from char(2) to varchar(32) so it can be more versatile
#
alter table places modify state varchar(32);
