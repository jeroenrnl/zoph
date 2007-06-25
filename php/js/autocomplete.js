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

window.onload=init;
window.onresize=setpos;
selectedvalue=new Array;
var http = getXMLHTTPRequest();
var dataarray=new Array;
var keyarray=new Array;
var Busy, timeout, retry, open;
var oldvalue=new Array;
var oldtext;

// The following arrays describe the root node and nodenames to look for in
// the XML output:
var xmlrootnode = {"location": "places", "photographer":  "people", "person": "people", "album": "albums", "category": "categories"};
var xmlnode = {"location": "place", "photographer": "person", "person": "person", "album": "album", "category": "category"};

function init() {
    autocomplete=new Array;
    autocomplete=getElementsByClass("autocomplete");

    selected=new Array;
    
    for (var i=0; i<autocomplete.length; i++) {
        // Take _id from the id.
        id = autocomplete[i].id;
        underscore=id.lastIndexOf("_");
        if(underscore>0) {
            id = id.substring(0,underscore);
        } else {
            // prevent duplicate id
            autocomplete[i].id=id + "_id";
        }
        
        text=document.createElement("input");
        
        text.onmousedown=show;
        text.onkeyup=change;
        text.onfocus=focus;
        text.onblur=unfocus;
        text.onmouseup=change;
        text.onkeypress=handleKeys;
    
        text.setAttribute("autocomplete", "off");
        
        text.id=id;
        text.className="autocompinput";
        text.style.width=autocomplete[i].offsetWidth + "px";

        dropdown=document.createElement("ul");
        
        dropdown.className="autocompdropdown";
        dropdown.id=id + "dropdown";

        for (var j=0; j < autocomplete[i].options.length; j++) {
            keyarray[j]=parseInt(autocomplete[i].options[j].value);
            dataarray[j]=autocomplete[i].options[j].text;
        }
        selected[dropdown.id]=parseInt(autocomplete[i].value);
        selectedvalue[dropdown.id]=findInArray(keyarray, selected[dropdown.id]);
        text.value=trim(dataarray[selectedvalue[dropdown.id]]);

        dropdown.style.position="absolute";
        dropdown.style.display="none";
        
        autocomplete[i].parentNode.insertBefore(dropdown,autocomplete[i].nextSibling)
        autocomplete[i].parentNode.insertBefore(text,autocomplete[i].nextSibling)
        autocomplete[i].style.display="none";
    }
    setTimeout('setpos()',1);
    
}

function setpos() {
    input=getElementsByClass("autocompinput");
    dropdown=getElementsByClass("autocompdropdown");
    for (var i=0; i<input.length; i++) {
        dropdown[i].style.left=findPos(input[i])[0] + "px";
        dropdown[i].style.top=findPos(input[i])[1] + input[i].offsetHeight + "px";
    }
}    
function show() {
    showdropdown(this);
    }

function showdropdown(obj) {
    var children;
    var dropdown=document.getElementById(obj.id + "dropdown");
    open=dropdown;
    var autocompdropdown=new Array;
    oldvalue[obj.id]=obj.value;
    oldtext=-1;
    autocompdropdown=getElementsByClass("autocompdropdown");
    
    for (var i=0; i<autocompdropdown.length; i++) {
        removeChildren(autocompdropdown[i]);
        hidedropdown(autocompdropdown[i].previousSibling);
    }

    if (navigator.appName=="Microsoft Internet Explorer" && parseInt(navigator.appVersion) <= 6) {
        // The following is hiding all select type inputs
        // Because some complete moron at Micro$oft decided
        // that it would be a good idea to always display them on
        // top... completely ignoring the z-index setting.
        var select=document.getElementsByTagName("select");
        for (var i=0; i<select.length; i++) {
            select[i].style.visibility="hidden";
        }
        // while we're at it... MSIE doesn't support max-height too, so
        // we'll fix it
        dropdown.style.height="15em";
    }
    obj.value="";
    dropdown.style.display="block";
    dropdown.style.width=obj.offsetWidth + "px";

    obj.focus();
    obj.onmousedown=hide;
}

function hide() {
    hidedropdown(this);
}

function hidedropdown(obj) {
    if(navigator.appName=="Microsoft Internet Explorer" && parseInt(navigator.appVersion) <= 6) {
        var select=document.getElementsByTagName("select");
        for (var i=0; i<select.length; i++) {
            select[i].style.visibility="visible";
        }
    }
    dropdown=document.getElementById(obj.id + "dropdown");
    dropdown.style.display="none";
    if(oldvalue[obj.id]) {
        obj.value=oldvalue[obj.id];
    }
    obj.onmousedown=show;
    window.focus;
}

