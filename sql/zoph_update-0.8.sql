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

