<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\Exception;

/**
 * An extension exception.
 */
class ExtensionException extends Exception { }

/**
 * Extension not found.
 */
class ExtensionNotFoundException extends ExtensionException {}

/**
 * Extension is invalid.
 */
class ExtensionInvalidException extends ExtensionException {}