function getXMLdata(object, constraint) {
    var url="getxmldata.php?object=" + object;
    if(constraint) {
        url+="&search=" + constraint;
    }

    if (!Busy && http) {
        Busy=object;
        http.open("GET", url, true);
        http.onreadystatechange = useHttpResponse;
        http.send(null);
    } else {
        // try again in 500 ms
        clearTimeout(retry);
        retry=setTimeout("getXMLdata('" + object + "','" + constraint + "')", 500);
    }
}

function useHttpResponse() {
    var dropdown=document.getElementById(Busy + "dropdown");
    var text=document.getElementById(Busy);
    var root=new Array();
    if (http.readyState == 4) {
        if(http.status == 200) {
            removeChildren(dropdown);
            text.style.backgroundImage="url('images/down2.gif')";
            root=http.responseXML.getElementsByTagName(xmlrootnode[Busy]);

            // These will be rebuilt during the XML processing
            //
            dataarray=new Array;
            keyarray=new Array;
            selectedvalue[dropdown.id]=0;
            build_tree(root[0], dropdown, xmlrootnode[Busy], xmlnode[Busy]);
            Busy=false;
        }
    } else {
        text.style.backgroundImage="url('images/pleasewait.gif')";
    }
}


function build_tree(xmltree, parent, branchname, nodename) {
    var children=xmltree.childNodes;
    for (var i=0; i<children.length; i++) {
        if(children[i].nodeName==branchname) {
           var ul=document.createElement("ul");
           ul=build_tree(children[i], ul, branchname, nodename);
//ul.appendChild(newbranch);
           parent.appendChild(ul);
        } else if (children[i].nodeName==nodename) {
            if(children[i].childNodes.length> 0 && children[i].childNodes[0].nodeName!=branchname) {
                var li=document.createElement("li");
                var span=document.createElement("span");
                var key=children[i].childNodes[0].firstChild.nodeValue;
                keyarray.push(parseInt(key));
                var name=children[i].childNodes[1].firstChild.nodeValue;
                dataarray.push(name);
                li.appendChild(span);
                li.onclick=clickli;

                if (nodename=="place") {
                    li.className="location";
                } else {
                    li.className=nodename;
                }
                span.appendChild(document.createTextNode(key));
                span.style.display="none";
            
                li.appendChild(document.createTextNode(name));
                parent.appendChild(li);
            }
            parent=build_tree(children[i], parent, branchname, nodename);
        }
            if (i==selectedvalue[dropdown.id] && i!=0) {
                li.id="selected";
            }
    }
    return parent;
}

function getXMLHTTPRequest() {
    try {
        req = new XMLHttpRequest();
    } catch(err1) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (err2) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (err3) {
                req = false;
            }
        }
    }
    return req;
}
function change() {
    update(this.id);
}

function update(objid) {
    obj=document.getElementById(objid);
    dropdown = document.getElementById(obj.id + "dropdown");

    if(dropdown.style.display!="none") {
        // if the dropdown is invisible, don't bother updating it.
        var value=obj.value;
        if(oldtext!=value) {
            getXMLdata(obj.id, value);
            oldtext=value;
        }
        selectedli=document.getElementById("selected");
        if (selectedli) {
            selectedli.scrollIntoView(true);
        }
    }
}

function focus() {
    dropdown=document.getElementById(this.id + "dropdown");
    if (dropdown.style.display=="none") {
        showdropdown(this);
        // Konqueror does not automatically trigger the change
        update(this.id);
    }
}

function unfocus() {
    // Whenever a selection from a list is made, the textbox will
    // also lose focus, this delay is made to give the browser
    // time to process the click, before the dropdown is destroyed.
    if(!(navigator.appName=="Microsoft Internet Explorer" && parseInt(navigator.appVersion) <= 6)) {
        // In MSIE, scrolling the dropdown will make the textbox lose focus
        // so there we do not hide it on losing focus.
        obj=this;
        setTimeout("hidedropdown(obj)", 200);
    }
}
function clickli() {
    var li=this
    var key=li.firstChild.innerHTML;
    var newvalue=li.lastChild.nodeValue;
    oldvalue[open.previousSibling.id]=null;
    selectli(open.id, key, newvalue);
    open=false;
}

