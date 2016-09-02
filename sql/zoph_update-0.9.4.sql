#
# Zoph 0.9.3 -> 0.9.4 update
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

ALTER TABLE zoph_group_permissions ADD subalbums CHAR(1) NOT NULL DEFAULT '0' AFTER writable;

INSERT INTO `zoph_color_schemes` VALUES 
	(0,'steel','cccccc','000000','0000aa','bb0000','eeeeef','222222','6699dd','dddddf','99bbdd','444444','0000bb','bbbbbb','555555'),
	(0,'happy','16c0ff','333333','444444','444444','ffff77','333333','ffff77','e43f7d','e43f7d','333333','e43f7d','333333','333333'),
	(0,'grey - blue','eeeeee','111111','222222','555555','f4f4f4','000000','dddddd','dfdfdf','0000ff','eeeeee','000077','ffffff','131313'),
	(0,'grey - green','eeeeee','111111','222222','555555','f4f4f4','000000','dddddd','dfdfdf','00dd00','eeeeee','007700','ffffff','131313'),
	(0,'grey - red','eeeeee','111111','222222','555555','f4f4f4','000000','dddddd','dfdfdf','ff0000','eeeeee','770000','ffffff','131313');


