<?php
/**
 * Display the header of the page
 *
 * This file is part of Zoph.
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
 * @author Jason Geiger
 * @author Jeroen Roos
 */
use conf\conf;

?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link type="text/css" rel="stylesheet" href="css.php">
    <link type="image/png" rel="icon" href="<?php echo static::getImage("icons/favicon.png") ?>">
    <script type="text/javascript">
        var template = "<?php echo conf::get("interface.template"); ?>";
        var icons={
        <?php foreach ($tpl_icons as $icon=>$file): ?>
            "<?php echo $icon ?>": "<?php echo $file ?>",
        <?php endforeach ?>
        };

    </script>
    <?php foreach ($tpl_javascript as $js): ?>
        <script type="text/javascript">
            <?= $js ?>
        </script>
    <?php endforeach ?>
    <?php foreach ($tpl_scripts as $script): ?>
        <script type="text/javascript" src="<?= $script ?>"></script>
    <?php endforeach ?>
    <?php if (isset($tpl_extrastyle)): ?>
        <style type="text/css">
            <?php echo $tpl_extrastyle ?>
        </style>
    <?php endif ?>
    <title><?php echo $tpl_title ?></title>
</head>
