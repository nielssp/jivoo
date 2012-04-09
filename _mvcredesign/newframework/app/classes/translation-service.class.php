<?php

abstract class TranslationService {
  private static $translationService;

  public static function setService(TranslationService $translationService) {
    self::$translationService = $translationService;
  }

  public static function getService() {
    if (isset(self::$translationService)) {
      return self::$translationService;
    }
    else {
      return FALSE;
    }
  }

  public abstract function translate($text);
  public abstract function translateList($single, $plural, $glue, $gluel, $pieces);
  public abstract function translateNumeral($single, $plural, $number);

  public abstract function fdate($timestamp = NULL);
  public abstract function ftime($timestamp = NULL);
  public abstract function date($format, $timestamp = NULL);
}