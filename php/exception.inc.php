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
 * @author Jeroen Roos
 * @package ZophException
 */
class ZophException extends Exception {}

/**
 * Exception class for configuration-related exceptions
 * @author Jeroen Roos
 * @package ZophException
 */
class ConfigurationException extends ZophException {}

/**
 * Exceptions for Organizers
 * @author Jeroen Roos
 * @package ZophException
 */
class OrganizerException extends ZophException {}

/**
 * Exceptions for person
 * @author Jeroen Roos
 * @package ZophException
 */
class PersonException extends OrganizerException {}

/**
 * Cannot find person
 * @author Jeroen Roos
 * @package ZophException
 */
class PersonNotFoundException extends PersonException {}

/**
 * Exceptions for places
 * @author Jeroen Roos
 * @package ZophException
 */
class PlaceException extends OrganizerException {}

/**
 * Cannot find place
 * @author Jeroen Roos
 * @package ZophException
 */
class PlaceNotFoundException extends PlaceException {}

/**
 * Exceptions for albums 
 * @author Jeroen Roos
 * @package ZophException
 */
class AlbumException extends OrganizerException {}

/**
 * Cannot find album
 * @author Jeroen Roos
 * @package ZophException
 */
class AlbumNotFoundException extends AlbumException {}

/**
 * Exceptions for categories 
 * @author Jeroen Roos
 * @package ZophException
 */
class CategoryException extends OrganizerException {}

/**
 * find category
 * @author Jeroen Roos
 * @package ZophException
 */
class CategoryNotFoundException extends CategoryException {}

/**
 * Exception class for Import-related exceptions
 * @author Jeroen Roos
 * @package ZophException
 */
class ImportException extends ZophException {}

/**
 * Exception class for Import-auto-rotate exceptions
 * @author Jeroen Roos
 * @package ZophException
 */
class ImportAutorotException extends ImportException {}

/**
 * Exception thrown when file is not in the import path
 * @author Jeroen Roos
 * @package ZophException
 */
class ImportFileNotInPathException extends ImportException {}

/**
 * Exception thrown when file is not found
 * @todo merge with FileNotFoundException ?
 * @author Jeroen Roos
 * @package ZophException
 */
class ImportFileNotFoundException extends ImportException {}

/**
 * Exception thrown when ID is not numeric 
 * @todo migrate to a more general exception
 * @author Jeroen Roos
 * @package ZophException
 */
class ImportIdIsNotNumericException extends ImportException {}

/**
 * Exception thrown when multiple files have been found 
 * @author Jeroen Roos
 * @package ZophException
 */
class ImportMultipleMatchesException extends ImportException {}

/**
 * Exception thrown when a file is tried to be imported that
 * for some reason can not be imported
 * @author Jeroen Roos
 * @package ZophException
 */
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

/**
 * Exception thrown when something is wrong with a photo 
 * @author Jeroen Roos
 * @package ZophException
 */
class PhotoException extends ZophException {}

/**
 * Exception thrown when thumbnail can not be created
 * @author Jeroen Roos
 * @package ZophException
 */
class PhotoThumbCreationFailedException extends PhotoException {}

/**
 * Exception thrown when a photo can not be found
 * @author Jeroen Roos
 * @package ZophException
 */
class PhotoNotFoundException extends PhotoException {}

/**
 * Exception thrown when a file is tried to be imported that
 * for some reason can not be imported
 * @author Jeroen Roos
 * @package ZophException
 */
class MailException extends ZophException {}

/**
 * Exception thrown when something goes wrong with 
 * relation between photos
 * @author Jeroen Roos
 * @package ZophException
 */
class RelationException extends ZophException {}

/**
 * Exceptions for CLI
 * @author Jeroen Roos
 * @package ZophException
 */
class CliException extends ZophException {}

/**
 * Exception for CLI: No arguments have been given
 * @author Jeroen Roos
 * @package ZophException
 */
class CliNoArgumentsException extends CliException {}

/**
 * Exception for CLI: No files have been given
 * @author Jeroen Roos
 * @package ZophException
 */
class CliNoFilesException extends CliException {}

/**
 * Exception for CLI: Cannot find image
 * @author Jeroen Roos
 * @package ZophException
 * @todo Can maybe merged with FileNotFoundException and/or PhotoNotFoundException ?
 */
class CliImageNotFoundException extends CliException {}


/**
 * Exception for CLI: command can only be used from current working direectory.
 * This is used in conjuction with the --dirpattern option
 * @author Jeroen Roos
 * @package ZophException
 */
class CliNotInCWDException extends CliException {}

/**
 * Exception for CLI: Illegal dirpattern
 * This is used in conjuction with the --dirpattern option
 * @author Jeroen Roos
 * @package ZophException
 */
class CliIllegalDirpatternException extends CliException {}

/**
 * Exception for CLI: No parent
 * This is used in conjuction with the --new option
 * @author Jeroen Roos
 * @package ZophException
 */
class CliNoParentException extends CliException {}

/**
 * Exception for CLI: zoph.ini can not be found
 * @author Jeroen Roos
 * @package ZophException
 */
class CliININotFoundException extends CliException {}

/**
 * Exception for CLI: instance can not be found
 * @author Jeroen Roos
 * @package ZophException
 */
class CliInstanceNotFoundException extends CliException {}

/**
 * Exception for CLI: CLI user is not admin
 * @author Jeroen Roos
 * @package ZophException
 */
class CliUserNotAdminException extends CliException {}

/**
 * Exception for CLI: CLI user not valid
 * @author Jeroen Roos
 * @package ZophException
 */
class CliUserNotValidException extends CliException {}

/**
 * Exception for CLI: API not compatible.
 * API version between /bin/zoph and web-dir differs
 * @author Jeroen Roos
 * @package ZophException
 */
class CliAPINotCompatibleException extends CliException {}

/**
 * Exception for CLI: Unknown Error
 * 
 * @author Jeroen Roos
 * @package ZophException
 */
class CliUnknownErrorException extends CliException {}
?>
