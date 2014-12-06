<?php
/**
 * Unittests for photo class
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
 * Test photo class
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class photoTest extends ZophDataBaseTestCase {
    

    /**
     * test display() method
     */
    public function testDisplay() {

        $photo=new photo(5);
        $photo->lookup();

        list($header, $ph) = $photo->display();

        $image = imagecreatefromstring($ph);
        
        // Compare dimensions to stored size
        $this->assertEquals($photo->get("width"), imagesx($image));

        $modified=gmdate("D, d M Y H:i:s", filemtime($photo->getFilePath())) . " GMT";
        $exp_header=array(
            "Content-Length" => strlen($ph),
            "Content-Disposition" => "inline; filename=" . $photo->get("name"),
            "Last-Modified" => $modified,
            "Content-type" => "image/jpeg"
        );

        $this->assertEquals($exp_header, $header);
        
        // test midsize image
        list($header, $ph) = $photo->display(MID_PREFIX);

        $image = imagecreatefromstring($ph);
        $this->assertEquals(MID_SIZE, imagesx($image));
        
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $modified;

        list($header, $ph) = $photo->display();

        $exp_header=array(
            "http_status" => "HTTP/1.1 304 Not Modified"
        );

        $this->assertEquals($exp_header, $header);
        $this->assertNull($ph);

    }

    /**
     * test updateRelations function
     */
    public function testUpdateRelations() {
        $photo=new photo(1);
        $photo->lookup();

        $photo->_album_id=array(4);
        $photo->_category_id=array(5);
        $photo->_person_id=array(3,6);

        $vars=array(
            "_album_id" => 5,
            "_category_id" => array(8, 9),
            "_person_id" => 4,
            "_remove_album_id" => 2,
            "_remove_category_id" => array(2,3),
            "_remove_person_id" => 7
        );

        $photo->updateRelations($vars, "_id");

        $albums=$photo->getAlbums();
        $cats=$photo->getCategories();
        $people=$photo->getPeople();

        $album_ids=array();
        $cat_ids=array();
        $people_ids=array();

        foreach($albums as $album) {
            $album_ids[]=$album->getId();
        }

        foreach($cats as $cat) {
            $cat_ids[]=$cat->getId();
        }

        foreach($people as $person) {
            $people_ids[]=$person->getId();
        }

        array_multisort($album_ids);
        array_multisort($cat_ids);
        array_multisort($people_ids);

        $this->assertEquals(array(3,4,5), $album_ids);
        $this->assertEquals(array(5,8,9), $cat_ids);
        $this->assertEquals(array(2,3,4,5,6,9), $people_ids);
    }

    public function testUpdateEXIF() {
        $photo=new photo(2);
        $photo->lookup();
       
        $data=array(
            "ISO"       => "100", 
            "Make"      => "Zoph", 
            "Model"     => "Zoph Digital 2000",
            "ApertureValue" => "2.8",
            "ExposureTime" => "1/200", 
            "ExposureProgram#"  => "3",
            "EXIF:Flash#"    => "16",
            "FocalLength"   => "1200",
            "GPSLatitude"   => "150,0,0",
            "GPSLatitudeRef#"   => "E",
            "GPSLongitude"  => "80,0,0", 
            "GPSLongitudeRef#"  => "N"
            );
         
        helpers::writeEXIFdata($photo->getFilePath(), $data);

        $photo->updateEXIF();
        
        $display=$photo->getCameraDisplayArray();

        $expected=array(
            "camera make"   => "Zoph",
            "camera model"  => "Zoph Digital 2000",
            "flash used"    => "No",
            "focal length"  => "1200.0mm",
            "exposure"      => "0.005 s  (1/200) [aperture priority (semi-auto)]",
            "aperture"      => "f/2.8",
            "compression"   => "",
            "iso equiv"     => "100",
            "metering mode" => "",
            "focus distance" => "",
            "ccd width"     => "",
            "comment"       => ""
        );

        $this->assertEquals($expected, $display);

    }

    /**
     * Test thumbnail link
     */
    public function testGetThumbnailLink() {
        $photo=new photo(3);
        $photo->lookup();

        $block=$photo->getThumbnailLink();

        $this->assertInstanceOf("block", $block);
        $this->assertEquals("templates/default/blocks/link.tpl.php", $block->template);
        $this->assertEquals("photo.php?photo_id=3", $block->vars["href"]);
        $this->assertInstanceOf("block", $block->vars["link"]);
        $this->assertEquals("templates/default/blocks/img.tpl.php", $block->vars["link"]->template);
        $this->assertEquals("", $block->vars["target"]);
        
        $block=$photo->getThumbnailLink("http://test");

        $this->assertInstanceOf("block", $block);
        $this->assertEquals("http://test", $block->vars["href"]);
        $this->assertInstanceOf("block", $block->vars["link"]);
        $this->assertEquals("", $block->vars["target"]);
    }

    /**
     * Test fullsize link
     */
    public function testGetFullsizeLink() {
        $photo=new photo(3);
        $photo->lookup();

        $block=$photo->getFullsizeLink("photo");

        $this->assertInstanceOf("block", $block);
        $this->assertEquals("templates/default/blocks/link.tpl.php", $block->template);
        $this->assertEquals("image.php?photo_id=3", $block->vars["href"]);
        $this->assertEquals("photo", $block->vars["link"]);
        $this->assertEquals("", $block->vars["target"]);
        
        $user=user::getCurrent();
        $user->prefs->set("fullsize_new_win",true);
        $block=$photo->getFullsizeLink("photo");
        $this->assertEquals("_blank", $block->vars["target"]);
    }

    /**
     * Test getURL() function
     */
    public function testGetURL() {
        $photo=new photo(3);
        $photo->lookup();
        
        $url=$photo->getURL();
        $this->assertEquals("image.php?photo_id=3", $url);

        $url=$photo->getURL("mid");
        $this->assertEquals("image.php?photo_id=3&amp;type=mid",$url);
    }

    /**
     * test getImageTag() function
     */
    public function testGetImageTag() {
        $photo=new photo(3);
        $photo->lookup();

        $photo->set("title", "Nothing");
        $photo->update();

        $block=$photo->getImageTag();

        $this->assertInstanceOf("block", $block);
        $this->assertEquals("image.php?photo_id=3", $block->vars["src"]);
        $this->assertEquals("", $block->vars["class"]);
        $this->assertEquals("width=\"600\" height=\"400\"", $block->vars["size"]);
        $this->assertEquals("Nothing", $block->vars["alt"]);
        
        $block=$photo->getImageTag("mid");
        $this->assertInstanceOf("block", $block);
        $this->assertEquals("image.php?photo_id=3&amp;type=mid", $block->vars["src"]);
        $this->assertEquals("mid", $block->vars["class"]);
        $this->assertEquals("width=\"480\" height=\"320\"", $block->vars["size"]);
        $this->assertEquals("Nothing", $block->vars["alt"]);
    }

    /**
     * Test setting of location
     * @dataProvider getLocation
     */
    public function testSetLocation($photo, $loc) {
        $photo=new photo($photo);
        $place=new place($loc);

        $photo->setLocation($place);
        $photo->update();
        $photo->lookup();
        $this->assertInstanceOf("place", $photo->getLocation());
        $this->assertEquals($loc, $photo->getLocation()->getId());
    }

    /**
     * Test setting of photographer
     * @dataProvider getPhotographer
     */
    public function testSetPhotographer($photo, $phg) {
        $photo=new photo($photo);
        $photo->set("photographer_id",$phg);
        $photo->update();
        $photo->lookup();
        $this->assertInstanceOf("person", $photo->photographer);
        $this->assertEquals($photo->photographer->getId(), $phg);
    }

    /**
     * Test adding to albums
     * @dataProvider getNewAlbums
     */
    public function testAddToAlbum($photo, array $newalbums) {
        $ids=array();
        $photo=new photo($photo);
        foreach($newalbums as $alb) {
            $photo->addTo(new album($alb));
        }
        $albums=$photo->getAlbums();
        foreach($albums as $album) {
            $ids[]=$album->getId();
            $this->assertInstanceOf("album", $album);
        }
        foreach($newalbums as $album_id) {
            $this->assertContains($album_id, $ids);
        }
    }

    /**
     * Test getting album list
     * @dataProvider getAlbums
     */
    public function testGetAlbums($photo_id, $user_id, array $exp_albums) {
        user::setCurrent(new user($user_id));
        $photo=new photo($photo_id);

        $photo->lookup();

        $albums=$photo->getAlbums();
        $act_albums=array();
        foreach($albums as $album) {
            $act_albums[]=$album->getId();
        }

        $this->assertEquals($exp_albums, $act_albums);

        user::setCurrent(new user(1));

    }

    /**
     * Test adding to categories
     * @dataProvider getCategories
     */
    public function testAddToCategories($photo, array $newcats) {
        $ids=array();
        $photo=new photo($photo);
        foreach($newcats as $cat) {
            $photo->addTo(new category($cat));
        }
        $cats=$photo->getCategories();
        foreach($cats as $cat) {
            $ids[]=$cat->getId();
            $this->assertInstanceOf("category", $cat);
        }
        foreach($newcats as $cat_id) {
            $this->assertContains($cat_id, $ids);
        }
    }

    /**
     * Test adding people
     * @dataProvider getPeople
     */
    public function testAddPerson($photo, array $newpers) {
        $ids=array();
        $photo=new photo($photo);
        foreach($newpers as $pers) {
            $photo->addTo(new person($pers));
        }
        $peo=$photo->getPeople();
        foreach($peo as $per) {
            $ids[]=$per->getId();
            $this->assertInstanceOf("person", $per);
        }
        foreach($newpers as $per_id) {
            $this->assertContains($per_id, $ids);
        }
    }


    /**
     * Test adding comments
     * @dataProvider getComments
     */
    public function testAddComment($photo_id, $comment, $user_id) {
        $obj = new comment();
        $user = new user($user_id);
        $user->lookup();

        $photo=new photo($photo_id);
        $photo->lookup();

        $subj="Comment by " . $user->getName();

        $obj->set("comment", $comment);
        $obj->set("subject", $subj);
        $obj->set("user_id", $user_id);
        $_SERVER["REMOTE_ADDR"]=$user->getName() . ".zoph.org";
        $obj->insert();
        $obj->addToPhoto($photo);
        
        $this->assertInstanceOf("comment", $obj);
        $this->assertEquals($obj->get_photo()->getId(), $photo->getId());
    }

    /**
     * Test getTime function
     */
    private function setupTime() {
        ini_set("date.timezone", "Europe/Amsterdam");
        $photo=new photo(9);
        $photo->lookup();

        $photo->set("date", "2013-01-01");
        $photo->set("time", "4:00:00");
        $photo->update();

        // Camera's timezone is UTC
        $conf=conf::set("date.tz", "UTC");
        $conf->update();
        
        $conf=conf::set("date.format", "d-m-Y");
        $conf->update();

        $conf=conf::set("date.timeformat", "H:i:s T");
        $conf->update();
        
        $location=$photo->location;
        
        // Timezone for New York, where photo was taken.
        $location->set("timezone", "America/New_York");
        $location->update();

    }


    /**
     * Test getTime function
     */
    public function testGetTime() {
        $this->setupTime();
        
        $photo=new photo(9);
        $photo->lookup();
        
        $datetime=$photo->getTime();

        $expected=new Time("31-12-2012 23:00:00 America/New_York");

        $this->assertEquals($expected, $datetime);
    }

    /**
     * test getFormattedDateTime
     */
    public function testGetFormattedDateTime() {
        $this->setupTime();
        
        $photo=new photo(9);
        $photo->lookup();
        // First test:
        // camera timezone is UTC
        // place timezone is America/New York
        // The time should be -5 hrs from 1/1/13 4:00, New York timezone (EST)
        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("31-12-2012", $datetime[0]);
        $this->assertEquals("23:00:00 EST", $datetime[1]);

        // Second test:
        // camera timezone is Moscow
        // place timezone is Invalid
        // The time should be 1/1/13 4:00, Moscow timezone (MSK)
        $conf=conf::set("date.tz", "Europe/Moscow");
        $conf->update();
        $location=$photo->location;
        $location->set("timezone", "Nonsense/Timezone");
        $location->update();

        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("04:00:00 MSK", $datetime[1]);

        // Third test:
        // camera timezone is empty (local time)
        // place timezone is Invalid
        // The time should be 1/1/13 4:00, Default timezone (php.ini) (CET)
        $conf=conf::set("date.tz", "");
        $conf->update();
        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("04:00:00 CET", $datetime[1]);

        // Fourth test:
        // camera timezone is empty (local time)
        // place timezone is New York
        // The time should be 1/1/13 4:00, EST
        $location->set("timezone", "America/New_York");
        $location->update();
        
        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("04:00:00 EST", $datetime[1]);
        
        // Fifth Test
        // camera timezone is Australia (+8)
        // place timezone is Los Angeles (-8)
        // this makes the time 31/12/12 12:00
        // however, with an extra correction of -1 minute, it becomes 11:59
        $conf=conf::set("date.tz", "Australia/Perth");
        $conf->update();
        $location->set("timezone", "America/Los_Angeles");
        $location->update();

        $photo->set("time_corr", "-1");
        $photo->update();

        $datetime=$photo->getFormattedDateTime();
        $this->assertEquals("31-12-2012", $datetime[0]);
        $this->assertEquals("11:59:00 PST", $datetime[1]);

    }

    /**
     * test getReverseDate
     */
    public function testGetReverseDate() {
        $this->setupTime();
        
        $photo=new photo(9);
        $photo->lookup();
        
        $date=$photo->getReverseDate();
        $this->assertEquals("2012-12-31", $date);
    }
        
        

    /**
     * Test getUTCtime function
     */
    public function testGetUTCtime() {
        $this->setupTime();
        
        conf::set("date.tz", "Europe/Amsterdam");
        
        $photo=new photo(9);
        $photo->lookup();
        
        $datetime=$photo->getUTCtime();

        $this->assertEquals("01-01-2013", $datetime[0]);
        $this->assertEquals("03:00:00 UTC", $datetime[1]);
    }

    /**
     * Test importing images
     * @dataProvider getImages
     * @todo should also test adding albums, categories, etc.
     */
    public function testImportImages($id, $name, $bg, $fg, $exif) {
        user::setCurrent(new user(1));
        if(file_exists(conf::get("path.images") . "/" . $name)) {
            unlink(conf::get("path.images") . "/" . $name);        
        }
        helpers::createTestImage($name, $bg, $fg, $exif);
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

    /**
     * Test importing images with dateddirs
     * @dataProvider getImages
     * @todo should also test adding albums, categories, etc.
     */

    public function testImportImagesDated($id, $name, $bg, $fg, $exif) {
        user::setCurrent(new user(1));
        if(file_exists(conf::get("path.images") . "/" . $name)) {
            unlink(conf::get("path.images") . "/" . $name);        
        }
        helpers::createTestImage($name, $bg, $fg, $exif);
        $photos[]=new file("/tmp/" . $name);
        conf::set("import.cli.thumbs", true);
        conf::set("import.cli.size", true);
        conf::set("import.dated", true);


        $imported=cliimport::photos($photos, array());
        foreach($imported as $photo) {
            $this->assertInstanceOf("photo", $photo);
            $this->assertEquals($name, $photo->get("name"));

            $this->assertEquals($id, $photo->get("photo_id"));
            $date=str_replace("-", ".", $photo->get("date"));
            $this->assertFileExists(conf::get("path.images") . "/" . $date . "/" . $name);
        }
        unlink(conf::get("path.images") . "/" . $date . "/" . $name);        
    }

    /**
     * Create a track to test geotagging functions
     * creates a line north-south in western europe.
     */
    private function buildTrack() {
        conf::set("maps.provider", "googlev3")->update();
        query("truncate zoph_point");
        $track=new track();

        $track->set("name", "Test Track");

        for($x=0; $x<500; $x++) {
            $point=new point();
            $point->set("lat", round(52 - ($x/100),2));
            $point->set("lon", 5);
            $hour=floor($x/60);
            $minute=$x % 60;
            $datetime="2013-01-01 " . $hour . ":" . $minute . ":00";
            $point->set("datetime", $datetime);
            $track->addPoint($point);
        }
        $track->insert();
        return $track;
    }

    /**
     * Test getLatLon() function
     */
    public function testGetLatLon() {
        conf::set("date.tz", "UTC")->update();
        $track=$this->buildTrack();

        $lat=array(
            2   => 51.8,
            3   => 51.7,
            4   => 51.6,
            5   => 51.5
        );

        $photos=array();
        for($i=1; $i<=5; $i++) {
            $photo=new photo($i);
            $photo->lookup();

            $photo->set("date", "2013-01-01");
            $photo->set("time", "00:" . $i . "0:00");

            $photo->update();
            $photos[]=$photo;
        }

        foreach(array(4,5) as $i) {
            $place=new place($i);
            $place->set("timezone", "UTC");
            $place->update();
        }

        $this->assertEquals(5, sizeof($photos));
        $photos=photo::removePhotosWithNoValidTz($photos);
        $this->assertEquals(4, sizeof($photos));
        $ph=photo::removePhotosWithLatLon($photos);
        $this->assertEquals(4, sizeof($ph));

        foreach($photos as $photo) {
            $point=$photo->getLatLon($track, 300, true);
            $photo->setLatLon($point);
            $photo->update();
            $this->assertEquals(5, $photo->get("lon"));
            // This is rounded because floats sometimes vary by 0.0000001
            $this->assertEquals($lat[$photo->getId()], round($photo->get("lat"),4));
        }

        $this->assertEquals(4, sizeof($photos));
        $ph=photo::removePhotosWithLatLon($photos);
        $this->assertEquals(0, sizeof($ph));
    }

    /**
     * Test getSubset function
     */
    public function testGetSubset() {

        $photos=array();
        $first=array();
        $last=array();

        for($i=1; $i<=50; $i++) {
            if($i<=5) {
                $first[$i]=new photo($i);
            }
            $photos[]=new photo($i);
            if($i>45) {
                $last[$i]=new photo($i);
            }

        }
        $firstlast=$first + $last;

        $subset=photo::getSubset($photos, array("first", "last"), 5);
        $this->assertEquals($firstlast, $subset);


        $subset=photo::getSubset($photos, array("random"), 5);

        $this->assertCount(5, $subset);

        $subset=photo::getSubset($photos, array("first", "random", "last"), 5);
        $this->assertCount(15, $subset);

        $photos=array();
        
        for($i=1; $i<=4; $i++) {
            $photos[]=new photo($i);
        }
        $subset=photo::getSubset($photos, array("first", "random", "last"), 5);
        
        $photos=array();
        for($i=1; $i<=8; $i++) {
            $photos[]=new photo($i);
        }
        $subset=photo::getSubset($photos, array("first", "random"), 5);


    }
    /**
     * Check that rotate indeed does not touch the file
     * when rotating is not allowed by comparing hashes
     */
    public function testRotateNotAllowed() {
        conf::set("rotate.enable", false);

        $photo=new photo(5);
        $photo->lookup();

        $hash=$photo->getHash();

        $photo->rotate(90);

        $newhash=$photo->getHash();

        $this->assertEquals($hash, $newhash);
    }
    /**
     * Test rotating a photo
     * @dataProvider getRotateCmds
     */
    public function testRotate($cmd) {
        conf::set("rotate.command", $cmd);
        conf::set("rotate.enable", true);

        $photo=new photo(5);
        $photo->lookup();

        list($width, $height)=getimagesize($photo->getFilePath());
        list($mwidth, $mheight)=getimagesize($photo->getFilePath(MID_PREFIX));
        list($twidth, $theight)=getimagesize($photo->getFilePath(THUMB_PREFIX));

        $photo->rotate(90);

        list($rwidth, $rheight)=getimagesize($photo->getFilePath());
        list($rmwidth, $rmheight)=getimagesize($photo->getFilePath(MID_PREFIX));
        list($rtwidth, $rtheight)=getimagesize($photo->getFilePath(THUMB_PREFIX));

        // Check if image is rotated by checking whether width and height have been swapped
        $this->assertEquals($width, $rheight);
        $this->assertEquals($height, $rwidth);
        $this->assertEquals($mwidth, $rmheight);
        $this->assertEquals($mheight, $rmwidth);
        $this->assertEquals($twidth, $rtheight);
        $this->assertEquals($theight, $rtwidth);

        $this->assertEquals($width, $photo->get("height"));
        $this->assertEquals($height, $photo->get("width"));

        // Now move back the original so it's available for the next test
        $dir = conf::get("path.images") . "/" . $photo->get("path") . "/";
        $name=$dir . $photo->get("name");
        $backupname=$dir . conf::get("rotate.backup.prefix") . $photo->get("name");

        unlink($name);
        rename($backupname, $name);

        $photo->updateSize();
        $photo->thumbnail(true);
    }

    /**
     * Test rotating with invalid files
     * @expectedException ZophException
     */
    public function testRotateConvertError() {
        conf::set("rotate.command", "convert");
        conf::set("rotate.enable", true);

        $photo=new photo();
        $photo->set("name", "invalid.jpg");
        
        $dir = conf::get("path.images") . "/";

        $ph=$dir .  $photo->get("name");
        $backup=$dir .  conf::get("rotate.backup.prefix") . $photo->get("name");

        touch($ph);
        touch($backup);
        $photo->rotate(90);
        unlink($ph);
        unlink($backup);
    }

    /**
     * Test rotating with failed backup creation.
     * @expectedException FileCopyFailedException
     */
    public function testRotateFailedBackup() {
        conf::set("rotate.enable", true);

        $photo=new photo(5);
        $photo->lookup();
        
        // Mess up by changing imagedir
        $imagedir=conf::get("path.images");
        conf::set("path.images", "/tmp");
        try {
            $photo->rotate(90);
        } catch (FileCopyFailedException $e) {
            conf::set("path.images", $imagedir);
            throw $e;
        }
    }

    //================== DATA PROVIDERS =======================

    public function getLocation() {
        return array(
            array(1, 5),
            array(2, 6),
            array(3, 7),
            array(4, 8)
         );
    }

    public function getPhotographer() {
        return array(
            array(1, 5),
            array(2, 6),
            array(3, 7),
            array(4, 8)
         );
    }

    public function getImages() {
        return array(
            array(11, "FILE_0001.JPG", "blue", "yellow", 
                array("DateTimeOriginal" => "2013-01-01 13:00:00")),
            array(11, "FILE_0002.JPG", "red", "yellow",
                array("DateTimeOriginal" => "2012-12-31 15:00:00")),
            array(11, "FILE_0003.JPG", "yellow", "blue", 
                array("DateTimeOriginal" => "2013-01-01 14:00:00"))
         );
    }

    public function getNewAlbums() {
        return array(
            array(1, array(2,3,4)),
            array(2, array(1,5,6)),
            array(3, array(7)),
            array(4, array(8,9))
         );
    }

    public function getCategories() {
        return array(
            array(1, array(4,5,6)),
            array(2, array(1,5,6)),
            array(3, array(7)),
            array(4, array(8,9))
         );
    }

    public function getPeople() {
        return array(
            array(1, array(3,4,6)),
            array(2, array(1,6,8)),
            array(3, array(7)),
            array(4, array(6,9))
         );
    }

    public function getComments() {
        return array(
            array(1, "Test Comment", 3),
            array(2, "Test comment [b]with bold[/b]", 4),
            array(3, "Test comment with [i]unclosed tag",5),
            array(4, "Test comment with <b>html</b>", 6)
         );
    }

    public function getAlbums() {
        // photo_id, user_id, albums
        return array(
            array(1, 3, array(2)),
            array(7, 3, array(2)),
            array(1, 1, array(2, 3)),
            array(8, 4, array(3))
        );


    }
    public function getRatings() {
        return array(
            array(1, 10, 3, 8),
            array(1, 8, 6, 7.6),
            array(1, 8, 4, 7.6),
            array(2, 3, 4, 4),
            array(2, 7, 5, 5.25)
         );
    }

    public function getRotateCmds() {   
        return array(
            array("jpegtran"), 
            array("convert")
        );
    }
}
