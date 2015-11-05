<?php
/**
 * Get photos based on criteria
 *
 * This file really is the core of Zoph. Make changes to it very carefully!
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
 * @todo This should be replaced by a proper OO based construction
 */

use db\select;
use db\param;
use db\clause;
use db\selectHelper;

function get_photos($vars, $offset, $rows, &$thumbnails, $user = null) {
    $good_ops = array("=", "!=", "less than", "more than", ">", ">=",
        "<", "<=", "like", "not like", "is in photo", "is not in photo");

    $good_conj = array("and", "or");

    $good_dir = array("asc", "desc");

    $good_fields= array("location_id", "rating", "photographer_id",
        "date", "time", "timestamp", "name", "path", "title", "view", "description",
        "width", "height", "size", "aperture", "camera_make", "camera_model",
        "compression", "exposure", "flash_used", "focal_length", "iso_equiv", "metering_mode");
    $good_text= array("album", "category", "person", "photographer");


    if (!is_numeric($offset)) {
        die("offset must be numeric");
    }
    if (!is_numeric($rows)) {
        die("rows must be numeric");
    }

    $qry = new select(array("p" => "photos"));

    /**
     * @todo this part could be moved into a separate function
     */
    if (isset($vars["_order"])) {
        $order = $vars["_order"];
    } else {
        $order = conf::get("interface.sort.order");
    }

    if (isset($vars["_dir"])) {
        $dir = $vars["_dir"];
    } else {
        $dir = conf::get("interface.sort.dir");
    }

    if (!in_array(strtolower($dir), $good_dir)) {
        die ("Illegal sort direction: " . e($dir));
    }

   foreach($vars as $key => $val) {
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
        if (!empty($vars[$index . "-conj"])) {
            $conj = $vars[$index . "-conj"];
        } else {
            $conj = "and";
        }
        if (!in_array($conj, $good_conj)) {
            die ("Illegal conjunction: " . e($conj));
        }

        if (!empty($vars[$index . "-op"])) {
            $op = $vars[$index . "-op"];
        } else {
            $op = "=";
        }

        if (!in_array($op, $good_ops)) {
            die ("Illegal operator: " . e($op));
        }

        if (!empty($vars[$index . "-children"])) {
            $object=explode("_", $key);
            if ($object[0]=="location") {
                $object[0] = "place";
            }
            $obj=new $object[0]($val);
            $val=$obj->getBranchIdArray();
        }

        if ($key == "text") {
            $key = $vars["_" . $key . $origSuffix];

            if (!in_array($key, $good_text)) {
                die ("Illegal text search: " . e($key));
            }

            $val = e($val);
            $key = e($key);
        }

        // the regexp matches a list of numbers, separated by comma's.
        if (!is_array($val) && preg_match("/^([0-9]+)(,([0-9]+))+$/", $val)) {
            $val=explode(",", $val);
        }


        /**
         * The code below can be used to reference persons by name directly from the URL
         * it's not actually used in Zoph and it's not well documented.
         * But it could be used to create a URL like http://www.zoph.org/search.php?person=Jeroen Roos
         * With the help of url rewrite, one could even change that into something like
         * http://www.zoph.org/person/Jeroen Roos
         */
        if ($key == "person" || $key == "photographer") {
            $personQry=new select(array("pp" => "photo_people"));
            $personQry->join(array("ppl" => "people"), "pp.person_id=ppl.person_id");
            $personQry->addFields(array("person_id"));
            $personQry->where(new clause("lower(concat(first_name, \" \", last_name)) like :name"));
            $personQry->addParam(new param(":name", "%" . $val . "%", PDO::PARAM_STR));

            $people = person::getRecordsFromQuery($personQry);

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
            $qry->addParam($param);
            if ($key=="person") {
                $alias = "pp" . substr($suffix, 1);
                $qry->addClause(clause::InClause($alias . ".person_id", $param), $conj);
                $qry->join(array($alias => "photo_people"), "p.photo_id=" . $alias . ".photo_id");
            } else if ($key=="photographer") {
                $qry->addClause(clause::InClause("photographer_id", $param), $conj);
            }
            continue;
        } else if ($key == "album") {
            $album=album::getByNameHierarchical($val);
            if ($album instanceof album) {
                $key="album_id";
                $val=$album->getId();
            } else if (is_array($album)) {
                $key="album_id";
                $val=array();
                foreach($album as $alb) {
                    $val[]=$alb->getId();
                }
            } else {
                // the album did not exist, no photos should be found
                // however, we can't just return 0 here, as there may be an OR clause in the query...
                $key="album_id";
                $val=-1;
            }
        } else if ($key == "category") {
            $category=category::getByNameHierarchical($val);
            if ($category instanceof category) {
                $key="category_id";
                $val=$category->getId();
            } else if (is_array($category)) {
                $key="category_id";
                $val=array();
                foreach($category as $cat) {
                    $val[]=$cat->getId();
                }
            } else {
                // the category did not exist, no photos should be found
                // however, we can't just return 0 here, as there may be an OR clause in the query...
                $key="category_id";
                $val=-1;
            }

        }

        if ($key == "album_id") {
            if ($op == "=") {
                $alias = "pa" . substr($suffix, 1);
                $qry->join(array($alias => "photo_albums"), "p.photo_id=" . $alias . ".photo_id");
                if (is_int($val)) {
                    $qry->addClause(new clause($alias . ".album_id=:albumId" . $suffix), $conj);
                    $qry->addParam(new param(":albumId" . $suffix, $val, PDO::PARAM_INT));
                } else if (is_array($val)) {
                    $param=new param(":albumIds" . $suffix, $val, PDO::PARAM_INT);
                    $qry->addParam($param);
                    $qry->addClause(clause::InClause($alias . ".album_id", $param), $conj);
                } else {
                    throw new KeyMustBeNumericSecurityException("album_id must be numeric");
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
                $qry->addParam($param);
                $qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
            }
        } else if ($key == "category_id") {
            if ($op == "=") {
                $alias = "pc" . substr($suffix, 1);
                $qry->join(array($alias => "photo_categories"), "p.photo_id=" . $alias . ".photo_id");
                if (is_int($val)) {
                    $qry->addClause(new clause($alias . ".category_id=:categoryId" . $suffix), $conj);
                    $qry->addParam(new param(":categoryId" . $suffix, $val, PDO::PARAM_INT));
                } else if (is_array($val)) {
                    $param=new param(":categoryIds" . $suffix, $val, PDO::PARAM_INT);
                    $qry->addParam($param);
                    $qry->addClause(clause::InClause($alias . ".category_id", $param), $conj);
                } else {
                    throw new KeyMustBeNumericSecurityException("category_id must be numeric");
                }
            } else { // assume "not in"
                $exclCategoryQry=new select(array("p" => "photos"));
                $exclCategoryQry->addFields(array("photo_id"), true);

                $exclCategoryQry->join(array("pc" => "photo_categories"), "p.photo_id=pc.photo_id");

                $param=new param(":categoryIds" . $suffix, (array) $val, PDO::PARAM_INT);
                $exclCategoryQry->addParam($param);
                $exclCategoryQry->where(clause::InClause("pc.category_id", $param));

                $exclPhotoIds=$exclCategoryQry->toArray();
                $param=new param(":photoIds" . $suffix, (array) $exclPhotoIds, PDO::PARAM_INT);
                $qry->addParam($param);
                $qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
            }
        } else if ($key == "location_id") {
            if (is_int($val)) {
                $qry->addParam(new param(":locationId" . $suffix, $val, PDO::PARAM_INT));
                if ($op == "=") {
                    $qry->addClause(new clause("p.location_id=:locationId" . $suffix), $conj);
                } else {
                    $clause=new clause("p.location_id != :locationId" . $suffix);
                    $clause->addOr(new clause("p.location_id is null"));
                    $qry->addClause($clause, $conj);
                }
            } else if (is_array($val)) {
                $param=new param(":locationIds" . $suffix, $val, PDO::PARAM_INT);
                $qry->addParam($param);
                $qry->addClause(clause::InClause("p.location_id", $param), $conj);
            } else {
                throw new KeyMustBeNumericSecurityException("location_id must be numeric");
            }
        } else if ($key == "person_id") {
            if ($op == "=") {
                $alias = "ppl" . substr($suffix, 1);
                $qry->join(array($alias => "photo_people"), "p.photo_id=" . $alias . ".photo_id");
                if (is_int($val)) {
                    $qry->addClause(new clause($alias . ".person_id=:personId" . $suffix), $conj);
                    $qry->addParam(new param(":personId" . $suffix, $val, PDO::PARAM_INT));
                } else if (is_array($val)) {
                    $param=new param(":personIds" . $suffix, $val, PDO::PARAM_INT);
                    $qry->addParam($param);
                    $qry->addClause(clause::InClause($alias . ".person_id", $param), $conj);
                } else {
                    throw new KeyMustBeNumericSecurityException("person_id must be numeric");
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
                $qry->addParam($param);
                $qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
            }
        } else if ($key == "userrating") {
            if ($user->is_admin() && isset($vars["_userrating_user"])) {
                $ratinguser_id=$vars["_userrating_user"];
            } else {
                $ratinguser_id=$user->getId();
            }
            if ($val != "null") {
                $alias = "pr" . substr($suffix, 1);
                $qry->join(array($alias => "photo_ratings"), "p.photo_id=" . $alias . ".photo_id");

                $clause=new clause($alias . ".user_id=:ratingUserId" . $suffix);
                $clause->addAnd(new clause($alias . ".rating=:rating" . $suffix));

                $qry->addParam(new param(":ratingUserId", $ratinguser_id, PDO::PARAM_INT));
                $qry->addParam(new param(":rating", $val, PDO::PARAM_INT));

                $qry->addClause($clause, $conj);
            } else {
                $noRateQry=new select(array("pr" => "photo_ratings"));
                $noRateQry->addFields(array("photo_id"), true);
                $noRateQry->where(new clause("pr.user_id=:ratingUserId"));
                $noRateQry->addParam(new param(":ratingUserId", $ratinguser_id, PDO::PARAM_INT));

                $photoIds=$noRateQry->toArray();

                if (sizeOf($photoIds) > 0) {
                    $param=new param(":photoIds" . $suffix, (array) $photoIds, PDO::PARAM_INT);
                    $qry->addParam($param);
                    $qry->addClause(clause::NotInClause("p.photo_id", $param), $conj);
                }
            }
        } else if ($key=="rating") {
            $alias = "vpr" . substr($suffix, 1);
            $qry->join(array($alias => "view_photo_avg_rating"), "p.photo_id=" . $alias . ".photo_id");
            if ($val=="null") {
                if ($op == "!=") {
                    $clause=new clause($alias . ".rating is not null");
                } else if ($op == "=") {
                    $clause=new clause($alias . ".rating is null");
                }
            } else {
                $qry->addParam(new param(":rating" . $suffix, $val, PDO::PARAM_INT));
                $clause=new clause($alias . ".rating " . $op . " :rating" . $suffix);
            }
            $qry->addClause($clause, $conj);
        } else if ( $key=="lat" || $key=="lon") {

            $latlon[$key]=$val;

            if ( !empty($latlon["lat"]) && !empty($latlon["lon"])) {
                $ids=array();
                $lat=(float) $latlon["lat"];
                $lon=(float) $latlon["lon"];
                $distance=(float) $vars["_latlon_distance"];
                if (isset($vars["_latlon_entity"]) && $vars["_latlon_entity"]=="miles") {
                    $distance=$distance * 1.609344;
                }
                if (isset($vars["_latlon_photos"])) {
                    $photos=photo::getPhotosNear($lat, $lon, $distance, null);
                    if ($photos) {
                        foreach($photos as $photo) {
                            $ids[]=$photo->getId();
                        }
                    }
                }
                if (isset($vars["_latlon_places"])) {
                    $places=place::getPlacesNear($lat, $lon, $distance, null);
                    foreach($places as $place) {
                        $photos=$place->getPhotos($user);
                        foreach($photos as $photo) {
                            $ids[]=$photo->getId();
                        }
                    }
                }
                if ($ids) {
                    $param=new param(":photoIds" . $suffix, $ids, PDO::PARAM_INT);
                    $qry->addParam($param);
                    $qry->addClause(clause::InClause("p.photo_id", $param), $conj);
                } else {
                    // No photos were found
                    $qry->addClause(new clause("p.photo_id=-1"), $conj);
                }
            }
        } else {
            // any other field
            $clause=null;
            /** @todo check why a strncasecmp is necessary here */
            if (strncasecmp($key, "field", 5) == 0) {
                $key = $vars["_" . $key . $origSuffix];
            }
            if (!in_array($key, $good_fields)) {
                die ("Illegal field: " . e($key));
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

                if ($op == "like" or $op == "not like") {
                    $val="%" . $val . "%";
                } else if ($op == "!=") {
                    $clause->addOr(new clause("p." . $key . " is null"));
                }

                $qry->addParam(new param(":" . $key . $suffix, $val, PDO::PARAM_STR));

            }
            if ($clause instanceof clause) {
                $qry->addClause($clause, $conj);
            }
        }

    }

    if (!$user->is_admin()) {
        $qry->join(array("pa" => "photo_albums"), "p.photo_id=pa.photo_id");
        list($qry, $where) = selectHelper::expandQueryForUser($qry, null, $user);
        $qry->addClause($where, "AND");
    }

    $num_photos = 0;

    // do this count separately since the select uses limit
    $countQry=clone $qry;
    $countQry->addFunction(array("count" => "COUNT(distinct p.photo_id)"));

    $num_photos = $countQry->getCount();

    if ($num_photos > 0) {
        if (isset($vars["_random"]) && $num_photos > 1) {
            // get one random result
            mt_srand((double) microtime() * 1000000);
            $offset = mt_rand(0, $num_photos - 1);
            $rows = 1;
            $num_photos = 1;
        } else {
            $qry->addOrder("p." . $order . " " . $dir);
            if ($order == "date") {
                $qry->addOrder("p.time " . $dir);
            }
            $qry->addOrder("p.photo_id " . $dir);
        }
        $qry->addLimit($rows, $offset);
        $distinct=true;
        $qry->addFields(array("p.photo_id"), $distinct);
        $qry->addFields(array("p.name", "p.path", "p.width", "p.height"));

        $thumbnails = photo::getRecordsFromQuery($qry);

    }

    return $num_photos;

}
?>
