<?php
/**
 * Template for the info page.
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
require_once "header.inc.php";
?>
<h1>
    <?= $tpl_title ?>
</h1>
<div class="main">
    <h2>zoph</h2>
    <p>
        <?= translate("Zoph stands for <strong>z</strong>oph <strong>o</strong>rganizes <strong>ph</strong>otos.", 0) ?>
        <?= translate("Zoph is free software.", 0) ?>
    </p>
    <p>
        <?= sprintf(translate("Releases and documentation can be found at %s.", 0),
            "<a href=\"http://www.zoph.org/\">http://www.zoph.org/</a>") ?>
        <?= sprintf(translate("Send feedback to %s.", 0), "<img src=\"" . $tpl_mailaddr . "\">") ?>
    </p>
    <?php if ($user->isAdmin()): ?>
        <br>
        <table id="zophinfo">
            <?php foreach ($tpl_infoArray as $field => $value): ?>
                <tr>
                    <th><?= $field ?></th>
                    <td><?= $value ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endif ?>
    <p>
        <?= sprintf(translate("Zoph version %s, released %s.", 0), VERSION, RELEASEDATE) ?>
    </p>
    <p>
        <?= translate("Originally written by Jason Geiger, now maintained by Jeroen Roos " .
            "with thanks to the following for their contributions:", 0) ?>
    </p>
    <?php include "credits.html"; ?>
</div>

<?php
require_once "footer.inc.php";
?>
