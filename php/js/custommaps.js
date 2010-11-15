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

var zMapsCustom=function() {
    function customMap(map) {
        // This function is called after the map has been built
        // add functions here to add a custom mapping code.
        
        // ============================================
        // Overlay image
        // ============================================
        // Add an overlay image. 
        // (note: this function is not yet implemented in OpenLayers 
        // and CloudMade)
        
        // map.addImageOverlay("id", "url", opacity [0-100], west, south, east, north)
        // Example: 
        // This example adds a previously made image over the city of Santo
        // Domingo.
        //map.addImageOverlay("overlay","http://mapstraction.com/images/santodomingo.png",50,-70.01544, 18.39777, -69.80567, 18.563517);


        // ============================================
        // Add tile layer
        // ============================================
        // add a tile (map) layer.
        // (note: I only could get this to work with Google)
        // Example: 
        // This example adds openstreetmap tiles.
        // map.addTileLayer("http://tile.openstreetmap.org/{Z}/{X}/{Y}.png", 1.0, "Openstreetmap", 1, 19, false);
        // Add Big Tin Can tile layer:
        // map.addTileLayer("http://tiles.bigtincan.com/john/{Z}/{X}/{Y}.png", 1.0, "Big Tin Can", 1, 19, false);
        
        // ============================================
        // Add a map overlay
        // ============================================

        // map.addOverlay("url", autoCenterAndZoom [true/false]);
        // Example:
        // Add Flickr's GeoRSS feed:
        // map.addOverlay("http://api.flickr.com/services/feeds/geo/?format=rss_200", true);
        // Add Panoramio's KML feed:
        //map.addOverlay("http://www.panoramio.com/kml", true);
//        map.addOverlay("http://www.ambiotek.com/srtm", true);
    }

    return {
        customMap:customMap
    };
}();
