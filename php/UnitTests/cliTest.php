<?php
/**
 * Test CLI interface
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
 * Test CLI interface
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class cliTest extends ZophDataBaseTestCase {

    /**
     * Test import without additional parameters
     */
    public function testBasicImport() {
        $testdata=$this->getFilenames();
        foreach($testdata as $testimg) {
            helpers::createTestImage($testimg[0], $testimg[1], $testimg[2], $testimg[3]);
        }

        $cli="zoph --instance " . INSTANCE . " /tmp/PHOTO-01.JPG " .  
        "/tmp/PHOTO-02.JPG /tmp/PHOTO-03.JPG";
    
        $this->runCLI($cli);


        foreach(array(
            "2013.02.01" => "PHOTO-01.JPG", 
            "2013.02.02" => "PHOTO-02.JPG", 
            "2013.02.03" => "PHOTO-03.JPG") as $dir=>$file) {
                $this->checkFilesExistAndCleanFiles($dir, $file);
        }
    }

    /**
     * Test import with album, category, photographer and location
     */
    public function testOrganizedImport() {
        $testdata=$this->getFilenames();
        foreach($testdata as $testimg) {
            helpers::createTestImage($testimg[0], $testimg[1], $testimg[2], $testimg[3]);
        }

        $cli="zoph --instance " . INSTANCE . " --album Album_1 " .
            "--category blue --photographer Brian_May --person Jimi_Hendrix " . 
            "--location Netherlands /tmp/PHOTO-01.JPG " .  
            "/tmp/PHOTO-02.JPG /tmp/PHOTO-03.JPG";
    
        $this->runCLI($cli);

        for($i=1; $i<=3; $i++) {
            $photos=photo::getByName("PHOTO-0" . $i . ".JPG");
            $photo=array_pop($photos);
            $this->assertInstanceOf("photo", $photo);

            $albums=$photo->getAlbums();
            $this->assertEquals(1, sizeof($albums));
            $album=array_pop($albums);
            $this->assertInstanceOf("album", $album);
            $this->assertEquals(2, $album->getId());

            $categories=$photo->getCategories();
            $this->assertEquals(1, sizeof($categories));
            $category=array_pop($categories);
            $this->assertInstanceOf("category", $category);
            $this->assertEquals(5, $category->getId());
            
            $people=$photo->getPeople();
            $this->assertEquals(1, sizeof($people));
            $person=array_pop($people);
            $this->assertInstanceOf("person", $person);
            $this->assertEquals(3, $person->getId());
            
            $photographer=$photo->getPhotographer();
            $this->assertInstanceOf("person", $photographer);
            $this->assertEquals(2, $photographer->getId());
            
            $place=$photo->getLocation();
            $this->assertInstanceOf("place", $place);
            $this->assertEquals(3, $place->getId());
        }

        // cleanup
        foreach(array(
            "2013.02.01" => "PHOTO-01.JPG", 
            "2013.02.02" => "PHOTO-02.JPG", 
            "2013.02.03" => "PHOTO-03.JPG") as $dir=>$file) {
                $this->checkFilesExistAndCleanFiles($dir, $file);
        }
    }

    public function testDatedDirs() {
        $testdata=$this->getFilenames();
        foreach($testdata as $testimg) {
            helpers::createTestImage($testimg[0], $testimg[1], $testimg[2], $testimg[3]);
        }
        
        $cli="zoph --instance " . INSTANCE . 
            " --datedDirs --nohier " .
            " /tmp/PHOTO-01.JPG ";
            
        $this->runCLI($cli);

        $cli="zoph --instance " . INSTANCE . 
            " --no-dateddirs " .
            " /tmp/PHOTO-02.JPG ";
            
        $this->runCLI($cli);

        $cli="zoph --instance " . INSTANCE . 
            " -H " .
            " /tmp/PHOTO-03.JPG ";
            
        $this->runCLI($cli);
        // cleanup
        foreach(array(
            "2013.02.01" => "PHOTO-01.JPG", 
            "" => "PHOTO-02.JPG", 
            "2013/02/03" => "PHOTO-03.JPG") as $dir=>$file) {
                $this->checkFilesExistAndCleanFiles($dir, $file);
        }
    }

    /**
     * Test Create Album with no --parent
     * @expectedException CliNoParentException
     */
    public function testCreateAlbumNoParent() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --album Test_new_album ";
            
        $this->runCLI($cli);
    }

    /**
     * Test Create Category with no --parent
     * @expectedException CliNoParentException
     */
    public function testCreateCategoryNoParent() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --category Test_new_category ";
            
        $this->runCLI($cli);
    }

    /**
     * Test Create Place with no --parent
     * @expectedException CliNoParentException
     */
    public function testCreatePlaceNoParent() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --place Test_new_place ";
            
        $this->runCLI($cli);
    }

    private function checkFilesExistAndCleanFiles($dir, $file) {
        $prefixes=array(
            "", 
            THUMB_PREFIX,
            MID_PREFIX
        );
        foreach($prefixes as $prefix) {
            if(!empty($prefix)) {
                $filename=IMAGE_DIR . "/" . $dir . "/" . $prefix . "/" . $prefix . "_" . $file;
            } else {
                $filename=IMAGE_DIR . "/" . $dir . "/" . $file;
            }

            $this->assertFileExists($filename);
            unlink($filename);
            if(!empty($prefix)) {
                @rmdir(IMAGE_DIR . "/" . $dir . "/" . $prefix);
            }
        }
        @rmdir(IMAGE_DIR . "/" . $dir);
    }

    private function runCLI($cli) {
        $cli_array=explode(" ", $cli);
        $args=str_replace("_", " ", $cli_array);
        $admin=new user(1);
        $admin->lookup();
        $admin->lookup_person();
        $admin->lookup_prefs();
        $cli=new cli($admin, 3, $args);
        $cli->run();
    }        

    private function getFilenames() {
        return array(
            array ("PHOTO-01.JPG", "blue", "yellow", array(
                "DateTimeOriginal" => "2013-02-01 13:00:00")),
            array ("PHOTO-02.JPG", "blue", "yellow", array(
                "DateTimeOriginal" => "2013-02-02 13:00:00")),
            array ("PHOTO-03.JPG", "blue", "yellow", array(
                "DateTimeOriginal" => "2013-02-03 13:00:00"))
        );
    }
}
