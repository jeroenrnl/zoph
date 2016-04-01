<?php
/**
 * Takes care of the import throught the CLI
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
 * @package Zoph
 */

/**
 * Class that takes care of the import through the CLI
 */
class CliImport extends Import {
    /**
     * Displays a progressbar on the CLI
     *
     * The progressbar will not be wider than 60 characters, so we have
     * 20 chars left for counter etc. on a 80 char screen
     * the real width of the screen is not checked because it cannot be
     * done in PHP without external programs
     * After displaying the progressbar, it will 'backspace' to the
     * beginning of the line, so any error message will
     * not cause a distorted screen
     * @var int progress
     * @var int total
     */

    public static function progress($cur, $total) {
        if (!defined("CLI")) {
            return;
        }
        if ($total>=60) {
            $calccur=$cur/$total*60;
            $dispcur=floor($calccur);
            $disptotal=60;
        } else {
            $calccur=0;
            $dispcur=$cur;
            $disptotal=$total;
        }
        $display="[";
        $display.=str_repeat("|", $dispcur);
        $rem=round($calccur - $dispcur,2);
        $num=$total/$disptotal;
        if ($num > 3) {
            if ($rem > 0.333  && $rem < 0.666) {
                $display.=".";
            } else if ($rem > 0.6666 && $rem < 0.999) {
                $display.=":";
            } else if ($rem > 0.999) {
                $display.="|";
            }
        } else if ($num == 2) {
            if ($rem >= 0.5) {
                $display.=".";
            }
        }

        $display=str_pad($display, $disptotal + 1);
        $display.="]";
        $perc=floor($cur / $total * 100);
        $display.= " [ $cur / $total (" . $perc . "%) ]";
        echo $display;
        echo str_repeat(chr(8), strlen($display));
    }

}

