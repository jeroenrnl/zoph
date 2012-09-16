<?php
/**
 * Template to display a map.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package ZophTemplates
 * @author Jeroen Roos
 */

if(!ZOPH) { die("Illegal call"); }
?>

<div id="<?php echo $tpl_id ?>" class="map">
</div>

<script type="text/javascript">
    // Transform div into map:
    zMaps.createMap("<?php echo $tpl_id ?>","<?php echo $tpl_provider?>");
    <?php if($this->hasMarkers()): ?>
        // Add markers:
        <?php foreach ($this->getMarkers() as $m): ?>
            zMaps.createMarker("<?php echo $m->lat ?>","<?php echo $m->lon ?>", "<?php echo ICONSET . "/" . $m->icon ?>", '<?php echo $m->title ?>','<?php echo $m->quicklook ?>');
        <?php endforeach ?>
    <?php endif ?>
    
    <?php if($this->hasTracks()): ?>
        // Add tracks:
        // @todo: might not work with multiple tracks.
        <?php foreach ($this->getTracks() as $track): ?>
            var points=new Array();
            <?php foreach($track->getPoints() as $point): ?>
                points.push(new mxn.LatLonPoint(<?php echo $point->get("lat") ?>, <?php echo $point->get("lon") ?>));
            <?php endforeach; ?>
            track=new mxn.Polyline(points)
            mapstraction.addPolyline(track);
        <?php endforeach ?>
    <?php endif ?>

    <?php if(!is_null($this->clat) && (!is_null($this->clon)) && (!is_null($this->zoom))): ?>
        var center=new mxn.LatLonPoint(
            <?php echo $this->clat ?>,
            <?php echo $this->clon ?>);
        var zoomlevel=<?php echo $this->zoom ?>;
        mapstraction.setCenterAndZoom(center,zoomlevel);
    <?php else: ?>
        mapstraction.autoCenterAndZoom();
    <?php endif ?> 

    <?php if($this->edit): ?>
        zMaps.setUpdateHandlers();
    <?php endif ?>

</script>

            
