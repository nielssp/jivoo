<?php
interface ITranslationService {
  public function translate($text);
  public function translateList($single, $plural, $glue, $gluel, $pieces);
  public function translateNumeral($single, $plural, $number);

  public function fdate($timestamp = null);
  public function ftime($timestamp = null);
  public function date($format, $timestamp = null);
}
