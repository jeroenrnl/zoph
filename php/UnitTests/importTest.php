<?php
/*
 * Import test
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
 * Test photo import
 * @todo: fix
 */
class importTest extends ZophDataBaseTestCase {

    private static function createTestImage($name, $bg, $fg) {
        $bgcolour=new ImagickPixel();
        $bgcolour->setColor($bg);
        
        $text=new ImagickDraw();
        $text->setFillColor($fg);
        $text->setFontsize(60);

        $image=new Imagick();
        $image->newImage(600,400, $bgcolour);

        $image->annotateImage($text, 200, 200, 0, $name);

        $image->writeImage("/tmp/" . $name);

        $image->destroy();
        unset($image);
    }

    /**
     * Test importing images
     * @dataProvider getImages
     * @todo should also test adding albums, categories, etc.
     */

    public function testImportImages($id, $name, $bg, $fg) {
        if(file_exists(conf::get("path.images") . "/" . $name)) {
            unlink(conf::get("path.images") . "/" . $name);        
        }
        self::createTestImage($name, $bg, $fg);
        $photos[]=new file("/tmp/" . $name);
        conf::set("import.cli.thumbs", true);
        conf::set("import.cli.size", true);


        $imported=cliimport::photos($photos, array());
        foreach($imported as $photo) {
            $this->assertInstanceOf("photo", $photo);
            $this->assertEquals($name, $photo->get("name"));

            $this->assertEquals($id, $photo->get("photo_id"));
            $this->assertFileExists(conf::get("path.images") . "/" . $name);
        }
    }

    public function getImages() {
        return array(
            array(11, "FILE_0001.JPG", "blue", "yellow"),
            array(11, "FILE_0002.JPG", "red", "yellow"),
            array(11, "FILE_0003.JPG", "yellow", "blue")
         );
    }

}
