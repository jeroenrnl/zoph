<?php
/**
 * This file contains all exceptions for Zoph
 *
 * An exception name should start with the name of the class it is used in.
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
 * @author Jeroen Roos
 * @package ZophException
 */

/**
 * This class is a general exception class for Zoph
 */
class ZophException extends Exception {}

class ConfigurationException extends ZophException {}


class ImportException extends ZophException {}
class ImportAutorotException extends ImportException {}
class ImportFileNotInPathException extends ImportException {}
class ImportFileNotFoundException extends ImportException {}
class ImportIdIsNotNumericException extends ImportException {}
class ImportMultipleMatchesException extends ImportException {}
class ImportFileNotImportableException extends ImportException {}

class FileException extends ZophException {}
class FileDirNotWritableException extends FileException {}
class FileDirectoryNotSupportedException extends FileException {}
class FileDirCreationFailedException extends FileException {}
class FileNotFoundException extends FileException {}
class FileExistsException extends FileException {}
class FileNotReadableException extends FileException {}
class FileNotWritableException extends FileException {}
class FileMoveFailedException extends FileException {}
class FileCopyFailedException extends FileException {}
class FileRenameException extends FileException {}
class FileSymlinkProblemException extends FileException {}

class PhotoException extends ZophException {}
class PhotoThumbCreationFailedException extends PhotoException {}
class PhotoNotFoundException extends PhotoException {}

class MailException extends ZophException {}
?>
