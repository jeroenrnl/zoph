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

var zMaps = JSClass({
    singleton: true ,

    create: function (div, provider) {
        var map = new L.Map(div);
        var layer;

        if ( provider == 'googlev3' ) {
            layer = new L.Google('ROADMAP');
        } else {
            var url;

            if ( provider == 'mapquest') {
                url = 'http://otile4.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
            } else {
                url = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            }

            layer = new L.tileLayer(url);
        }

        map.addLayer(layer);

        this.map = map;
        this.markerGroup = new L.featureGroup();
        this.latField = $('#lat');
        this.lonField = $('#lon');
        this.zoomField = $('#mapzoom');
    } ,
    createMarker: function (lat, lon, icon, title, infoBubble) {
        var marker = new L.marker(
            [lat, lon],
            {
                title: title,
            }
        );
        if ( icon ) {
            marker.setIcon( new L.icon( {iconUrl: icon} ) );
        }
        this.map.setView( new L.LatLng(lat, lon), (this.map.getZoom() || 14) );
        marker.addTo(this.map)
            .bindPopup(infoBubble);

        this.markerGroup.addLayer(marker);
        if ( this.markerGroup.getLayers().length > 1 ) {
            this.map.fitBounds(this.markerGroup.getBounds());
        }
    } ,
    setCenterAndZoom: function (lat, lon, zoomlevel) {
        this.map.setView(
            [(lat || 0), (lon || 0)],
            zoomlevel
        );
    } ,
    removeAllMarkers: function () {
        var map = this.map;
        $.map(this.markerGroup.getLayers(), function(layer) {
            map.removeLayer(layer);
        });
        this.markerGroup.clearLayers();
    } ,
    setFieldUpdate: function () {
        // This makes sure that the map is updated when a user changes the
        // Lat and Lon fields manually.
        this.latField.add(this.lonField).add(this.zoomField).on(
            'change',
            { this: this },
            function(e){
                var distance = document.getElementById("latlon_distance");

                var lat = e.data.this.latField.val();
                var lon = e.data.this.lonField.val();
                var zoomlevel = e.data.this.map.getZoom();

                if (e.data.this.zoomField) {
                   zoomlevel = parseInt(e.data.this.zoomField.val());
               }

                // remove all markers
                e.data.this.removeAllMarkers();
                e.data.this.createMarker(lat, lon, null, null, null);
                e.data.this.setCenterAndZoom(lat, lon, zoomlevel);
            }
        );
    } ,
    clickMap: function (event_name, event_source, event_args) {
        this.latField.val(event_args.location.lat);
        this.lonfield.val(event_args.location.lon);
        if(zoomfield) {
            zoomField.val( this.map.getZoom() );
        }
        this.map.removeMarkers();
        this.createMarker(latField.val(), lonField.val(), null, null, null);
    } ,
    zoomUpdate: function (event_name, event_source, event_args) {
        if ( this.zoomField ) {
            this.zoomField.val( this.map.getZoom() );
        }
    } ,
    setUpdateHandlers: function () {
        this.map.on('click', this.clickMap);
        this.map.on('zoomend', this.zoomUpdate);
        this.map.on('dragend', this.zoomUpdate);
        this.setFieldUpdate();
    } ,
    drawPolyline: function (path, color, opacity, weight) {
        L.polyline(
            path,
            {
                color: strokeColor,
                opacity: opacity,
                weight: weight
            }
        ).addTo(this.map);
    }
});
