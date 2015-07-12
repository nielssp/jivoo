<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Core\Exception;

/**
 * A database exception.
 */
class DatabaseException extends Exception { }

/**
 * A database selection has failed.
 */
class DatabaseSelectFailedException extends DatabaseException {}

/**
 * A database query has failed.
 */
class DatabaseQueryFailedException extends DatabaseException {}

/**
 * A table could not be found.
 */
class TableNotFoundException extends DatabaseException { }

/**
 * A database connection has failed.
 */
class DatabaseConnectionFailedException extends DatabaseException {}

/**
 * Invalid database configuration.
 */
class DatabaseNotConfiguredException extends DatabaseException { }

/**
 * Unknown table schema.
 */
class DatabaseMissingSchemaException extends DatabaseException { }