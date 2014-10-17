<?php
/**
 * JSON encoding and decoding
 * @TODO Fallback when json php extension is missing
 */
class Json {
  public static function encode($object) {
    return json_encode($object);
  }
  public static function decode($json) {
    return json_decode($json, true);
  }
  public static function decodeFile($file) {
    return self::decode(file_get_contents($file));
  }
}