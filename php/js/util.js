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
    var classElements = [];
    if (node === null || node === undefined) {
        node = document;
    }
    if (tag === null || tag === undefined) {
        tag = '*';
    }
//    if(tag == '*' && node.getElementsByClassName) {
//        return node.getElementsByClassName(searchClass);
//    }
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
    if(stringToTrim === null) {
        return "";
    } else {
        // in some browsers &nbsp; is not part of \s, so it is 
        // specified separately with it's unicode representation  \u00A0
        return stringToTrim.replace(/^[\s\u00A0]+|[\s\u00A0]+$/g,"");
    }
}

function removeChildren(obj) {
    if(obj && obj.childNodes) {
        while (obj.childNodes[0]) {
            obj.removeChild(obj.firstChild);
        }
    }
}

function clr(obj_id) {
    object=document.getElementById(obj_id);
    removeChildren(object);
}

function deleteNode(obj) {
    var par=obj.parentNode;
    par.removeChild(obj);
}

function createNode(nodetype, value) {
    var node=document.createElement(nodetype);
    nodetext=document.createTextNode(value);
    node.appendChild(nodetext);
    return node;
}

function findInArray(array, search) {
    // Some browsers cannot use .indexOf on an array...
    //
    for (var index in array) {
        if(array.hasOwnProperty(index)) {
            if(array[index]==search) {
                return index;
            }
        }
    }
    return -1;
}

function findPos(obj) {
    var curleft = 0;
    var curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft;
        curtop = obj.offsetTop;
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft;
            curtop += obj.offsetTop;
        }
    }
    return [curleft,curtop];
}

function adjustIframe(min, max, iframe) {
    // This function will adjust the height of an iframe
    // to it's contents, with a set maximum;
    html=document.getElementsByTagName("html")[0];
    height=html.clientHeight + 20;
    if(height > max) {
        height = max;
    } else if (height < min) {
        height = min;
    }
    objiframe=top.document.getElementById(iframe);
    objiframe.style.height=height + "px";
}

function getFileType(url) {
    ext=url.substr(url.lastIndexOf('.') + 1).toLowerCase();
    switch(ext) {
    case "jpg":
    case "gif":
    case "png":
        return "image";
    case "zip":
    case "gz":
    case "tar":
    case "bz":
        return "archive";
    default:
        return "unknown";
    }
}


function increaseValueInBrackets(value) {
    leftbracket=value.indexOf("[");
    rightbracket=value.indexOf("]");

    num=parseInt(value.substring(leftbracket + 1, rightbracket),10);
    num++;
    return value.substring(0,leftbracket) + "[" + num + "]";
}
