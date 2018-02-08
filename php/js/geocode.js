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

var zGeocode=function() {
    var geourl="https://secure.geonames.org/search?style=SHORT&username=zoph&q=";
    var wikiurl="https://secure.geonames.org/wikipediaSearch?username=zoph&q=";
    var url;
    var geotag="geoname";
    var wikitag="entry"
    var xmltag;

    function checkGeocode() {
        // To prevent overwrite of tediously set lat & lon
        // you need to click the 'find' button twice, if a lat&lon have
        // already been set.
        var button=document.getElementById("geocode");
        var lat=document.getElementById("lat").value;
        var lon=document.getElementById("lon").value;

        if (lat==0 && lon==0) {
            enableGeocode(button);
        } else {
            disableGeocode(button);
        }
    }

    function enableGeocode() {
        var button=document.getElementById("geocode");
        button.className=button.className.replace("geo_disabled", "geocode");
        button.onclick=zGeocode.startGeocode;
    }

    function disableGeocode() {
        var button=document.getElementById("geocode");
        button.className=button.className.replace("geocode", "geo_disabled");
        button.onclick=zGeocode.enableGeocode;
    }




    function startGeocode() {
        url=geourl;
        xmltag=geotag;
        var objQuery={
            title:      document.getElementById("title").value,
            address:    document.getElementById("address").value,
            address2:   document.getElementById("address2").value,
            city:       document.getElementById("city").value,
            state:      document.getElementById("state").value,
            zip:        document.getElementById("zip").value,
            country:    document.getElementById("country").value
        };

        // remove empty items
        for (var i in objQuery) {
            if (trim(objQuery[i])==="") {
                delete objQuery[i];
            }
        }

        geocode(objQuery);
    }

    function geocode(objQuery) {
        var divResult=document.getElementById("geocoderesults");
        var query="";
        for (var i in objQuery) {
            if (trim(query)!=="") {
                query += ", ";
            }
            query+=objQuery[i];
        }

        divResult.innerHTML="searching for...<br>" + query;

        var http=new XMLHttpRequest();
        http.open("GET", url + encodeURI(query), true);

       http.onreadystatechange=function() {
            zGeocode.handleGeocode(http, objQuery);
        };
        http.send(null);
    }

    function handleGeocode(http, objQuery) {
        var divResult=document.getElementById("geocoderesults");
        if (http.readyState == 4) {
            if (http.status == 200) {
                var response=http.responseXML;
                var geonames=response.getElementsByTagName(xmltag);
                if (geonames.length > 0) {
                    displayGeocode(geonames, divResult, 0);
                } else {
                    // No results, let's try again with some less fields
                    if (objQuery.zip) {
                        delete (objQuery.zip);
                    } else if (objQuery.address2) {
                        delete objQuery.address2;
                    } else if (objQuery.title) {
                        delete objQuery.title;
                    } else if (objQuery.address) {
                        delete objQuery.address;
                    } else if (objQuery.state) {
                        delete objQuery.state;
                    } else if (objQuery.country) {
                        delete objQuery.country;
                    }

                    if ((Object.keys(objQuery).length == 0) && (url != wikiurl)) {

                        objQuery={
                            title:      document.getElementById("title").value
                        }
                        url=wikiurl;
                        xmltag=wikitag;
                    }
                    if (Object.keys(objQuery).length > 0) {
                        geocode(objQuery);
                    } else {
                        divResult.innerHTML="";
                        var b=document.createElement("b");
                        b.innerHTML=translate['Nothing found'];
                        divResult.appendChild(b);
                        return;
                    }

                }

            } else if (http.status == 0) {
                divResult.innerHTML="";
                var b=document.createElement("b");
                b.innerHTML=translate['An error occurred'];
                divResult.appendChild(b);
            }
        }
    }

    function displayGeocode(geonames, divResult, result) {
        var total=geonames.length;
        var titlefield=document.getElementById("title");
        var title, lat, lon, content, tag;

        // Define zoomlevels for different kinds of respones
        // see http://www.geonames.org/export/codes.html
        var zoomlevels= {
            "A": 6, // Country, state, region
            "H": 8, // Stream, lake
            "L": 15, // Parks, area
            "P": 12, // City, village
            "R": 17, // Road, railroad
            "S": 18, // Spot, building, farm
            "T": 12, // Mountain, hill, rock
            "U": 5,   // Undersea
            "V": 14  // Forest, heath
        };

        //define zoomlevels for different "features" in Wikipedia
        // see http://www.geonames.org/wikipedia/wikipedia_features.html

        var features={
            "city":             12,
            "railwaystation":   18,
            "edu":              17,
            "waterbody":        8,
            "landmark":         18,
            "adm2nd":           13,
            "mountain":         12,
            "adm3rd":           10,
            "airport":          16,
            "river":            8,
            "isle":             14,
            "event":            17,
            "adm1st":           15,
            "glacier":          16,
            "country":          6,
            "forest":           14,
            "pass":             17,
            "church":           18
        };

        var zoomlevel=12;

        for (var c=0; c<geonames[result].childNodes.length; c++) {
            tag=geonames[result].childNodes[c];
            content=null;
            if (tag.textContent) {
                content=tag.textContent;
            } else {
                // And again, M$ is to dumb to follow simple
                // standards
                content=tag.text;
            }
            switch(tag.nodeName) {
                case "title":
                    title=content;
                    break;
                case "toponymName":
                    title=content;
                    break;
                case "lat":
                    lat=content;
                    break;
                case "lng":
                case "lon":
                    lon=content;
                    break;
                case "fcl":
                    zoomlevel=zoomlevels[content];
                    break;
                case "feature":
                    zoomlevel=features[content];
                    break;
            }
        }
        if (lat && lon) {
            document.getElementById("lat").value=lat;
            document.getElementById("lon").value=lon;
            document.getElementById("mapzoom").value=zoomlevel;
            zMaps.updateMap();
        }
        var left=document.createElement("input");
        left.setAttribute("type", "button");
        left.className="leftright";
        left.setAttribute("value","<");

        var right=document.createElement("input");
        right.setAttribute("type", "button");
        right.setAttribute("value",">");
        right.className="leftright";

        if (result===0) {
            left.disabled=true;
        } else if ((result + 1) == total) {
            right.disabled=true;
        }

        right.onclick=function() { displayGeocode(geonames, divResult, result + 1); };
        left.onclick=function() { displayGeocode(geonames, divResult, result - 1); };
        // This is a little bit of a hidden feature, click the title of the
        // found place to set this place's title.
        var b=document.createElement("b");
        b.onclick=function() { titlefield.value=title; };
        b.innerHTML=title;
        var text=document.createTextNode((result + 1) + " / " + total);

        divResult.innerHTML="";
        divResult.appendChild(b);
        divResult.appendChild(document.createElement("br"));
        divResult.appendChild(text);
        divResult.appendChild(document.createElement("br"));
        divResult.appendChild(left);
        divResult.appendChild(right);
        disableGeocode();
    }
    return {
        checkGeocode:checkGeocode,
        enableGeocode:enableGeocode,
        startGeocode:startGeocode,
        handleGeocode:handleGeocode
    };
}();
