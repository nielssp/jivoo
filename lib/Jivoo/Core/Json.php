<?php
/**
 * JSON encoding and decoding
 * @TODO Fallback when json php extension is missing
 */
class Json {
  private static $hasPrettyPrinting = null;
  
  public static function encode($object) {
    return json_encode($object);
  }
  public static function prettyPrint($object) {
    if (!isset(self::$hasPrettyPrinting))
      self::$hasPrettyPrinting = version_compare(PHP_VERSION, '5.4', '>=');
    if (self::$hasPrettyPrinting)
      return json_encode($object, JSON_PRETTY_PRINT);
    else
      throw new Exception('implement');
  }
  public static function decode($json) {
    return json_decode($json, true);
  }
  public static function decodeFile($file) {
    return self::decode(file_get_contents($file));
  }
}