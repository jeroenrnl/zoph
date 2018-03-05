<?php
/**
 * Database helper class
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
 * @author Jeroen Roos
 */

namespace db;

use \user;
use \PDO;

/**
 * This object contains a few functions that could be in the select object,
 * but are Zoph-specific and I want to keep the database objects generic.
 * Eventually, this should be changed into either a composition or inheritance of the
 * select object, but since all functions are now static, it can be a separate function.
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class selectHelper {
    /**
     * Get the ORDER BY and LIMIT statements to pick an autocover
     * This is a temporary function until all old SQL has been phased out
     * @param select Query to add the ORDER BY and LIMIT statements to
     * @param string [oldest|newest|first|last|random|highest]
     * @return select Modified query
     */
    public static function getAutoCoverOrder(select $query, $autocover="highest") {
        switch ($autocover) {
        case "oldest":
            $query->addFields(array("p.date", "p.time"));
            $qry=$query->addOrder("p.date")->addOrder("p.time")->addLimit(1);
            break;
        case "newest":
            $query->addFields(array("p.date", "p.time"));
            $qry=$query->addOrder("p.date DESC")->addOrder("p.time DESC")->addLimit(1);
            break;
        case "first":
            $query->addFields(array("p.timestamp"));
            $qry=$query->addOrder("p.timestamp")->addLimit(1);
            break;
        case "last":
            $query->addFields(array("p.timestamp"));
            $qry=$query->addOrder("p.timestamp DESC")->addLimit(1);
            break;
        case "random":
            $qry=$query->addOrder("rand()")->addLimit(1);
            break;
        case "highest":
        default:
            $query->addFields(array("ar.rating"));
            $qry=$query->addOrder("ar.rating DESC")->addLimit(1);
            break;
        }
        return $qry;
    }

    /**
     * Expand the query so that it is restricted it to the photos the (current) user can see
     * @param select SELECT query to be expanded
     * @param user user to expand the query for - if null, use the currently logged in user
     */
    public static function expandQueryForUser(select $qry, user $user=null) {
        if (!$user) {
            $user=user::getCurrent();
        }

        // The user is an admin, simply return the query and where clause unaltered
        if ($user->canSeeAllPhotos()) {
            return $qry;
        }

        if (!$qry->hasTable("photos")) {
            $qry=static::addPhotoTableToQuery($qry);
        }


        $subqry=new select(array("pu" => "view_photo_user"));
        $subqry->where(new clause("pu.user_id = :userid"));
        $qry->addParam(new param(":userid", $user->getId(), PDO::PARAM_INT));
        $qry->join(array("spu" => $subqry), "p.photo_id = spu.photo_id");

        return $qry;
     }

    /**
     * This function adds a relation table to the query, in order to make it possible to
     * JOIN with the photo table
     * @param select query
     * @return select modified query
     */
    private static function addRelationTableToQuery(select $qry) {
        if ($qry->hasTable("albums") && !$qry->hasTable("photo_albums")) {
            $qry->join(array("pa" => "photo_albums"), "pa.album_id = a.album_id", "LEFT");
        } else if ($qry->hasTable("categories") && !$qry->hasTable("photo_categories")) {
            $qry->join(array("pc" => "photo_categories"), "pc.category_id = c.category_id", "LEFT");
        } else if ($qry->hasTable("people") && !$qry->hasTable("photo_people")) {
            $qry->join(array("pp" => "photo_people"), "pp.person_id = ppl.person_id", "LEFT");
        }

        return $qry;
    }

    /**
     * This function tries to figure out how to JOIN the current query with the photo table
     * @param select query
     * @return select modified query
     */
    private static function addPhotoTableToQuery(select $qry) {
        $qry=static::addRelationTableToQuery($qry);

        if ($qry->hasTable("photo_albums")) {
            $qry->join(array("p" => "photos"), "pa.photo_id = p.photo_id", "LEFT");
        } else if ($qry->hasTable("photo_categories")) {
            $qry->join(array("p" => "photos"), "pc.photo_id = p.photo_id", "LEFT");
        } else if ($qry->hasTable("photo_people")) {
            $qry->join(array("p" => "photos"), "pp.photo_id = p.photo_id", "LEFT");
        } else if ($qry->hasTable("places")) {
            $qry->join(array("p" => "photos"), "p.location_id = pl.place_id", "LEFT");
        } else {
            throw new DatabaseException("JOIN failed");
        }

        return $qry;
   }

    /**
     * Add modify query to ORDER BY a calculated field
     * @param select SQL query to modify
     * @param string [oldest|newest|first|last|lowest|highest|average|random]
     * @return query modified query
     */
    public static function addOrderToQuery(select $qry, $order) {
        if (!$qry->hasTable("photos") &&
                in_array($order, array("oldest", "newest", "first", "last",
                    "lowest", "highest", "average"))) {
            $qry=static::addPhotoTableToQuery($qry);
        }

        if (!$qry->hasTable("view_photo_avg_rating") &&
                in_array($order, array("lowest", "highest", "average"))) {
            $qry->join(array("ar" => "view_photo_avg_rating"), "ar.photo_id = p.photo_id");
        }

        switch ($order) {
        case "oldest":
            $qry->addFunction(array("oldest" => "min(p.date)"));
            break;
        case "newest":
            $qry->addFunction(array("newest" => "max(p.date)"));
            break;
        case "first":
            $qry->addFunction(array("first" => "min(p.timestamp)"));
            break;
        case "last":
            $qry->addFunction(array("last" => "max(p.timestamp)"));
            break;
        case "lowest":
            $qry->addFunction(array("lowest" => "min(rating)"));
            break;
        case "highest":
            $qry->addFunction(array("highest" => "max(rating)"));
            break;
        case "average":
            $qry->addFunction(array("average" => "avg(rating)"));
            break;
        case "random":
            $qry->addFunction(array("random" => "rand()"));
            break;
        }

        if (!empty($order)) {
            $qry->addOrder($order);
        }
        return $qry;
    }
}
