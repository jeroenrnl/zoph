<?php
/*
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
 */
function get_photos($vars, $offset, $rows, &$thumbnails, $user = null) {

    $good_ops = array ( "=", "!=", "less than", "more than", ">", ">=", "<", "<=", "like", "not like", "is in photo", "is not in photo" );

    $good_conj = array ( "and", "or" );

    $good_fields= array ( "location_id", "rating", "photographer_id", "date", "time", "timestamp", "name", "path", "title", "view", "description", "width", "height", "size", "aperture", "camera_make", "camera_model", "compression", "exposure", "flash_used", "focal_length", "iso_equiv", "metering_mode" );

    $select = "distinct ph.photo_id, ph.name, ph.path, ph.width, ph.height";

    if (MAX_THUMB_DESC && $user && $user->prefs->get("desc_thumbnails")) {
        $select .= ", ph.description";
    }

    $from_clause=DB_PREFIX . "photos as ph";

    if ($user && !$user->is_admin()) {
//        $from["pa"] = "photo_albums";
//        $from["ap"] = "album_permissions";
        $from_clause .= " JOIN " . DB_PREFIX . "photo_albums AS pa " .
            "ON ph.photo_id = pa.photo_id " .
            "JOIN " . DB_PREFIX . "album_permissions AS ap " .
            "ON pa.album_id = ap.album_id ";

        $where =
             " ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
             " AND (ap.access_level >= ph.level)";
    }
    else {
        $where = "";
    }

    global $DEFAULT_ORDER;
    $ord = $vars["_order"];
    if (!$ord) { $ord = $DEFAULT_ORDER; }

    global $DEFAULT_DIRECTION;
    $dir = $vars["_dir"];
    if (!$dir) { $dir = $DEFAULT_DIRECTION; }

    $order = "ph." . $ord . " $dir";
    if ($ord == "date") { $order .= ", ph.time $dir"; }
    $order .= ", ph.photo_id $dir";

    while (list($key, $val) = each($vars)) {

        if (empty($key) || empty($val))  { continue; }
        if ($key[0] == '_')              { continue; }
        if (strpos(" $key", "PHP") == 1) { continue; }

        // handle refinements of searches
        $suffix = "";
        $hashPos = strrpos($key, "#");
        if ($hashPos > 0) { // don't care about first position
            $suffix = substr($key, $hashPos);
            $key = substr($key, 0, $hashPos);
        }

        //echo "key = $key<br>";
        //echo "suffix = $suffix<br>";

        $conj = $vars["_" . $key . $suffix . "-conj"];
        if (!$conj) { $conj = "and"; }
        if (!in_array($conj, $good_conj)) 
            { die ("Illegal conjunction: " . $conj); }

        $op = $vars["_" . $key . $suffix . "-op"];
        if (!$op) { $op = "="; }
        if (!in_array($op, $good_ops)) 
            { die ("Illegal operator: " . $op); }

        if ($val == "null") {
            if ($op == "=") { $op = "is"; }
            else if ($op = "!=") { $op = "is not"; }
        }

        if ($key == "person" || $key == "photographer") {
            // val could be "last_name", "last_name,first_name" or ",first_name"

            list($last_name, $first_name) = explode(',', $val);

            $key .= "_id";
            $val = "";

            $people = get_person_by_name($first_name, $last_name);
            if ($people && count($people) > 0) {

                foreach ($people as $person) {
                    if ($val) { $val .= ","; }
                    $val .= $person->get("person_id");
                }
            }
            else {
                // the person did not exist, no photos should be found
                $val = "-1";
            }
        }

        //echo "<p>key = '$key'; op = '$op', value = '$val'</p>\n";

        if ($key == "album_id") {
            $pa = "pa" . substr($suffix, 1);
            if ($op == "=") {
                if ($where) { $where .= " $conj "; }
                // If the user is not an admin, the albums table
                // is already in the join
                if ($user->is_admin() || $pa != "pa") {
                    $from["$pa"] = "photo_albums";
                }

                // the regexp matches a list of numbers, separated by comma's.
                // "1" matches, "1," not, "1,2" matches "1,333" matches
                // "1, a" not, etc.
                if (!preg_match("/^([0-9]+)+(,([0-9]+))*$/", $val)) { die("$key must be numeric"); }

                $op = "in";
                $where .=
                    "(${pa}.album_id $op (" . escape_string($val) . "))";

            }
            else { // assume "not in"
                // a simple join won't work for the "not in" case
                $excluded_albums["$pa"] = $val;
                $excluded_albums["${pa}-conj"] = $conj;
            }
        }
        else if ($key == "category_id") {
            $pc = "pc" . substr($suffix, 1);
            if ($op == "=") {
                if ($where) { $where .= " $conj "; }

                $from["$pc"] = "photo_categories";
                
                if (!preg_match("/^([0-9]+)+(,([0-9]+))*$/", $val)) { die("$key must be numeric"); }

                $op = "in";
                $where .=
                    "(${pc}.category_id $op (" . escape_string($val) . ")" .
                    " and ${pc}.photo_id = ph.photo_id)";
            }
            else { // assume "not in"
                // a simple join won't work for the "not in" case
                $excluded_categories["$pc"] = $val;
                $excluded_categories["${pc}-conj"] = $conj;
            }
        }
        else if ($key == "location_id") {
                if ($where) { $where .= " $conj "; }
                if(preg_match("/[a-zA-Z]+/", $val)) { die("No letters allowed in $key"); }

                if ($op == "=") {
                    $op = "in";
                    $add = "";
                } else {
                    $op = "not in";
                    $add = " or ph.location_id is null";
                }
                $where .=
                    "(ph.location_id $op (" . escape_string($val) . ")$add)";
            }
        else if ($key == "person_id") {
            $ppl = "ppl" . substr($suffix, 1);
            if ($op == "=") {
                if ($where) { $where .= " $conj "; }

                $from["$ppl"] = "photo_people";

                $op = "in";
                if (!is_numeric($val)) { die("$key must be numeric"); }
                $where .=
                    "(${ppl}.person_id $op (" . escape_string($val) . ")" .
                    " and ${ppl}.photo_id = ph.photo_id)";
            }
            else {
                // a simple join won't work for the "not in" case
                $excluded_people["$ppl"] = $val;
                $excluded_people["${ppl}-conj"] = $conj;
            }
        }
        else { // any other field

            if (strncasecmp($key, "field", 5) == 0) {
                $key = $vars["_" . $key . $suffix];
            }
            if (!in_array($key, $good_fields))
                { die ("Illegal field: " . $key); }
			  
            $key = "ph.$key";

            $val = escape_string($val);
            if ($op == "like" or $op == "not like") {
                $val = "'%" . strtolower($val) . "%'";
                $key = "lower($key)";
            }
            else if ($val != "null") {
                if (!is_numeric($val)) {
                    $val = "'" . escape_string($val) . "'";
                }
            }

            if ($where) { $where .= " $conj "; }
            $where .= "(" . escape_string($key) . " " . $op . " " . $val;
            
            if ($op == "!=" ) {
                $where .= " or " . escape_string($key) . " is null)";
            } else {
                $where .= ")";
            }

        }

    }

    $from_clause .= generate_from_clause($from);

    if ($excluded_albums) {
        $where .= generate_excluded_albums_clause(
            $excluded_albums, $from_clause, $where);
    }

    if ($excluded_categories) {
        $where .= generate_excluded_categories_clause(
            $excluded_categories, $from_clause, $where);
    }

    if ($excluded_people) {
        $where .= generate_excluded_people_clause(
            $excluded_people, $from_clause, $where);
    }
    
    if ($where) { $where = "where $where"; }

    $num_photos = 0;

    // do this count separately since the select uses limit
    $query = "select count(distinct ph.photo_id) from $from_clause $where";
        //echo $query . "\n"; //DEBUG
    $num_photos = get_count_from_query($query);

    if ($num_photos > 0) {

        if ($vars["_random"] && $num_photos > 1) {
            // get one random result
            mt_srand((double) microtime() * 1000000);
            $offset = mt_rand(0, $num_photos - 1);
            $rows = 1;
            $num_photos = 1;

            // don't bother with order
            $query =
                "select $select from $from_clause $where limit $offset, $rows";
        }
        else {
            $query =
                "select $select from $from_clause $where order by $order " .
                "limit $offset, $rows";
        }
        //echo $query . "\n"; //DEBUG

        $thumbnails = get_records_from_query("photo", $query);

    }

    return $num_photos;

}

