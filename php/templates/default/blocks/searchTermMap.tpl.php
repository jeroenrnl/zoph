<?php
/**
 * Template for search term for searching the map
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
    <fieldset class="map">
        <legend><?= translate("map"); ?></legend>
<div class="searchTerm">
        <div class="searchIncrement">
        </div>
        <div class="searchConj">
            <?= $tpl_conj ?>
        </div>
        <div class="searchLabel searchLatLon">
            <input type="checkbox" name="_latlon_photos" value="photos" <?= $tpl_photos_checked ?> >
            <?= translate("photos taken") ?><br>
            <input type="checkbox" name="_latlon_places" value="places" <?= $tpl_places_checked ?>>
            <?= translate("locations") ?>
        </div>
        <div class="searchOp">
            <span class="searchOpText"><?= translate("less than") ?></span>
        </div>
        <div class="searchValue">
            <?= $tpl_value ?>
            <?= $tpl_entity ?>
            <span class="searchValueText"><?= translate("from"); ?></span>
        </div>
</div>
<div class="searchTerm">
        <div class="searchLabel">
            <?= translate("latitude") ?>
        </div>
        <div class="searchValue">
            <?= $tpl_lat ?><br>
        </div>
</div>
<div class="searchTerm">
        <div class="searchLabel">
            <?= translate("longitude") ?>
        </div>
        <div class="searchValue">
            <?= $tpl_lon ?><br>
        </div>
</div>
    </fieldset>
