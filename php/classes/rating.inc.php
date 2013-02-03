<?php
/**
 * Rating for a photo
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
 * @author Jeroen Roos
 * @package Zoph
 */

/**
 * Photo ratings
 * @author Jeroen Roos
 * @package Zoph
 */
class rating extends zophTable {

    /** @var string The name of the database table */
    protected static $table_name="photo_ratings";
    /** @var array List of primary keys */
    protected static $primary_keys=array("rating_id");
    /** @var array Fields that may not be empty */
    protected static $not_null=array("photo_id", "rating", "user_id");
    /** @var bool keep keys with insert. In most cases the keys are set by the db with auto_increment */
    protected static $keepKeys = false;
    /** @var string URL for this class */
    protected static $url="photo.php?rating_id=";


    /**
     * Create new rating object
     * @param int id
     * @return rating rating object
     */
    public function __construct($id = 0) {
         if($id && !is_numeric($id)) { die("rating_id must be numeric"); }
         $this->set("rating_id", $id);

     }

    /**
     * Retrieve ratings from the database
     * @param photo photo to get ratings for
     * @param user user to get ratings for
     * @return rating rating object
     */
    public static function getRatings(photo $photo = null, user $user = null) {
        $constraints=array();

        if($photo instanceof photo) {
            $constraints["photo_id"] = (int) $photo->getId();
        }

        if($user instanceof user) {
            $constraints["user_id"] = (int) $user->getId();
            if ($user->get("allow_multirating")) {
                // This user is allowed to rate the same photoe  multiple 
                // times, however we will allow only one from the same IP
                $constraints["ipaddress"] = escape_string($_SERVER["REMOTE_ADDR"]);
            }
        }

        return self::getRecords(null, $constraints);
     }

    /**
     * Get average rating for a photo
     * @param photo photo to get rating for
     * @return float average rating
     */
     public static function getAverage(photo $photo) {
        $sql = "SELECT AVG(rating) FROM " . DB_PREFIX . "photo_ratings ".
            " WHERE photo_id = '" . (int) $photo->getId() . "'";
        
        $result = query($sql, "Rating recalculation failed");

        $row = fetch_array($result);

        $avg = (round(100 * $row[0])) / 100.0;

        if($avg == 0) {
            $avg = null;
        }

        return $avg;

     }
    
    /**
     * Get the user who made this rating
     * @return user user
     */
     public function getUser() {
        $user=new user($this->get("user_id"));
        $user->lookup();
        return $user;
     }
    
    /**
     * Add a new rating to the database
     * @param int rating
     * @param photo Photo to rate
     */
    public static function setRating($rating, photo $photo) {
        $user=user::getCurrent();
        $user->lookup();

        $where="";
        if(!($user->is_admin() || $user->get("allow_rating"))) {
            return;
        }
        
        $current_ratings=self::getRatings($photo, $user);

        if(sizeof($current_ratings) > 0) {
            $cur_rating=array_pop($current_ratings);

            $cur_rating->set("rating", (int) $rating);
            $cur_rating->set("ipaddress", e($_SERVER["REMOTE_ADDR"]));
            $cur_rating->update();
        } else {
            $new_rating=new rating();
            $new_rating->set("photo_id", (int) $photo->getId());
            $new_rating->set("user_id", (int) $user->getId());
            $new_rating->set("rating", (int) $rating);
            $new_rating->set("ipaddress", e($_SERVER["REMOTE_ADDR"]));
            $new_rating->insert();
        }
    }
    /**
     * Get details about rating for a specific photo
     * @param photo photo to get details for
     * @return block template block to display details
     */
    public static function getDetails(photo $photo) {
        $rating=self::getAverage($photo);

        $ratings=self::getRatings($photo);

        $tpl=new block("rating_details",array(
            "rating" => $rating,
            "ratings" => $ratings,
            "photo_id" => $photo->getId()
        ));


        return $tpl;
    }

