#
# Zoph 0.3.2 -> 0.3.3 update
# 18 Nov 2002
#

#
# create a pref for displaying descriptions under thumbnails
#
alter table prefs add desc_thumbnails char(1) not null default '0';
