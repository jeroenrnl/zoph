<?php
/**
 * Template for edit tracks form
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
 * @todo This is a very simple form, it should be replaces by a generic
 *       form template, at this moment this is not possible because Zoph
 *       has not fully switched to a template-based system
 */
if(!ZOPH) { die("Illegal call"); }
?>

   <br>
   <form method="post" action="track.php">
       <p>
           <input type="hidden" name="_action" value="<?php echo $tpl_action ?>">
           <input type="hidden" name="track_id" value="<?php echo $tpl_track_id ?>">
           <label for="name"><?php echo translate("name") ?></label>
           <input id="name" name="name" maxlength=32 size=20 value="<?php echo $tpl_name; ?>"><br>
           <input type="submit" value="<?php echo translate($tpl_action, 0) ?>">
       </p>
   </form>

