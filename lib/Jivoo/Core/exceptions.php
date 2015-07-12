<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Jivoo base exception.
 */
class Exception extends \Exception { }

/**
 * A configuration file format is not supported, see {@see Config}.
 */
class UnsupportedConfigurationFormatException extends Exception { }

/**
 * Thrown when language is invalid, see {@see I18n}
 */
class I18nException extends Exception { }

/**
 * A library exception, see {@see Lib}.
 */
class LibException extends Exception { }

/**
 * Thrown when a class could not be found
 */
class ClassNotFoundException extends LibException { }

/**
 * Thrown when a class is invalid
 */
class ClassInvalidException extends LibException { }

/**
 * A map exception, see {@see Map}.
 */
class MapException extends Exception { }

/**
 * Thrown if a key is not defined in map.
 */
class MapKeyInvalidException extends MapException { }

/**
 * Thrown when editting a read-only map.
 */
class MapReadOnlyException extends MapException { }

/**
 * JSON encoding or decoding error.
 */
class JsonException extends Exception { }

/**
 * JSON encoding error.
 */
class JsonEncodeException extends JsonException { }

/**
 * JSON decoding error.
 */
class JsonDecodeException extends JsonException { }