function generate_from_clause($from_array) {
    $fromClause = "";
    $joinClause = "";
    if ($from_array) {
        while (list($abbrev, $table) = each($from_array)) {
//            if ($fromClause) {
                $fromClause .= " JOIN ";
                $joinClause = " on ${abbrev}.photo_id = ph.photo_id";
//            }
                
            $fromClause .= DB_PREFIX . "$table as $abbrev" . $joinClause;
        }
    }
    return $fromClause;
}

/*
  The generate_excluded methods below simulate subselects since MySQL
  doesn not support them.  These are kind of ugly but the problem is
  that for "not in" or "!=" constraints on albums, categories or people,
  a simple joining will not work (as it does in the non-negated case).
  This is because when a photo is in multiple albums or cats, or there
  are multiple people in the photo, the join will match one of the
  other rows.  I hope there is a better way to do this.
*/

function generate_excluded_albums_clause($excluded_albums, $from, $where) {

    $album_from = $from;
    $album_constraints = "";

    while (list($pa, $album_ids) = each($excluded_albums)) {
        if (strpos($pa, "-conj")) {continue;}
        if ($album_from) { $album_from .= ", "; }
        $album_from .= DB_PREFIX . "photo_albums as $pa";
        $photo_id_query =
            "select distinct ${pa}.photo_id from $album_from " .
            "where (ph.photo_id = ${pa}.photo_id and ${pa}.album_id in (" .
            escape_string($album_ids) . "))";

        if ($where) {
            $photo_id_query .= " and $where";
        }

        //echo $photo_id_query . "\n"; //DEBUG
        $ids = implode(',', get_records_from_query(null, $photo_id_query));

        if ($ids) {
            if ($album_constraints || $where) {
                $album_constraints .= " " . $excluded_albums["${pa}-conj"] . " ";
            }
            $album_constraints .= "(ph.photo_id not in ($ids))";
        }

    }

    return $album_constraints;
}

