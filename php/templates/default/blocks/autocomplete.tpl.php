<?php
/**
 * Template for html for autocomplete dropdown box
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

if(!ZOPH) { die("Illegal call"); }
?>
<!-- Do not insert enter between the two inputs because the Javascript chokes on that! //-->
<input type=hidden id="<?php echo $tpl_id ?>" name="<?php echo $tpl_name ?>" value="<?php echo $tpl_value ?>"><input type=text id="_<?php echo $tpl_id ?>" name="_<?php echo $tpl_name ?>" value="<?php echo $tpl_text ?>" class="autocomplete">
