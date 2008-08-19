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

/*
 * zoph_tree_table represents a hierarchical table.  Since the album
 * and category tables are identical in structure, some of the methods
 * those classes share are abstracted and placed here.
 */
class zoph_tree_table extends zoph_table {

    var $children;
    var $ancestors;

    /*
     * Deletes a record along with all of its descendants.
     */
    function delete($extra_tables = null) {
        $this->get_children();
        if ($this->children) {
            foreach ($this->children as $child) {
                $child->delete();
            }
        }

        parent::delete(null, $extra_tables);
    }

    /*
     * Gets the children of this record.
     */
    function get_children($user = null, $order = null) {

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

        if (DEBUG) { echo "$sql<br>\n"; }

        $this->children = get_records_from_query(get_class($this), $sql);
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
    function get_branch_id_array(&$id_array, $user = null) {
        $key = $this->primary_keys[0];
        $id_array[] = $this->get($key);

        $this->get_all_children($user);
        if ($this->children) {
            foreach($this->children as $c) {
                $c->get_branch_id_array($id_array, $user);
            }
        }
        return $id_array;
    }

    /*
     * Gets a comma separated string of this record's id along with
     * all of its descendant's ids.  Useful to make "record_id in
     * (id_list)" clauses.
     */
    function get_branch_ids($user = null) {
        $id_array;
        $this->get_branch_id_array($id_array, $user);
        return implode(",", $id_array);
    }

    function get_html_tree($open=true) {
        $this->lookup();
        $children=$this->get_children();

        $html="\n<li>";
        if($children) {
          if ($open) {
                $html.="<span class='treenode' onclick=\"unbranch(this)\">-&nbsp;</span>";
           } else {
                $html.="<span class='treenode' onclick=\"branch(this)\">+&nbsp;</span>";
           }
        } else {
            $html.="<span>&nbsp;&nbsp;</span>";
        }
        $html.=$this->get_link();
        if($children) {
            $html.= "<ul>";
        }
        foreach($children as $child) {
            $html .= $child->get_html_tree(false);
        }
        if($children) {
            $html .= "</ul>";
        }
        $html.="</li>\n";
        return $html;
    }




    function get_xml_tree($xml, $search, $user=null) {
        $rootname=$this->xml_rootname();
        $nodename=$this->xml_nodename();
        $idname=$this->primary_keys[0];

        $newchild=$xml->createElement($nodename);

        $title=$this->get_name();
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
       $children=$this->get_children_sorted($user);
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
}

function get_root($class) {
    return new $class(1);
}

function create_tree_select_array($name, $user = null, $rec = null,
    $level = "", $select_array = null, $search = 0) {
    if (!$rec) {
        $rec = get_root($name);
        $rec->lookup();
        $select_array[""] = "";
    }
    if ($search) {
        $key = $rec->get_branch_ids($user);
    }
    else {
        $key = $rec->get($name . "_id");
    }
    /* The main descriptor field for location is not "place", but "title" */
    $descname=$name;
    if($descname=="place"){ $descname="title"; }

    $select_array[$key] = $level . $rec->get($descname);
    $children = $rec->get_children($user, $descname);
    if ($children) {
        foreach ($children as $child) {
            $select_array = create_tree_select_array($name, $user, $child,
                "$level&nbsp;&nbsp;&nbsp;", $select_array, $search);
        }
    }
    return $select_array;
}

?>
