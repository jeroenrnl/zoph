<?php
/**
 * A confItem defines a configuration item
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
namespace conf\item;

use PDO;

use db\select;
use db\param;
use db\clause;

use zophTable;
use template\block;

/**
 * Configuration item
 * @package Zoph
 * @author Jeroen Roos
 */
abstract class item extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="conf";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("conf_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array();
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = true;
    /** @var string URL for this class */
    protected static $url="config.php#";


    /** @var string Label to display */
    protected $label;
    /** @var string Longer description of item */
    protected $desc;
    /** @var string Default value */
    protected $default;
    /** @var string Input hint (format to use) */
    protected $hint;
    /** @var bool required, whether or not field may be empty*/
    protected $required=false;
    /** @var bool internal, internal settings can not be changed from webinterface */
    protected $internal=false;
    /** @var array list of items that MUST be enabled for this item to be enabled */
    protected $requiresEnabled=array();
    /** @var array list of unmet requirements */
    protected $unmet=array();

    /**
     * Create conf\item object
     * @param string id, to fetch object from database.
     * @retrun conf\item new object
     */
    public function __construct($id = 0) {
        if ($id === 0 || preg_match("/^[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/", $id)) {
            $this->set("conf_id", $id);
        } else {
            log::msg("Illegal configuration id", log::FATAL, log::VARS);
        }

    }

    /**
     * Update or insert configuration item
     * checks if item already exists in db
     * and updates it if it does or inserts
     * if it does not.
     */
    final public function update() {
        if ($this->checkValue($this->get("value"))) {
            $qry=new select(array("co" => "conf"));
            $qry->addFunction(array("count" => "COUNT(conf_id)"));
            $qry->where(new clause("conf_id=:confid"));
            $qry->addParam(new param(":confid", $this->fields["conf_id"], PDO::PARAM_STR));

            if ($qry->getCount() > 0) {
                parent::update();
            } else {
                parent::insert();
            }
        }
    }

    /**
     * Get name of item
     * @return string name
     */
    final public function getName() {
        return $this->fields["conf_id"];
    }


    /**
     * Get label for item
     * @return string label
     */

    final public function getLabel() {
        return $this->label;
    }

    /**
     * Get description for item
     * @return string description
     */
    final public function getDesc() {
        return $this->desc;
    }

    /**
     * Get value of item
     * if value is not set, get default
     * @return string value
     */
    final public function getValue() {
        if (!isset($this->fields["value"]) || $this->fields["value"]===null || !$this->requirementsMet()) {
            return $this->getDefault();
        } else {
            return $this->fields["value"];
        }
    }

    /**
     * Set value of item
     * @param string value
     * @throws ConfigurationException
     */
    public function setValue($value) {
        if ($this->checkValue($value)) {
            $this->fields["value"]=$value;
        } else {
            throw new \ConfigurationException("Configuration value for " .
                $this->getName() . " is illegal");
        }
    }

    /**
     * Get default value of item
     * @return string default value
     */
    final public function getDefault() {
        return $this->default;
    }

    /**
     * Get hint for item
     * @return string hint
     */
    final public function getHint() {
        return $this->hint;
    }

    /**
     * Set name (id) of item
     * @param string name
     */
    final public function setName($name) {
        $this->fields["conf_id"]=$name;
    }

    /**
     * Set label for item
     * @param string label
     */
    final public function setLabel($label) {
        $this->label=$label;
    }

    /**
     * Set label for item
     * @param string label
     */
    final public function setDesc($desc) {
        $this->desc=$desc;
    }

    /**
     * Set hint for item
     * @param string hint
     */
    final public function setHint($hint) {
        $this->hint=$hint;
    }

    /**
     * Set whether or not a field is required
     * @param bool
     */
    final public function setRequired($req=true) {
        $this->required=(bool) $req;
    }

    /**
     * Set whether or not a field is internal
     * an internal field is not exposed in the webinterface
     * and (at this moment) not stored in the database, although this is not enforced
     * as there may be a future use-case where this will change.
     * @param bool
     */
    final public function setInternal($int=true) {
        $this->internal=(bool) $int;
    }

    /**
     * Set default value for item
     * @param string default
     */
    final public function setDefault($default) {
        $this->default=$default;
    }

    /**
     * This item requires another item to be enabled
     * @param checkbox configuration item checkbox that must be enabled to use this parameter
     */
    final public function requiresEnabled(checkbox $item) {
        $this->requiresEnabled[]=$item;
    }

    /**
     * Are all requirements met?
     */
    final protected function requirementsMet() {
        $met=true;
        foreach ($this->requiresEnabled as $req) {
            $req->lookup();
            if ((bool) $req->getValue() === false) {
                $met=false;
                $this->unmet[$req->getName()]="enabled";
            }
        }
        return $met;
    }

    /**
     * Return a template block to show the unmet requirements for this
     * confItem.
     * @return block overview of unmet items
     */
    final protected function displayUnmetRequirements() {
        if (!$this->requirementsMet()) {
            return new block("confUnmetRequirements", array(
                "unmet" => $this->unmet
            ));
        }
    }

    /**
     * Display the item
     */
    abstract public function display();

    /**
     * Check whether value is legal
     * @param string value
     */
    abstract public function checkValue($value);

}
