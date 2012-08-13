<?php

abstract class TranslationService {
  private static $translationService;

  public static function setService(ITranslationService $translationService) {
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
}
