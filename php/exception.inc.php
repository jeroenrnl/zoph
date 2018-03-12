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
 * @package zophException
 */

/**
 * This class is a general exception class for Zoph
 * @author Jeroen Roos
 * @package zophException
 */
class zophException extends Exception {}

/**
 * Exception class for configuration-related exceptions
 * @author Jeroen Roos
 * @package zophException
 */
class configurationException extends zophException {}

/**
 * Exceptions for Organizers
 * @author Jeroen Roos
 * @package zophException
 */
class organizerException extends zophException {}

/**
 * Exceptions for person
 * @author Jeroen Roos
 * @package zophException
 */
class personException extends organizerException {}

/**
 * Cannot find person
 * @author Jeroen Roos
 * @package zophException
 */
class personNotFoundException extends personException {}

/**
 * Exceptions for places
 * @author Jeroen Roos
 * @package zophException
 */
class placeException extends organizerException {}

/**
 * Cannot find place
 * @author Jeroen Roos
 * @package zophException
 */
class placeNotFoundException extends placeException {}

/**
 * Exceptions for albums
 * @author Jeroen Roos
 * @package zophException
 */
class albumException extends organizerException {}

/**
 * Cannot find album
 * @author Jeroen Roos
 * @package zophException
 */
class albumNotFoundException extends albumException {}

/**
 * Exceptions for categories
 * @author Jeroen Roos
 * @package zophException
 */
class categoryException extends organizerException {}

/**
 * find category
 * @author Jeroen Roos
 * @package zophException
 */
class categoryNotFoundException extends categoryException {}

/**
 * Exceptions for data errors
 * @author Jeroen Roos
 * @package zophException
 */
class dataException extends zophException {}

/**
 * A value that may not be NULL is NULL
 * @author Jeroen Roos
 * @package zophException
 */
class notNullValueIsNullDataException extends dataException {}

/**
 * A circular reference is found or created
 * @author Jeroen Roos
 * @package zophException
 */
class circularReferenceException extends dataException {}

/**
 * Exceptions for pages and pagesets
 * @author Jeroen Roos
 * @package zophException
 */
class pageException extends zophException {}

/**
 * The pageset contains no pages
 * @author Jeroen Roos
 * @package zophException
 */
class pagePagesetHasNoPagesException extends pageException {}

/**
 * No pageset exception
 * @author Jeroen Roos
 * @package zophException
 */
class pageNoPagesetForObjectException extends pageException {}

/**
 * Exception class for Import-related exceptions
 * @author Jeroen Roos
 * @package zophException
 */
class importException extends zophException {}

/**
 * Exception class for Import-auto-rotate exceptions
 * @author Jeroen Roos
 * @package zophException
 */
class importAutorotException extends importException {}

/**
 * Exception thrown when file is not in the import path
 * @author Jeroen Roos
 * @package zophException
 */
class importFileNotInPathException extends importException {}

/**
 * Exception thrown when file is not found
 * @todo merge with fileNotFoundException ?
 * @author Jeroen Roos
 * @package zophException
 */
class importFileNotFoundException extends importException {}

/**
 * Exception thrown when ID is not numeric
 * @todo migrate to a more general exception
 * @author Jeroen Roos
 * @package zophException
 */
class importIdIsNotNumericException extends importException {}

/**
 * Exception thrown when multiple files have been found
 * @author Jeroen Roos
 * @package zophException
 */
class importMultipleMatchesException extends importException {}

/**
 * Exception thrown when a file is tried to be imported that
 * for some reason can not be imported
 * @author Jeroen Roos
 * @package zophException
 */
class importFileNotImportableException extends importException {}

class fileException extends zophException {}
class fileDirNotWritableException extends fileException {}
class fileDirectoryNotSupportedException extends fileException {}
class fileDirCreationFailedException extends fileException {}
class fileNotFoundException extends fileException {}
class fileExistsException extends fileException {}
class fileNotReadableException extends fileException {}
class fileNotWritableException extends fileException {}
class fileMoveFailedException extends fileException {}
class fileCopyFailedException extends fileException {}
class fileRenameException extends fileException {}
class fileSymlinkProblemException extends fileException {}

