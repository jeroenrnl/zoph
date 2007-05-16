// This file is part of Zoph.
//
// Zoph is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// 
// Zoph is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with Zoph; if not, write to the Free Software
// Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

function getElementsByClass(searchClass,node,tag) {
    var classElements = new Array();
    if ( node == null )
        node = document;
    if ( tag == null )
        tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    var pattern = new RegExp('(^|\\s)'+searchClass+'(\\s|$)');
    for (var i=0, j=0; i < elsLen; i++) {
        if ( pattern.test(els[i].className) ) {
            classElements[j] = els[i];
            j++;
        }
    }
    return classElements;
}

function trim(stringToTrim) {
    if(stringToTrim == null) {
        return "";
    } else {
        // in some browsers &nbsp; is not part of \s, so it is 
        // specified separately with it's unicode representation  \u00A0
        return stringToTrim.replace(/^[\s\u00A0]+|[\s\u00A0]+$/g,"");
    }
}

function removeChildren(obj) {
    while (obj.childNodes[0]) {
        obj.removeChild(obj.firstChild);
    }
}

function findInArray(array, search) {
    // Some browsers cannot use .indexOf on an array...
    //
    for (index in array) {
        if(array[index]==search) {
            return index;
            break;
        }
    }
    return -1;
}

function findPos(obj) {
    var curleft = curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    return [curleft,curtop];
}

