#
# Zoph 0.2.1 -> 0.3 update
# 20 Sep 2002
#

#
# increase size of name and path photo fields
#
alter table photos modify name varchar(128);
alter table photos modify path varchar(255);

#
# create timestamp field
#
alter table photos add timestamp timestamp(14);

#
# create a language pref field
#
alter table prefs add language char(2);

#
# create a field for the number of days used by the view recent links
#
alter table prefs add recent_photo_days smallint not null default 7;

#
# create a import permission field
#
alter table users add import char(1) not null default '0';