/**
 * Exception thrown when something is wrong with a photo
 * @author Jeroen Roos
 * @package zophException
 */
class photoException extends zophException {}

/**
 * Exception thrown when thumbnail can not be created
 * @author Jeroen Roos
 * @package zophException
 */
class photoThumbCreationFailedException extends photoException {}

/**
 * Exception thrown when a photo can not be found
 * @author Jeroen Roos
 * @package zophException
 */
class photoNotFoundException extends photoException {}

/**
 * Exception thrown when selection-related functions are called
 * while there is no selection.
 * @author Jeroen Roos
 * @package zophException
 */
class photoNoSelectionException extends photoException {}

/**
 * Exception thrown when a file is tried to be imported that
 * for some reason can not be imported
 * @author Jeroen Roos
 * @package zophException
 */
class mailException extends zophException {}

/**
 * Exception thrown when something goes wrong with
 * relation between photos
 * @author Jeroen Roos
 * @package zophException
 */
class relationException extends zophException {}

/**
 * Exceptions for CLI
 * @author Jeroen Roos
 * @package zophException
 */
class cliException extends zophException {}

/**
 * Exception for CLI: No arguments have been given
 * @author Jeroen Roos
 * @package zophException
 */
class cliNoArgumentsException extends cliException {}

/**
 * Exception for CLI: No files have been given
 * @author Jeroen Roos
 * @package zophException
 */
class cliNoFilesException extends cliException {}

/**
 * Exception for CLI: Cannot find image
 * @author Jeroen Roos
 * @package zophException
 * @todo Can maybe merged with fileNotFoundException and/or photoNotFoundException ?
 */
class cliImageNotFoundException extends cliException {}


/**
 * Exception for CLI: command can only be used from current working direectory.
 * This is used in conjuction with the --dirpattern option
 * @author Jeroen Roos
 * @package zophException
 */
class cliNotInCWDException extends cliException {}

/**
 * Exception for CLI: Illegal dirpattern
 * This is used in conjuction with the --dirpattern option
 * @author Jeroen Roos
 * @package zophException
 */
class cliIllegalDirpatternException extends cliException {}

/**
 * Exception for CLI: No parent
 * This is used in conjuction with the --new option
 * @author Jeroen Roos
 * @package zophException
 */
class cliNoParentException extends cliException {}

/**
 * Exception for CLI: zoph.ini can not be found
 * @author Jeroen Roos
 * @package zophException
 */
class cliININotFoundException extends cliException {}

/**
 * Exception for CLI: instance can not be found
 * @author Jeroen Roos
 * @package zophException
 */
class cliInstanceNotFoundException extends cliException {}

/**
 * Exception for CLI: CLI user is not admin
 * @author Jeroen Roos
 * @package zophException
 */
class cliUserNotAdminException extends cliException {}

/**
 * Exception for CLI: CLI user not valid
 * @author Jeroen Roos
 * @package zophException
 */
class cliUserNotValidException extends cliException {}

/**
 * Exception for CLI: API not compatible.
 * API version between /bin/zoph and web-dir differs
 * @author Jeroen Roos
 * @package zophException
 */
class cliAPINotCompatibleException extends cliException {}

/**
 * Exception for CLI: Unknown Error
 *
 * @author Jeroen Roos
 * @package zophException
 */
class cliUnknownErrorException extends cliException {}


/**
 * Database Exception
 *
 * @author Jeroen Roos
 * @package zophException
 */
class databaseException extends zophException {}

/**
 * Security Exception
 *
 * @author Jeroen Roos
 * @package zophException
 */
class securityException extends zophException {}
class keyMustBeNumericSecurityException extends securityException {}
class illegalValueSecurityException extends securityException {}


/**
 * User Exception
 *
 * @author Jeroen Roos
 * @package zophException
 */
class userException extends zophException {}

/**
 * User Not Found Exception
 *
 * @author Jeroen Roos
 * @package zophException
 */
class userNotFoundException extends userException {}

/**
 * User Multiple Found Exception
 * This means there are multiple users with the same username in the database
 * this should not happen.
 *
 * @author Jeroen Roos
 * @package zophException
 */
class userMultipleFoundException extends userException {}



?>
