<?php
/**
 * Setup unit test environment
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
 * @package ZophUnitTest
 * @author Jeroen Roos
 */

define("TEST", true);

//require_once "php/settings.inc.php";
require_once "php/include.inc.php";
require_once "PHPUnit/Extensions/Database/TestCase.php";
require_once "PHPUnit/Extensions/Database/ITester.php";
require_once "PHPUnit/Extensions/Database/AbstractTester.php";
require_once "PHPUnit/Extensions/Database/DefaultTester.php";
require_once "PHPUnit/Extensions/Database/DB/IDatabaseConnection.php";
require_once "PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php";
require_once "PHPUnit/Extensions/Database/DB/IMetaData.php";
require_once "PHPUnit/Extensions/Database/DB/MetaData.php";
require_once "PHPUnit/Extensions/Database/DB/MetaData/MySQL.php";
require_once "PHPUnit/Extensions/Database/Operation/IDatabaseOperation.php";
require_once "PHPUnit/Extensions/Database/Operation/Factory.php";
require_once "PHPUnit/Extensions/Database/Operation/Composite.php";
require_once "PHPUnit/Extensions/Database/Operation/Truncate.php";
require_once "PHPUnit/Extensions/Database/Operation/RowBased.php";
require_once "PHPUnit/Extensions/Database/Operation/Insert.php";
require_once "PHPUnit/Extensions/Database/Operation/Null.php";
require_once "PHPUnit/Extensions/Database/Operation/Exception.php";
require_once "PHPUnit/Extensions/Database/DataSet/IDataSet.php";
require_once "PHPUnit/Extensions/Database/DataSet/AbstractDataSet.php";
require_once "PHPUnit/Extensions/Database/DB/DataSet.php";
require_once "PHPUnit/Extensions/Database/DataSet/AbstractXmlDataSet.php";
require_once "PHPUnit/Extensions/Database/DataSet/MysqlXmlDataSet.php";
require_once "PHPUnit/Extensions/Database/DataSet/ITableMetaData.php";
require_once "PHPUnit/Extensions/Database/DataSet/AbstractTableMetaData.php";
require_once "PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php";
require_once "PHPUnit/Extensions/Database/DataSet/ITable.php";
require_once "PHPUnit/Extensions/Database/DataSet/AbstractTable.php";
require_once "PHPUnit/Extensions/Database/DataSet/DefaultTable.php";
require_once "PHPUnit/Extensions/Database/DataSet/ITableIterator.php";
require_once "PHPUnit/Extensions/Database/DataSet/DefaultTableIterator.php";
require_once "databaseTest.inc.php";
require_once "testHelpers.inc.php";
user::setCurrent(new user(1));
conf::set("path.images", getcwd() . "/.images");
?>
