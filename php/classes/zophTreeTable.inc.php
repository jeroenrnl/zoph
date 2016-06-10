<?php
/**
 * zophTreeTable represents a hierarchical table.
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

/**
 * zophTreeTable represents a hierarchical table.  Since the album
 * and category tables are identical in structure, some of the methods
 * those classes share are abstracted and placed here.
 *
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
abstract class zophTreeTable extends zophTable {

    protected $children;
    protected $ancestors;

    abstract public function getChildren($order = null);

    /**
     * Insert a new record in the database
     */
    public function insert() {
        $this->set("createdby", (int) user::getCurrent()->getId());
        return parent::insert();
    }

    /**
     * Deletes a record along with all of its descendants.
     * @param array Names of tables from which entries also should be deleted.
     */
    public function delete() {

        // simulate overloading
        if (func_num_args()>=1) {
            $extra_tables = func_get_arg(0);
        } else {
            $extra_tables = null;
        }

        $this->getChildren();
        if ($this->children) {
            foreach ($this->children as $child) {
                $child->delete();
            }
        }

        parent::delete($extra_tables);
    }

    /**
     * Check whether this organizer is the root of the tree
     * At this moment the root always has id 1 but this may
     * change in the future, so to be safe we'll make a function for
     * this
     * @return bool
     */
    public function isRoot() {
        $root=static::getRoot();
        return ($this->getId() == $root->getId());
    }



    /*
     * Gets the ancestors of this record.
     */
    public function get_ancestors($anc = array()) {
        $key = static::$primaryKeys[0];
        $pid = $this->get("parent_" . $key);

        if (!$pid) {
            $this->lookup();
            $pid = $this->get("parent_" . $key);
        }

        // root of tree
        if ($pid == 0) {
            $this->ancestors = null;
            return $anc;
        }

        $parent = new static($pid);
        $parent->lookup();

        array_push($anc, $parent);
        return $parent->get_ancestors($anc);
    }

    /**
     * Get all ancestors of this a list of records, in order to get
     * all viewable records
     *
     * We now have a list of records this person can see, (that is, albums,
     * categories or places that contain photos this user can see). However,
     * sometimes it may be neededi to have access to a category, album or
     * place with no viewable photos, in order to reach a viewable
     * album, category or place. Therefore, we are going to backtrack up to
     * the root for each.
     */
    public static function getAllAncestors(array $records) {
        $ids=array();
        foreach ($records as $record) {
            $ids[$record->getId()]=$record->getId();

            $parents=$record->get_ancestors();

            foreach ($parents as $parent) {
                $ids[$parent->getId()]=$parent->getId();
            }
        }
        return $ids;
    }
    /*
     * Gets a list of the id of this record along with the ids of
     * all of its descendants.
     * @param array id_array add values to this array
     * @todo refactor the pass by reference out
     */
    public function getBranchIdArray(array &$id_array=null) {
        if (!is_array($id_array)) {
            $id_array=array();
        }
        $id_array[] = (int) $this->getId();
        $this->getChildren();

        if ($this->children) {
            foreach ($this->children as $c) {
                $c->getBranchIdArray($id_array);
            }
        }
        return $id_array;
    }

    /*
     * Gets a comma separated string of this record's id along with
     * all of its descendant's ids.  Useful to make "record_id in
     * (id_list)" clauses.
     */
    public function getBranchIds() {
        $id_array;
        $this->getBranchIdArray($id_array);
        return implode(",", $id_array);
    }

    private function getXMLtree(DOMDocument $xml, $search) {
        $rootname=static::XMLROOT;
        $nodename=static::XMLNODE;
        $idname=static::$primaryKeys[0];

        $newchild=$xml->createElement($nodename);

        $title=$this->getName();
        $titleshort=strtolower(substr($title, 0, strlen($search)));
        if ($titleshort == strtolower($search)) {
            $key=$this->get($idname);

            $newchildkey=$xml->createElement("key");
            $newchildkey->appendChild($xml->createTextNode($key));
            $newchildtitle=$xml->createElement("title");
            $newchildtitle->appendChild($xml->createTextNode($title));

            $newchild->appendChild($newchildkey);
            $newchild->appendChild($newchildtitle);
        }
        $order = user::getCurrent()->prefs->get("child_sortorder");
        $children=$this->getChildren($order);
        if ($children) {
            $childset=$xml->createElement($rootname);
            foreach ($children as $child) {
                $newnode=$child->getXMLtree($xml, $search);
                if (isset($newnode)) {
                    $childset->appendChild($newnode);
                }
            }
            $newchild->appendChild($childset);

        }
        return $newchild;
    }

    /**
     * Turn the array from @see getDetails() into XML
     * @param array Don't fetch details, but use the given array
     */
    public function getDetailsXML(array $details=null) {
        if (!isset($details)) {
            $details=$this->getDetails();
        }
        $children=$this->getChildren();
        if (is_array($children)) {
            $details["children"]=count($children);
        }
        return parent::getDetailsXML($details);
    }

    /**
     * Return the root of the tree
     * @return album|category|place
     */
    public static function getRoot() {
        return new static(1);
    }

    /**
     * Search for an object by hierarchical name
     * @example If you have an album "Vacation" with subalbums "2010"
     *          and "2012", both with a subalbum named "France"
     *          album::getByNameHierarchical("Vacation/2010/France");
     *          will match the "France" album in 2010, but not in 2012, even
     *          if they are both called "France"
     * @param string name to search for
     * @return zophTreeTable found object
     */
    public static function getByNameHierarchical($name) {
        if (strpos($name, "/") === false) {
            return static::getByName($name, true);
        }

        $found=0;

        $searchString=explode("/", $name);
        $depth=sizeof($searchString);
        foreach ($searchString as $namePart) {
            $objs = static::getByName($namePart, true);
            foreach ($objs as $obj) {
                $obj->lookup();
                if (!isset($parentObj)) {
                    $found++;
                    $parentObj=$obj;
                } else {
                    $nextObjId=$obj->getId();
                    $children=$parentObj->getChildren();
                    foreach ($children as $child) {
                        $child->lookup();
                        if ($child->getId()==$nextObjId) {
                            $parentObj=$obj;
                            $found++;
                            break;
                        }
                    }
                }
            }
        }

        // Only report success if we have traversed the full depth of the search.
        if ($depth == $found) {
            return $obj;
        } else {
            return false;
        }

    }


    public static function getXMLdata($search, DOMDocument $xml, DOMElement $rootnode) {
        $obj = static::getRoot();
        $obj->lookup();
        $tree=$obj->getXMLtree($xml, $search);
        $rootnode->appendChild($tree);
        $xml->appendChild($rootnode);
        return $xml;
    }

    public static function getSelectArray() {
        return static::getTreeSelectArray();
    }

    public static function getTreeSelectArray($rec = null, $select_array = null, $depth=0) {
        $user=user::getCurrent();
        $user->lookupPrefs();
        $order = $user->prefs->get("child_sortorder");

        if (!$rec) {
            $rec = static::getRoot();
            $rec->lookup();
            $select_array[""] = "";
        }

        $select_array[$rec->getId()] = str_repeat("&nbsp;", $depth * 3) . e($rec->getName());

        $children = $rec->getChildren($order);
        if ($children) {
            $depth++;
            foreach ($children as $child) {
                $select_array = static::getTreeSelectArray($child, $select_array, $depth);
            }
        }
        return $select_array;
    }
}


?>