function selectli(dropdownid, key, newvalue) {
    var dropdown = document.getElementById(dropdownid);
    var field = dropdown.previousSibling;
    var orig_field = field.previousSibling;
    
    field.value = newvalue;
    longkey=findselectkey(orig_field.id, key);

    orig_field.value = longkey;

    selectedvalue[dropdown.id]=parseInt(findInArray(keyarray,key));

    hidedropdown(field);
}

function handleKeys(event) {
    obj=this; 
    dropdown = document.getElementById(obj.id + "dropdown");
    keycode=event.keyCode;
 
    // 40 = cursor down
    // 38 = cursor up
    // 9 = tab
    // 13 = enter
    var children = dropdown.childNodes;

    var j=0;
    var maxlength=0;
    var maxmatch=0;
    var value, key;
    match = new Array();

    if(keycode==9) {
        constraint=obj.value;
        for(var i=0; i<dataarray.length; i++) {
            data=trim(dataarray[i]);
            datashort=data.substring(0,constraint.length);
            
            if(datashort.toUpperCase()==constraint.toUpperCase()) {
                match[j]=trim(dataarray[i]);
                maxlength=Math.max(maxlength, match[j].length);
                j++;
            }   
        }
        if(match.length>1) {
            for (var j=0; j<=maxlength; j++) {
                for (var i=1; i<match.length; i++) {
                    if (match[i].substring(0,j).toUpperCase() == match[0].substring(0,j).toUpperCase()) {
                        maxmatch=j;
                    } else {
                        maxmatch=j-1;
                        j=maxlength+1; // to break out of the outer loop
                        break;
                    }
                }
            }
            obj.value=trim(match[0].substring(0,maxmatch));
        
        } else if (match.length==1) {
            obj.value=trim(match[0]);
        }
        return false;  // prevents losing focus
    } else if (keycode==38) {
        selectedvalue[dropdown.id]--

        // First deselect the currently selected
        var nowselected=document.getElementById("selected")
        if(nowselected) {
            nowselected.id="";
        }
        flattree=flattentree(dropdown, "LI");
        if (selectedvalue[dropdown.id] < 0) { 
            selectedvalue[dropdown.id]=0; 
        }
        flattree[selectedvalue[dropdown.id]].id="selected";
        return false;  // prevents update
    } else if (keycode==40) {
        selectedvalue[dropdown.id]++
        var nowselected=document.getElementById("selected")
        if(nowselected) {
            nowselected.id="";
        }
        flattree=flattentree(dropdown, "LI");
        if (selectedvalue[dropdown.id] > (flattree.length - 1)) { 
            selectedvalue[dropdown.id] =flattree.length - 1;
        }
        flattree[selectedvalue[dropdown.id]].id="selected";
        return false;  // prevents update
    } else if (keycode==13) {
        var nextTab;

        flattree=flattentree(dropdown, "LI");
        if(flattree.length==1) {
            // If there's only one element in the list
            // we suppose one will select that on pressing enter
            flattree[0].id="selected";
            oldvalue[open.previousSibling.id]=null;
        }
        var nowselected=document.getElementById("selected");

        if(nowselected) {
            var key=parseInt(nowselected.firstChild.innerHTML);
            var newvalue=nowselected.lastChild.nodeValue;
            selectli(dropdown.id, key, newvalue);
        }
        inputfields=document.getElementsByTagName("input");
        for (var j=0; j<inputfields.length; j++) {
            if(inputfields[j]==open.previousSibling) {
                nextTab=inputfields[j+1];
                break;
            }
        }
        nextTab.focus();
        return false;  // prevents submit
    }
}

function flattentree(root, element, flattree) {
    if (!flattree) {
        flattree=new Array;
    }

    for (var i=0; i<root.childNodes.length; i++) {
        if (root.childNodes[i].tagName==element) {
            flattree.push(root.childNodes[i]);
        }
        if(root.childNodes[i].childNodes.length>0) {
            flattree=flattentree(root.childNodes[i], element, flattree);
        }
    }
    return flattree;
}

function findselectkey(selectid, key) {
    // On the search page, the keys in the original select are not just the
    // key of that album/category/place but a comma-separated list of
    // a/c/p that are children of that a/c/p. This function takes just the
    // first (thus the parent) key from that list.

    select=document.getElementById(selectid);
    for(var i=0; i<select.childNodes.length; i++) {
        var option=select.childNodes[i];
        if(option.value) {
            var comma=option.value.indexOf(",");
            if(comma>0) {
                var shortkey=option.value.substring(0,comma);
            } else {
                var shortkey=option.value;
            }
            if(shortkey == key) {
                return option.value;
            }
        }
    }
} 

