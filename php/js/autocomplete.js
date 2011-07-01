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


var autocomplete=function() {
    var selectedvalue=[];
    var dataarray=[];
    var keyarray=[];
    var oldvalue=[];
    var oldtext;

    var open;

    function init() {
        var ac=getElementsByClass('autocomplete');

        selected=[];
        
        for (var i=0; i<ac.length; i++) {
            inputToAutocomplete(ac[i]);
        }
        setTimeout(autocomplete.setpos,10);
    
    }

    function inputToAutocomplete(el) {
        // This function converts an input field to an autocomplete field

        // Take _id from the id.
        var id = el.id;
        var underscore=id.indexOf("_");
        if(underscore>0) {
            id = id.substring(0,underscore);
        } else {
            // prevent duplicate id
            el.id=id + "_id";
        }
        
        var text=el;
        text.id=id; 
        text.onmousedown=show;
        text.onkeyup=change;
        text.onfocus=focus;
        text.onblur=unfocus;
        text.onmouseup=change;
        text.onkeypress=handleKeys;
    
        text.setAttribute("autocomplete", "off");
        
//        text.className="autocompinput";
        text.className=text.className.replace("autocomplete", "autocompinput");
        text.style.width="200px";

        var dropdown=document.createElement("ul");
        
        dropdown.className="autocompdropdown";
        dropdown.id=id + "dropdown";

        dropdown.style.position="absolute";
        dropdown.style.display="none";
        
        el.parentNode.insertBefore(dropdown,el.nextSibling);
        
        if(el.parentNode.className.indexOf("multiple")>=0 && el.id.indexOf("[")) {
            // This is a field that can appear multiple times, so we add a
            // 'remove' link
            var remove=document.createElement("img");
            remove.setAttribute("onClick", "autocomplete.remove(this); return false");
            remove.setAttribute("src", "images/icons/default/remove.png");
            remove.className="actionlink";

            el.parentNode.insertBefore(remove,el.nextSibling.nextSibling);
            
        }
    }

    function remove(obj) {
        obj.parentNode.removeChild(obj.previousSibling); // remove dropdown
        obj.parentNode.removeChild(obj.previousSibling); // remove input
        obj.parentNode.removeChild(obj.previousSibling); // remove hidden input
        obj.parentNode.removeChild(obj);             // remove icon
    }

    function httpResponse(object, xml) {
        var dropdown=document.getElementById(object + "dropdown");
        var text=document.getElementById(object);
        var root=[];
        removeChildren(dropdown);
        text.style.backgroundImage="url('images/down2.gif')";
        xmlobj=object.split("_");
        if(xmlobj[1]=="parent") {
            xmlobj.shift();
        }
        node=xmlobj[1];
        root=xml.getElementsByTagName(XML.rootnode[node]);

        // These will be rebuilt during the XML processing
        //
        var dataarray=[];
        var keyarray=[];
        selectedvalue[dropdown.id]=0;
        build_tree(root[0], dropdown, XML.rootnode[node], XML.node[node]);
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
        var autocompdropdown=[];
        oldvalue[obj.id]=obj.value;
        oldtext=-1;
        autocompdropdown=getElementsByClass("autocompdropdown");
        
        for (var i=0; i<autocompdropdown.length; i++) {
            removeChildren(autocompdropdown[i]);
            hidedropdown(autocompdropdown[i].previousSibling);
        }

        if (navigator.appName=="Microsoft Internet Explorer" && parseInt(navigator.appVersion, 10) <= 6) {
            // The following is hiding all select type inputs
            // Because some complete moron at Micro$oft decided
            // that it would be a good idea to always display them on
            // top... completely ignoring the z-index setting.
            var select=document.getElementsByTagName("select");
            for (var s=0; s<select.length; s++) {
                select[s].style.visibility="hidden";
            }
            // while we're at it... MSIE doesn't support max-height too, so
            // we'll fix it
            dropdown.style.height="15em";
        }
        obj.value="";
        dropdown.style.display="block";
        dropdown.style.width=obj.offsetWidth + "px";
        setpos();
        obj.focus();
        obj.onmousedown=hide;
    }

    function hide() {
        hidedropdown(this);
    }

    function hidedropdown(obj) {
        if(navigator.appName=="Microsoft Internet Explorer" && parseInt(navigator.appVersion, 10) <= 6) {
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
        window.focus();
    }

    function build_tree(xmltree, parent, branchname, nodename) {
        var children=xmltree.childNodes;
        var li, span, key, name;
        for (var i=0; i<children.length; i++) {
            if(children[i].nodeName==branchname) {
               var ul=document.createElement("ul");
               ul=build_tree(children[i], ul, branchname, nodename);
               parent.appendChild(ul);
            } else if (children[i].nodeName==nodename) {
                if(children[i].childNodes.length> 0 && children[i].childNodes[0].nodeName!=branchname) {
                    li=document.createElement("li");
                    span=document.createElement("span");
                    key=children[i].childNodes[0].firstChild.nodeValue;
                    keyarray.push(parseInt(key,10));
                    name=children[i].childNodes[1].firstChild.nodeValue;
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
                    if (name=="&nbsp;") {
                        // You cannot use &nbsp; in a textnode
                        // However, to minimize cross site scripting attacks
                        // I don't want to use innerHTML for all elements
                        li.innerHTML=("&nbsp;");
                    } else {
                        li.appendChild(document.createTextNode(name));
                    }
                    parent.appendChild(li);
                }
                parent=build_tree(children[i], parent, branchname, nodename);
            }
                if (i==selectedvalue[dropdown.id] && i!==0) {
                    li.id="selected";
                }
        }
        return parent;
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
                XML.getData(obj.id, value);
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
        if(!(navigator.appName=="Microsoft Internet Explorer" && parseInt(navigator.appVersion, 10) <= 6)) {
            // In MSIE, scrolling the dropdown will make the textbox lose focus
            // so there we do not hide it on losing focus.
            obj=this;
            setTimeout(function() { autocomplete.hidedropdown(obj); } , 200);
        }
    }
    function clickli() {
        var li=this;
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

        orig_field.value = key;

        hidedropdown(field);
        if(field.parentNode.className.indexOf("multiple")>=0 && field.id.indexOf("[")) {
            // if a dropdown field is inside a fieldset with class 'multiple'
            // we will automatically generate a new dropdown for this field.
            createNewInput(field);
        }
    }

    function createNewInput(after) {
        input=after.cloneNode(true);
        hidden=after.previousSibling.cloneNode(true);
        
        input.id=increaseValueInBrackets(after.id);
        if(!document.getElementById(input.id)) {
            input.name=increaseValueInBrackets(after.name);
            
            hidden.id=increaseValueInBrackets(after.previousSibling.id);
            hidden.name=increaseValueInBrackets(after.previousSibling.name);
            
            input.value="";
            hidden.value="";
            after.parentNode.insertBefore(input,after.nextSibling.nextSibling.nextSibling);
            after.parentNode.insertBefore(hidden,input);
            inputToAutocomplete(input);
        }
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
        var match=[];
        var nowselected;

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
                for (var m=0; m<=maxlength; m++) {
                    for (var l=1; l<match.length; l++) {
                        if (match[l].substring(0,m).toUpperCase() == match[l].substring(0,m).toUpperCase()) {
                            maxmatch=m;
                        } else {
                            maxmatch=m-1;
                            l=maxlength+1; // to break out of the outer loop
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
            selectedvalue[dropdown.id]--;

            // First deselect the currently selected
            nowselected=document.getElementById("selected");
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
            selectedvalue[dropdown.id]++;
            nowselected=document.getElementById("selected");
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
            }
            nowselected=document.getElementById("selected");

            if(nowselected) {
                oldvalue[open.previousSibling.id]=null;
                key=parseInt(nowselected.firstChild.innerHTML, 10);
                var newvalue=nowselected.lastChild.nodeValue;
                selectli(dropdown.id, key, newvalue);
            }
            inputfields=document.getElementsByTagName("input");
            for (var f=0; f<inputfields.length; f++) {
                if(inputfields[f]==open.previousSibling) {
                    nextTab=inputfields[f+1];
                    break;
                }
            }
            nextTab.focus();
            return false;  // prevents submit
        }
    }

    function flattentree(root, element, flattree) {
        if (!flattree) {
            flattree=[];
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
    
    return {
        setpos:setpos,
        init:init,
        hidedropdown:hidedropdown,
        httpResponse:httpResponse,
        remove:remove
    };
}();

if(window.addEventListener) {
    window.addEventListener("load",autocomplete.init,false);
    window.addEventListener("resize",autocomplete.setpos, false);
} else {
    // M$ refuses to play by the rules... as always
    window.attachEvent("onload",autocomplete.init);
    window.attachEvent("onresize",autocomplete.setpos);
}
