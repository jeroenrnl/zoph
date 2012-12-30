<?php
/**
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

/**
 * zophTreeTable represents a hierarchical table.  Since the album
 * and category tables are identical in structure, some of the methods
 * those classes share are abstracted and placed here.
 */
abstract class zophTreeTable extends zophTable {

    var $children;
    var $ancestors;

    /**
     * Deletes a record along with all of its descendants.
     * @param array Names of tables from which entries also should be deleted.
     */
    public function delete() {

        // simulate overloading
        if(func_num_args()>=1) {
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

    /*
     * Gets the children of this record.
     */
    function getChildren($order = null) {

        if ($this->children) { return $this->children; }
        if (!$this->primary_keys) { return; }
        $key = $this->primary_keys[0];
        $id = $this->get($key);
        if (!$id) { return; }

        $sql =
            "select * from $this->table_name " .
            "where parent_$key = '" . escape_string($id) . "'";

        if ($order) {
            $sql .= " order by $order";
        }

        $this->children = zophTreeTable::getRecordsFromQuery(get_class($this), $sql);
        return $this->children;

    }

    /*
     * Gets the ancestors of this record.
     */
    function get_ancestors($anc = array()) {
        if (!$this->primary_keys) { return $anc; }
        $key = $this->primary_keys[0];
        $pid = $this->get("parent_$key");
        // root of tree
        if ($pid == 0) {
            $this->ancestors = $anc;
            return $this->ancestors;
        }

        $class = get_class($this);
        $parent = new $class;
        $parent->set($key, $pid);
        $parent->lookup();

        array_push($anc, $parent);

        $this->ancestors = $parent->get_ancestors($anc);
        return $this->ancestors;
    }

    /*
     * Gets a list of the id of this record along with the ids of
     * all of its descendants.
     */
    function getBranchIdArray(&$id_array) {
        $key = $this->primary_keys[0];
        $id_array[] = $this->get($key);

        $this->getChildren();
        if ($this->children) {
            foreach($this->children as $c) {
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
    function getBranchIds() {
        $id_array;
        $this->getBranchIdArray($id_array);
        return implode(",", $id_array);
    }

    function get_xml_tree($xml, $search, $user=null) {
        $rootname=$this->xml_rootname();
        $nodename=$this->xml_nodename();
        $idname=$this->primary_keys[0];

        $newchild=$xml->createElement($nodename);

        $title=$this->getName();
        $titleshort=strtolower(substr($title, 0, strlen($search)));
        if($titleshort == strtolower($search)) {
            $key=$this->get($idname);

            $newchildkey=$xml->createElement("key");
            $newchildkey->appendChild($xml->createTextNode($key));
            $newchildtitle=$xml->createElement("title");
            $newchildtitle->appendChild($xml->createTextNode($title));

            $newchild->appendChild($newchildkey);
            $newchild->appendChild($newchildtitle);
       }
       $order = $user->prefs->get("child_sortorder");
       $children=$this->getChildren($order);
        if($children) {
            $childset=$xml->createElement($rootname);
            foreach($children as $child) {
                $newnode=$child->get_xml_tree($xml, $search,$user);
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
        if(!isset($details)) {
            $details=$this->getDetails();
        }
        $children=$this->getChildren();
        if(is_array($children)) {
            $details["children"]=count($children);
        }
        return parent::getDetailsXML($details);
    }
}

function create_tree_select_array($name, $user = null, $rec = null,
    $level = "", $select_array = null, $search = 0) {
    if (!$rec) {
        $rec = $name::getRoot();
        $rec->lookup();
        $select_array[""] = "";
    }
    $key = $rec->get($name . "_id");
    $descname=$name;
    if($descname=="place"){ $descname="title"; }

    $select_array[$key] = $level . e($rec->get($descname));
    if($user) {
        $user->lookup_prefs();
        $order = $user->prefs->get("child_sortorder");
    } else {
        $order="name";
    }
    $children = $rec->getChildren($order);
    if ($children) {
        foreach ($children as $child) {
            $select_array = create_tree_select_array($name, $user, $child,
                "$level&nbsp;&nbsp;&nbsp;", $select_array, $search);
        }
    }
    return $select_array;
}

?>
