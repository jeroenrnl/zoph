#!/usr/bin/php
<?php
/*
 * This test fills the database with testdata 
 * You should normally not need to use this, as an XML-file with
 * testdata is included with the unittests. If you require to make
 * changes to that data, this script can be used.
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

define("TEST", true);

global $INSTANCE;
$INSTANCE="zophtest";

require_once("testData.php");
require_once("testImage.php");

require_once("../../settings.inc.php");
require_once("../../include.inc.php");
require_once("../../cli/cliimport.inc.php");

$lang=new language("en");

user::setCurrent(new user(1));
createTestData::run();
/**
 * Fill the database with data, so tests can be run.
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class createTestData {

    public static function run() {
        self::createAlbums();
        self::createCategories();
        self::createLocations();
        self::createUsers();
        self::createPeople();
        self::createGroups();
        self::createGroupPermissions();
        self::createTestImages();
        self::importTestImages();
    }
    /**
     * Create Albums in the database
     */
    private static function createAlbums() {
        $albums=testData::getAlbums();
        foreach ($albums as $id=>$alb) {
            $parent=$alb[0];
            $name=$alb[1];
            $album=new album();
            $album->set("album",$name);
            $album->set("parent_album_id", $parent);
            $album->insert();
        }
    }
    
    /**
     * Create categories in the database
     */
    private static function createCategories() {
        $categories=testData::getCategories();
        foreach ($categories as $id=>$cat) {
            $parent=$cat[0];
            $name=$cat[1];
            $category=new category();
            $category->set("category",$name);
            $category->set("parent_category_id", $parent);
            $category->insert();
        }
    }
    /**
     * Create locations in the database
     */
    private static function createLocations() {
        $locations=testData::getLocations();
        foreach ($locations as $id=>$loc) {
            $parent=$loc[0];
            $name=$loc[1];
            $place=new place();
            $place->set("title",$name);
            $place->set("parent_place_id", $parent);
            $place->insert();
        }
    }
    
    /**
     * Create Users in the database
     */
    private static function createUsers() {
        $users=testData::getUsers();
        $adminUsers=testData::getAdminUsers();
        foreach ($users as $id=>$name) {
            $user=new user();
            $user->set("user_name",$name);
            $user->insert();
            if(array_key_exists($id, $adminUsers)) {
                $user->set("user_class", 0);
                $user->update();
            } else {
                $user->set("user_class", 1);
                $user->update();
            }
        }
    }
    
    /**
     * Create people in the database
     */
    private static function createPeople() {
        $people=testData::getPeople();
        $users=testData::getUsers();
        $lightboxAlbums=testData::getLightboxAlbums();

        foreach ($people as $id=>$name) {
            list($first, $last) = explode(" ", $name);

            $person=new person();
            $person->set("first_name", $first);
            $person->set("last_name", $last);
            $person->insert();
            if(array_key_exists($id, $users)) {
                $user=new user($id);
                $user->lookup();
                $user->set("person_id", $id);
                $user->update();
            }
            if(array_key_exists($id, $lightboxAlbums)) {
                $user=new user($id);
                $user->lookup();
                $user->set("lightbox_id", $lightboxAlbums[$id]);
                $user->update();
            }
        }
    }


    private static function createGroups() {
        $groups=testData::getGroups();

        foreach ($groups as $id=>$arr_group) {
            $group=new group();
            $group->set("group_name", $arr_group[0]);
            $group->insert();
            foreach($arr_group[1] as $member) {
                $user=user::getByName($member);
                $group->add_member($user->getId());
            }
            $group->update();
        }
    }

    private static function createGroupPermissions() {
        $groupPermissions=testData::getGroupPermissions();

        foreach ($groupPermissions as $group=>$albums) {
            $gr=new group($group);
            $gr->lookup();
            foreach($albums as $alb) {
                $prm=new group_permissions($group, $alb);
                $prm->set("access_level", 5);
                $prm->set("watermark_level", 3);
                $prm->set("writable", 0);
                $prm->insert();

                $perm=$gr->get_group_permissions($alb);
           }

        }
    }

    private static function importTestImages() {

        $photos=testData::getPhotos();
        $photoLocation=testData::getPhotoLocation();
        $photoAlbums=testData::getPhotoAlbums();
        $photoCategories=testData::getPhotoCategories();
        $photoPeople=testData::getPhotoPeople();
        $photographer=testData::getPhotographer();
        $comments=testData::getComments();
        $ratings=testData::getRatings();
        $relations=testData::getRelations(); 

        $files=array();
        foreach($photos as $id=>$photo) {
            $files[]=new file(conf::get("path.images") . "/" . $photo);
        }
        conf::set("import.cli.thumbs", true);
        conf::set("import.cli.size", true);


        $imported=cliimport::photos($files, array());
        foreach($imported as $photo) {
            $user=new user(1);
            $user->lookup();
            user::setCurrent($user);

            $id=$photo->get("photo_id");
            if(isset($photoLocation[$id])) {
                $photo->set("location_id",$photoLocation[$id]);
                $photo->update();
                $photo->lookup();
            }
            if(isset($photographer[$id])) {
                $photo->set("photographer_id",$photographer[$id]);
                $photo->update();
                $photo->lookup();
            }
            if(is_array($photoAlbums[$id])) {
                foreach($photoAlbums[$id] as $alb) {
                    $photo->addTo(new album($alb));
                }
            }
            if(is_array($photoCategories[$id])) {
                foreach($photoCategories[$id] as $cat) {
                    $photo->addTo(new category($cat));
                }
            }
            if(is_array($photoPeople[$id])) {
                foreach($photoPeople[$id] as $pers) {
                    $photo->addTo(new person ($pers));
                }
            }
            if(isset($relations[$id])) {
                foreach($relations[$id] as $related => $rel_desc) {
                    photoRelation::defineRelation(
                        $photo,
                        new photo($related),
                        $rel_desc[0],
                        $rel_desc[1]);
                }
            }

            // WARNING, below this line other users log in!
            if(isset($comments[$id])) {
                foreach($comments[$id] as $user_id => $comment) {
                    $obj = new comment();
                    $user = new user($user_id);
                    $user->lookup();
                    user::setCurrent($user);

                    $subj="Comment by " . $user->getName();

                    $obj->set("comment", $comment);
                    $obj->set("subject", $subj);
                    $obj->set("user_id", $user_id);
                    // Set fake remote IP address:
                    $_SERVER["REMOTE_ADDR"]=$user->getName() . ".zoph.org";
                    $obj->insert();
                    $obj->add_comment_to_photo($photo->get("photo_id"));
                }
            }

            if(isset($ratings[$id])) {
                $total=0;
                $count=0;
                foreach($ratings[$id] as $user_id => $rating) {
                    $user = new user($user_id);
                    $user->lookup();
                    user::setCurrent($user);

                    // Set fake remote IP address:
                    $_SERVER["REMOTE_ADDR"]=$user->getName() . ".zoph.org";

                    $photo->rate($rating);
                    $photo->update();
                    $total+=$rating;
                    $count++;

                }
                $avg=$total/$count;
                $photo->lookup();
            }

        }
    }

    private static function createTestImages() {
        $photos=testData::getPhotos();
        $photoLocation=testData::getPhotoLocation();
        $photoAlbums=testData::getPhotoAlbums();
        $photoCategories=testData::getPhotoCategories();
        $photoPeople=testData::getPhotoPeople();
        $photographer=testData::getPhotographer();
        $comments=testData::getComments();
        $ratings=testData::getRatings();
        foreach($photos as $num=>$name) {
            $image=new testImage();
            $image->setName($name);

            $image->setLocation($photoLocation[$num]);
            $image->setPhotographer($photographer[$num]);

            foreach($photoAlbums[$num] as $alb) {
                $image->addToAlbum($alb);
            }
            foreach($photoCategories[$num] as $cat) {
                $image->addToCategory($cat);
            }

            foreach($photoPeople[$num] as $pers) {
                $image->addPerson($pers);
            }

            $image->writeImage();
        }
    }
}
