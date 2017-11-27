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

if (!ZOPH) { die("Illegal call"); }
?>

<div id="<?php echo $tpl_id ?>" class="map">
</div>

<script type="text/javascript">
    // Transform div into map:
    zMaps.createMap("<?= $tpl_id ?>","<?= $tpl_provider?>");
    <?php if ($this->hasMarkers()): ?>
        // Add markers:
        <?php foreach ($this->getMarkers() as $m): ?>
            zMaps.createMarker("<?= $m->lat ?>","<?= $m->lon ?>",
                icons["<?= $m->icon ?>"], '<?= str_replace("'", "\\'", $m->title) ?>',
                '<?= str_replace("'", "\\'", $m->quicklook) ?>');
        <?php endforeach ?>
    <?php endif ?>

    <?php if ($this->hasTracks()): ?>
        // Add tracks:
        // @todo: might not work with multiple tracks.
        <?php foreach ($this->getTracks() as $track): ?>
            var points=new Array();
            <?php foreach ($track->getPoints() as $point): ?>
                points.push([ <?= $point->get("lat") ?>, <?= $point->get("lon") ?> ] );
            <?php endforeach; ?>
            zMaps.createPolyline(points);
        <?php endforeach ?>
    <?php endif ?>

    <?php if (!is_null($this->clat) && (!is_null($this->clon)) && (!is_null($this->zoom))): ?>
        zMaps.setCenterAndZoom([ <?= $this->clat ?>, <?= $this->clon ?> ], <?= $this->zoom ?>);
    <?php elseif ($this->hasMarkers()): ?>
        zMaps.autoCenterAndZoom();
    <?php endif ?>

    <?php if ($this->edit): ?>
        zMaps.setUpdateHandlers();
    <?php endif ?>

</script>


