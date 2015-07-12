<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Core\Exception;

/**
 * A record class is invalid.
 */
class InvalidRecordClassException extends Exception { }

/**
 * A data source was not found
 */
class DataSourceNotFoundException extends Exception { }

/**
 * An association is invalid.
 */
class InvalidAssociationException extends Exception { }

/**
 * A mixin is invalid.
 */
class InvalidMixinException extends Exception { }

/**
 * A data model is invalid.
 */
class InvalidModelException extends Exception { }