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
var mapstraction;

function createMap(div, provider) {
    // This creates a new map
    mapstraction=new Mapstraction(div, provider);
    mapstraction.addControls({ pan: true, zoom: 'large', scale: true, map_type: true });

    var center=new LatLonPoint(0,0);
    mapstraction.setCenterAndZoom(center, 2);
}

function clickmap(point) {
    var latfield=document.getElementById('lat');
    var lonfield=document.getElementById('lon');
    var zoomfield=document.getElementById('mapzoom');
    var maptypefield=document.getElementById('maptype');

    latfield.value=point.lat;
    lonfield.value=point.lon;
    zoomfield.value=mapstraction.getZoom();
    mapstraction.removeMarker(mapstraction.markers[0]);
    marker = new Marker(point);
    mapstraction.addMarker(marker);
}

function zoomUpdate() {
    var zoomfield=document.getElementById('mapzoom');
    zoomfield.value=mapstraction.getZoom();
}

function createMarker(lat, lon, icon, title, infoBubble) {
    var point=new LatLonPoint(lat, lon);
    var marker=new Marker(point);
    if (title) {
        marker.setLabel(title);
    }
    if(icon) {
        marker.setIcon('images/icons/' + icon, [22,22]);
    }
    if (infoBubble) {
        marker.setInfoBubble(infoBubble);
    }
    mapstraction.addMarker(marker);
}

function setFieldUpdate() {
    // This makes sure that the map is updated when a user changes the
    // Lat and Lon fields manually.
    var latfield=document.getElementById("lat");
    var lonfield=document.getElementById("lon");
    var zoomfield=document.getElementById("mapzoom");

    latfield.onchange=updateMap;
    lonfield.onchange=updateMap;
    zoomfield.onchange=updateMap;
}

function updateMap() {
    var latfield=document.getElementById("lat");
    var lonfield=document.getElementById("lon");
    var zoomfield=document.getElementById("mapzoom");
    var lat=latfield.value;
    var lon=lonfield.value;
    var zoomlevel=parseInt(zoomfield.value);
    mapstraction.removeMarker(mapstraction.markers[0]);
    createMarker(lat, lon,null,null,null);
    mapstraction.setCenterAndZoom(new LatLonPoint(lat,lon),zoomlevel);
}

function setUpdateHandlers() {
    mapstraction.addEventListener('click', clickmap);
//    mapstraction.addEventListener('zoomend', zoomUpdate);
    mapstraction.moveendHandler(function(e) { zoomUpdate(); });
    setFieldUpdate();
}

