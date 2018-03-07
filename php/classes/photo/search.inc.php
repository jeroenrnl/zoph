<?php
/**
 * Photo Search
 *
 * This file converts a set of http request vars into an SQL query
 * in this way, a selection of photos can be made, based on constraints
 * This file is more or less the core of Zoph. Any modification should
 * be made with extreme care!
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
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */

namespace photo;

use album;
use category;
use PDO;
use person;
use photo;
use place;
use user;

use db\select;
use db\param;
use db\clause;
use db\selectHelper;

use conf\conf;

/**
 * Photo Search
 *
 * This class converts a set of http request vars into an SQL query
 * in this way, a selection of photos can be made, based on constraints
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
class search {
    /** @var Valid comparison operators */
    const OPS       = array("=", "!=", "less than", "more than", ">", ">=",
                        "<", "<=", "like", "not like", "is in photo", "is not in photo");
    /** @var Valid conjunction operators */
    const CONJ      = array("and", "or");
    /** @var Valid sort directions */
    const SORTDIR   = array("asc", "desc");
    /** @var Valid search fields */
    const FIELDS    = array("location_id", "rating", "photographer_id",
                        "date", "time", "timestamp", "name", "path", "title", "view", "description",
                        "width", "height", "size", "aperture", "camera_make", "camera_model",
                        "compression", "exposure", "flash_used", "focal_length", "iso_equiv", "metering_mode");
    /** @var Valid text search fields */
    const TEXT      = array("album", "category", "person", "photographer");

    /** @var Holds the query */
    private $qry;
    /** Holds the variables that are used to build the constraint */

    /** @var array holds the request vars */
    private $vars;

    /**
     * Create seach object based on http request vars
     * @param array vars http request vars
     */
    public function __construct(array $vars) {

        $this->qry = new select(array("p" => "photos"));
        $this->vars = $vars;
        $this->processVars();
        $this->setOrder();
    }

    /**
     * Get the resulting query
     * @return db\query SQL query that can be used to get photos from database
     */
    public function getQuery() {
        return $this->qry;
    }

    /**
     * Process the fields needed to determine the ORDER in the SQL query
     */
    private function setOrder() {
        if (isset($this->vars["_order"])) {
            $order = $this->vars["_order"];
        } else {
            $order = conf::get("interface.sort.order");
        }

        if (isset($this->vars["_dir"])) {
            $dir = $this->vars["_dir"];
        } else {
            $dir = conf::get("interface.sort.dir");
        }

        if (!in_array(strtolower($dir), static::SORTDIR)) {
            throw new \illegalValueSecurityException("Illegal sort direction: " . e($dir));
        }

        if (isset($this->vars["_random"])) {
            // get one random result
            $this->qry->addOrder("rand()");
            $this->qry->addLimit(1);
        } else {
            $this->qry->addFields(array($order));
            $this->qry->addOrder("p." . $order . " " . $dir);
            if ($order == "date") {
                $this->qry->addFields(array("p.time"));
                $this->qry->addOrder("p.time " . $dir);
            }
            $this->qry->addOrder("p.photo_id " . $dir);
        }
    }

    /**
     * Process variables
     * This function loops over all the variables and adds the various
     * contstraints (clauses) to the SQL query
     */
    private function processVars() {

        foreach ($this->vars as $key => $val) {
            if (empty($key) || empty($val) || $key[0] == "_" || strpos(" $key", "PHP") == 1) {
                continue;
            }

            // handle refinements of searches
            $suffix = "";
            $hashPos = strrpos($key, "#");
            if ($hashPos > 0) {
                $suffix = substr($key, $hashPos);
                $key = substr($key, 0, $hashPos);
            }

            $index = "_" . $key . $suffix;
            $origSuffix=$suffix;
            $suffix=str_replace("#", "_", $suffix);
            if (!empty($this->vars[$index . "-conj"])) {
                $conj = $this->vars[$index . "-conj"];
            } else {
                $conj = "and";
            }
            if (!in_array($conj, static::CONJ)) {
                throw new \illegalValueSecurityException("Illegal conjunction: " . e($conj));
            }

            if (!empty($this->vars[$index . "-op"])) {
                $op = $this->vars[$index . "-op"];
            } else {
                $op = "=";
            }

            if (!in_array($op, static::OPS)) {
                throw new \illegalValueSecurityException("Illegal operator: " . e($op));
            }

            if (!empty($this->vars[$index . "-children"])) {
                $object=explode("_", $key);
                if ($object[0]=="location") {
                    $object[0] = "place";
                }
                $obj=new $object[0]($val);
                $val=$obj->getBranchIdArray();
            }

            if ($key == "text") {
                $key = $this->vars["_" . $key . $origSuffix];

                if (!in_array($key, static::TEXT)) {
                    throw new \illegalValueSecurityException("Illegal text search: " . e($key));
                }

                $val = e($val);
                $key = e($key);
            }

            // the regexp matches a list of numbers, separated by comma's.
            if (!is_array($val) && preg_match("/^([0-9]+)(,([0-9]+))+$/", $val)) {
                $val=explode(",", $val);
            }


            if ($key == "person" || $key == "photographer") {
                $this->processPerson($key, $val, $suffix, $conj);
                // continue, because processPerson already modifies the query
                continue;
            } else if ($key == "album") {
                $key = "album_id";
                $val = $this->processAlbum($val);
            } else if ($key == "category") {
                $key = "category_id";
                $val = $this->processCategory($val);
            }

            if (($key == "album_id" || $key == "category_id") && $op == "like") {
                $op = "=";
            } else if (($key == "album_id" || $key == "category_id") && $op == "not like") {
                $op = "!=";
            }

            if ($key == "album_id") {
                $this->processAlbumId($val, $suffix, $op, $conj);
            } else if ($key == "category_id") {
                $this->processCategoryId($val, $suffix, $op, $conj);
            } else if ($key == "location_id") {
                $this->processLocationId($val, $suffix, $op, $conj);
            } else if ($key == "person_id") {
                $this->processPersonId($val, $suffix, $op, $conj);
            } else if ($key == "userrating") {
                $this->processUserRating($val, $suffix, $conj);
            } else if ($key=="rating") {
                $this->processRating($val, $suffix, $op, $conj);
            } else if ($key=="lat" || $key=="lon") {
                $latlon[$key]=$val;

                if (!empty($latlon["lat"]) && !empty($latlon["lon"])) {
                    $lat=(float) $latlon["lat"];
                    $lon=(float) $latlon["lon"];
                    $this->processLatLon($lat, $lon, $suffix, $conj);
                }
            } else {
                $this->processOtherFields($key, $val, $suffix, $origSuffix, $op, $conj);
            }

        }

        $this->qry = selectHelper::expandQueryForUser($this->qry, user::getCurrent());

        $distinct=true;
        $this->qry->addFields(array("p.photo_id"), $distinct);
        $this->qry->addFields(array("p.name", "p.path", "p.width", "p.height"));
    }

    /**
     * This can be used to reference persons by name directly from the URL
     * it's not actually used in Zoph and it's not well documented.
     * But it could be used to create a URL like http://www.zoph.org/search.php?person=Jeroen Roos
     * With the help of url rewrite, one could even change that into something like
     * http://www.zoph.org/person/Jeroen Roos
     * @param string key name of the field (person|photographer)
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processPerson($key, $val, $suffix, $conj) {
        $people = person::getByName($val, true);

        $peopleIds=array();

        if ($people && count($people) > 0) {
            foreach ($people as $person) {
                $peopleIds[]=$person->getId();
            }
        } else {
            // the person did not exist, no photos should be found
            // however, we can't just return 0 here, as there may be an OR clause in the query...
            $peopleIds[]=-1;
        }

        $param=new param(":peopleIds" . $suffix, $peopleIds, PDO::PARAM_INT);
        $this->qry->addParam($param);
        if ($key=="person") {
            $alias = "pp" . substr($suffix, 1);
            $this->qry->addClause(clause::InClause($alias . ".person_id", $param), $conj);
            $this->qry->join(array($alias => "photo_people"), "p.photo_id=" . $alias . ".photo_id");
        } else if ($key=="photographer") {
            $this->qry->addClause(clause::InClause("photographer_id", $param), $conj);
        }
    }

    /**
     * Search for album by name
     * @param string val value of the field
     * @return int|array album_id or array of album_ids
     */
    private function processAlbum($val) {
        $album=album::getByNameHierarchical($val);
        if ($album instanceof album) {
            $val=$album->getId();
        } else if (is_array($album)) {
            $val=array();
            foreach ($album as $alb) {
                $val[]=$alb->getId();
            }
        } else {
            // the album did not exist, no photos should be found
            // however, we can't just return 0 here, as there may be an OR clause in the query...
            $val=-1;
        }
        return $val;
    }

    /**
     * Search for category by name
     * @param string val value of the field
     * @return int|array category_id or array of category_ids
     */
    private function processCategory($val) {
        $category=category::getByNameHierarchical($val);
        if ($category instanceof category) {
            $val=$category->getId();
        } else if (is_array($category)) {
            $val=array();
            foreach ($category as $cat) {
                $val[]=$cat->getId();
            }
        } else {
            // the category did not exist, no photos should be found
            // however, we can't just return 0 here, as there may be an OR clause in the query...
            $val=-1;
        }
        return $val;
    }

    /**
     * Search for album by id
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string operator, how the values should be compared (=, !=, like, etc.)
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processAlbumId($val, $suffix, $op, $conj) {
        if ($op == "=") {
            $alias = "pa" . substr($suffix, 1);
            /*
             * Because the query builder expects the photo_album table to be aliased to "pa",
             * the first occurence does not have number suffix
             */
            if ($alias=="pa1") {
                $alias="pa";
            }
            $this->qry->join(array($alias => "photo_albums"), "p.photo_id=" . $alias . ".photo_id");
            if (is_numeric($val)) {
                $this->qry->addClause(new clause($alias . ".album_id=:albumId" . $suffix), $conj);
                $this->qry->addParam(new param(":albumId" . $suffix, (int) $val, PDO::PARAM_INT));
            } else if (is_array($val)) {
                $param=new param(":albumIds" . $suffix, $val, PDO::PARAM_INT);
                $this->qry->addParam($param);
                $this->qry->addClause(clause::InClause($alias . ".album_id", $param), $conj);
            } else {
                throw new \keyMustBeNumericSecurityException("album_id must be numeric");
            }
        } else {
            // assume "not in"
            $exclAlbumsQry=new select(array("p" => "photos"));
            $exclAlbumsQry->addFields(array("photo_id"), true);

            $exclAlbumsQry->join(array("pa" => "photo_albums"), "p.photo_id=pa.photo_id");

            $param=new param(":albumIds" . $suffix, (array) $val, PDO::PARAM_INT);
            $exclAlbumsQry->addParam($param);
            $exclAlbumsQry->where(clause::InClause("pa.album_id", $param));

            $exclPhotoIds=$exclAlbumsQry->toArray();

            $param=new param(":photoIds" . $suffix, (array) $exclPhotoIds, PDO::PARAM_INT);
            $this->qry->addParam($param);
            $this->qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
        }
    }

    /**
     * Search for category by id
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string operator, how the values should be compared (=, !=, like, etc.)
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processCategoryId($val, $suffix, $op, $conj) {
        if ($op == "=") {
            $alias = "pc" . substr($suffix, 1);
            $this->qry->join(array($alias => "photo_categories"), "p.photo_id=" . $alias . ".photo_id");
            if (is_numeric($val)) {
                $this->qry->addClause(new clause($alias . ".category_id=:categoryId" . $suffix), $conj);
                $this->qry->addParam(new param(":categoryId" . $suffix, (int) $val, PDO::PARAM_INT));
            } else if (is_array($val)) {
                $param=new param(":categoryIds" . $suffix, $val, PDO::PARAM_INT);
                $this->qry->addParam($param);
                $this->qry->addClause(clause::InClause($alias . ".category_id", $param), $conj);
            } else {
                throw new \keyMustBeNumericSecurityException("category_id must be numeric");
            }
        } else {
            /* assume "not in" */
            $exclCategoryQry=new select(array("p" => "photos"));
            $exclCategoryQry->addFields(array("photo_id"), true);

            $exclCategoryQry->join(array("pc" => "photo_categories"), "p.photo_id=pc.photo_id");

            $param=new param(":categoryIds" . $suffix, (array) $val, PDO::PARAM_INT);
            $exclCategoryQry->addParam($param);
            $exclCategoryQry->where(clause::InClause("pc.category_id", $param));

            $exclPhotoIds=$exclCategoryQry->toArray();
            $param=new param(":photoIds" . $suffix, (array) $exclPhotoIds, PDO::PARAM_INT);
            $this->qry->addParam($param);
            $this->qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
        }
    }

    /**
     * Search for location by id
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string operator, how the values should be compared (=, !=, like, etc.)
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processLocationId($val, $suffix, $op, $conj) {
        if (is_numeric($val)) {
            $this->qry->addParam(new param(":locationId" . $suffix, (int) $val, PDO::PARAM_INT));
            if ($op == "=") {
                $this->qry->addClause(new clause("p.location_id=:locationId" . $suffix), $conj);
            } else {
                $clause=new clause("p.location_id != :locationId" . $suffix);
                $clause->addOr(new clause("p.location_id is null"));
                $this->qry->addClause($clause, $conj);
            }
        } else if (is_array($val)) {
            $param=new param(":locationIds" . $suffix, $val, PDO::PARAM_INT);
            $this->qry->addParam($param);
            $this->qry->addClause(clause::InClause("p.location_id", $param), $conj);
        } else {
            throw new \keyMustBeNumericSecurityException("location_id must be numeric");
        }
    }

    /**
     * Search for person by id
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string operator, how the values should be compared (=, !=, like, etc.)
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processPersonId($val, $suffix, $op, $conj) {
        if ($op == "=") {
            $alias = "ppl" . substr($suffix, 1);
            $this->qry->join(array($alias => "photo_people"), "p.photo_id=" . $alias . ".photo_id");
            if (is_numeric($val)) {
                $this->qry->addClause(new clause($alias . ".person_id=:personId" . $suffix), $conj);
                $this->qry->addParam(new param(":personId" . $suffix, (int) $val, PDO::PARAM_INT));
            } else if (is_array($val)) {
                $param=new param(":personIds" . $suffix, $val, PDO::PARAM_INT);
                $this->qry->addParam($param);
                $this->qry->addClause(clause::InClause($alias . ".person_id", $param), $conj);
            } else {
                throw new \keyMustBeNumericSecurityException("person_id must be numeric");
            }

        } else {
            // assume "not in"
            $exclPeopleQry=new select(array("p" => "photos"));
            $exclPeopleQry->addFields(array("photo_id"), true);

            $exclPeopleQry->join(array("ppl" => "photo_people"), "p.photo_id=ppl.photo_id");

            $param=new param(":personIds" . $suffix, (array) $val, PDO::PARAM_INT);
            $exclPeopleQry->addParam($param);
            $exclPeopleQry->where(clause::InClause("ppl.person_id", $param));

            $exclPhotoIds=$exclPeopleQry->toArray();

            $param=new param(":photoIds" . $suffix, (array) $exclPhotoIds, PDO::PARAM_INT);
            $this->qry->addParam($param);
            $this->qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
        }
    }

    /**
     * Search for user rating
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processUserRating($val, $suffix, $conj) {
        $user=user::getCurrent();
        if ($user->isAdmin() && isset($this->vars["_userrating_user"])) {
            $ratingUserId=$this->vars["_userrating_user"];
        } else {
            $ratingUserId=$user->getId();
        }
        if ($val != "null") {
            $alias = "pr" . substr($suffix, 1);
            $this->qry->join(array($alias => "photo_ratings"), "p.photo_id=" . $alias . ".photo_id");

            $clause=new clause($alias . ".user_id=:ratingUserId" . $suffix);
            $clause->addAnd(new clause($alias . ".rating=:rating" . $suffix));

            $this->qry->addParam(new param(":ratingUserId", $ratingUserId, PDO::PARAM_INT));
            $this->qry->addParam(new param(":rating", $val, PDO::PARAM_INT));

            $this->qry->addClause($clause, $conj);
        } else {
            $noRateQry=new select(array("pr" => "photo_ratings"));
            $noRateQry->addFields(array("photo_id"), true);
            $noRateQry->where(new clause("pr.user_id=:ratingUserId"));
            $noRateQry->addParam(new param(":ratingUserId", $ratingUserId, PDO::PARAM_INT));

            $photoIds=$noRateQry->toArray();

            if (sizeOf($photoIds) > 0) {
                $param=new param(":photoIds" . $suffix, (array) $photoIds, PDO::PARAM_INT);
                $this->qry->addParam($param);
                $this->qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
            }
        }
    }

    /**
     * Search for rating
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string operator, how the values should be compared (=, !=, like, etc.)
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processRating($val, $suffix, $op, $conj) {
        $alias = "vpr" . substr($suffix, 1);
        $this->qry->join(array($alias => "view_photo_avg_rating"), "p.photo_id=" . $alias . ".photo_id");
        if ($val=="null") {
            if ($op == "!=") {
                $clause=new clause($alias . ".rating is not null");
            } else if ($op == "=") {
                $clause=new clause($alias . ".rating is null");
            }
        } else {
            $this->qry->addParam(new param(":rating" . $suffix, $val, PDO::PARAM_INT));
            $clause=new clause($alias . ".rating " . $op . " :rating" . $suffix);
        }
        $this->qry->addClause($clause, $conj);
    }

    /**
     * Search for Latitude / longitude
     * @param float latitude value
     * @param float longitude value
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processLatLon($lat, $lon, $suffix, $conj) {
        $ids=array();
        $distance=(float) $this->vars["_latlon_distance"];
        if (isset($this->vars["_latlon_entity"]) && $this->vars["_latlon_entity"]=="miles") {
            $distance=$distance * 1.609344;
        }
        if (isset($this->vars["_latlon_photos"])) {
            $photos=photo::getPhotosNear($lat, $lon, $distance, null);
            if ($photos) {
                foreach ($photos as $photo) {
                    $ids[]=$photo->getId();
                }
            }
        }
        if (isset($this->vars["_latlon_places"])) {
            $places=place::getPlacesNear($lat, $lon, $distance, null);
            foreach ($places as $place) {
                $photos=$place->getPhotos(user::getCurrent());
                foreach ($photos as $photo) {
                    $ids[]=$photo->getId();
                }
            }
        }
        if ($ids) {
            $param=new param(":photoIds" . $suffix, $ids, PDO::PARAM_INT);
            $this->qry->addParam($param);
            $this->qry->addClause(clause::InClause("p.photo_id", $param), $conj);
        } else {
            // No photos were found
            $this->qry->addClause(new clause("p.photo_id=-1"), $conj);
        }
    }

    /**
     * Search for other fields
     * @param string key name of the field
     * @param string val value of the field
     * @param string suffix, the suffix can be used to search for the same field multiple times
     * @param string original suffix, the unprocessed suffix
     * @param string operator, how the values should be compared (=, !=, like, etc.)
     * @param string conj, conjugation, whether this is an AND or OR search
     */
    private function processOtherFields($key, $val, $suffix, $origSuffix, $op, $conj) {
        // any other field
        $clause=null;
        /* if the key name starts with is "field", we replace te keyname with the contents
           of _field#0, which holds the real field name */
        if (strncasecmp($key, "field", 5) == 0) {
            $key = $this->vars["_" . $key . $origSuffix];
        }
        if (!in_array($key, static::FIELDS)) {
            throw new \illegalValueSecurityException("Illegal field: " . e($key));
        }

        $val = e($val);
        $key = e($key);
        if ($val=="null") {
            if ($op == "!=") {
                $clause=new clause("p." . $key . " is not null");
            } else if ($op == "=") {
                $clause=new clause("p." . $key . " is null");
            }
        } else {
            $clause=new clause("p." . $key . " " . $op . " :" . $key . $suffix);

            if ($op == "like" || $op == "not like") {
                $val="%" . $val . "%";
            } else if ($op == "!=") {
                $clause->addOr(new clause("p." . $key . " is null"));
            }

            $this->qry->addParam(new param(":" . $key . $suffix, $val, PDO::PARAM_STR));

        }
        if ($clause instanceof clause) {
            $this->qry->addClause($clause, $conj);
        }
    }
}
?>
