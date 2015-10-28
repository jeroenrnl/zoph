<?php
/**
 * Testdata for Zoph Unit tests
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
 * Testdata for Zoph Unit tests
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class testData {


    public static function getPhotos() {
        $photos=array();
        for($num=1; $num<=12; $num++) {
            $photos[$num]=sprintf("TEST_%04d.JPG", $num);
        }
        return $photos;
    }

    public static function getPhotoData() {
        /** date, time, timestamp, lat, lon */
        return array(
            1 => array("2014-01-01", "00:01:00", "2014-01-01 00:01:00", 52.25, 5.75),
            2 => array("2014-01-02", "00:01:00", "2014-01-02 00:01:00", 51.9225, 4.47917),
            3 => array("2014-01-03", "00:01:00", "2014-01-03 00:01:00", 51.9225, 4.47917),
            4 => array("2014-01-04", "00:01:00", "2014-01-04 00:01:00", 52.37403, 4.88969),
            5 => array("2014-01-05", "00:01:00", "2014-01-05 00:01:00", 52.37403, 4.88969),
            6 => array("2014-01-06", "00:01:00", "2014-01-06 00:01:00", 51.5, 10.5),
            7 => array("2014-01-07", "00:01:00", "2014-01-10 00:05:00", 52.52437, 13.41053),
            8 => array("2014-01-08", "00:01:00", "2014-01-10 00:04:00", 43.70011, -79.4163),
            9 => array("2014-01-09", "00:01:00", "2014-01-10 00:03:00", 40.71427, -74.00597),
            10 => array("2014-01-10", "00:01:00", "2014-01-10 00:02:00", 38.89511, -77.03637),
            11 => array("2015-06-17", "00:01:00", "2015-06-17 00:01:00", -25, 135),
            12 => array("2015-06-17", "00:02:00", "2015-06-17 00:02:00", -25, 135)
       );
    }

    public static function getCategories() {
        return array(
            /* 1 => "Root Category", */
            2 => array(1,"red"),
            3 => array(2,"indianRed1"),
            4 => array(2,"DarkRed"),
            5 => array(1,"blue"),
            6 => array(5,"LightBlue"),
            7 => array(6,"DarkBlue"),
            8 => array(1,"Yellow"),
            9 => array(1,"white"),
            10 => array(9,"grey25"),
            11 => array(9,"grey50"),
            12 => array(9,"grey75"),
            13 => array(9,"black")
        );
    }

    public static function getAlbums() {
        return array(
            /* 1 => "Root Album", */
            2 => array(1,"Album 1"),
            3 => array(2,"Album 10"),
            4 => array(3,"Album 100"),
            5 => array(1,"Album 2"),
            6 => array(5,"Album 20"),
            7 => array(6,"Album 200"),
            8 => array(5,"Album 21"),
            9 => array(7,"Album 2001"),
            10 => array(7,"Album 2002"),
            11 => array(8,"Album 210"),
            12 => array(1,"Album 3"),
            13 => array(1,"Album 4"),
            14 => array(1,"Album 5")
        );
    }

    public static function getLocations() {
        return array(
            /* 1 => "World" */
            2 => array(1, "Europe", 48.69096, 9.14062),
            3 => array(2, "Netherlands", 52.25, 5.75),
            4 => array(3,"Rotterdam", 51.9225, 4.47917),
            5 => array(3,"Amsterdam", 52.37403, 4.88969),
            6 => array(2,"Germany", 51.5, 10.5),
            7 => array(6,"Berlin", 52.52437, 13.41053),
            8 => array(1,"North America", 6.07323, -100.54688),
            9 => array(8,"Canada", 60.10867, -113.64258),
            10 => array(9,"Toronto", 43.70011, -79.4163),
            11 => array(8,"USA", 39.76, -98.5),
            12 => array(11,"New York",43.00035, -75.4999),
            13 => array(12,"New York City", 40.71427, -74.00597),
            14 => array(11,"New Jersey", 40.16706, -74.49987),
            14 => array(11,"DC", 38.91706, -77.00025),
            15 => array(14,"Washington DC", 38.89511, -77.03637),
            16 => array(11,"Washington", 47.50012, -120.50147),
            17 => array(16,"Seattle", 47.60621, -122.33207),
            18 => array(1, "Australia", -25, 135)
        );
    }

    public static function getPeople() {
        return array(
            /* 1=>"Unknown person" */
            2 => "Brian May",
            3 => "Jimi Hendrix",
            4 => "Paul McCartney",
            5 => "Freddie Mercury",
            6 => "Phil Collins",
            7 => "Roger Taylor",
            8 => "John Lennon",
            9 => "John Deacon",
            10 => "Mike Rutherford"
        );
    }

    public static function getUsers() {
        return array(
            /* 1=> "admin", */
            2 => "brian",
            3 => "jimi",
            4 => "paul",
            5 => "freddie",
            6 => "phil",
            7 => "roger",
            8 => "johnl",
            9 => "johnd"
        );
    }

    public static function getAdminUsers() {
        return array(2);
    }

    public static function getLightboxAlbums() {
        return array(
            /* user => lb */
            7 => 7,
            8 => 8,
            9 => 9
        );
    }

    public static function getGroups() {
        return array(
            1 => array("Queen", array("brian", "freddie", "roger", "johnd")),
            2 => array("Beatles", array("johnl", "paul")),
            3 => array("Genesis", array("phil")),
            4 => array("guitarists", array("brian", "jimi"))
        );
    }

    public static function getPhotoAlbums() {
        // photo => albums
        return array(
            1 => array(2,3),
            2 => array(3,4),
            3 => array(5,6),
            4 => array(5,6),
            5 => array(5,6),
            6 => array(7,8),
            7 => array(2,11),
            8 => array(3,11),
            9 => array(4,11),
            10 => array(4,12),
            11 => array(),
            12 => array()
        );
    }

    public static function getGroupPermissions() {
        // group => albums
        return array(
            1 => array(1,2),
            2 => array(1,3), // !!! album 2 is added because it's the parent of 3 !!!
            4 => array(2)    // !!! album 1 is added because it's the parent of 1 !!!
        );
    }

    public static function getPhotoCategories() {
        return array(
            1 => array(2,3),
            2 => array(2,4),
            3 => array(2,5),
            4 => array(2,6),
            5 => array(7,8),
            6 => array(9,10),
            7 => array(10,11),
            8 => array(10,11),
            9 => array(10,11),
            10 => array(3,4,5,7,12),
            11 => array(),
            12 => array()
        );
    }

    public static function getPhotoPeople() {
        return array(
            1 => array(2,5,7,9),
            2 => array(2,5,7,9),
            3 => array(4,8),
            4 => array(8,4),
            5 => array(3),
            6 => array(2,3),
            7 => array(2,3,5),
            8 => array(),
            9 => array(),
            10 => array(),
            11 => array(),
            12 => array()
        );
    }

    public static function getPhotoLocation() {
        return array(
            1 => 3,
            2 => 4,
            3 => 4,
            4 => 5,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 10,
            9 => 13,
            10 => 14,
            11 => 18,
            12 => 18
        );
    }

    public static function getPhotographer() {
        return array(
            1 => 2,
            2 => 2,
            3 => 2,
            4 => 3,
            5 => 3,
            6 => 3,
            7 => 3,
            8 => 4,
            9 => 5,
            10 => 6,
            11 => 7,
            12 => 7
        );
    }

    public static function getRatings() {
        return array(
            1 => array(2=>8, 5=>7, 7=>8, 9=>7),
            2 => array(2=>5, 5=>3, 7=>6, 9=>3),
            3 => array(3=>5),
            4 => array(4=>7, 8=>6),
            5 => array(6=>10, 3=>9),
            6 => array(3=>7, 2=>5),
            8 => array(4=>9, 6=>3),
            10 => array(6=>10, 4=>1),
            11 => array(),
            12 => array()
        );
    }

    public static function getRelations() {
        return array(
            1 => array(
                    2 => array("first photo", "second photo"),
                    3 => array("first photo", "third photo"),
                    4 => array("first photo", "fourth photo")),
            2 => array(
                    3 => array("second photo", "third photo")),
            5 => array(
                    4 => array("fifth photo", "fourth photo")),
            6 => array(
                    5 => array("sixth photo", "fifth photo"))
        );
    }

    public static function getComments() {
        return array(
            1 => array(
                2 => "I love that Special Red :-)",
                5 => "Beautiful!",
                7 => "Nice"
                ),
            2 => array(
                2 => "The letters are all distorted!",
                5 => "Crap :-("
                ),
            5 => array(
                3 => "Self portrait!",
                ),
            6 => array(
                3 => "Me and [b]Brian[/b]!",
                2 => ":-)"
                ),
            8 => array(
                4 => "Just beautiful",
                6 => "That <span style=\"background: grey\">grey</span> is annoying me so badly, " .
                    "I'm illegally using HTML in this comment!",
                1 => "Can you guys stop this, or I am [b]revoking[/b] your accounts!"
                ),
            10 => array(
                6 => "Love this picture!",
                4 => "Can't [i]you[/i] [b]see[/b] it isn't even [u]sharp[/u] on the right side, " .
                    "Phil? :mrgreen:",
                1 => "Really, I am going to [b]revoke[/b] your accounts! :mad:"
                )
        );
    }
}



?>
