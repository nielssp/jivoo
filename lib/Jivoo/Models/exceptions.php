<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

use Jivoo\Core\Exception;

/**
 * Thrown when data type is unknown.
 */
class InvalidDataTypeException extends Exception { }

/**
 * For invalid enums.
 */
class InvalidEnumException extends Exception { }

/**
 * A model exception.
 */
class ModelException extends Exception { }

/**
 * Thrown if primary key is invalid
 */
class InvalidPrimaryKeyException extends ModelException { }

/**
 * A model could not be found.
 */
class ModelNotFoundException extends ModelException { }