    /**
     * Get array that shows the distribution of ratings
     * @return array array of rating => count pairs;
     */
      public static function getPhotoCount() {
        $user=user::getCurrent();

        if ($user->is_admin()) {
            $sql = "SELECT rating, count(*) AS count FROM ( " .
                   "    SELECT p.photo_id, FLOOR(avg(pr.rating)+0.5) AS rating FROM " .
                        DB_PREFIX . "photos AS p LEFT JOIN " .
                        DB_PREFIX . "photo_ratings AS pr ON " .
                        "p.photo_id = pr.photo_id " .
                        "GROUP BY p.photo_id) AS avg_rating " .
                "GROUP BY rating ORDER BY rating;";
        } else {
            $sql = "SELECT rating, count(*) AS count FROM ( " .
                    "   SELECT p.photo_id, FLOOR(avg(pr.rating)+0.5) AS rating FROM " .
                        DB_PREFIX . "photo_ratings AS pr RIGHT JOIN " .
                        DB_PREFIX . "photos AS p " .
                    "   ON pr.photo_id = p.photo_id JOIN " .
                        DB_PREFIX . "photo_albums AS pa " .
                    "   ON p.photo_id = pa.photo_id JOIN " .
                        DB_PREFIX . "group_permissions AS gp " .
                    "   ON pa.album_id = gp.album_id JOIN " .
                        DB_PREFIX . "groups_users AS gu " .
                    "   ON gp.group_id = gu.group_id " .
                    "   WHERE gu.user_id = " . (int) $user->getId() .
                    "   AND gp.access_level >= p.level " .
                    "   GROUP BY p.photo_id) AS avg_rating " .
                    "GROUP BY rating ORDER BY rating;";
        }

        $result = query($sql, "Rating grouping failed");
        
        $ratings=array_fill(0, 11, 0);
        while($row = fetch_array($result)) {
            $rating=(int) $row["rating"];
            $ratings[$rating]=(int) $row["count"];
        }
        return $ratings;
    }



    /**
     * Get array that shows the distribution of ratings
     * as given by a specific user
     * @return array array of rating => count pairs;
     */
    public static function getPhotoCountForUser(user $user) {
        $sql = "SELECT ROUND(rating) AS rating, count(*) AS count FROM " .
            DB_PREFIX . "photo_ratings " .
            "WHERE user_id=" . (int) $user->getId() .  
            " GROUP BY ROUND(rating) ORDER BY ROUND(rating) ";

        $result = query($sql, "Rating grouping failed");
        $ratings=array_fill(1, 10, 0);
        while($row = fetch_array($result)) {
            $rating=(int) $row["rating"];
            $ratings[$rating]=(int) $row["count"];
        }
        return $ratings;
    }

    /**
     * Turn the array from `getPhotoCountForUser()` into
     * an array that can be fed to the bar_graph template
     * @param user user to create graph for
     * @return array graph array
     */
    public static function getGraphArrayForUser(user $user) {
        $ratings=self::getPhotoCountForUser($user);
        $max = max($ratings);
        if($max == 0) {
            // no ratings
            $max=100;
        }

        $link=array(
            "_action" => translate("search"),
            "_userrating_user" => (int) $user->getId()
        );


        foreach($ratings as $rating=>$count) {
            $graph[$rating]=array(
                "count" => (int) $count,
                "width" => round($count / $max * 100, 2),
                "value" => (int) $rating
            );

            if($count > 0) {
                $link["userrating"]=$rating;
                $graph[$rating]["link"]="search.php?" . http_build_query($link);
            }
        }
        return $graph;
    }
        

    /**
     * Turn array from `getPhotoCount()` into an array that
     * can be fed to the template
     * @return array graph array
     */
    public static function getGraphArray() {
        $ratings=self::getPhotoCount();
        $max = max($ratings);
        if($max == 0) {
            // no ratings
            $max=100;
        }

        $link=array(
            "_rating_op" => array(">=","<"),
            "_action" => translate("search")
        );


        foreach($ratings as $rating=>$count) {
            $graph[$rating]=array(
                "count" => (int) $count,
                "width" => round($count / $max * 100, 2),
                "value" => (int) $rating
            );

            if($count > 0) {
                if($rating == 0) {
                    $graph[0]["link"] = "photos.php?rating=null";
                    $graph[0]["value"] = translate("not rated");
                } else {
                    $link["rating"]=array($rating - 0.5, $rating + 0.5);
                    $graph[$rating]["link"]="search.php?" . http_build_query($link);
                }
            }
        }

        return $graph;

     }
}
