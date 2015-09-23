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
        if (file_exists("/tmp/" . $name)) {
            unlink("/tmp/" . $name);
        }
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

    public static function createTrack($num=10, $random=false) {

        $track=new track();
        $track->set("name", "Test Track");

        $points=array();
        for ($x=0; $x<$num; $x++) {
            $point=new point();
            $point->set("lat", round(52 - ($x/100),2));
            $point->set("lon", 5);
            $datetime="2013-01-01 0:" . $x . ":00";
            $point->set("datetime", $datetime);
            $points[]=$point;
        }

        if ($random) {
            // We shuffle the point, so the database keys do NOT represent the
            // date/time order!
            shuffle($points);
        }

        foreach ($points as $point) {
            $track->addPoint($point);
        }
        $track->insert();

        return $track;
    }

    public static function createPagesPagesets($num=10) {
        $pagesets=array();
        $psIds=array();
        $pIds=array();
        for ($i=0; $i<=1; $i++) {
            $pagesets[$i]=new pageset();
            $pagesets[$i]->set("title", "Test Page " . $i);
            $pagesets[$i]->insert();
            $psIds[]=$pagesets[$i]->getId();
        }

        for ($i=1; $i<=$num; $i++) {
            $page=new page();
            $page->set("title", "Test Page " . $i);
            $page->set("text", "Test Page [b]" . $i . "[/b]");
            $page->insert();

            $pIds[]=$page->getId();

            // Add even pages to $pagesets[0] and odd pages to $pagesets[1]
            $pagesets[$i%2]->addPage($page);

            // Add page 1 and 2 to the other as well
            if ($i == 1) {
                $pagesets[0]->addPage($page);
            } else if ($i == 2) {
                $pagesets[1]->addPage($page);
            }
        }
        return array($psIds, $pIds);
    }
}

?>
