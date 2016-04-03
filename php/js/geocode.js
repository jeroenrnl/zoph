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

var zGeocode = JSClass({
    create: function(provider) {
        if ( provider === "googlev3" ) {
            this.geocoder = L.Control.Geocoder.google();
        } else if ( provider === "photon" ) {
            this.geocoder = L.Control.Geocoder.photon();
        } else {
            this.geocoder = L.Control.Geocoder.nominatim()
        }
    } ,
    geocode: function() {
        var accordion = $("#geocoderesults > #accordion")

        // remove geocoderesults previous contents
        accordion.accordion( "destroy" );

        // start query object
        var objQuery = {
            title:      $("#title").val(),
            address:    $("#address").val(),
            address2:   $("#address2").val(),
            city:       $("#city").val(),
            state:      $("#state").val(),
            zip:        $("#zip").val(),
            country:    $("#country").val()
        };

        // remove empty items
        for (var i in objQuery) {
            if ( trim(objQuery[i]) === "" ) {
                delete objQuery[i];
            }
        }

        // don't use title if we have an address
        if (objQuery.address || objQuery.address2 ) {
            delete objQuery["title"];
        }

        // build up query
        var query = "";
        for ( var i in objQuery ) {
            if ( trim(query) !== "") {
                query += ", ";
            }
            query += objQuery[i];
        }

        // interstitial
        $("#geocoderesults").html("searching for...<br>" + query);

        // submit the query
        this.geocoder.geocode(query, this.displayGeocode);
    } ,
    displayGeocode: function(results) {
        var geocoderesults = $("#geocoderesults");

        if ( results.length > 0 ) {
            var accordion = $('<div id="divAccordion"></div>');

            // overwrite geocode HTML with the new accordion div
            geocoderesults.html(accordion);

            // itterate through results
            $.each(results, function(index, result) {
                // build up code that we'll use to create an accordion
                var html = '<h3>' + result.name + '<h3>'
                    + '<div>'
                    + '<label for="txtLatitude.' + index + '">Latitude</label>'
                    + '<input id="txtLatitude.' + index + '" type="text" value="' + result.center.lat + '" disabled>'
                    + '<label for="txtLongitude.' + index + '">Longitude</label>'
                    + '<input id="txtLongitude.' + index + '" type="text" value="' + result.center.lng + '" disabled>'
                    + '<input id="txtBBox.' + index + '" type="hidden">'
                    + '</div>';

                $.each(result.properties, function(key, value) {
                    + '<label for="txt' + key + '.' + index + '">Lat/Lon</label>'
                    + '<input id="txt' + key + '.' + index + '" type="text" value="' + value + '" disabled>'
                });

                html += '</div>';

                // append the new HTML to the accordion div
                accordion.append(html);

                // save the BBox data
                $("#txtBBox\\." + index).data("bbox", result.bbox);
            });

            // add a button for applying geocoding results
            $('<button id="btnApplyGeocode">Apply</button>')
                .click(function(event) {
                    // get the index of the active accordion panal
                    var index = accordion.accordion( "option", "active" );

                    $('#lat').val($('#txtLatitude\\.' + index).val());
                    $('#lon').val($('#txtLongitude\\.' + index).val());

                    // get our map
                    var map = zMaps.instance().map;
                    if ( map ) {
                        // zoom the map to the bounding box of our selected info
                        map.fitBounds($("#txtBBox\\." + index).data("bbox"));
                            //[bbox[0].lat, bbox[0].lng], [bbox[1].lat, bbox[1].lng]);

                        // set the zoom level field
                        var mapzoom = $("#mapzoom");
                        if ( mapzoom ) {
                            mapzoom.val(map.getZoom());
                        }
                    }
                })
                .appendTo(geocoderesults);

            accordion.accordion();
        } else {
            geocoderesults.html('No results found!');
        }
    }
});
