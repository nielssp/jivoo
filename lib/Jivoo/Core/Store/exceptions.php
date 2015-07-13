<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

use Jivoo\Core\Exception;

/**
 * Store base exception.
 */
class StoreException extends Exception { }

/**
 * Can be thrown if a store is locked.
 */
class StoreLockException extends StoreException { }

/**
 * Failure to read from a store.
 */
class StoreReadFailedException extends StoreException { }

/**
 * Failure to write to a store.
 */
class StoreWriteFailedException extends StoreException { }

/**
 * State base exception.
 */
class StateException extends Exception { }

/**
 * A state has already been closed.
 */
class StateClosedException extends StateException { }

/**
 * A state is invalid or corrupted.
 */
class StateInvalidException extends StateException { }