function generate_excluded_categories_clause($excluded_categories, $from, $where) {

    $cat_from = $from;
    $cat_constraints = "";

    while (list($pc, $cat_ids) = each($excluded_categories)) {
        if (strpos($pc, "-conj")) {continue;}
        if ($cat_from) { $cat_from .= ", "; }
        $cat_from .= DB_PREFIX . "photo_categories as $pc";
        $photo_id_query =
            "select distinct ${pc}.photo_id from $cat_from " .
            "where (ph.photo_id = ${pc}.photo_id and ${pc}.category_id in (" .
            escape_string($cat_ids) . "))";

        if ($where) {
            $photo_id_query .= " and $where";
        }

        //echo $photo_id_query . "\n"; //DEBUG
        $ids = implode(',', get_records_from_query(null, $photo_id_query));

        if ($ids) {
            if ($cat_constraints || $where) {
                $cat_constraints .= " " . $excluded_categories["${pc}-conj"] . " ";
            }
            $cat_constraints .= "(ph.photo_id not in ($ids))";
        }

    }

    return $cat_constraints;
}

function generate_excluded_people_clause($excluded_people, $from, $where) {

    $person_from = $from;
    $person_constraints = "";

    while (list($pp, $person_ids) = each($excluded_people)) {
        if (strpos($pp, "-conj")) {continue;}
        if ($person_from) { $person_from .= ", "; }
        $person_from .= DB_PREFIX . "photo_people as $pp";
        $photo_id_query =
            "select distinct ${pp}.photo_id from $person_from " .
            "where (ph.photo_id = ${pp}.photo_id and ${pp}.person_id in (" .
            escape_string($person_ids) . "))";

        if ($where) {
            $photo_id_query .= " and $where";
        }

        //echo $photo_id_query . "\n"; //DEBUG
        $ids = implode(',', get_records_from_query(null, $photo_id_query));

        if ($ids) {
            if ($person_constraints || $where) {
                $person_constraints .= " " . $excluded_people["${pp}-conj"] . " ";
            }
            $person_constraints .= "(ph.photo_id not in ($ids))";
        }

    }

    return $person_constraints;
}

?>
