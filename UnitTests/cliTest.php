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

require_once "testSetup.php";

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
        $files=$this->getFilelist(array(
            "2013.02.01" => "PHOTO-01.JPG", 
            "2013.02.02" => "PHOTO-02.JPG", 
            "2013.02.03" => "PHOTO-03.JPG"));
        $this->doCleanup($files);

        $testdata=$this->getFilenames();
        foreach($testdata as $testimg) {
            helpers::createTestImage($testimg[0], $testimg[1], $testimg[2], $testimg[3]);
        }

        $cli="zoph --instance " . INSTANCE . " /tmp/PHOTO-01.JPG " .  
        "/tmp/PHOTO-02.JPG /tmp/PHOTO-03.JPG";
    
        $this->runCLI($cli);

        foreach($files as $file) {
            $this->assertFileExists($file);
        }

        $this->doCleanup($files);

    }

    /**
     * Test import with album, category, photographer and location
     */
    public function testOrganizedImport() {
        $files=$this->getFilelist(array(
            "2013.02.01" => "PHOTO-01.JPG", 
            "2013.02.02" => "PHOTO-02.JPG", 
            "2013.02.03" => "PHOTO-03.JPG"));
        $this->doCleanup($files);
        
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
        foreach($files as $file) {
            $this->assertFileExists($file);
        }

        $this->doCleanup($files);
        foreach(array("2013.02.01", "2013.02.02", "2013.02.03") as $dir) {
            $this->cleanDirs($dir);
        }
    }

    public function testDatedDirs() {
        $files=$this->getFilelist(array(
            "2013.02.01" => "PHOTO-01.JPG", 
            "" => "PHOTO-02.JPG", 
            "2013/02/03" => "PHOTO-03.JPG"));
        $this->doCleanup($files);

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

        foreach($files as $file) {
            $this->assertFileExists($file);
        }

        $this->doCleanup($files);
        foreach(array("2013.02.01", "", "2013/02/03") as $dir) {
            $this->cleanDirs($dir);
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

    /**
     * Test Create Album with non-existent --parent
     * @expectedException AlbumNotFoundException
     */
    public function testCreateAlbumNonExistentParent() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --parent NonExistent --album Test_new_album ";
            
        $this->runCLI($cli);
    }

    /**
     * Test Create Category with non-existent --parent
     * @expectedException CategoryNotFoundException
     */
    public function testCreateCategoryNonExistentParent() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --parent NonExistent --category Test_new_category ";
            
        $this->runCLI($cli);
    }

    /**
     * Test Create Place with non-existent --parent
     * @expectedException PlaceNotFoundException
     */
    public function testCreatePlaceNonExistentParent() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --parent NonExistent --place Test_new_place ";
            
        $this->runCLI($cli);
    }

    /**
     * Test Create Album 
     */
    public function testCreateAlbum() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --parent Album_1 --album Test_new_album ";
            
        $this->runCLI($cli);
        
        $albums=album::getByName("Test new album");
        $album=array_shift($albums);
        $album->lookup();
        $this->assertInstanceOf("album", $album);
        $parent=$album->get("parent_album_id");
        $this->assertEquals(2, $parent);
    }

    /**
     * Test Create Category 
     */
    public function testCreateCategory() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --parent Blue --category Test_new_category ";
            
        $this->runCLI($cli);
        
        $cats=category::getByName("Test new category");
        $cat=array_shift($cats);
        $cat->lookup();
        $this->assertInstanceOf("category", $cat);
        $parent=$cat->get("parent_category_id");
        $this->assertEquals(5, $parent);
    }

    /**
     * Test Create Place
     */
    public function testCreatePlace() {
        $cli="zoph --instance " . INSTANCE . 
            " --new --parent Netherlands --place Test_new_place ";
        $this->runCLI($cli);

        $places=place::getByName("Test new place");
        $place=array_shift($places);
        $place->lookup();
        $this->assertInstanceOf("place", $place);
        $parent=$place->get("parent_place_id");
        $this->assertEquals(3, $parent);
    }

    /**
     * Test calling with --help argument
     */
    public function testHelp() {
        $cli="zoph --instance " . INSTANCE . " --help";

        ob_start();
            $this->runCLI($cli);
        $output=ob_get_clean();
        $this->assertRegExp("/zoph.+\nUsage:.+\nOPTIONS:\n.+/", $output);
    }

    /**
     * Test calling with --version argument
     */
    public function testVersion() {
        $cli="zoph --instance " . INSTANCE . " --version";

        ob_start();
            $this->runCLI($cli);
        $output=ob_get_clean();
        $this->assertRegExp("/Zoph v.+, released [0-9]{1,2}-[0-9]{1,2}-[0-9]{2,4}/", $output);
    }

    private function doCleanup($files) {
        foreach($files as $file) {
            @unlink($file);
        }
    }

    private function getFileList($files) {
        $filelist=array();

        $prefixes=array(
            "", 
            THUMB_PREFIX,
            MID_PREFIX
        );

        foreach($files as $dir => $file) {
            if(is_int($dir)) {
                $dir="";
            } else {
                $dir.="/";
            }

            foreach($prefixes as $prefix) {
                if(!empty($prefix)) {
                    $filename=conf::get("path.images") . "/" . $dir . $prefix . "/" . 
                        $prefix . "_" . $file;
                } else {
                    $filename=conf::get("path.images") . "/" . $dir . $file;
                }

                $filelist[]=$filename;
            }
        }
        return $filelist;
    }

    private function cleanDirs($dir) {
        $prefixes=array(
            "", 
            THUMB_PREFIX,
            MID_PREFIX
        );
        foreach($prefixes as $prefix) {
            if(!empty($prefix)) {
                @rmdir(conf::get("path.images") . "/" . $dir . "/" . $prefix);
            }
        }
        @rmdir(conf::get("path.images") . "/" . $dir);
    }

    private function runCLI($cli) {
        $cli_array=explode(" ", trim($cli));
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
