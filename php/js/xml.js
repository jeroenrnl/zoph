
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


var XML=function() {
    var retry;
    // The following arrays describe the root node and nodenames to look for in
    // the XML output:
    var rootnode = {
        "location":     "places", 
        "place":        "places", 
        "home":         "places", 
        "work":         "places", 
        "photographer": "people", 
        "person":       "people", 
        "father":       "people", 
        "mother":       "people", 
        "spouse":       "people", 
        "album":        "albums", 
        "category":     "categories", 
        "timezone":     "zones"
        };
    var node = {
        "location":     "place", 
        "place":        "place", 
        "home":         "place", 
        "work":         "place", 
        "photographer": "person", 
        "person":       "person", 
        "father":       "person", 
        "mother":       "person", 
        "spouse":       "person", 
        "album":        "album", 
        "category":     "category", 
        "timezone":     "tz"
        };

    function getData(object, constraint) {
        var http=new XMLHttpRequest();
        if(object=='import_progress') {
            xmlobj='import_progress';
        } else {
            newobj=object.split("_");
            if(newobj[1]=="parent") {
                newobj.shift();
            }
            xmlobj=newobj[1];
        }

        var url="getxmldata.php?object=" + xmlobj;
        if(constraint) {
            url+="&search=" + constraint;
        }

        if (http) {
            input=document.getElementById(object);
            if(input) {
                input.style.backgroundImage="url('images/pleasewait.gif')";
            }
            http.open("GET", url, true);
            http.onreadystatechange=function() {
               httpResponse(http, object);
            };
            http.send(null);
        } else {
            // try again in 500 ms
            clearTimeout(retry);
            retry=setTimeout("XML.getData('" + object + "','" + constraint + "')", 500);
        }
    }

    function httpResponse(http, object) {
        input=document.getElementById(object);
        if (http.readyState == 4) {
            if(http.status == 200) {
                if(input) {
                    input.style.backgroundImage="url('images/down2.gif')";
                }
                if(object=='import_progress') {
                    zImport.httpResponse(object, http.responseXML);
                } else if ((object=='import') || (object=='action')) {
                    zImport.processDone(http.responseText);
                } else {
                    autocomplete.httpResponse(object, http.responseXML);
                }
            }
        }
    }

    function submitForm(form, url) {
        if(form.tagName=="FORM") {
            inputs=form.getElementsByTagName("input");
            selects=form.getElementsByTagName("select");
            if(url.indexOf("?")) {
                url += "&";
            } else {
                url += "?";
            }
            for(var i=0; i<inputs.length; i++) {
                if(inputs[i].value) {
                    url += escape(inputs[i].name) + "=" + escape(inputs[i].value) + "&";
                }
            }
            for(i=0; i<selects.length; i++) {
                if(selects[i].value) {
                    url += selects[i].name + "=" + selects[i].value + "&";
                }
            }
            var http=new XMLHttpRequest();
            http.open("POST", url, true);
            http.onreadystatechange=function() {
               httpResponse(http, "import");
            };
            http.send(null);

        }
    }
            

    return {
        rootnode:rootnode,
        node:node,
        getData:getData,
        submitForm:submitForm,
        httpResponse:httpResponse
    };
}();
