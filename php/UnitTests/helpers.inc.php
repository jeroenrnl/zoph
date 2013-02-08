<?php
/**
 * Helper functions that can be reused in different tests
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
 * @package ZophUnitTest
 * @author Jeroen Roos
 */

/**
 * Helper functions that can be reused in different tests
 */
class helpers {

    public static function createTestImage($name, $bg, $fg, $exif) {
        $bgcolour=new ImagickPixel();
        $bgcolour->setColor($bg);

        $text=new ImagickDraw();
        $text->setFillColor($fg);
        $text->setFontsize(60);

        $image=new Imagick();
        $image->newImage(600,400, $bgcolour);

        $image->annotateImage($text, 300 - strlen($name) * 15, 200, 0, $name);

        $image->writeImage("/tmp/" . $name);

        self::writeEXIFdata("/tmp/" . $name, $exif);

        $image->destroy();
        unset($image);
    }

    public static function writeEXIFdata($file, array $data) {

        $cmd="exiftool ";
        foreach ($data as $label => $value) {
            $cmd.=" -" . $label . "=\"" . $value . "\"";
        }

        $cmd .=" " . escapeshellarg($file);
        exec($cmd);
    }
}

?>
