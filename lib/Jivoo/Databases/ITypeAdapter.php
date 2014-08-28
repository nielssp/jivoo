<?php
/**
 * Convert types from database to schema and vice versa
 * @package Jivoo\Database
 */
interface ITypeAdapter {
  /**
   * Encode value for database
   * @param DataType $type Data type to convert from
   * @param mixed $value Value to convert
   */
  public function encode(DataType $type, $value);

  /**
   * Decode value from database
   * @param DataType $type Data type to convert to
   * @param mixed $value Value from database
   */
  public function decode(DataType $type, $value);
}
