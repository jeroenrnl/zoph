<?php
/**
 * A request represents a http request
 *
 * Currently, superglobals such as $_GET, $_POST and $_SERVER are accessed
 * either through getvar() or directly, this is bad practice and hard to make
 * testable. Eventually, this class must replace all of this (and more).
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

namespace web;

use ArrayAccess;
use generic\variable;

/**
 * The request class is used to access request-related variables
 * such as $_GET, $_POST and $_SERVER.
 *
 * In the future $_FILES and $_COOKIE will be added.
 *
 * A variable can be accessed through ArrayAccess ($request["variable"] or object
 * access ($request->variable);
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class request implements ArrayAccess {
    /** @var holds $_GET variables */
    private $get;

    /** @var holds $_POST variables */
    private $post;

    /** @var holds $_SERVER variables */
    private $server;

    /** @var request vars, holds $_GET for GET requests and $_POST for POST requests
             actually, a POST request can have GET variables as well, but this has
             always been how Zoph works, so for now I am not changing this, note
             that this is *different* from the $_REQUEST superglobals - hence it's
             not called $request */
    private $requestVars;

    /**
     * Create object
     * @param array array of variables, can contain GET, POST and SERVER
     */
    public function __construct(array $vars) {
        foreach ([ "GET", "POST", "SERVER" ] as $var) {
            if (isset($vars[$var])) {
                $value=new variable($vars[$var]);
                $prop=strtolower($var);
                $this->$prop=$value->input();
            }
        }
        $this->buildRequest();
    }

    /**
     * Create object and fill with superglobals
     * @return request new request
     */
    public static function create() {
        return new self(array(
            "GET"   =>  $_GET,
            "POST"  =>  $_POST,
            "SERVER"    => $_SERVER
        ));
    }

    /**
     * Fill the REQUESTVARS property with either the GET variables
     * OR the POST variables.
     * Note that this behaviour is different from PHP's $_REQUEST superglobal
     */
    private function buildRequest() {
        if (!empty($this->get)) {
            $this->requestVars=&$this->get;
        } else {
            $this->requestVars=&$this->post;
        }
    }

    /**
     * For ArrayAccess: does the offset exist
     * @param int|string offset
     * @return bool offset exists
     */
    public function offsetExists($off) {
        return (isset($this->get[$off]) || isset($this->post[$off]));
    }

    /**
     * For ArrayAccess: Get value of parameter
     * if $_GET parameter is available, return it, if it is not but $_POST is available
     * return that, otherwise null
     * @param int|string offset
     * @return mixed value
     */
    public function offsetGet($off) {
        if (isset($this->get[$off])) {
            return $this->get[$off];
        } else if (isset($this->post[$off])) {
            return $this->post[$off];
        } else {
            return null;
        }
    }

    /**
     * For ArrayAccess: Set value of parameter
     * not supported
     * @param int|string offset
     * @param mixed value
     */
    public function offsetSet($off, $val) {
    }

    /**
     * For ArrayAccess: Unset value of parameter
     * not supported
     * @param int|string offset
     */
    public function offsetUnset($off) {
    }

    /**
     * For ObjectAccess: Get value of parameter
     * if $_GET parameter is available, return it, if it is not but $_POST is available
     * return that, otherwise null
     * @param int|string offset
     * @return mixed value
     */
    public function __get($off) {
        return $this->offsetGet($off);
    }

    /**
     * Get RequestVars
     * @return array requestvars
     */
    public function getRequestVars() {
        return (array) $this->requestVars;
    }

    /**
     * Get $_SERVER variables
     * @param Variable to return
     * @return mixed value
     */
    public function getServerVar($var) {
        if (isset($this->server[$var])) {
            return $this->server[$var];
        } else {
            return null;
        }
    }

    /**
     * Remove any params without values and operator params without corresponding
     * fields (e.g. _album_id-op when there is no _album_id).  This can be called
     * once after a search is performed.  It allows for shorter urls that are
     * more readable and easier to debug.
     * @todo This code is pretty horrible and I wonder if we could do without...
     */
    public function getRequestVarsClean() {
        $cleanVars = array();
        $interimVars = array();

        /*
          First pass through vars will flatten out any arrays in the list.
          arrays were used in search.php to make the form extensible. -RB
        */
        foreach ((array)$this->requestVars as $key => $val) {
            // trim empty values
            if (($key == "_button") || empty($val)) {
                continue;
            }

            if (is_array($val)) {
                foreach ($val as $subkey => $subval) {
                    if (empty($subval)) {
                        continue;
                    }

                    if (substr($key, -3) == "_op") {
                        //  change var_op[key] to var#key_op
                        $newkey = substr($key, 0, -3) . '#' . $subkey . '_op';
                    } else if (substr($key, -5) == "_conj") {
                        //  change var_conj[key] to var#key_conj
                        $newkey = substr($key, 0, -5) . '#' . $subkey . '_conj';
                    } else if (substr($key, -9) == "_children") {
                        //  change var_children[key] to var#key_children
                        $newkey = substr($key, 0, -9) . '#' . $subkey . '_children';
                    } else {
                        //  change var[key] to var#key
                        $newkey = $key . '#' . $subkey;
                    }

                    $interimVars[$newkey] = $subval;
                }
            } else {
                $interimVars[$key] = $val;
            }
        }

        /*
          Second pass through will get rid of ops and conjs without fields
          and fix the keys for compatability with the rest of zoph.  It will also remove
          "field" entries without a corresponding "_field" type and vice versa.
          A hyphen is not valid as part of a variable name in php so underscore was used
          while processing the form in search.php
        */
        foreach ($interimVars as $key => $val) {
            // process _var variables
            if (substr($key, 0, 1) == "_") {

                //process _op variables
                if (substr($key, -3) == "_op") {
                    // replace _op with -op to be compatible with the rest of application
                    $key = substr_replace($key, '-', -3, -2);
                    // get rid of ops without fields
                    $field = substr($key, 1, -3);
                    if (empty($interimVars[$field]) && empty($interimVars["_$field"])) {
                        continue;
                    }

                    //process _conj variables
                } else if (substr($key, -5) == "_conj") {
                    // replace _conj with -conj to be compatible
                    // with the rest of application
                    $key = substr_replace($key, '-', -5, -4);
                    // get rid of ops without fields
                    $field = substr($key, 1, -5);
                    if (empty($interimVars[$field]) && empty($interimVars["_$field"])) {
                        continue;
                    }
                } else if (substr($key, -9) == "_children") {
                    // process _children variables
                    // replace _children with -children to be compatable
                    // with the rest of application
                    $key = substr_replace($key, '-', -9, -8);
                    // get rid of ops without fields
                    $field = substr($key, 1, -9);
                    if (empty($interimVars[$field]) && empty($interimVars["_$field"])) {
                        continue;
                    }
                } else {
                    $field = substr($key, 1);
                }

                //process "_field" type variables
                if (substr($field, 0, 5) == "field" && empty($interimVars[$field]) && empty($interimVars["_$field"])) {
                    continue;
                }
            } else {
                //process "field" type variables
                if (substr($key, 0, 5) == "field" && empty($interimVars["_$key"])) {
                    continue;
                }
            }

            $cleanVars[$key] = $val;
        }

        return $cleanVars;
    }
}
