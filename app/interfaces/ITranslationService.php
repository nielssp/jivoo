<?php
interface ITranslationService {
  public function translate($text);
  public function translateList($single, $plural, $glue, $gluel, $pieces);
  public function translateNumeral($single, $plural, $number);

  public function fdate($timestamp = NULL);
  public function ftime($timestamp = NULL);
  public function date($format, $timestamp = NULL);
}
