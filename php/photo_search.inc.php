<?php

function get_photos($vars, $offset, $rows, &$thumbnails, $user = null) {

    $select = "distinct ph.photo_id, ph.name, ph.path, ph.width, ph.height";

    if (MAX_THUMB_DESC && $user && $user->prefs->get("desc_thumbnails")) {  
        $select .= ", ph.description";
    }

    $from["ph"] = "photos";

    if ($user && !$user->is_admin()) {
        $from["pa"] = "photo_albums";
        $from["ap"] = "album_permissions";
        $where =
             "(ph.photo_id = pa.photo_id" .
             " and pa.album_id = ap.album_id" .
             " and ap.user_id = '" . escape_string($user->get("user_id")) . "'" .
             " and ap.access_level >= ph.level)";
    }
    else {
        $where = "";
    }

    $dir = $vars["_dir"];
    if (!$dir) { $dir = "asc"; }

    if ($vars["_order"]) {
        $order = "ph." . $vars["_order"] . " $dir";
        if ($order == "ph.date") { $order .= ", ph.time $dir"; }
        $order .= ", ph.photo_id $dir";
    }
    else {
        $order = "ph.date $dir, ph.time $dir, ph.photo_id $dir";
    }

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

        $conj = $vars["_" . $key . "-conj" . $suffix];
        if (!$conj) { $conj = "and"; }

        $op = $vars["_" . $key . "-op" . $suffix];
        if (!$op) { $op = "="; }

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
            if ($op == "=") {
                if ($where) { $where .= " $conj "; }

                $from["pa"] = "photo_albums";

                $op = "in";
                $where .=
                    "(pa.album_id $op (" . escape_string($val) . ")" .
                    " and pa.photo_id = ph.photo_id)";
            }
            else { // assume "not in"
                // a simple join won't work for the "not in" case
                $excluded_albums[$conj] = $val;
            }
        }
        else if ($key == "category_id") {
            if ($op == "=") {
                if ($where) { $where .= " $conj "; }

                $from["pc"] = "photo_categories";

                $op = "in";
                $where .=
                    "(pc.category_id $op (" . escape_string($val) . ")" .
                    " and pc.photo_id = ph.photo_id)";
            }
            else { // assume "not in"
                // a simple join won't work for the "not in" case
                $excluded_categories[$conj] = $val;
            }
        }
        else if ($key == "person_id") {
            if ($op == "=") {
                if ($where) { $where .= " $conj "; }

                $from["ppl"] = "photo_people";

                $op = "in";
                $where .=
                    "(ppl.person_id $op (" . escape_string($val) . ")" .
                    " and ppl.photo_id = ph.photo_id)";
            }
            else {
                // a simple join won't work for the "not in" case
                $excluded_people[$conj] = $val;
            }
        }
        else { // any other field

            if (strncasecmp($key, "field", 5) == 0) {
                $key = $vars["_$key"];
            }

            $key = "ph.$key";

            $val = escape_string($val);
            if ($op == "like" or $op == "not like") {
                $val = "'%" . strtolower($val) . "%'";
                $key = "lower($key)";
            }
            else if ($val != "null") {
                // a crude way to see if the string can be treated as a number
                $num = ($val + 1) - 1;
                if ((string)$num != $val) {
                    $val = "'" . $val . "'";
                }
            }

            if ($where) { $where .= " $conj "; }
            $where .= "$key $op $val";

        }

    }

    $from_clause = generate_from_clause($from);

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
        //echo $query;

        $thumbnails = get_records_from_query("photo", $query);

    }

    return $num_photos;

}

function generate_from_clause($from_array) {
    $fromClause = "";
    if ($from_array) {
        while (list($abbrev, $table) = each($from_array)) {
            if ($fromClause) { $fromClause .= ", "; }
            $fromClause .= "$table as $abbrev";
        }
    }
    return $fromClause;
}

/*
  The generate_excluded methods below simulate subselects since MySQL
  doesn not support them.  These are kind of ugly but the problem is
  that for "not in" or "!=" constraints on albums, categories or people,
  a simple joing will not work (as it does in the non-negated case).
  This is because when a photos is in multiple albums or cats, or there
  are multiple people in the photo, the join will match one of the
  other rows.  I hope there is a better way to do this.
*/

function generate_excluded_albums_clause($excluded_albums, $from, $where) {

    $album_from = $from;
    if ($album_from) { $album_from .= ", "; }
    $album_from .= "photo_albums as pa";

    $album_constraints = "";

    while (list($conj, $album_ids) = each($excluded_albums)) {
        $photo_id_query =
            "select distinct pa.photo_id from $album_from " .
            "where (ph.photo_id = pa.photo_id and pa.album_id in (" .
            escape_string($album_ids) . "))";

        if ($where) {
            $photo_id_query .= " and $where";
        }

        $ids = implode(',', get_records_from_query(null, $photo_id_query));

        if ($ids) {
            if ($album_constraints || $where) {
                $album_constraints .= " $conj ";
            }
            $album_constraints .= "(ph.photo_id not in ($ids))";
        }

    }

    return $album_constraints;
}

function generate_excluded_categories_clause($excluded_categories, $from, $where) {

    $cat_from = $from;
    if ($cat_from) { $cat_from .= ", "; }
    $cat_from .= "photo_categories as pc";

    $cat_constraints = "";

    while (list($conj, $cat_ids) = each($excluded_categories)) {
        $photo_id_query =
            "select distinct pc.photo_id from $cat_from " .
            "where (ph.photo_id = pc.photo_id and pc.category_id in (" .
            escape_string($cat_ids) . "))";

        if ($where) {
            $photo_id_query .= " and $where";
        }

        $ids = implode(',', get_records_from_query(null, $photo_id_query));

        if ($ids) {
            if ($cat_constraints || $where) {
                $cat_constraints .= " $conj ";
            }
            $cat_constraints .= "(ph.photo_id not in ($ids))";
        }

    }

    return $cat_constraints;
}

function generate_excluded_people_clause($excluded_people, $from, $where) {

    $person_from = $from;
    if ($person_from) { $person_from .= ", "; }
    $person_from .= "photo_people as pp";

    $person_constraints = "";

    while (list($conj, $person_ids) = each($excluded_people)) {
        $photo_id_query =
            "select distinct pp.photo_id from $person_from " .
            "where (ph.photo_id = pp.photo_id and pp.person_id in (" .
            escape_string($person_ids) . "))";

        if ($where) {
            $photo_id_query .= " and $where";
        }

        $ids = implode(',', get_records_from_query(null, $photo_id_query));

        if ($ids) {
            if ($person_constraints || $where) {
                $person_constraints .= " $conj ";
            }
            $person_constraints .= "(ph.photo_id not in ($ids))";
        }

    }

    return $person_constraints;
}

?>
