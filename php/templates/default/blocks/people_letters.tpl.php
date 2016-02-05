<?php
/**
 * Template for the a to z links top of the people overview
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
 * @author Jeroen Roos
 * @package ZophTemplates
 */

if (!ZOPH) { die("Illegal call"); }
?>
<div class="letter">
    <?php for ($l = "a"; $l != "aa"; $l++): ?>
        <a href="people.php?_l=<?= $l ?>" <?= ($l == $tpl_l) ? "class=\"selected\"" : "" ?>"><?= $l ?></a> |
    <?php endfor ?>
    <a href="people.php?_l=no%20last%20name"><?php echo translate("no last name") ?></a> |
    <a href="people.php?_l=all"><?php echo translate("all") ?></a>
</div>

